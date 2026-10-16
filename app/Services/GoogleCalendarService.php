<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Mirrors confirmed bookings into the salon's Google Calendar through a
 * service account (no Google SDK: a signed JWT and the REST API).
 */
class GoogleCalendarService
{
    private const SCOPE = 'https://www.googleapis.com/auth/calendar.events';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const API_URL = 'https://www.googleapis.com/calendar/v3';

    public function __construct(private BookingCalendarEvents $events) {}

    public function isEnabled(): bool
    {
        return config('services.google_calendar.enabled') && filled(config('services.google_calendar.calendar_id'));
    }

    /**
     * Create or update the booking's event. Uses the booking's own event id,
     * so a retried call never creates a duplicate.
     *
     * @return string The Google event id.
     */
    public function upsert(Booking $booking): string
    {
        $event = $this->events->forSalon($booking->loadMissing(['service', 'stylist']));
        $payload = [
            'id' => $event['uid'],
            'status' => 'confirmed',
            'summary' => $event['summary'],
            'description' => $event['description'],
            'location' => $event['location'],
            'start' => ['dateTime' => $event['starts_at']->toIso8601String(), 'timeZone' => config('app.timezone')],
            'end' => ['dateTime' => $event['ends_at']->toIso8601String(), 'timeZone' => config('app.timezone')],
        ];

        $response = $this->client()->put($this->eventUrl($event['uid']), $payload);

        if ($response->notFound()) {
            $response = $this->client()->post($this->eventsUrl(), $payload);
        }

        return $response->throw()->json('id');
    }

    /**
     * Remove the booking's event. An event that is already gone counts as removed.
     */
    public function delete(Booking $booking): void
    {
        $response = $this->client()->delete($this->eventUrl($this->events->uid($booking)));

        if ($response->notFound() || $response->status() === 410) {
            return;
        }

        $response->throw();
    }

    private function client(): PendingRequest
    {
        return Http::connectTimeout(5)
            ->timeout(15)
            ->acceptJson()
            ->withToken($this->accessToken());
    }

    private function eventsUrl(): string
    {
        return self::API_URL.'/calendars/'.rawurlencode(config('services.google_calendar.calendar_id')).'/events';
    }

    private function eventUrl(string $eventId): string
    {
        return $this->eventsUrl().'/'.rawurlencode($eventId);
    }

    /**
     * OAuth access token for the service account, cached until shortly before it expires.
     */
    private function accessToken(): string
    {
        $cached = Cache::get('google-calendar.token');

        if (is_string($cached)) {
            return $cached;
        }

        $credentials = $this->credentials();
        $now = now()->getTimestamp();

        $response = Http::connectTimeout(5)
            ->timeout(15)
            ->asForm()
            ->post(self::TOKEN_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $this->signedJwt([
                    'iss' => $credentials['client_email'],
                    'scope' => self::SCOPE,
                    'aud' => self::TOKEN_URL,
                    'iat' => $now,
                    'exp' => $now + 3600,
                ], $credentials['private_key']),
            ])
            ->throw();

        $token = (string) $response->json('access_token');
        Cache::put('google-calendar.token', $token, max(60, (int) $response->json('expires_in', 3600) - 120));

        return $token;
    }

    /**
     * @return array{client_email: string, private_key: string}
     */
    private function credentials(): array
    {
        $path = (string) config('services.google_calendar.credentials');
        $credentials = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        if (! is_array($credentials) || blank($credentials['client_email'] ?? null) || blank($credentials['private_key'] ?? null)) {
            throw new RuntimeException("Google Calendar service account key not found or invalid at [{$path}].");
        }

        return $credentials;
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private function signedJwt(array $claims, string $privateKey): string
    {
        $encode = fn (string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        $unsigned = $encode((string) json_encode(['alg' => 'RS256', 'typ' => 'JWT'])).'.'.$encode((string) json_encode($claims));

        if (! openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Could not sign the Google Calendar token request.');
        }

        return $unsigned.'.'.$encode($signature);
    }
}

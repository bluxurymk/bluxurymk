<?php

use App\Jobs\SyncBookingToGoogleCalendar;
use App\Models\Booking;
use App\Services\GoogleCalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

const EVENTS_URL = 'https://www.googleapis.com/calendar/v3/calendars/salon%40group.calendar.google.com/events';

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-19 08:00'));
    Mail::fake();
});

/**
 * Enable the integration with a throwaway service account key.
 */
function enableGoogleCalendar(): void
{
    // Windows PHP builds ship without an openssl.cnf, so pass a minimal one.
    $opensslConfig = tempnam(sys_get_temp_dir(), 'ssl');
    file_put_contents($opensslConfig, "[req]\ndistinguished_name = dn\n[dn]\n");
    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'config' => $opensslConfig]);
    openssl_pkey_export($key, $privateKey, null, ['config' => $opensslConfig]);

    $credentialsPath = tempnam(sys_get_temp_dir(), 'gcal');
    file_put_contents($credentialsPath, json_encode([
        'client_email' => 'bookings@bluxury.iam.gserviceaccount.com',
        'private_key' => $privateKey,
    ]));

    config([
        'services.google_calendar.enabled' => true,
        'services.google_calendar.calendar_id' => 'salon@group.calendar.google.com',
        'services.google_calendar.credentials' => $credentialsPath,
    ]);

    Http::preventStrayRequests();
}

function fakeGoogleToken(): array
{
    return ['https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'token-123', 'expires_in' => 3600])];
}

test('nothing is queued while the integration is disabled', function () {
    Queue::fake();
    $booking = Booking::factory()->at('2026-10-22 10:00')->create();

    $booking->confirmDeposit();

    Queue::assertNotPushed(SyncBookingToGoogleCalendar::class);
});

test('confirming or cancelling a booking queues a calendar sync', function (Closure $change) {
    enableGoogleCalendar();
    Queue::fake();
    $booking = Booking::factory()->at('2026-10-22 10:00')->create();

    $change($booking);

    Queue::assertPushed(SyncBookingToGoogleCalendar::class, fn (SyncBookingToGoogleCalendar $job): bool => $job->booking->is($booking));
})->with([
    'confirm' => [fn (Booking $booking) => $booking->confirmDeposit()],
    'cancel' => [fn (Booking $booking) => $booking->cancel(Booking::CANCELLED_BY_SALON)],
]);

test('a confirmed booking without an event is created in Google Calendar', function () {
    enableGoogleCalendar();
    $booking = Booking::factory()->at('2026-10-22 10:00')->confirmed()->create();
    $eventId = sprintf('bl%010d', $booking->id);

    Http::fake([
        ...fakeGoogleToken(),
        EVENTS_URL.'/'.$eventId => Http::response(['error' => ['code' => 404]], 404),
        EVENTS_URL => Http::response(['id' => $eventId]),
    ]);

    (new SyncBookingToGoogleCalendar($booking))->handle(app(GoogleCalendarService::class));

    expect($booking->fresh()->google_event_id)->toBe($eventId);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://oauth2.googleapis.com/token'
        && $request['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer'
        && substr_count($request['assertion'], '.') === 2);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === EVENTS_URL
        && $request->hasHeader('Authorization', 'Bearer token-123')
        && $request['id'] === $eventId
        && $request['start']['dateTime'] === '2026-10-22T10:00:00+02:00'
        && $request['start']['timeZone'] === 'Europe/Skopje'
        && str_contains($request['summary'], $booking->customer_name));
});

test('an existing event is updated in place, never duplicated', function () {
    enableGoogleCalendar();
    $booking = Booking::factory()->at('2026-10-22 10:00')->confirmed()->create(['google_event_id' => sprintf('bl%010d', 1)]);
    $eventId = sprintf('bl%010d', $booking->id);

    Http::fake([
        ...fakeGoogleToken(),
        EVENTS_URL.'/'.$eventId => Http::response(['id' => $eventId]),
    ]);

    (new SyncBookingToGoogleCalendar($booking))->handle(app(GoogleCalendarService::class));

    Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT');
    Http::assertNotSent(fn (Request $request): bool => $request->method() === 'POST' && $request->url() === EVENTS_URL);
    expect($booking->fresh()->google_event_id)->toBe($eventId);
});

test('a cancelled booking loses its event, even if it was already gone', function (int $googleStatus) {
    enableGoogleCalendar();
    $booking = Booking::factory()->at('2026-10-22 10:00')->cancelled()->create(['google_event_id' => 'existing']);

    Http::fake([
        ...fakeGoogleToken(),
        EVENTS_URL.'/*' => Http::response(null, $googleStatus),
    ]);

    (new SyncBookingToGoogleCalendar($booking))->handle(app(GoogleCalendarService::class));

    Http::assertSent(fn (Request $request): bool => $request->method() === 'DELETE');
    expect($booking->fresh()->google_event_id)->toBeNull();
})->with([
    'deleted now' => [204],
    'already deleted' => [410],
]);

test('a Google outage fails the job so the queue retries it', function () {
    enableGoogleCalendar();
    $booking = Booking::factory()->at('2026-10-22 10:00')->confirmed()->create();

    Http::fake([
        ...fakeGoogleToken(),
        EVENTS_URL.'/*' => Http::response(null, 503),
    ]);

    expect(fn () => (new SyncBookingToGoogleCalendar($booking))->handle(app(GoogleCalendarService::class)))
        ->toThrow(RequestException::class);

    expect($booking->fresh()->google_event_id)->toBeNull();
});

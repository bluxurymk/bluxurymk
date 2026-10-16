<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Admin-editable salon settings. Each one overrides the value with the same
 * dot path in `config/salon.php`, which keeps acting as the default.
 */
class SalonSettings
{
    private const CACHE_KEY = 'salon.settings';

    /**
     * Editable settings and their types.
     *
     * @var array<string, 'string'|'int'>
     */
    public const KEYS = [
        'phone' => 'string',
        'email' => 'string',
        'address' => 'string',
        'city' => 'string',
        'map_query' => 'string',
        'socials.instagram' => 'string',
        'socials.facebook' => 'string',
        'booking.deposit_amount' => 'int',
        'booking.deposit_hours' => 'int',
        'booking.reminder_hours' => 'int',
        'booking.buffer_minutes' => 'int',
        'booking.slot_interval_minutes' => 'int',
        'booking.min_notice_minutes' => 'int',
        'booking.max_days_ahead' => 'int',
    ];

    /**
     * Saved overrides keyed by dot path.
     *
     * @return array<string, string|int|null>
     */
    public function overrides(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => Setting::query()
            ->whereIn('key', array_keys(self::KEYS))
            ->pluck('value', 'key')
            ->all());
    }

    /**
     * Push the saved overrides into the `salon` config for this request.
     */
    public function applyToConfig(): void
    {
        foreach ($this->overrides() as $key => $value) {
            if (filled($value) || str_starts_with($key, 'socials.')) {
                config(['salon.'.$key => $value]);
            }
        }
    }

    /**
     * Current values as a nested array, for the admin form.
     *
     * @return array<string, mixed>
     */
    public function current(): array
    {
        $overrides = $this->overrides();

        return Arr::undot(collect(self::KEYS)
            ->mapWithKeys(fn (string $type, string $key): array => [
                $key => array_key_exists($key, $overrides) ? $overrides[$key] : config('salon.'.$key),
            ])
            ->all());
    }

    /**
     * @param  array<string, mixed>  $values  Nested values as submitted by the admin form.
     */
    public function save(array $values): void
    {
        $flat = Arr::dot($values);

        foreach (self::KEYS as $key => $type) {
            if (! array_key_exists($key, $flat)) {
                continue;
            }

            $value = $flat[$key];

            Setting::updateOrCreate(['key' => $key], [
                'value' => match (true) {
                    blank($value) => null,
                    $type === 'int' => (int) $value,
                    default => trim((string) $value),
                },
            ]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}

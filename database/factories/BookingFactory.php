<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Stylist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = now()->addDays(3)->setTime(10, 0);

        return [
            'service_id' => Service::factory(),
            'stylist_id' => Stylist::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => '070 123 456',
            'customer_email' => fake()->safeEmail(),
            'note' => null,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHour(),
            'status' => BookingStatus::PendingDeposit,
            'deposit_amount' => 500,
            'deposit_due_at' => now()->addHours(48),
            'manage_token' => Str::random(48),
            'consent_at' => now(),
        ];
    }

    public function at(string $startsAt, int $durationMinutes = 60): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => Carbon::parse($startsAt),
            'ends_at' => Carbon::parse($startsAt)->addMinutes($durationMinutes),
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Confirmed,
            'deposit_paid_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}

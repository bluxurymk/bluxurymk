<?php

namespace Database\Factories;

use App\Models\WorkingHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkingHour>
 */
class WorkingHourFactory extends Factory
{
    /**
     * Define the model's default state: salon-wide, Monday 09:00–17:00.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stylist_id' => null,
            'day_of_week' => 1,
            'opens_at' => '09:00:00',
            'closes_at' => '17:00:00',
        ];
    }

    public function on(int $dayOfWeek, string $opensAt = '09:00', string $closesAt = '17:00'): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $dayOfWeek,
            'opens_at' => $opensAt.':00',
            'closes_at' => $closesAt.':00',
        ]);
    }
}

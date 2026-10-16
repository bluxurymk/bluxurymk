<?php

namespace Database\Factories;

use App\Models\BlockedSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockedSlot>
 */
class BlockedSlotFactory extends Factory
{
    /**
     * Define the model's default state: a salon-wide block for the whole next day.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $day = now()->addDay()->startOfDay();

        return [
            'stylist_id' => null,
            'starts_at' => $day,
            'ends_at' => $day->copy()->endOfDay(),
            'reason' => fake()->sentence(3),
        ];
    }

    public function between(string $startsAt, string $endsAt): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }
}

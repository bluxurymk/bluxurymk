<?php

namespace Database\Seeders;

use App\Models\WorkingHour;
use Illuminate\Database\Seeder;

class WorkingHourSeeder extends Seeder
{
    /**
     * Seed placeholder salon-wide hours (Mon–Fri 09–20, Sat 09–16, Sunday
     * closed) until the salon confirms its real schedule.
     */
    public function run(): void
    {
        $schedule = [
            1 => ['09:00:00', '20:00:00'],
            2 => ['09:00:00', '20:00:00'],
            3 => ['09:00:00', '20:00:00'],
            4 => ['09:00:00', '20:00:00'],
            5 => ['09:00:00', '20:00:00'],
            6 => ['09:00:00', '16:00:00'],
        ];

        foreach ($schedule as $dayOfWeek => [$opensAt, $closesAt]) {
            WorkingHour::updateOrCreate(
                ['stylist_id' => null, 'day_of_week' => $dayOfWeek],
                ['opens_at' => $opensAt, 'closes_at' => $closesAt],
            );
        }
    }
}

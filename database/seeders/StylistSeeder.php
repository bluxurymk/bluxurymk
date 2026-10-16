<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Stylist;
use Illuminate\Database\Seeder;

class StylistSeeder extends Seeder
{
    /**
     * Seed placeholder stylists who offer every service. Replace with the
     * salon's real team once they confirm names and photos.
     */
    public function run(): void
    {
        $serviceIds = Service::pluck('id');

        foreach (['Стилист 1', 'Стилист 2'] as $name) {
            Stylist::firstOrCreate(['name' => $name])
                ->services()
                ->sync($serviceIds);
        }
    }
}

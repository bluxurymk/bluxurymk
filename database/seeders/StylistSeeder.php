<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Stylist;
use Illuminate\Database\Seeder;

class StylistSeeder extends Seeder
{
    /**
     * Seed the salon's stylist, who offers every service.
     */
    public function run(): void
    {
        $serviceIds = Service::pluck('id');

        Stylist::firstOrCreate(['name' => 'Бобан'])
            ->services()
            ->sync($serviceIds);
    }
}

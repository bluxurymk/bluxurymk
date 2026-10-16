<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Seed the salon's services. Durations are placeholders until the salon
     * confirms the final list; prices are left empty for the same reason.
     */
    public function run(): void
    {
        foreach ($this->services() as $sortOrder => $service) {
            Service::updateOrCreate(
                ['slug' => $service['slug']],
                [...$service, 'sort_order' => $sortOrder],
            );
        }
    }

    /**
     * @return list<array{slug: string, name: string, subtitle: string, icon: string, duration_min: int, description: string}>
     */
    private function services(): array
    {
        return [
            [
                'slug' => 'zenski-frizuri',
                'name' => 'Женски фризури',
                'subtitle' => 'Класични и модерни трендови',
                'icon' => 'scissors',
                'duration_min' => 60,
                'description' => 'Шишање, фенирање и свечени фризури прилагодени на обликот на лицето и вашиот стил. Од класична елеганција до најновите трендови — секоја фризура е изработена со внимание кон детали.',
            ],
            [
                'slug' => 'keratin',
                'name' => 'Кератин',
                'subtitle' => 'За здрава и сјајна коса',
                'icon' => 'droplet',
                'duration_min' => 150,
                'description' => 'Кератинскиот третман ја исправа и зацврстува косата, ја намалува накостреленоста и ѝ враќа природен сјај. Резултатот е мазна и лесна за одржување коса со недели.',
            ],
            [
                'slug' => 'bojaz',
                'name' => 'Бојаж',
                'subtitle' => 'Природен и совршен изглед',
                'icon' => 'bowl',
                'duration_min' => 120,
                'description' => 'Професионално боење со квалитетни бои кои ја штитат структурата на косата. Ви помагаме да ја пронајдете нијансата која најдобро ви одговара.',
            ],
            [
                'slug' => 'balajaz',
                'name' => 'Балајаж',
                'subtitle' => 'Светлина и димензија',
                'icon' => 'waves',
                'duration_min' => 180,
                'description' => 'Рачно нанесени преливи кои создаваат мек, сончев ефект и длабочина. Балајажот расте природно и бара минимално одржување.',
            ],
            [
                'slug' => 'airtouch',
                'name' => 'Airtouch',
                'subtitle' => 'Нежен преод, природен резултат',
                'icon' => 'sparkle',
                'duration_min' => 210,
                'description' => 'Напредна техника на осветлување со помош на воздух која дава исклучително мек и природен преод на бојата, без остри линии.',
            ],
            [
                'slug' => 'laminacija',
                'name' => 'Ламинација',
                'subtitle' => 'Силна, гладка и светкава коса',
                'icon' => 'lotus',
                'duration_min' => 90,
                'description' => 'Ламинацијата го обвиткува секое влакно со заштитен слој, ја прави косата погуста, помазна и видно посјајна.',
            ],
            [
                'slug' => 'terapii',
                'name' => 'Терапии',
                'subtitle' => 'Нега и обнова на косата',
                'icon' => 'leaf',
                'duration_min' => 45,
                'description' => 'Длабински третмани за хидратација, обнова и јакнење на оштетена или исцрпена коса и скалп, прилагодени на потребите на вашата коса.',
            ],
        ];
    }
}

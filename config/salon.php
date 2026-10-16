<?php

/*
|--------------------------------------------------------------------------
| Salon Information
|--------------------------------------------------------------------------
|
| Static salon data used by the public pages during Phase 1. Contact info
| and services will move to the database (settings / services tables)
| and become editable in the admin panel in later phases.
|
*/

return [

    'name' => 'B Luxury Hair Salon',

    'address' => 'ул. Димитар Влахов',

    'city' => 'Куманово',

    'phone' => '+389 78 906 112',

    'email' => 'boban_hair@yahoo.com',

    'map_query' => 'Dimitar Vlahov, Kumanovo, North Macedonia',

    'socials' => [
        'instagram' => null,
        'facebook' => null,
    ],

    /*
    | Placeholder services. Durations are placeholders until the salon
    | confirms the final list. Keys are the public URL slugs.
    */
    'services' => [
        'zenski-frizuri' => [
            'name' => 'Женски фризури',
            'subtitle' => 'Класични и модерни трендови',
            'icon' => 'scissors',
            'duration_min' => 60,
            'description' => 'Шишање, фенирање и свечени фризури прилагодени на обликот на лицето и вашиот стил. Од класична елеганција до најновите трендови — секоја фризура е изработена со внимание кон детали.',
        ],
        'keratin' => [
            'name' => 'Кератин',
            'subtitle' => 'За здрава и сјајна коса',
            'icon' => 'droplet',
            'duration_min' => 150,
            'description' => 'Кератинскиот третман ја исправа и зацврстува косата, ја намалува накостреленоста и ѝ враќа природен сјај. Резултатот е мазна и лесна за одржување коса со недели.',
        ],
        'bojaz' => [
            'name' => 'Бојаж',
            'subtitle' => 'Природен и совршен изглед',
            'icon' => 'bowl',
            'duration_min' => 120,
            'description' => 'Професионално боење со квалитетни бои кои ја штитат структурата на косата. Ви помагаме да ја пронајдете нијансата која најдобро ви одговара.',
        ],
        'balajaz' => [
            'name' => 'Балајаж',
            'subtitle' => 'Светлина и димензија',
            'icon' => 'waves',
            'duration_min' => 180,
            'description' => 'Рачно нанесени преливи кои создаваат мек, сончев ефект и длабочина. Балајажот расте природно и бара минимално одржување.',
        ],
        'airtouch' => [
            'name' => 'Airtouch',
            'subtitle' => 'Нежен преод, природен резултат',
            'icon' => 'sparkle',
            'duration_min' => 210,
            'description' => 'Напредна техника на осветлување со помош на воздух која дава исклучително мек и природен преод на бојата, без остри линии.',
        ],
        'laminacija' => [
            'name' => 'Ламинација',
            'subtitle' => 'Силна, гладка и светкава коса',
            'icon' => 'lotus',
            'duration_min' => 90,
            'description' => 'Ламинацијата го обвиткува секое влакно со заштитен слој, ја прави косата погуста, помазна и видно посјајна.',
        ],
        'terapii' => [
            'name' => 'Терапии',
            'subtitle' => 'Нега и обнова на косата',
            'icon' => 'leaf',
            'duration_min' => 45,
            'description' => 'Длабински третмани за хидратација, обнова и јакнење на оштетена или исцрпена коса и скалп, прилагодени на потребите на вашата коса.',
        ],
    ],

];

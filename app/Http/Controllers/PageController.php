<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', ['services' => $this->serviceList()]);
    }

    public function services(): View
    {
        return view('pages.services', ['services' => $this->serviceList()]);
    }

    public function serviceShow(string $slug): View
    {
        $services = $this->serviceList();

        abort_unless(isset($services[$slug]), 404);

        return view('pages.service-show', [
            'service' => $services[$slug],
            'otherServices' => collect($services)->except($slug)->take(3)->all(),
        ]);
    }

    public function gallery(): View
    {
        return view('pages.gallery');
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    /**
     * Temporary placeholder until online booking is built in Phase 2.
     */
    public function booking(): View
    {
        return view('pages.booking-soon');
    }

    /**
     * @return array<string, array{slug: string, name: string, subtitle: string, icon: string, duration_min: int, description: string}>
     */
    private function serviceList(): array
    {
        return collect(config('salon.services'))
            ->map(fn (array $service, string $slug): array => ['slug' => $slug, ...$service])
            ->all();
    }
}

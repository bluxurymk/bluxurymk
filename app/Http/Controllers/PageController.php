<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Models\Service;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', ['services' => Service::active()->ordered()->get()]);
    }

    public function services(): View
    {
        return view('pages.services', ['services' => Service::active()->ordered()->get()]);
    }

    public function serviceShow(Service $service): View
    {
        abort_unless($service->is_active, 404);

        return view('pages.service-show', [
            'service' => $service,
            'otherServices' => Service::active()->ordered()->whereKeyNot($service->id)->limit(3)->get(),
        ]);
    }

    public function gallery(): View
    {
        return view('pages.gallery', ['images' => GalleryImage::visible()->ordered()->get()]);
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
}

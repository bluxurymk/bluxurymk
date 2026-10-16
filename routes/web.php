<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/uslugi', 'services')->name('services.index');
    Route::get('/uslugi/{slug}', 'serviceShow')->name('services.show');
    Route::get('/galerija', 'gallery')->name('gallery');
    Route::get('/za-nas', 'about')->name('about');
    Route::get('/kontakt', 'contact')->name('contact');
    Route::get('/politika-na-privatnost', 'privacy')->name('privacy');
    Route::get('/rezerviraj', 'booking')->name('booking.create');
});

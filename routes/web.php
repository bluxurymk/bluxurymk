<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/uslugi', 'services')->name('services.index');
    Route::get('/uslugi/{service:slug}', 'serviceShow')->name('services.show');
    Route::get('/galerija', 'gallery')->name('gallery');
    Route::get('/za-nas', 'about')->name('about');
    Route::get('/kontakt', 'contact')->name('contact');
    Route::get('/politika-na-privatnost', 'privacy')->name('privacy');
});

Route::controller(BookingController::class)->group(function () {
    Route::get('/rezerviraj', 'create')->name('booking.create');
    Route::post('/rezerviraj', 'store')->middleware('throttle:10,1')->name('booking.store');
    Route::get('/rezerviraj/uspeshno', 'success')->name('booking.success');
    Route::get('/rezervacija/{booking:manage_token}', 'show')->name('booking.show');
    Route::delete('/rezervacija/{booking:manage_token}', 'destroy')->middleware('throttle:10,1')->name('booking.destroy');
});

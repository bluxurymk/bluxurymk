<?php

namespace App\Providers;

use App\Services\SalonSettings;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');

        // Queued emails render outside a web request, so apply the admin's salon settings first.
        Queue::before(fn () => $this->app->make(SalonSettings::class)->applyToConfig());
    }
}

<?php

namespace App\Providers;

use App\Models\SensorReading;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.dashboard', function ($view) {
            $latest = SensorReading::latest()->first();
            $view->with('latestStatus', $latest->status ?? 'aman');
        });
    }
}

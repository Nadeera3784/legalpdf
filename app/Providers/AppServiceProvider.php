<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PdfGenerationService;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PdfGenerationService::class, function ($app) {
            return new PdfGenerationService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Configure Horizon
        Horizon::auth(function ($request) {
            // Allow all users to access Horizon in local environment
            return app()->environment('local');
        });
    }
}

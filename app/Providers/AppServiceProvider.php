<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AICertificateGeneratorService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AICertificateGeneratorService::class, function ($app) {
            return new AICertificateGeneratorService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

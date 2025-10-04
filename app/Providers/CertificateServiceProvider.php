<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AICertificateGeneratorService;

class CertificateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AICertificateGeneratorService::class, function ($app) {
            return new AICertificateGeneratorService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
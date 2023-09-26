<?php

namespace App\Providers;

use App\Services\EnjoyUrlService;
use Illuminate\Support\ServiceProvider;

class EnjoyUrlServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(EnjoyUrlService::class, function () {
            return new EnjoyUrlService();
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

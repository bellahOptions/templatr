<?php

namespace App\Providers;

use App\Services\Download\DownloadSecurityManager;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, function ($app) {
            return new PaymentManager();
        });

        $this->app->singleton(DownloadSecurityManager::class, function ($app) {
            return new DownloadSecurityManager($app->make(PaymentManager::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}

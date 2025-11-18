<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register service bindings
        if ($this->app->environment('local')) {
            // Local environment specific bindings
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL
        Schema::defaultStringLength(191);

        // Prevent lazy loading in non-production environments
        Model::preventLazyLoading(!app()->isProduction());

        // Prevent silently discarding attributes
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());

        // Prevent accessing missing attributes
        Model::preventAccessingMissingAttributes(!app()->isProduction());
    }
}

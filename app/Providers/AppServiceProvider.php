<?php

namespace App\Providers;

use App\Models\Menu;
use App\Observers\MenuObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        Menu::observe(MenuObserver::class);
        if (app()->environment(['production', 'staging'])) {
            URL::forceScheme('https');
        }
    }
}

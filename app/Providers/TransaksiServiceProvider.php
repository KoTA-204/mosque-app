<?php

namespace App\Providers;

use App\Services\MutasiBankParserService;
use App\Services\TransaksiService;
use Illuminate\Support\ServiceProvider;

class TransaksiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MutasiBankParserService::class);
        $this->app->singleton(TransaksiService::class);
    }

    public function boot(): void
    {
        //
    }
}
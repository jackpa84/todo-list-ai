<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GeminiService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(GeminiService::class, function ($app) {
            return new GeminiService();
        });
    }

    public function boot()
    {
        //
    }
}

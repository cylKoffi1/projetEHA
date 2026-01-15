<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // charge tes helpers si besoin
        foreach (glob(app_path('Helpers') . '/*.php') as $file) {
            require_once $file;
        }
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}

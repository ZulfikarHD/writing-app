<?php

namespace App\Providers;

use App\Models\Scene;
use App\Observers\SceneObserver;
use Illuminate\Support\ServiceProvider;

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
        Scene::observe(SceneObserver::class);
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Library\Services\Dict;
use Illuminate\Support\Facades\Validator;

class DictServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('App\Library\Services\Dict', function ($app) {
            return new Dict();
        });
    }
}

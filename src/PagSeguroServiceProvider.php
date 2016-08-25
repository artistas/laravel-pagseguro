<?php

namespace PHPampa\PagSeguro;

use Illuminate\Support\ServiceProvider;

class PagSeguroServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('pagseguro', function ($app) {
            return new Pagseguro();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/pagseguro.php' => config_path('pagseguro.php'),
        ]);

        $this->loadViewsFrom(__DIR__.'/../views', 'pagseguro');
    }
}

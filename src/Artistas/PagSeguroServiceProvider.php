<?php

namespace Artistas\PagSeguro;

use Illuminate\Support\ServiceProvider;

class PagSeguroServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('pagseguro', function ($app) {
            return new PagSeguro($app['log'], $app['validator']);
        });

        $this->app->bind('pagseguro_recorrente', function ($app) {
            return new PagSeguroRecorrente($app['log'], $app['validator']);
        });
    }

    public function boot()
    {
        if (!$this->app->routesAreCached()) {
            require __DIR__.'/routes.php';
        }
    }
}

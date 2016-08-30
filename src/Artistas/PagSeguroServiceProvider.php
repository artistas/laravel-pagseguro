<?php

namespace Artistas\PagSeguro;

use Illuminate\Support\ServiceProvider;

class PagSeguroServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('pagseguro', function ($app) {
            return new PagSeguro($app['session'], $app['config'], $app['log'], $app['validator']);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/pagseguro.php' => config_path('pagseguro.php'),
        ]);

        $this->loadViewsFrom(__DIR__.'/views', 'pagseguro');
    }
}

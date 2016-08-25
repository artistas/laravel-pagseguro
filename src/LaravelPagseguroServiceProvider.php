<?php

namespace PHPampa\laravelPagseguro;

use Illuminate\Support\ServiceProvider;

class LaravelPagseguroServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('pagseguro', function ($app) {
            return new Pagseguro();
        });
    }

    public function boot()
    {
    	//loading the routes file
    	require __DIR__ . '/Http/routes.php';
    }
}

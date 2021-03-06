<?php

namespace SheldonKnight\Payfast;


use Illuminate\Support\ServiceProvider;


class PayfastServiceProvider extends ServiceProvider
{

    public function register()
    {
        // $this->app->bind('', '');
    }

    public function boot()
    {
         $this->publishes([
             __DIR__.'/config/payfast.php' => config_path('payfast.php'),
         ]);

        $this->mergeConfigFrom(
            __DIR__ . '/config/payfast.php', 'payfast'
        );

    }

}

<?php

namespace Pay2go;

use Illuminate\Support\ServiceProvider;

class Pay2goServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind(CreditCard::class, function ($app) {
            return new CreditCard();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            CreditCard::class
        ];
    }
}

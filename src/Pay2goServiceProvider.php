<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Pay2go\CreditCard;

class Pay2goServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/pay2go.php' => config_path('pay2go.php'),
        ]);
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

<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\MailObserver;
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
        //Llamar a observers
        User::observe(MailObserver::class);
    }
}

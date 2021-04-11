<?php

namespace App\Providers;

use App\User;
use Dusterio\LumenPassport\LumenPassport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Dingo\Api\Auth\Provider\Basic;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.


        Passport::tokensCan([
            'user' => "user's only  scope",
            'admin' => "The admin with lesser privilege",
            'super_admin' => "The super admin",

        ]);

        LumenPassport::routes($this->app);
    }
}

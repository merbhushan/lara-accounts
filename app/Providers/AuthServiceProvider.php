<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Model\Scope;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
        //

        // Access Tokens & refresh tokens lifetime
        // Passport::tokensExpireIn(now()->addDays(15));
        // Passport::refreshTokensExpireIn(now()->addDays(30));

        // Defining Passport Scopes
        Passport::tokensCan(Scope::select('name', 'display_name')
            ->where('is_active',1)
            ->get()
            ->pluck('display_name', 'name')
            ->toArray());

        // Passport implicit grant token method enabled.
        Passport::enableImplicitGrant();
    }
}

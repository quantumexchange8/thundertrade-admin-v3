<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('check-permission', function (User $user, $code) {
            return RolePermission::whereRelation('permission', 'code', $code)->where('role_id', $user->role_id)->exists();
        });
    }
}

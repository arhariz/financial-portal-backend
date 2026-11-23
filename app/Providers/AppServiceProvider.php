<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\UserProfile;
use App\Observers\RoleObserver;
use App\Observers\UserProfileObserver;
use App\Policies\ExchangeRatePolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserProfilePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(UserProfile::class, UserProfilePolicy::class);
        Gate::policy(\App\Models\ExchangeRate::class, ExchangeRatePolicy::class);
        Role::observe(RoleObserver::class);
        UserProfile::observe(UserProfileObserver::class);
    }
}

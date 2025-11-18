<?php

namespace App\Providers;

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
        // Phase 2: Team Management & Loyalty Policies
        \App\Models\TeamMember::class => \App\Policies\TeamMemberPolicy::class,
        \App\Models\LoyaltyReward::class => \App\Policies\LoyaltyRewardPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register admin gate for admin-only routes
        Gate::define('admin', function ($user) {
            return $user->role === 'admin' || $user->is_admin === true;
        });

        // Register team owner gate
        Gate::define('team-owner', function ($user, $teamMember) {
            return $teamMember->role === 'owner' && $teamMember->account_id === $user->id;
        });
    }
}

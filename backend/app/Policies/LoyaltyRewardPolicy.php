<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LoyaltyReward;

class LoyaltyRewardPolicy
{
    /**
     * Determine if the user can view any rewards.
     */
    public function viewAny(User $user): bool
    {
        return true; // Public
    }

    /**
     * Determine if the user can view the reward.
     */
    public function view(User $user, LoyaltyReward $reward): bool
    {
        return $reward->is_active || $user->hasRole('admin');
    }

    /**
     * Determine if the user can create rewards.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine if the user can update the reward.
     */
    public function update(User $user, LoyaltyReward $reward): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine if the user can delete the reward.
     */
    public function delete(User $user, LoyaltyReward $reward): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine if the user can redeem the reward.
     */
    public function redeem(User $user, LoyaltyReward $reward): bool
    {
        return $user->is_verified &&
               $user->is_active &&
               $reward->isAvailable();
    }
}

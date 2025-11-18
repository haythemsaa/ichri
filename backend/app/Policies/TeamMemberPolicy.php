<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TeamMember;

class TeamMemberPolicy
{
    /**
     * Determine if the user can view any team members.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_verified && $user->is_active;
    }

    /**
     * Determine if the user can view the team member.
     */
    public function view(User $user, TeamMember $teamMember): bool
    {
        return $user->id === $teamMember->account_id ||
               $user->id === $teamMember->user_id;
    }

    /**
     * Determine if the user can create team members.
     */
    public function create(User $user): bool
    {
        return $user->is_verified && $user->is_active;
    }

    /**
     * Determine if the user can update the team member.
     */
    public function update(User $user, TeamMember $teamMember): bool
    {
        // Only account owner or admin can update
        if ($user->id === $teamMember->account_id) {
            return true;
        }

        $userMember = TeamMember::where('account_id', $teamMember->account_id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        return $userMember && in_array($userMember->role, ['admin', 'owner']);
    }

    /**
     * Determine if the user can delete the team member.
     */
    public function delete(User $user, TeamMember $teamMember): bool
    {
        // Cannot delete owner
        if ($teamMember->role === 'owner') {
            return false;
        }

        return $user->id === $teamMember->account_id;
    }
}

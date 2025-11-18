<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get all team members
     */
    public function members()
    {
        $members = TeamMember::where('account_id', auth()->id())
            ->with(['user', 'invitedBy'])
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ['members' => $members]
        ]);
    }

    /**
     * Invite new team member
     */
    public function invite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string',
            'role' => 'required|in:admin,manager,employee,viewer',
            'spending_limit' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $invitation = TeamInvitation::create([
            'account_id' => auth()->id(),
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'token' => Str::random(64),
            'invited_by' => auth()->id(),
            'expires_at' => now()->addDays(7),
        ]);

        // TODO: Send invitation email/SMS

        return response()->json([
            'success' => true,
            'message' => 'Invitation envoyée avec succès',
            'data' => ['invitation' => $invitation]
        ], 201);
    }

    /**
     * Update team member
     */
    public function update(Request $request, $id)
    {
        $member = TeamMember::where('account_id', auth()->id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'role' => 'sometimes|in:admin,manager,employee,viewer',
            'spending_limit' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $member->update($request->only(['role', 'spending_limit', 'is_active', 'permissions']));

        return response()->json([
            'success' => true,
            'message' => 'Membre mis à jour',
            'data' => ['member' => $member]
        ]);
    }

    /**
     * Remove team member
     */
    public function remove($id)
    {
        $member = TeamMember::where('account_id', auth()->id())->findOrFail($id);
        
        if ($member->role === 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de retirer le propriétaire'
            ], 403);
        }

        $member->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Membre retiré de l\'équipe'
        ]);
    }
}

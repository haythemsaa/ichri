<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TeamMember;
use Symfony\Component\HttpFoundation\Response;

class CheckTeamPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }

        // Check if user is team member
        $teamMember = TeamMember::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$teamMember) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas membre d\'une équipe'
            ], 403);
        }

        // Check permission
        if (!$teamMember->hasPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Permission refusée'
            ], 403);
        }

        return $next($request);
    }
}

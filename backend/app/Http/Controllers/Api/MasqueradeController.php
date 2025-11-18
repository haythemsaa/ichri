<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MasqueradeSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class MasqueradeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Start masquerade session (login as customer)
     */
    public function start(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasRole(['admin', 'sales_rep'])) {
            return response()->json([
                'success' => false,
                'message' => 'Permission non autorisée'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $targetUser = User::findOrFail($request->user_id);

        // Create masquerade session
        $session = MasqueradeSession::create([
            'admin_user_id' => auth()->id(),
            'target_user_id' => $targetUser->id,
            'reason' => $request->reason,
            'started_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Generate token for target user
        $token = JWTAuth::fromUser($targetUser);

        return response()->json([
            'success' => true,
            'message' => 'Session masquerade démarrée',
            'data' => [
                'session_id' => $session->id,
                'target_user' => $targetUser,
                'token' => $token,
                'token_type' => 'bearer',
                'warning' => 'Vous agissez en tant que ' . $targetUser->store_name,
            ]
        ]);
    }

    /**
     * End masquerade session
     */
    public function end(Request $request)
    {
        $sessionId = $request->session_id;

        $session = MasqueradeSession::where('id', $sessionId)
            ->where('admin_user_id', auth()->id())
            ->whereNull('ended_at')
            ->firstOrFail();

        $session->end();

        // Return admin token
        $admin = User::find($session->admin_user_id);
        $token = JWTAuth::fromUser($admin);

        return response()->json([
            'success' => true,
            'message' => 'Session masquerade terminée',
            'data' => [
                'token' => $token,
                'user' => $admin,
            ]
        ]);
    }

    /**
     * Get masquerade history
     */
    public function history()
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Permission non autorisée'
            ], 403);
        }

        $sessions = MasqueradeSession::with(['admin', 'target'])
            ->orderByDesc('started_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }
}

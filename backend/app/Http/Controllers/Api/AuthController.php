<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        $this->middleware('auth:api', ['except' => ['login', 'register', 'sendOTP', 'verifyOTP', 'resetPassword']]);
    }

    /**
     * Register a new user
     *
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     tags={"Authentication"},
     *     summary="Register new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone","password"},
     *             @OA\Property(property="phone", type="string", example="+21698123456"),
     *             @OA\Property(property="password", type="string", example="Password123"),
     *             @OA\Property(property="first_name", type="string", example="Ahmed"),
     *             @OA\Property(property="last_name", type="string", example="Ben Salah"),
     *             @OA\Property(property="store_name", type="string", example="Épicerie Essalem"),
     *             @OA\Property(property="store_type", type="string", example="epicerie"),
     *             @OA\Property(property="address", type="string", example="Rue République"),
     *             @OA\Property(property="city", type="string", example="Sfax")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users,phone|regex:/^\+216[0-9]{8}$/',
            'password' => 'required|string|min:8|regex:/^(?=.*[A-Z])(?=.*\d)/',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'store_name' => 'nullable|string|max:255',
            'store_type' => 'nullable|in:epicerie,superette,mini_market,autre',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'region' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->register($request->all());

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Login user
     *
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Authentication"},
     *     summary="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone","password"},
     *             @OA\Property(property="phone", type="string", example="+21698123456"),
     *             @OA\Property(property="password", type="string", example="Password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->login(
            $request->phone,
            $request->password
        );

        return response()->json($result, $result['success'] ? 200 : 401);
    }

    /**
     * Send OTP
     */
    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^\+216[0-9]{8}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->sendOTP($request->phone);

        return response()->json($result);
    }

    /**
     * Verify OTP
     */
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->verifyOTP(
            $request->phone,
            $request->otp
        );

        return response()->json($result);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->resetPassword(
            $request->phone,
            $request->password,
            $request->otp
        );

        return response()->json($result);
    }

    /**
     * Logout
     */
    public function logout()
    {
        $result = $this->authService->logout();
        return response()->json($result);
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        $result = $this->authService->refresh();
        return response()->json($result);
    }

    /**
     * Get authenticated user
     */
    public function me()
    {
        $user = $this->authService->me();
        return response()->json([
            'success' => true,
            'user' => $user->load(['addresses', 'cart'])
        ]);
    }
}

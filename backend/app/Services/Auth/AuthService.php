<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\Notification\SmsService;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Register a new user
     */
    public function register(array $data)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'store_name' => $data['store_name'] ?? null,
                'store_type' => $data['store_type'] ?? 'epicerie',
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'region' => $data['region'] ?? null,
                'is_verified' => false,
                'is_active' => true,
            ]);

            // Assign default role
            $user->assignRole('grocer');

            // Send OTP for phone verification
            $this->sendOTP($user->phone);

            DB::commit();

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Inscription réussie. Veuillez vérifier votre téléphone.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'inscription: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Login user
     */
    public function login($phone, $password)
    {
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Numéro de téléphone non trouvé.',
            ];
        }

        if (!$user->is_active) {
            return [
                'success' => false,
                'message' => 'Votre compte est désactivé. Contactez le support.',
            ];
        }

        if (!Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Mot de passe incorrect.',
            ];
        }

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        // Update last login
        $user->update(['last_login_at' => now()]);

        return [
            'success' => true,
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    /**
     * Send OTP to phone
     */
    public function sendOTP($phone)
    {
        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // Store OTP in cache for 5 minutes
        $cacheKey = "otp:{$phone}";
        Cache::put($cacheKey, $otp, now()->addMinutes(5));

        // Send SMS
        $message = "Votre code de vérification ichri.tn est: {$otp}. Valide pendant 5 minutes.";
        $this->smsService->send($phone, $message);

        return [
            'success' => true,
            'message' => 'Code de vérification envoyé.',
            // For development only
            'otp' => config('app.env') === 'local' ? $otp : null,
        ];
    }

    /**
     * Verify OTP
     */
    public function verifyOTP($phone, $otp)
    {
        $cacheKey = "otp:{$phone}";
        $cachedOTP = Cache::get($cacheKey);

        if (!$cachedOTP) {
            return [
                'success' => false,
                'message' => 'Code expiré ou invalide.',
            ];
        }

        if ($cachedOTP != $otp) {
            return [
                'success' => false,
                'message' => 'Code incorrect.',
            ];
        }

        // Mark phone as verified
        $user = User::where('phone', $phone)->first();
        if ($user) {
            $user->update([
                'phone_verified_at' => now(),
                'is_verified' => true,
            ]);

            // Update credit score after verification
            $user->updateCreditScore();
            $user->updateCreditLimit();
        }

        // Clear OTP from cache
        Cache::forget($cacheKey);

        return [
            'success' => true,
            'message' => 'Téléphone vérifié avec succès.',
            'user' => $user,
        ];
    }

    /**
     * Reset password
     */
    public function resetPassword($phone, $newPassword, $otp)
    {
        // Verify OTP first
        $otpResult = $this->verifyOTP($phone, $otp);

        if (!$otpResult['success']) {
            return $otpResult;
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Utilisateur non trouvé.',
            ];
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return [
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès.',
        ];
    }

    /**
     * Logout
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return [
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ];
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());

        return [
            'success' => true,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    /**
     * Get authenticated user
     */
    public function me()
    {
        return JWTAuth::user();
    }
}

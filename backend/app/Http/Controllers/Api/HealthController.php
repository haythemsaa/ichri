<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Basic health check endpoint
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '2.0.0'),
            'environment' => config('app.env'),
        ]);
    }

    /**
     * Detailed health check with all system components
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
        ];

        $overall = collect($checks)->every(fn($check) => $check['status'] === 'ok');

        return response()->json([
            'status' => $overall ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '2.0.0'),
            'environment' => config('app.env'),
            'checks' => $checks,
        ], $overall ? 200 : 503);
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $time = DB::connection()->selectOne('SELECT NOW() as time');

            return [
                'status' => 'ok',
                'message' => 'Database connection successful',
                'connection' => config('database.default'),
                'server_time' => $time->time ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Connection error',
            ];
        }
    }

    /**
     * Check Redis connectivity
     */
    private function checkRedis(): array
    {
        try {
            Redis::ping();

            return [
                'status' => 'ok',
                'message' => 'Redis connection successful',
                'driver' => config('database.redis.client'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Redis connection failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Connection error',
            ];
        }
    }

    /**
     * Check cache functionality
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'test', 60);
            $value = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $value === 'test' ? 'ok' : 'error',
                'message' => $value === 'test' ? 'Cache working' : 'Cache read/write failed',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache check failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Cache error',
            ];
        }
    }

    /**
     * Check queue connectivity
     */
    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');

            return [
                'status' => 'ok',
                'message' => 'Queue configured',
                'driver' => $connection,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue check failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Queue error',
            ];
        }
    }

    /**
     * Check storage accessibility
     */
    private function checkStorage(): array
    {
        try {
            $path = storage_path('logs');
            $writable = is_writable($path);

            return [
                'status' => $writable ? 'ok' : 'error',
                'message' => $writable ? 'Storage writable' : 'Storage not writable',
                'path' => $path,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Storage error',
            ];
        }
    }

    /**
     * System metrics endpoint
     */
    public function metrics(): JsonResponse
    {
        try {
            $metrics = [
                'users_total' => DB::table('users')->count(),
                'products_total' => DB::table('products')->count(),
                'orders_total' => DB::table('orders')->count(),
                'orders_today' => DB::table('orders')->whereDate('created_at', today())->count(),
                'orders_pending' => DB::table('orders')->where('status', 'pending')->count(),
                'cache_hits' => 0, // Can be enhanced with Redis info
                'queue_size' => 0, // Can be enhanced with queue size check
            ];

            return response()->json([
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
                'metrics' => $metrics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to collect metrics',
                'error' => app()->environment('local') ? $e->getMessage() : 'Metrics error',
            ], 500);
        }
    }
}

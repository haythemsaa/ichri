<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Log request details
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'user_agent' => $request->userAgent(),
        ];

        // Process the request
        $response = $next($request);

        // Calculate duration
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        // Add response data
        $logData['status'] = $response->getStatusCode();
        $logData['duration_ms'] = $duration;

        // Log based on status code
        if ($response->getStatusCode() >= 500) {
            Log::error('API Request - Server Error', $logData);
        } elseif ($response->getStatusCode() >= 400) {
            Log::warning('API Request - Client Error', $logData);
        } elseif ($duration > 1000) {
            Log::warning('API Request - Slow Response', $logData);
        } else {
            Log::info('API Request', $logData);
        }

        // Add performance headers for debugging
        $response->headers->set('X-Response-Time', $duration . 'ms');
        $response->headers->set('X-Request-ID', $request->id ?? uniqid());

        return $response;
    }
}

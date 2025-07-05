<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limitType = 'default'): Response
    {
        $key = $this->resolveRequestSignature($request, $limitType);
        $maxAttempts = $this->getMaxAttempts($limitType);
        $decayMinutes = $this->getDecayMinutes($limitType);

        // Check if rate limit is exceeded
        if (RateLimiter::tooManyAttempts($key, maxAttempts: $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            return response()->json([
                'error' => 'Rate limit exceeded',
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
                'limit' => $maxAttempts,
                'window' => $decayMinutes * 60
            ], 429)->header('Retry-After', $retryAfter);
        }

        // Hit the rate limiter
        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers
        $remaining = $maxAttempts - RateLimiter::attempts($key);
        $resetTime = time() + RateLimiter::availableIn($key);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset' => $resetTime,
        ]);
    }

    /**
     * Resolve the rate limiting key for the request
     */
    protected function resolveRequestSignature(Request $request, string $limitType): string
    {
        $userId = optional($request->user())->id;
        $ip = $request->ip();
        
        // Create unique key based on user (if authenticated) or IP
        $identifier = $userId ? "user:{$userId}" : "ip:{$ip}";
        
        return "rate_limit:{$limitType}:{$identifier}";
    }

    /**
     * Get max attempts for the limit type
     */
    protected function getMaxAttempts(string $limitType): int
    {
        return match($limitType) {
            'strict' => 10,      // 10 requests per window (for write operations)
            'moderate' => 30,    // 30 requests per window (for general API)
            'default' => 60,     // 60 requests per window (for read operations)
            'bulk' => 5,         // 5 requests per window (for bulk operations)
            default => 60,
        };
    }

    /**
     * Get decay minutes for the limit type
     */
    protected function getDecayMinutes(string $limitType): int
    {
        return match($limitType) {
            'strict' => 15,      // 15 minutes window
            'moderate' => 10,    // 10 minutes window  
            'default' => 5,      // 5 minutes window
            'bulk' => 30,        // 30 minutes window
            default => 5,
        };
    }
}

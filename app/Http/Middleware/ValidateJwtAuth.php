<?php

namespace App\Http\Middleware;

use App\Custom\Jwt\Config;
use App\Models\TokenBlacklist;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValidateJwtAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->header('authorization');
            $token = explode('Bearer ', $token)[1];
            $tokenObject = Config::get()->parser()->parse($token);

            $isTokenExpired = $tokenObject->isExpired(now());

            $isTokenBlacklisted = TokenBlacklist::where('token', $token)->first() != null;

            if ($isTokenExpired || $isTokenBlacklisted) {
                return response()->json([
                    'message' => 'Token expired',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token invalid or malformed',
            ], 400);
        }

        return $next($request);
    }
}

<?php
namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Illuminate\Support\Facades\Log; // Import Log class

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        Log::info('JwtMiddleware triggered'); // Log entry for debugging

        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                Log::error('User not found in JWT');
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (TokenExpiredException $e) {
            Log::error('Token expired');
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            Log::error('Invalid token');
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (TokenBlacklistedException $e) {
            Log::error('Token blacklisted');
            return response()->json(['error' => 'Token is blacklisted'], 401);
        } catch (JWTException $e) {
            Log::error('No token provided');
            return response()->json(['error' => 'Token not provided'], 401);
        }

        return $next($request);
    }
}

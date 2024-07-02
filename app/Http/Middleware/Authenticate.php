<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            
           $token = JWTAuth::parseToken(); // Parse token from the request
         
            $user = $token->authenticate(); // Attempt to authenticate the user
            
            if(!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            

            return $next($request);
        } catch (Exception $e) {
            // Token is invalid or user not found
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

}
    


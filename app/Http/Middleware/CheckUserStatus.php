<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            
            if ($user && $user->estado == 0) {
                // Invalidate token if they are somehow logged in
                Auth::guard('api')->logout();
                
                return response()->json([
                    'mensaje' => 'Usuario deshabilitado en el sistema',
                    'objeto' => [],
                    'status' => 403
                ], 403);
            }
        }

        return $next($request);
    }
}

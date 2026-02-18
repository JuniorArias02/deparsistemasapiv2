<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            /** @var \App\Models\Usuario $user */
            $user = Auth::user();
            if (!$user->last_activity || $user->last_activity->lt(now()->subMinute())) {
                $user->timestamps = false;
                $user->last_activity = now();
                $user->save();
            }
        }

        return $next($request);
    }
}

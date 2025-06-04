<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = auth()->user()) {
            $user->updateLastActivity();
            
            if ($user->isProvider()) {
                $user->provider->update([
                    'last_activity_at' => now()
                ]);
            }
        }

        return $next($request);
    }
} 
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->force_password_change == 1) {

        // allow only password change routes
        if (!$request->is('change-password') && !$request->is('logout')) {
            return redirect()->route('change.password');
        }
    }
    
        return $next($request);
    }
}

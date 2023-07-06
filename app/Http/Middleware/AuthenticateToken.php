<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $providedToken = $request->header('Authorization');

        if (!$providedToken || $providedToken !== 'Bearer ' . config('app.api_token')) {
            return response()->json(['message' => 'Acesso não autorizado', 'code' => 401], 401);
        }

        return $next($request);
    }
}

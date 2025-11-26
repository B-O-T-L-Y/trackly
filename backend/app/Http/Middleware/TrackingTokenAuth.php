<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackingTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (!str_starts_with((string)$header, 'Bearer ')) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $token = substr($header, 7);

        if ($token !== config('tracking.token')) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}

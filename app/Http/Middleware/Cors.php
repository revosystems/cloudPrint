<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $acceptedOrigins = explode(',', env('ALLOWED_DOMAINS'));
        $origin = $request->headers->get('origin');

        $headers = in_array($origin, $acceptedOrigins)
            ? [
                'Access-Control-Allow-Origin'      => $origin,
                'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
                'Access-Control-Max-Age'           => '86400',
                'Access-Control-Allow-Headers'     => 'Origin, Content-Type, X-Auth-Token, X-Requested-With, Authorization, Accept, account',
                'Vary' => 'Origin',
            ]
            : [];

        //Using this you don't need an method for 'OPTIONS' on controller
        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        // For all other cases
        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}

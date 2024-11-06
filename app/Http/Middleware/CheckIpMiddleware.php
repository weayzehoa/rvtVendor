<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIpMiddleware
{
    //IP白名單
    public $whiteIps = ['1.34.219.62', '127.0.0.1', '::1'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!in_array($request->ip(), $this->whiteIps)) {
            /*
                 You can redirect to any error page.
            */
            return response()->json([
                'code' => 401,
                'message' => 'your ip address is not valid.'
            ],401);
        }

        return $next($request);
    }
}

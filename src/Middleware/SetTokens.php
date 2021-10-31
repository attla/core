<?php

namespace Attla\Middleware;

use Symfony\Component\HttpFoundation\Cookie;

class SetTokens
{
    /**
     * Handle an incoming request
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        if (!empty($response->headers)) {
            foreach (tokens()->getAllToStore() as $name => $token) {
                $response->headers->setCookie(
                    new Cookie(
                        $name,
                        $token['value'],
                        $token['ttl'],
                        '/',
                        false,
                        true,
                        false,
                        false,
                        null
                    )
                );
            }
        }

        return $response;
    }
}

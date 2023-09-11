<?php

namespace Tests\Utility;

class SetCookieMiddleware
{
    public function handle($request, $next)
    {
        $response = $next($request);

        $response->headers->setCookie(cookie('SECRET_COOKIE', 'abcd'));
        $response->headers->setCookie(cookie('NON_SECRET_COOKIE', 'efgh'));

        return $response;
    }
}

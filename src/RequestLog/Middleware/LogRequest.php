<?php

namespace Cego\RequestLog\Middleware;

use Cego\RequestLog\Data\RequestLog;
use Closure;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Cego\RequestLog\Utilities\SecurityUtility;
use Illuminate\Http\Request;

class LogRequest
{
    /**
     * Holds the start time of the request
     *
     * @var string|float $startTime
     */
    protected $startTime;

    /**
     * Holds the received request
     */
    protected ?RequestLog $receivedRequest = null;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->startTime = microtime(true);

        // Proceed to the next middleware
        return $next($request);
    }

    /**
     * Updates the RequestLog in the database once the request has terminated
     */
    public function terminate(Request $request, Response $response): void
    {
        try {

            if (! config('request-log.enabled') || $this->routeIsBlacklisted($request)) {
                return;
            }

            $executionTime = microtime(true) - $this->startTime;

            if ($executionTime < 0) {
                $executionTime = 0;
            }

            (new RequestLog(
                clientIp: $request->ip(),
                userAgent: $request->userAgent(),
                method: $request->method(),
                url: $request->url(),
                root: $request->root(),
                path: $request->path(),
                queryString: SecurityUtility::getQueryWithMaskingApplied($request),
                requestHeaders: SecurityUtility::getHeadersWithMaskingApplied($request),
                requestBody: SecurityUtility::getBodyWithMaskingApplied($request) ?: '{}',
                status: $response->status(),
                responseHeaders: json_encode($response->headers->all()),
                responseBody: $response->getContent() ?: '{}',
                responseException: $response->exception,
                executionTime: $executionTime
            ))->log(Log::getLogger());

        } catch (Throwable $throwable) {
            Log::error($throwable);
        }
    }

    /**
     * Checks if the path of the request is a blacklisted route
     *
     * @param mixed $request
     *
     * @return bool
     */
    protected function routeIsBlacklisted($request)
    {
        $blacklistedRoutes = Config::get('request-log.blackListedRoutes', []);

        foreach ($blacklistedRoutes as $route) {
            if (fnmatch($route, $request->path(), FNM_PATHNAME)) {
                return true;
            }
        }

        return false;
    }
}

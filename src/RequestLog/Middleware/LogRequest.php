<?php

namespace Cego\RequestLog\Middleware;

use Cego\RequestLog\Data\RequestLog;
use Closure;
use Illuminate\Http\Response;
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
    protected RequestLog $receivedRequest;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->startTime = microtime(true);

        try {
            // We need to check if the path of the current request is blacklisted
            // if it is we out-out here and to not write a RequestLog entry
            if (config('request-log.enabled') && ! $this->routeIsBlacklisted($request)) {

                // Save request to database immediately so that we can see that it was received even if it is timed out
                $this->receivedRequest = new RequestLog(
                    clientIp: $request->ip(),
                    userAgent: $request->userAgent(),
                    method: $request->method(),
                    url: $request->url(),
                    root: $request->root(),
                    path: $request->path(),
                    queryString: SecurityUtility::getQueryWithMaskingApplied($request),
                    requestHeaders: SecurityUtility::getHeadersWithMaskingApplied($request),
                    requestBody: SecurityUtility::getBodyWithMaskingApplied($request) ?: '{}',
                );
            }
        } catch (Throwable $throwable) {
            Log::error($throwable);
        }

        // Proceed to the next middleware
        return $next($request);
    }

    /**
     * Updates the RequestLog in the database once the request has terminated
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            // If the request is blacklisted or request log is not enabled, then receivedRequest was never set.
            if ( ! isset($this->receivedRequest)) {
                return;
            }

            $executionTime = microtime(true) - $this->startTime;

            if ($executionTime < 0) {
                $executionTime = 0;
            }

            // Update the receivedRequest in the database with response data
            $requestLog = $this->receivedRequest;
            $requestLog->status = $response->status();
            $requestLog->responseHeaders = json_encode($response->headers->all());
            $requestLog->responseBody = $response->getContent() ?: '{}';
            $requestLog->responseException = $response->exception;
            $requestLog->executionTime = $executionTime;

            $requestLog->log(Log::getLogger());

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

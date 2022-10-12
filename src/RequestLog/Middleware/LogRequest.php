<?php

namespace Cego\RequestLog\Middleware;

use Closure;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Cego\RequestLog\Models\RequestLog;
use Illuminate\Support\Facades\Config;
use Cego\RequestLog\Utilities\SecurityUtility;
use Cego\RequestLog\Models\RequestLogBlacklistedRoute;
use Cego\RequestLog\Services\RequestLogOptionsService;

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
     *
     * @var $receivedRequest
     */
    protected $receivedRequest;

    protected RequestLogOptionsService $requestLogOptionsService;

    public function __construct(RequestLogOptionsService $requestLogOptionsService)
    {
        $this->requestLogOptionsService = $requestLogOptionsService;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->startTime = microtime(true);

        try {
            // We need to check if the path of the current request is blacklisted
            // if it is we out-out here and to not write a RequestLog entry
            if ($this->requestLogOptionsService->isRequestLogEnabled() && ! $this->routeIsBlacklisted($request)) {

                // Save request to database immediately so that we can see that it was received even if it is timed out
                $this->receivedRequest = RequestLog::create([
                    'client_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'method' => $request->method(),
                    'status' => 0,
                    'url' => $request->url(),
                    'root' => $request->root(),
                    'path' => $request->path(),
                    'query_string' => SecurityUtility::getQueryWithMaskingApplied($request),
                    'request_headers' => SecurityUtility::getHeadersWithMaskingApplied($request),
                    'request_body' => SecurityUtility::getBodyWithMaskingApplied($request) ?: '{}',
                    'response_headers' => '[]',
                    'response_body' => '{}',
                    'response_exception' => '[]',
                    'execution_time' => 0,
                ]);
            }
        } catch (Throwable $throwable) {
            echo($throwable->getMessage());
            Log::error($throwable);
        }

        // Proceed to the next middleware
        return $next($request);
    }

    /**
     * Writes the RequestLog to the database once the request has terminated
     *
     * @param mixed $request
     * @param mixed $response
     *
     * @return void
     */
    public function terminate($request, $response): void
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

            // Update the receivedRequest with response data, then save changes to the database.
            $this->receivedRequest->update([
                'status' => $response->getStatus(),
                'response_headers'   => json_encode($response->headers->all()),
                'response_body'      => $response->getContent() ?: '{}',
                'response_exception' => json_encode($response->exception),
                'execution_time'     => $executionTime,
            ]);

            $this->receivedRequest->save();

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
        $configBlackListedRoutes = Config::get('request-log.blackListedRoutes', []);

        // We get the list of blacklisted routes from the cache if present
        // or get it from the database and cache it forever
        $blacklistedRoutes = Cache::remember('request-log.blacklistedUrls', 10, function () {
            return RequestLogBlacklistedRoute::all();
        });

        $blacklistedRoutes = $blacklistedRoutes->map(fn (RequestLogBlacklistedRoute $route) => $route->path)->toArray();

        $blacklistedRoutes = array_merge($configBlackListedRoutes, $blacklistedRoutes);

        /** @var RequestLogBlacklistedRoute $route */
        foreach ($blacklistedRoutes as $route) {
            if (fnmatch($route, $request->path(), FNM_PATHNAME)) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use App\RequestLog;

class LogRequest
{
    /**
     * Holds the start time of the request
     *
     * @var string|float $startTime
     */
    protected $startTime;

    /**
     * Holds the end time of the request
     *
     * @var string|float $endTime
     */
    protected $endTime;

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

        // Proceed to the next middleware
        return $next($request);
    }

    /**
     * Writes the RequestLog to the database once the request has terminated
     *
     * @param $request
     * @param $response
     */
    public function terminate($request, $response)
    {
        $this->endTime = microtime(true);

        RequestLog::create([
            'client_ip'          => $request->ip(),
            'user_agent'         => $request->userAgent(),
            'method'             => $request->method(),
            'status'             => $response->status(),
            'url'                => $request->url(),
            'root'               => $request->root(),
            'path'               => $request->path(),
            'query_string'       => json_encode($request->query()),
            'request_headers'    => json_encode($request->headers->all()),
            'request_body'       => json_encode($request->all()),
            'response_headers'   => json_encode($response->headers->all()),
            'response_body'      => json_encode($response->original),
            'response_exception' => json_encode($response->exception),
            'execution_time'     => $this->endTime - $this->startTime,
        ]);
    }
}

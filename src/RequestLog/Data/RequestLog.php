<?php

namespace Cego\RequestLog\Data;

use Exception;
use Psr\Log\LoggerInterface;

class RequestLog
{
    public function __construct(
        public readonly string $method,
        public readonly string $url,
        public readonly string $root,
        public readonly string $path,
        public readonly string $queryString,
        public readonly array $requestHeaders,
        public readonly array $requestCookies,
        public readonly string $requestBody,
        public readonly int $status,
        public readonly array $responseHeaders,
        public readonly array $responseCookies,
        public readonly string $responseBody,
        public ?Exception $responseException,
        public int $executionTimeNs,
    ) {
    }

    public function log(LoggerInterface $logger)
    {
        $context = [
            'http' => [
                'request' => [
                    'url'          => $this->url,
                    'root'         => $this->root,
                    'path'         => $this->path,
                    'query_string' => $this->queryString,
                    'body'         => [
                        'content' => $this->requestBody
                    ],
                    'cookies' => $this->requestCookies,
                    'headers' => $this->requestHeaders,
                    'method'  => $this->method
                ],
                'response' => [
                    'body' => [
                        'content' => $this->responseBody
                    ],
                    'cookies'     => $this->responseCookies,
                    'headers'     => $this->responseHeaders,
                    'status_code' => $this->status
                ]
            ],
            'event' => [
                'duration' => $this->executionTimeNs // In nanoseconds, see https://www.elastic.co/guide/en/ecs/current/ecs-event.html
            ],
            'log' => [
                'type' => 'request-logs'
            ]
        ];

        if($this->responseException !== null) {
            $context['error'] = [
                'message'     => $this->responseException->getMessage(),
                'stack_trace' => $this->responseException->getTrace(),
                'type'        => get_class($this->responseException)
            ];
        }

        $logger->debug(
            sprintf("Timing for %s", $this->url),
            $context
        );
    }
}

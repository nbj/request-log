<?php

namespace Cego\RequestLog\Data;

use Exception;
use Psr\Log\LoggerInterface;

class RequestLog
{
    public function __construct(
        public readonly string $clientIp,
        public readonly string $userAgent,
        public readonly string $method,
        public readonly string $url,
        public readonly string $root,
        public readonly string $path,
        public readonly string $queryString,
        public readonly string $requestHeaders,
        public readonly string $requestBody,
    ) {
    }

    public ?int $status;
    public ?string $responseHeaders;
    public ?string $responseBody;
    public ?Exception $responseException;
    public null|float|string $executionTime;

    public function log(LoggerInterface $logger)
    {
        $context = [
            'client' => [
                'ip'         => $this->clientIp,
                'user_agent' => $this->userAgent
            ],
            'http' => [
                'request' => [
                    'url'          => $this->url,
                    'root'         => $this->root,
                    'path'         => $this->path,
                    'query_string' => $this->queryString,
                    'body'         => [
                        'content' => $this->requestBody
                    ],
                    'headers' => $this->requestHeaders,
                    'method'  => $this->method
                ],
                'response' => [
                    'body' => [
                        'content' => $this->responseBody
                    ],
                    'headers'     => $this->responseHeaders,
                    'status_code' => $this->status
                ]
            ],
            'event' => [
                'duration' => $this->executionTime * 1000 // In nanoseconds, see https://www.elastic.co/guide/en/ecs/current/ecs-event.html
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

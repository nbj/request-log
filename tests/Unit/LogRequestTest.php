<?php

namespace Tests\Unit;

use Cego\RequestLog\Services\RequestLogOptionsService;
use Illuminate\Support\Facades\Log;
use Monolog\Logger;
use Tests\TestCase;
use Cego\RequestLog\Models\RequestLog;
use Illuminate\Support\Facades\Config;
use Cego\RequestLog\Middleware\LogRequest;

class LogRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $kernel = app('Illuminate\Contracts\Http\Kernel');
        $kernel->pushMiddleware(LogRequest::class);
        Config::set('request-log.enabled', true);
    }

    /** @test */
    public function request_body_is_always_empty_when_not_json()
    {
        // Arrange
        $data = [
            'password' => '12345678',
            'person'   => [
                'sensitive_data' => 'secret',
            ]
        ];

        // Act
        $methods = ['get', 'post', 'put', 'patch', 'delete'];

        foreach ($methods as $method) {
            $loggerMock = $this->createMock(Logger::class);

            Log::partialMock()->shouldReceive('getLogger')->once()->withAnyArgs()->andReturn($loggerMock);

            // Assert debug was called on loggerMock once with {} request body
            $loggerMock->expects($this->once())->method('debug')->with($this->stringStartsWith('Timing for'))->willReturnCallback(function ($message, $context) {
                $this->assertEquals('{}', $context['http']['request']['body']['content']);
            });

            $this->$method('/test', $data, []);
        }
    }

    /** @test */
    public function it_masks_request_headers()
    {
        // Arrange
        $loggerMock = $this->createMock(Logger::class);
        Log::partialMock()->shouldReceive('getLogger')->once()->withAnyArgs()->andReturn($loggerMock);

        // Assert debug was called on loggerMock once with {} request body
        $loggerMock->expects($this->once())->method('debug')->with($this->stringStartsWith('Timing for'))->willReturnCallback(function ($message, $context) {
            $loggedHeaders = $context['http']['request']['headers'];
            $this->assertEquals('[ MASKED ]', $loggedHeaders['x-encrypt-this-header'][0]);
            $this->assertEquals('This is a non-secret header', $loggedHeaders['x-dont-encrypt-this-header'][0]);
        });


        $headers = [
            'X-SENSITIVE-REQUEST-HEADERS-JSON' => json_encode(['X-ENCRYPT-THIS-HEADER']),
            'X-ENCRYPT-THIS-HEADER'            => 'This is a secret header',
            'X-DONT-ENCRYPT-THIS-HEADER'       => 'This is a non-secret header',
        ];

        // Act
        $this->post('/test', [], $headers);
    }

    /** @test */
    public function it_masks_duplicate_request_headers()
    {
        // Arrange
        $loggerMock = $this->createMock(Logger::class);
        Log::partialMock()->shouldReceive('getLogger')->once()->withAnyArgs()->andReturn($loggerMock);

        // Assert debug was called on loggerMock once with {} request body
        $loggerMock->expects($this->once())->method('debug')->with($this->stringStartsWith('Timing for'))->willReturnCallback(function ($message, $context) {
            $loggedHeaders = $context['http']['request']['headers'];
            $this->assertEquals('[ MASKED ]', $loggedHeaders['x-encrypt-this-header'][0]);
            $this->assertEquals('[ MASKED ]', $loggedHeaders['x-encrypt-this-header'][1]);
            $this->assertEquals('This is a non-secret header', $loggedHeaders['x-dont-encrypt-this-header'][0]);
        });

        $headers = [
            'X-SENSITIVE-REQUEST-HEADERS-JSON' => json_encode(['X-ENCRYPT-THIS-HEADER']),
            'X-ENCRYPT-THIS-HEADER'            => ['This is a secret header', 'And we define it twice'],
            'X-DONT-ENCRYPT-THIS-HEADER'       => 'This is a non-secret header',
        ];

        // Act
        $this->post('/test', [], $headers);
    }

    /** @test */
    public function it_masks_request_body()
    {
        // Arrange
        $loggerMock = $this->createMock(Logger::class);
        Log::partialMock()->shouldReceive('getLogger')->once()->withAnyArgs()->andReturn($loggerMock);

        // Assert debug was called on loggerMock once with {} request body
        $loggerMock->expects($this->once())->method('debug')->with($this->stringStartsWith('Timing for'))->willReturnCallback(function ($message, $context) {
            $loggedBody = json_decode($context['http']['request']['body']['content'], true);
            $this->assertEquals([
                'password'  => '[ MASKED ]',
                'something' => [
                    'very' => [
                        'nested' => '[ MASKED ]'
                    ]
                ],
                'person' => [
                    'sensitive_data'   => '[ MASKED ]',
                    'insensitive_data' => 'not secret',
                ],
                'secret_array' => '[ MASKED ]'
            ], $loggedBody);
        });

        $data = [
            'password'  => '12345678',
            'something' => [
                'very' => [
                    'nested' => 'should not see'
                ]
            ],
            'person' => [
                'sensitive_data'   => 'secret',
                'insensitive_data' => 'not secret',
            ],
            'secret_array' => [
                'of' => 'stuff'
            ]
        ];

        $headers = [
            'X-SENSITIVE-REQUEST-BODY-JSON' => json_encode([
                'password',
                'person.sensitive_data',
                'something.very.nested',
                'this_key.does_not.exist',
                'secret_array',
            ]),
        ];

        // Act
        $this->postJson('/test', $data, $headers);
    }

    /** @test */
    public function it_tests()
    {
        // Arrange
        $loggerMock = $this->createMock(Logger::class);
        Log::partialMock()->shouldReceive('getLogger')->once()->withAnyArgs()->andReturn($loggerMock);

        // Assert debug was called on loggerMock once with {} request body
        $loggerMock->expects($this->once())->method('debug')->with($this->stringStartsWith('Timing for'))->willReturnCallback(function ($message, $context) {
            $loggedHeaders = $context['http']['request']['headers'];
            $this->assertEquals('{"token":"[ MASKED ]","cake":"not-secret"}', $context['http']['request']['query_string']);
            $this->assertEquals('[ MASKED ]', $loggedHeaders['authorization'][0]);
            $this->assertEquals('Not Secret', $loggedHeaders['something-else'][0]);
        });

        // Act
        $this->post('/test?token=very-secret&cake=not-secret', [], ['Authorization' => 'very secret', 'something-else' => 'Not Secret']);
    }
}

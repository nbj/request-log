<?php

namespace Tests\Unit;

use Tests\TestCase;
use Cego\RequestLog\Models\RequestLog;
use Cego\RequestLog\Middleware\LogRequest;

class LogRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $kernel = app('Illuminate\Contracts\Http\Kernel');
        $kernel->pushMiddleware(LogRequest::class);
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
            RequestLog::query()->truncate();
            $this->$method('/test', $data, []);

            // Assert
            $this->assertDatabaseCount('request_logs', 1);
            $this->assertEquals('{}', RequestLog::first()->request_body);
        }
    }

    /** @test */
    public function it_masks_request_headers()
    {
        // Arrange
        $headers = [
            'X-SENSITIVE-REQUEST-HEADERS-JSON' => json_encode(['X-ENCRYPT-THIS-HEADER']),
            'X-ENCRYPT-THIS-HEADER'            => 'This is a secret header',
            'X-DONT-ENCRYPT-THIS-HEADER'       => 'This is a non-secret header',
        ];

        // Act
        $this->post('/test', [], $headers);

        // Assert
        $this->assertDatabaseCount('request_logs', 1);

        $requestLog = RequestLog::first();
        $loggedHeaders = json_decode($requestLog->request_headers, true);

        $this->assertEquals('[ MASKED ]', $loggedHeaders['x-encrypt-this-header'][0]);
        $this->assertEquals('This is a non-secret header', $loggedHeaders['x-dont-encrypt-this-header'][0]);
    }

    /** @test */
    public function it_masks_request_body()
    {
        // Arrange
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

        // Assert
        $this->assertDatabaseCount('request_logs', 1);
        $loggedBody = json_decode(RequestLog::first()->request_body, true);
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
    }
}

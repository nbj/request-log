<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
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
    public function it_encrypts_headers_specified_in_header(): void
    {
        // Arrange
        $headers = [
            'X-SENSITIVE-REQUEST-HEADERS-JSON' => json_encode('X-ENCRYPT-THIS-HEADER'),
            'X-ENCRYPT-THIS-HEADER'            => 'This is a secret header',
            'X-DONT-ENCRYPT-THIS-HEADER'       => 'This is a non-secret header',
        ];

        // Act
        $methods = ['get', 'getJson', 'post', 'postJson', 'put', 'putJson', 'patch', 'patchJson', 'delete', 'deleteJson'];

        foreach ($methods as $method) {
            RequestLog::query()->truncate();

            if (Str::startsWith($method, 'get')) {
                $this->$method('/test', $headers);
            } else {
                $this->$method('/test', [], $headers);
            }

            // Assert
            $this->assertDatabaseCount('request_logs', 1);
            $requestLog = RequestLog::first();
            $loggedHeaders = json_decode($requestLog->request_headers, true);

            $this->assertEquals('This is a secret header', Crypt::decrypt($loggedHeaders['x-encrypt-this-header'][0]));
            $this->assertEquals('This is a non-secret header', $loggedHeaders['x-dont-encrypt-this-header'][0]);
        }
    }

    /** @test */
    public function it_encrypts_body_specified_in_header()
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
        $methods = ['postJson', 'putJson', 'patchJson'];

        foreach ($methods as $method) {
            RequestLog::query()->truncate();
            $this->$method('/test', $data, $headers);

            // Assert
            $this->assertDatabaseCount('request_logs', 1);
            $loggedBody = json_decode(RequestLog::first()->request_body, true);
            $this->assertEquals($data, [
                'password'  => Crypt::decrypt($loggedBody['password']),
                'something' => [
                    'very' => [
                        'nested' => Crypt::decrypt($loggedBody['something']['very']['nested'])
                    ]
                ],
                'person' => [
                    'sensitive_data'   => Crypt::decrypt($loggedBody['person']['sensitive_data']),
                    'insensitive_data' => 'not secret',
                ],
                'secret_array' => Crypt::decrypt($loggedBody['secret_array'])
            ]);
        }
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
    public function it_gets_request_headers_with_masking_applied()
    {
        // Arrange
        $headers = [
            'X-SENSITIVE-REQUEST-HEADERS-JSON' => json_encode('X-ENCRYPT-THIS-HEADER'),
            'X-ENCRYPT-THIS-HEADER'            => 'This is a secret header',
            'X-DONT-ENCRYPT-THIS-HEADER'       => 'This is a non-secret header',
        ];

        // Act
        $this->post('/test', [], $headers);

        // Assert
        $this->assertDatabaseCount('request_logs', 1);

        $requestLog = RequestLog::first();
        $loggedHeaders = json_decode($requestLog->getRequestHeadersWithMaskingApplied(), true);

        $this->assertEquals('[ MASKED ]', $loggedHeaders['x-encrypt-this-header'][0]);
        $this->assertEquals('This is a non-secret header', $loggedHeaders['x-dont-encrypt-this-header'][0]);
    }

    /** @test */
    public function it_gets_request_body_with_masking_applied()
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
        $loggedBody = json_decode(RequestLog::first()->getRequestBodyWithMaskingApplied(), true);
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

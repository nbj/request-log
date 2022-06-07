<?php

namespace Cego\RequestLog\Utilities;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class SecurityUtility
{
    /**
     * Get headers of the request with encryption applied according to 'X-SENSITIVE-REQUEST-HEADERS-JSON'
     *
     * @param Request $request
     *
     * @return string
     */
    public static function getHeadersWithEncryptionApplied(Request $request): string
    {
        $sensitiveHeaders = $request->hasHeader('X-SENSITIVE-REQUEST-HEADERS-JSON')
            ? (array) json_decode($request->header('X-SENSITIVE-REQUEST-HEADERS-JSON'))
            : [];

        $sensitiveHeaders = array_map(fn ($header) => strtolower($header), $sensitiveHeaders);

        $headers = $request->headers->all();

        foreach ($sensitiveHeaders as $sensitiveHeader) {
            if (isset($headers[$sensitiveHeader][0])) {
                $headers[$sensitiveHeader][0] = Crypt::encrypt($headers[$sensitiveHeader][0]);
            }
        }

        return json_encode($headers);
    }

    /**
     * Get body of the request with encryption applied according to 'X-SENSITIVE-REQUEST-BODY-JSON'
     *
     * @param Request $request
     *
     * @return string|null
     */
    public static function getBodyWithEncryptionApplied(Request $request): ?string
    {
        $sensitiveBodyFields = $request->hasHeader('X-SENSITIVE-REQUEST-BODY-JSON')
            ? (array) json_decode($request->header('X-SENSITIVE-REQUEST-BODY-JSON'))
            : [];

        if (empty($sensitiveBodyFields)) {
            return $request->getContent();
        }

        if ( ! $request->isJson()) {
            // If the request is not JSON, getContent(), which is what we log as request body, is always empty
            return $request->getContent();
        }

        $data = json_decode($request->getContent(), true);

        foreach ($sensitiveBodyFields as $field) {
            if ($dataValue = Arr::get($data, $field)) {
                Arr::set($data, $field, Crypt::encrypt($dataValue));
            }
        }

        return json_encode($data);
    }
}

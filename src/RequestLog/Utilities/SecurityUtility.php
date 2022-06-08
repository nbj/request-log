<?php

namespace Cego\RequestLog\Utilities;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityUtility
{
    /**
     * Get headers of the request with encryption applied according to 'X-SENSITIVE-REQUEST-HEADERS-JSON'
     *
     * @param Request $request
     *
     * @return string
     */
    public static function getHeadersWithMaskingApplied(Request $request): string
    {
        $headers = $request->headers->all();

        if ( ! $request->hasHeader('X-SENSITIVE-REQUEST-HEADERS-JSON')) {
            return json_encode($headers);
        }

        $sensitiveHeaders = json_decode($request->header('X-SENSITIVE-REQUEST-HEADERS-JSON'));

        $sensitiveHeaders = array_map(fn ($header) => strtolower($header), $sensitiveHeaders);

        foreach ($sensitiveHeaders as $sensitiveHeader) {
            if (isset($headers[$sensitiveHeader])) {
                $headers[$sensitiveHeader] = array_map(fn () => '[ MASKED ]', $headers[$sensitiveHeader]);
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
    public static function getBodyWithMaskingApplied(Request $request): ?string
    {
        if ( ! $request->hasHeader('X-SENSITIVE-REQUEST-BODY-JSON') || ! $request->isJson()) {
            // If the request is not JSON, getContent(), which is what we log as request body, is always empty
            return $request->getContent();
        }

        $sensitiveBodyFields = json_decode($request->header('X-SENSITIVE-REQUEST-BODY-JSON'));

        $data = json_decode($request->getContent(), true);

        foreach ($sensitiveBodyFields as $field) {
            if (Arr::get($data, $field)) {
                Arr::set($data, $field, '[ MASKED ]');
            }
        }

        return json_encode($data);
    }
}

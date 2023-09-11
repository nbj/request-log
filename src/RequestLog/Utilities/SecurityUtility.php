<?php

namespace Cego\RequestLog\Utilities;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SecurityUtility
{
    /**
     * Returns the query with masking applied
     *
     * @param Request $request
     *
     * @return string
     */
    public static function getQueryWithMaskingApplied(Request $request): string
    {
        $query = $request->query();

        if (( ! is_array($query)) || empty($query)) {
            return json_encode($query);
        }

        $query = collect($query)->mapWithKeys(fn ($value, string $key) => [strtolower($key) => $value]);
        $redactedQueryKeys = collect(Config::get('request-log.redact.query', []))->map(fn (string $query) => strtolower($query));

        foreach ($redactedQueryKeys as $key) {
            if ($query->has($key)) {
                $query->put($key, '[ MASKED ]');
            }
        }

        return $query->toJson();
    }

    /**
     * Get headers of the request with encryption applied according to 'X-SENSITIVE-REQUEST-HEADERS-JSON'
     *
     * @param Request $request
     *
     * @return string
     */
    public static function getHeadersWithMaskingApplied(Request $request): array
    {
        $headers = $request->headers->all();

        $senstiveHeaderIn = $request->header('X-SENSITIVE-REQUEST-HEADERS-JSON');
        $sensitiveHeaders = $senstiveHeaderIn ? json_decode($senstiveHeaderIn) : [];
        $redactedHeaders = Config::get('request-log.redact.headers', []);

        $headersToMask = collect($sensitiveHeaders)->concat($redactedHeaders)->map(fn (string $header) => strtolower($header));

        foreach ($headersToMask as $sensitiveHeader) {
            if (isset($headers[$sensitiveHeader])) {
                $headers[$sensitiveHeader] = array_map(fn () => '[ MASKED ]', $headers[$sensitiveHeader]);
            }
        }

        return $headers;
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
            if (Arr::has($data, $field)) {
                Arr::set($data, $field, '[ MASKED ]');
            }
        }

        return json_encode($data);
    }
}

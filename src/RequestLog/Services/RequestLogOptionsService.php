<?php

namespace Cego\RequestLog\Services;

use Illuminate\Support\Facades\Cache;
use Cego\RequestLog\Models\RequestLogOption;

class RequestLogOptionsService
{
    const REQUEST_LOG_ENABLED_KEY = "request_log_enabled";

    /**
     * Checks if Request Log is disabled or not, cached
     *
     * @param bool $cached If true, try to use the cache.
     *
     * @return bool
     */
    public function isRequestLogEnabled($cached = true): bool
    {
        if ($cached) {
            return Cache::remember('request-log.enabled', 10, function () {
                return $this->isRequestLogEnabled(false);
            });
        }

        $option = RequestLogOption::where('name', self::REQUEST_LOG_ENABLED_KEY)->first();

        if ($option) {
            return $option->value === 'true';
        }

        return $this->createDefaultEnabledKey();
    }

    /** Create default enabled key, returns if enabled or not.
     * @return bool
     */
    protected function createDefaultEnabledKey(): bool
    {
        // insert default option if it doesn't exist
        $option = new RequestLogOption();
        $option->name = self::REQUEST_LOG_ENABLED_KEY;
        $enabled = config('request-log.enabled');
        $option->value = $enabled ? 'true' : 'false';
        $option->save();

        return $enabled;
    }

    /**
     * Toggle Request Log Enabled/Disabled
     */
    public function toggleRequestLogEnabled()
    {
        $requestLogOption = RequestLogOption::where('name', self::REQUEST_LOG_ENABLED_KEY)->firstOrFail();
        $requestLogOption->value = $requestLogOption->value === 'true' ? 'false' : 'true';
        $requestLogOption->save();
    }
}

<?php

namespace Cego\RequestLog\Services;

use Cego\RequestLog\Models\RequestLogOption;
use Illuminate\Support\Facades\Cache;

class RequestLogOptionsService {
    const REQUEST_LOG_ENABLED_KEY = "request_log_enabled";

    /**
     * Checks if Request Log is disabled or not, cached
     *
     * @return bool
     */
    public function isRequestLogEnabledCached(): bool
    {
        return Cache::remember('request-log.enabled', 10, function () {
            return $this->isRequestLogEnabled();
        });
    }


    /**
     * Checks if Request Log is disabled or not
     *
     * @return bool
     */
    public function isRequestLogEnabled(): bool
    {
        $option = RequestLogOption::where('name', self::REQUEST_LOG_ENABLED_KEY)->first();
        if ($option) {
            return $option->value === 'true';
        }

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
    public function toggleRequestLogEnabled(){
        $requestLogOption = RequestLogOption::where('name', self::REQUEST_LOG_ENABLED_KEY)->firstOrFail();
        $requestLogOption->value = $requestLogOption->value === 'true' ? 'false' : 'true';
        $requestLogOption->save();
    }
}
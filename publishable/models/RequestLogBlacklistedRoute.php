<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RequestLogBlacklistedRoute
 *
 * @property string $path
 */
class RequestLogBlacklistedRoute extends Model
{
    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        // We make sure to bust the blacklisted routes cache whenever a new entry is created
        // or an existing one is updated
        static::saved(function () {
            Cache::forget('request-log.blacklistedUrls');
        });
    }
}

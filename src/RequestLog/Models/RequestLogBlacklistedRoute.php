<?php

namespace Cego\RequestLog\Models;

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

}

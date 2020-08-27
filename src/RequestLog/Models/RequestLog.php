<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Exceptions\InvalidFormatException;

class RequestLog extends Model
{
    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Scopes a query to only contain request logs with the given list of status codes
     *
     * @param Builder $query
     * @param array $statusCodes
     *
     * @return Builder
     */
    public function scopeWhereStatusGroup(Builder $query, array $statusCodes = [])
    {
        $statusCodes = new Collection($statusCodes);

        // We bail out here if no status codes were provided
        if ($statusCodes->isEmpty()) {
            return $query;
        }

        // We wrap the orWhere's in a closure to make they are added inside parentheses
        return $query->where(function (Builder $query) use ($statusCodes) {
            foreach ($statusCodes as $code) {
                $query->orWhere('status', 'like', sprintf('%s%%', $code));
            }
        });
    }

    /**
     * Scopes a query to only contain request logs by created_at timestamps in a specific period
     * $from and $to are both optional
     *
     * @param Builder $query
     * @param mixed $from
     * @param mixed $to
     *
     * @return Builder
     */
    public function scopeWhereCreatedAtDateBetween(Builder $query, $from = null, $to = null)
    {
        try {
            if ($from != null) {
                $from = Carbon::parse($from)->setHours(0)->setMinutes(0)->setSeconds(0);
                $query = $query->whereDate('created_at', '>=', $from);
            }

            if ($to != null) {
                $to = Carbon::parse($to)->setHours(23)->setMinutes(59)->setSeconds(59);
                $query = $query->whereDate('created_at', '<=', $to);
            }
        } catch (InvalidFormatException $exception) {
            Log::error(sprintf('Failed Carbon::parse() in scopeWhereCreatedAtDateBetween() from:[%s] to:[%s]', $from, $to));
        } finally {
            return $query;
        }
    }
}

<?php

namespace Cego\RequestLog\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Cego\RequestLog\Models\RequestLog;

class RequestLogController extends Controller
{
    /**
     * Frontend view for displaying and index of RequestLogs
     *
     * @param Request $request
     *
     * @return View|Factory
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        // Flash the request parameters, so we can redisplay the same filter parameters.
        $request->flash();

        // Paginate the filtered query, and only fetch the required data.
        $requestLogs = $this->getFilteredLogQuery($request)->paginate(25, [
            "id",
            "method",
            "status",
            "path",
            "query_string",
            "execution_time",
            "created_at"
        ]);

        $segmentedNumberOfStatuses = $this->getSegmentedNumberOfStatuses();

        $isEnabled = Cache::get('request-log.enabled');

        return view("request-logs::index")->with([
            "requestLogs"  => $requestLogs,
            'isEnabled'    => $isEnabled,
            'numberOf1XXs' => $segmentedNumberOfStatuses->get('one'),
            'numberOf2XXs' => $segmentedNumberOfStatuses->get('two'),
            'numberOf3XXs' => $segmentedNumberOfStatuses->get('three'),
            'numberOf4XXs' => $segmentedNumberOfStatuses->get('four'),
            'numberOf5XXs' => $segmentedNumberOfStatuses->get('five'),
        ]);
    }

    /**
     * Frontend view for displaying a single full requestLog
     *
     * @param RequestLog $requestLog
     *
     * @return View|Factory
     */
    public function show(RequestLog $requestLog)
    {
        $isEnabled = Cache::get('request-log.enabled');

        if ($isEnabled) {
            return view("request-logs::show")->with(["requestLog" => $requestLog]);
        }

        return redirect()->route('request-logs.index');
    }

    /**
     * Toggles if Request logging is enabled
     *
     * @return mixed
     */
    public function toggle()
    {
        Cache::set('request-log.enabled', Cache::get('request-log.enabled') == false);

        return redirect()->route('request-logs.index');
    }

    /**
     * Deletes all request logs
     *
     * @return mixed
     */
    public function delete()
    {
        RequestLog::query()->delete();

        return redirect()->route('request-logs.index');
    }

    /**
     * Returns an Eloquent query where all filters have been applied.
     *
     * @param Request $request
     *
     * @return Builder
     *
     * @throws Exception
     */
    protected function getFilteredLogQuery(Request $request)
    {
        return RequestLog::latest()
            ->whereStatusGroup($this->getFilteredStatusGroups($request))
            ->whereCreatedAtDateBetween($request->get("from"), $request->get("to"));
    }

    /**
     * Check the request for status groups the user wishes to filter on.
     * Convert the query into a usable array that matches the required format of RequestLogs::statusGroups
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getFilteredStatusGroups(Request $request)
    {
        $groups = collect(["1XX", "2XX", "3XX", "4XX", "5XX"]);

        return $groups

            // Filter the check boxes to only include the ones that are "on"
            ->filter(function ($group) use ($request) {
                return $request->get($group) == "on";
            })

            // Map each group to the first char of the status group
            ->map(function ($group) {
                return substr($group, 0, 1);
            })

            ->toArray();
    }

    /**
     * Gets a collection of segmented number of requests
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getSegmentedNumberOfStatuses()
    {
        $query = <<<SQL
select 
	a.one, 
	b.two,
	c.three,
	d.four,
	e.five
from 
	(
		select count(*) as one 
		from request_logs
		where status between 100 AND 199
	) as a,
	(
		select count(*) as two 
		from request_logs 
		where status between 200 AND 299
	) as b,
	(
		select count(*) as three 
		from request_logs 
		where status between 300 AND 399
	) as c,
	(
		select count(*) as four 
		from request_logs 
		where status between 400 AND 499
	) as d,
	(
		select count(*) as five 
		from request_logs 
		where status between 500 AND 599
	) as e
SQL;

        return collect(DB::select($query)[0]);
    }

    /**
     * Frontend view for displaying a single full requestLog
     *
     * @param RequestLog $requestLog
     *
     * @return View|Factory
     * @throws Exception
     */
    public function destroy(RequestLog $requestLog)
    {
        $requestLog->delete();

        return redirect()->back();
    }
}

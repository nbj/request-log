<?php

namespace Nbj\RequestLog\Controllers;

use App\RequestLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RequestLogController extends Controller
{
    /**
     * Frontend view for displaying and index of RequestLogs
     *
     * @param Request $request
     *
     * @return View|Factory
     *
     * @throws \Exception
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

        return view("request-logs::index")->with(["requestLogs" => $requestLogs]);
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
        return view("request-logs::show")->with(["requestLog" => $requestLog]);
    }


    /**
     * Returns an Eloquent query where all filters have been applied.
     *
     * @param Request $request
     *
     * @return Builder
     *
     * @throws \Exception
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
}

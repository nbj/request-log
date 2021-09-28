<?php

namespace Cego\RequestLog\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Cego\RequestLog\Models\RequestLogBlacklistedRoute;

class BlacklistedRoutesController extends Controller
{
    /**
     * Shows the list of black listed routes
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        // Flash the request parameters, so we can redisplay the same filter parameters.
        $request->flash();

        $routes = RequestLogBlacklistedRoute::latest()->paginate(25);

        return view('request-logs::blacklisted-routes.index')->with(['routes' => $routes]);
    }

    /**
     * Displays the form for adding a new route to the blacklist
     *
     * @return mixed
     */
    public function create()
    {
        return view('request-logs::blacklisted-routes.create');
    }

    /**
     * Stores a new route posted from the create form
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        RequestLogBlacklistedRoute::create([
            'path' => $request->get('path')
        ]);

        return redirect()->route('blacklisted-routes.index');
    }

    /**
     * Deletes a route from the blacklist
     *
     * @param RequestLogBlacklistedRoute $blacklisted_route
     *
     * @return mixed
     */
    public function destroy(RequestLogBlacklistedRoute $blacklisted_route)
    {
        $blacklisted_route->delete();

        return redirect()->route('blacklisted-routes.index');
    }
}

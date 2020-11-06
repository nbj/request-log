@extends("request-logs::layout.master")

@section("content")

    <div class="container-flex">
        <div class="row">
            <div class="col-12">

                <div class="pt-5">
                    <div class="form-group form-inline">
                        <h1 class="">Request Logs: </h1>
                        <div class="float-right">
                            <a href="{{ route('request-logs.toggle') }}" class="btn ml-2 {{ $isEnabled ? 'disabled btn-secondary' : 'btn-success' }}">Enable</a>
                            <a href="{{ route('request-logs.toggle') }}" class="btn ml-2 {{ $isEnabled ? 'btn-danger' : 'disabled btn-secondary' }}">Disable</a>
                        </div>
                    </div>
                    <div class="pb-5">
                        <div class="mr-5">
                            <div class="badge mr-2">{{ $requestLogs->total() }}</div><span class="mr-5"><strong>RequestLogs in total</strong></span>
                            <div class="badge badge-secondary mr-2">{{ $numberOf1XXs }}</div><span class="mr-4"><strong>1XX</strong></span>
                            <div class="badge badge-success mr-2">{{ $numberOf2XXs }}</div><span class="mr-4"><strong>2XX</strong></span>
                            <div class="badge badge-info mr-2">{{ $numberOf3XXs }}</div><span class="mr-4"><strong>3XX</strong></span>
                            <div class="badge badge-warning mr-2">{{ $numberOf4XXs }}</div><span class="mr-4"><strong>4XX</strong></span>
                            <div class="badge badge-danger mr-2">{{ $numberOf5XXs }}</div><span class="mr-5"><strong>5XX</strong></span>
                            <form method="post" action="{{ route('request-logs.delete') }}">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="delete">
                                <button type="submit" class="ml-3 btn btn-sm btn-danger float-right">Clear all logs</button>
                            </form>
                            <a href="{{ route('blacklisted-routes.index') }}" class="btn btn-info btn-sm float-right">Manage blacklisted routes</a>
                        </div>
                    </div>

                    <div class="{{ $isEnabled ? 'visible' : 'invisible' }}">
                        <div class="clearfix mb-3">
                            <form method="get" class="float-right form-inline">
                                <div class="form-group mr-5">
                                    <label class="form-check-label mr-3">
                                        From: <input class="form-control ml-2" type="date" name="from" style="width: 150px" placeholder="dd-mm-yyyy" value="{{ old("from") }}">
                                    </label>
                                    <label class="form-check-label">
                                        To: <input class="form-control ml-2" type="date" name="to" style="width: 150px" placeholder="dd-mm-yyyy" value="{{ old("to") }}">
                                    </label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input check-lg" type="checkbox" name="1XX" {{ old("1XX") === "on" ? "checked" : "" }}> 1XX
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input check-lg" type="checkbox" name="2XX" {{ old("2XX") === "on" ? "checked" : "" }}> 2XX
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input check-lg" type="checkbox" name="3XX" {{ old("3XX") === "on" ? "checked" : "" }}> 3XX
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input check-lg" type="checkbox" name="4XX" {{ old("4XX") === "on" ? "checked" : "" }}> 4XX
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input check-lg" type="checkbox" name="5XX" {{ old("5XX") === "on" ? "checked" : "" }}> 5XX
                                    </label>
                                </div>

                                <button class="btn btn-primary" type="submit">Filter</button>
                                <a href="{{ url()->current() }}" class="btn btn-secondary ml-2">Clear Filters</a>
                            </form>
                        </div>

                        <table class="table table-hover border bg-white">
                            <thead>
                            <tr>
                                <th>id</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Path</th>
                                <th>Query</th>
                                <th>Execution Time</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($requestLogs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->method }}</td>
                                    <td><x-request-log-status-code code="{{ $log->status }}"/></td>
                                    <td>/{{ $log->path }}</td>
                                    <td>{{ $log->query_string }}</td>
                                    <td>{{ number_format($log->execution_time, 4) }}</td>
                                    <td>{{ $log->created_at }}</td>
                                    <td>
                                        <a href="{{ route('request-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">Inspect</a>
                                        <form class="d-inline-block" method="POST" action="{{ route('request-logs.destroy', $log) }}">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 ml-1" style="font-size: 0.8em;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-center">
                            {{ $requestLogs->appends(Arr::except(Request::query(), "page"))->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

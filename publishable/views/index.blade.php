@extends("request-logs::layout.master")

@section("content")

    <div class="container-flex">
        <div class="row">
            <div class="col-12">

                <div class="pt-5">
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
                            <th>Log</th>
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
                                <td><a href="/request-logs/{{ $log->id }}" class="btn btn-sm btn-outline-primary">Full</a>
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
@endsection

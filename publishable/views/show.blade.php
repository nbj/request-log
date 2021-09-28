@extends("request-logs::layout.master")

@section("content")

    <div class="container-flex mt-5 col-12">
        <div class="row">

            <div class="form-group form-inline col-12">
                <h1 class="">Inspecting Request Log: <strong>#{{ $requestLog->id }}</strong></h1>
                <div class="float-right">
                    <a href="{{ route('request-logs.index') }}" class="btn ml-2 btn-danger">Back</a>
                </div>
            </div>

            <!-- Client -->
            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title text-center">
                            <h3>Client</h3>
                            <hr>
                        </div>
                        <div class="card-text">
                            <table class="table-hover w-100 table-vertical">
                                <tbody>
                                <tr>
                                    <td>Request Id:</td>
                                    <td>{{ $requestLog->id }}</td>
                                </tr>
                                <tr>
                                    <td>Client IP:</td>
                                    <td>{{ $requestLog->client_ip }}</td>
                                </tr>
                                <tr>
                                    <td>Agent:</td>
                                    <td>{{ $requestLog->user_agent }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endpoint -->
            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title text-center">
                            <h3>Endpoint</h3>
                            <hr>
                        </div>
                        <div class="card-text">
                            <table class="table-hover w-100 table-vertical">
                                <tbody>
                                <tr>
                                    <td>Request:</td>
                                    <td>{{ $requestLog->method }} <x-request-log-status-code code="{{ $requestLog->status }}"/> /{{ $requestLog->path }}</td>
                                </tr>
                                <tr>
                                    <td>Root:</td>
                                    <td>{{ $requestLog->root }}</td>
                                </tr>
                                <tr>
                                    <td>Time:</td>
                                    <td>{{ number_format($requestLog->execution_time, 4) }} seconds</td>
                                </tr>
                                <tr>
                                    <td>Query:</td>
                                    <td><x-request-log-pretty-print :content="$requestLog->query_string"/></td>
                                </tr>
                                <tr>
                                    <td>Date:</td>
                                    <td>{{ $requestLog->created_at }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row mt-4">

            <!-- Header -->
            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title text-center">
                            <h3>Request</h3>
                            <hr>
                        </div>
                        <div class="card-text">
                            <table class="table-hover w-100 table-vertical">
                                <tbody>
                                <tr>
                                    <td>Header:</td>
                                    <td><x-request-log-pretty-print :content="$requestLog->request_headers"/></td>
                                </tr>
                                <tr>
                                    <td>Body:</td>
                                    <td><x-request-log-pretty-print :content="$requestLog->request_body"/></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Response -->
            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title text-center">
                            <h3>Response</h3>
                            <hr>
                        </div>
                        <div class="card-text">
                            <table class="table-hover w-100 table-vertical">
                                <tbody>
                                <tr>
                                    <td>Header:</td>
                                    <td><x-request-log-pretty-print :content="$requestLog->response_headers"/></td>
                                </tr>
                                <tr>
                                    <td>Exception:</td>
                                    <td><x-request-log-pretty-print :content="$requestLog->response_exception"/></td>
                                </tr>
                                <tr>
                                    <td>Body:</td>
                                    <td><x-request-log-pretty-print :content="$requestLog->response_body"/></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

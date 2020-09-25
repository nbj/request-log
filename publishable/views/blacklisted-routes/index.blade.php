@extends("request-logs::layout.master")

@section("content")
    <div class="container-flex">
        <div class="row">
            <div class="col-12">

                <div class="pt-5">
                    <div class="form-group form-inline">
                        <h1 class="">Request Logs - Blacklisted routes: </h1>
                        <div class="float-right">
                            <a href="{{ route('request-logs.index') }}" class="btn ml-2 btn-danger">Back</a>
                        </div>
                    </div>

                    <div>
                        <div class="clearfix mb-3">

                        </div>

                        <table class="table table-hover border bg-white">
                            <thead>
                            <tr>
                                <th>id</th>
                                <th>Path</th>
                                <th>Created at</th>
                                <th>Updated at</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($routes as $route)
                                <tr>
                                    <td>{{ $route->id }}</td>
                                    <td>{{ $route->path }}</td>
                                    <td>{{ $route->created_at }}</td>
                                    <td>{{ $route->updated_at }}</td>
                                    <td>
                                        <form method="post" action="{{ route('blacklisted-routes.destroy', $route) }}">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="_method" value="delete">
                                            <button type="submit" class="btn btn-sm btn-danger float-right">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div class="form-group form-inline">
                            <div class="float-right">
                                <a href="{{ route('blacklisted-routes.create') }}" class="btn ml-2 btn-info">Add route</a>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $routes->appends(Arr::except(Request::query(), "page"))->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

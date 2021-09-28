@extends("request-logs::layout.master")

@section("content")
    <div class="container-flex">
        <div class="row">
            <div class="col-12">

                <div class="pt-5">
                    <div class="mb-5">
                        <h1 class="">Request Logs - Blacklisted routes - Add route </h1>
                        <span><strong>Use `*` as a wildcard. e.g. `request-logs/*`</strong></span>
                    </div>

                    <div>
                        <form method="post" action="{{ route('blacklisted-routes.store') }}" class="form-inline mb-5">
                            <div class="">
                                <div class="form-group">
                                    <label class="form-check-label mr-3">
                                        Path: <input class="form-control ml-2" style="width: 600px" type="text" name="path" placeholder="Path" value="{{ old("path") }}">
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="">
                                    <button type="submit" class="btn ml-2 btn-info">Add route</button>
                                </div>

                                <div class="">
                                    <a href="{{ route('blacklisted-routes.index') }}" class="btn ml-2 btn-danger">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

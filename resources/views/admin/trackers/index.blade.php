@extends('admin.layout')

@section('admin-title')
    Tracker Card Queue
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Tracker Queue' => 'admin/trackers/pending']) !!}

    <h1>Tracker Card Queue</h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/trackers/pending*') }} {{ set_active('admin/trackers') }}" href="{{ url('admin/trackers') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/trackers/approved*') }}" href="{{ url('admin/trackers/approved') }}">Approved</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/trackers/rejected*') }}" href="{{ url('admin/trackers/rejected') }}">Rejected</a>
        </li>
    </ul>


    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select(
                'sort',
                [
                    'newest' => 'Newest First',
                    'oldest' => 'Oldest First',
                ],
                Request::get('sort') ?: 'oldest',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    {!! Form::close() !!}

    {!! $trackers->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-md-2">
                    <div class="logs-table-cell">User</div>
                </div>
                <div class="col-md-2">
                    <div class="logs-table-cell">Character</div>
                </div>
                <div class="col-md-3">
                    <div class="logs-table-cell">Link</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="logs-table-cell">Submitted</div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="logs-table-cell">Status</div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="logs-table-cell"></div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($trackers as $tracker)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-md-2">
                            <div class="logs-table-cell">{!! $tracker->user->displayName !!}</div>
                        </div>
                        <div class="col-md-2">
                            <div class="logs-table-cell">{!! $tracker->character->displayName !!}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="logs-table-cell">
                                <span class="ubt-texthide"><a href="{{ $tracker->url }}">{{ $tracker->url }}</a></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">{!! pretty_date($tracker->created_at) !!}</div>
                        </div>
                        <div class="col-3 col-md-1">
                            <div class="logs-table-cell">
                                <span class="btn btn-{{ $tracker->status == 'Pending' ? 'secondary' : ($tracker->status == 'Approved' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $tracker->status }}</span>
                            </div>
                        </div>
                        <div class="col-3 col-md-1">
                            <div class="logs-table-cell"><a href="{{ $tracker->adminUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {!! $trackers->render() !!}
    <div class="text-center mt-4 small text-muted">{{ $trackers->total() }} result{{ $trackers->total() == 1 ? '' : 's' }} found.</div>
@endsection

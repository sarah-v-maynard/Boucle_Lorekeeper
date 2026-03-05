@extends('layouts.app')

@section('title')
    Tracker Card (#{{ $tracker->id }})
@endsection

@section('content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Tracker Queue' => 'admin/trackers/', 'Tracker (#' . $tracker->id . ')' => $tracker->viewUrl]) !!}
    @if ($tracker->status == 'Pending' && $tracker->staff_id && $tracker->updated_at)
        <div class="alert alert-info">You've requested an edit to this card.</div>
    @endif
    <h1>
        Tracker Card (#{{ $tracker->id }})
        <span class="float-right badge badge-{{ $tracker->status == 'Pending' || $tracker->status == 'Draft' ? 'secondary' : ($tracker->status == 'Approved' ? 'success' : 'danger') }}">
            {{ $tracker->status }}
        </span>
    </h1>

    <div class="mb-1">
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>User</h5>
            </div>
            <div class="col-md-10 col-8">{!! $tracker->user->displayName !!}</div>
        </div>
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>URL</h5>
            </div>
            <div class="col-md-10 col-8"><a href="{{ $tracker->url }}">{{ $tracker->url }}</a></div>
        </div>
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>Submitted</h5>
            </div>
            <div class="col-md-10 col-8">{!! format_date($tracker->created_at) !!} ({{ $tracker->created_at->diffForHumans() }})</div>
        </div>
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>Character</h5>
            </div>
            <div class="col-md-10 col-8">{!! $tracker->character->getDisplayNameAttribute() !!}</div>
        </div>
    </div>
    {!! Form::open(['url' => url()->current(), 'id' => 'trackerForm']) !!}
    {!! Form::hidden('tracker_id', $tracker->id, ['class' => 'form-control']) !!}
    @include('tracker._tracker_card', ['tracker' => $tracker, 'editable' => $editable])

    <div class="card my-3">
        @if (Auth::check() && $tracker->staff_comments && ($tracker->user_id == Auth::user()->id || Auth::user()->hasPower('manage_submissions')))
            <div class="card-header h2">Staff Comments</div>
            <div class="card-body">
                @if (isset($tracker->staff_comments))
                    {!! $tracker->staff_comments !!}
                @else
                    {!! $tracker->staff_comments !!}
                @endif
            </div>
        @endif
    </div>

    @if ($tracker->status !== 'Pending' && Auth::user()->id === $tracker->user->id && $editable)
        <div class="text-right">
            <a href="#" class="btn btn-secondary mr-2" id="requestButton">Submit Edit</a>
        </div>

        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content" id="requestContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Request an Edit</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will request an edit to the current card. Great for if an error got through, or perhaps there was an update with the counting system.</p>
                        <div class="text-right">
                            <a href="#" id="requestSubmit" class="btn btn-secondary">Request an Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {!! Form::close() !!}

    @if (!$editable && $tracker->status === 'Approved' && Auth::user()->id === $tracker->user->id)
        <div class="text-right">
            <a class="btn btn-warning" href="{{ url()->current() . '/edit' }}">Request an Edit</a>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    @if ($tracker->status !== 'Pending')
        @include('js._character_select_js')

        <script>
            $(document).ready(function() {



                var $confirmationModal = $('#confirmationModal');
                var $trackerForm = $('#trackerForm');

                var $requestButton = $('#requestButton');
                var $requestSubmit = $('#requestSubmit');


                $requestButton.on('click', function(e) {
                    e.preventDefault();
                    console.log('clicked!')
                    $confirmationModal.modal('show');
                });

                $requestSubmit.on('click', function(e) {
                    e.preventDefault();
                    $trackerForm.attr('action', '{{ url('tracker/' . $tracker->id) }}/request-edit');
                    $trackerForm.submit();
                });

            });
        </script>
    @endif
@endsection

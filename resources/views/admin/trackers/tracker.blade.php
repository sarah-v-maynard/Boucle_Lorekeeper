@extends('admin.layout')

@section('admin-title')
    Tracker Card (#{{ $tracker->id }})
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Tracker Queue' => 'admin/trackers/', 'Tracker (#' . $tracker->id . ')' => $tracker->viewUrl]) !!}



    @if ($tracker->status == 'Pending')
        <h1>
            Tracker Card (#{{ $tracker->id }})
            <span class="float-right badge badge-{{ $tracker->status == 'Pending' || $tracker->status == 'Draft' ? 'secondary' : ($tracker->status == 'Approved' ? 'success' : 'danger') }}">
                {{ $tracker->status }}
            </span>
        </h1>
        @if ($tracker->data_temp)
            <div class="text-right mt-2">
                <h3><span class="badge badge-info">Edit Requested</span></h3>
            </div>
        @endif

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
        </div>

        <!-- Tracker Content -->
        <style>
            .line-rows .line-item:nth-of-type(odd) {
                background-color: var(--gray-800);
            }
        </style>

        {!! Form::open(['url' => url()->current(), 'id' => 'trackerForm']) !!}

        <div class="card my-3">
            <h4 class="card-header">Tracker Card Count
                @if ($tracker->data_temp)
                    <a href="{{ $tracker->viewUrl }}" class="btn float-right btn-sm btn-secondary">Original Card</a>
                @endif
            </h4>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <!-- Gallery or optional image src here -->
                        @if ($gallery)
                            <h5>Gallery</h5>
                            <a href="{{ $gallery->url }}">
                                <img src="{{ $gallery->getThumbnailUrlAttribute() }}" class="img-fluid mb-2" />
                                <br>
                                {!! $gallery->displayName !!}
                            </a>
                        @else
                            <h5>External Submission</h5>
                            <a href="{{ $tracker->url }}" target="_blank"><img src="/images/account.png" class="img-fluid" /></a>
                        @endif
                        <hr />
                        <img src="{!! $tracker->character->image->thumbnailUrl !!}" alt="{!! $tracker->character->fullName !!}" class="img-fluid mt-3" />
                        <div class="row">
                            <div class="col-md-6 col-6">
                                <h5>Character</h5>
                            </div>
                            <div class="col-md-6 col-6">{!! $tracker->character->displayName !!}</div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="px-4 line-rows">
                            <?php
                            $total = 0;
                            $i = 0;
                            ?>
                            @if (count($cardData) > 1)
                                <div class="accordion" id="MultiTracker">
                                    <?php $sb = 0; ?>
                                    @foreach ($cardData as $title => $value)
                                        @if (gettype($value) === 'array')
                                            <?php $ssb = 0; ?>
                                            @foreach ($value as $sub_title => $sub_value)
                                                <div class="card">
                                                    <div class="card-header" id="subHeading-{{ $sb }}">
                                                        <h4 class="mb-0">
                                                            <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse-{{ $sb }}" aria-expanded="true"
                                                                aria-controls="collapse-{{ $sb }}">Sub-Tracker #{{ $sb }}</button>
                                                        </h4>
                                                    </div>
                                                    <div id="collapse-{{ $sb }}" class="collapse {{ $sb === 0 ? 'show' : '' }}" aria-labelledby="subHeading-{{ $sb }}" data-parent="#MultiTracker">
                                                        <div class="card-body">
                                                            @include('tracker._tracker_group', ['title' => $sub_title, 'cardData' => $sub_value, 'i' => $i])
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php $ssb++; ?>
                                            @endforeach
                                        @endif
                                        <?php $sb++; ?>
                                    @endforeach
                                </div>
                            @else
                                @foreach ($cardData as $title => $value)
                                    @include('tracker._tracker_group', ['title' => $title, 'value' => $value, 'i' => $i])
                                    <?php
                                    $i++;
                                    ?>
                                @endforeach
                            @endif
                        </div>
                        <div class="text-right">
                            <a href="#" id="addGroup" class="btn btn-sm btn-primary mt-2">Add Group</a>
                            <a href="#" id="addLine" class="btn btn-sm btn-primary mt-2">Add Line</a>
                        </div>
                    </div>
                </div>
                <div class="card-footer mt-3">
                    <div class="w-100 d-inline-flex justify-content-between p-2">
                        <h5 class="lh-1">Total {{ __('art_tracker.xp') }}</h5>
                        <p class="lh-1"><span class="xp-total">{!! $total !!}</span> {{ __('art_tracker.xp') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('staff_comments', 'Staff Comments (Optional)') !!}
            {!! Form::textarea('staff_comments', $tracker->staffComments, ['class' => 'form-control wysiwyg']) !!}
        </div>

        <div class="text-right">
            <a href="#" class="btn btn-danger mr-2" id="rejectionButton">Reject</a>
            <a href="#" class="btn btn-secondary mr-2" id="cancelButton">Cancel</a>
            <a href="#" class="btn btn-success" id="approvalButton">Approve</a>
        </div>

        {!! Form::close() !!}


        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content hide" id="approvalContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Approval</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will approve the card and update the character.</p>
                        <div class="text-right">
                            <a href="#" id="approvalSubmit" class="btn btn-success">Approve</a>
                        </div>
                    </div>
                </div>
                <div class="modal-content hide" id="cancelContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Cancellation</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will cancel the card and send it back to drafts. Make sure to include a staff comment if you do this!</p>
                        <div class="text-right">
                            <a href="#" id="cancelSubmit" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
                <div class="modal-content hide" id="rejectionContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Rejection</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will reject the card.</p>
                        <div class="text-right">
                            <a href="#" id="rejectionSubmit" class="btn btn-danger">Reject</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-danger">This card has already been processed.</div>
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
        </div>
        @include('tracker._tracker_card', ['tracker' => $tracker, 'editable' => $tracker->status == 'Pending' ? true : false])

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
    @endif

    <div class="template hide">
        <!-- Grouped Template -->
        <div class="line-group border border-secondary my-2">
            <div class="line-header p-2">
                <div class="d-flex justify-content-between mb-2">
                    <h5>Group</h5>
                    <a href="#" class="remove-group btn btn-sm btn-danger ml-2">-</a>
                </div>
                {!! Form::text('card[__INDEX__][title]', null, ['class' => 'form-control']) !!}
            </div>
            <hr class="my-1 border border-secondary" />
            <div class="line-item w-100 d-inline-flex align-items-center justify-content-between p-2">
                {!! Form::text('card[__INDEX__][sub_card][__SUB_INDEX__][title]', null, ['class' => 'form-control']) !!}
                {!! Form::number('card[__INDEX__][sub_card][__SUB_INDEX__][value]', 1, ['class' => 'form-control w-25 ml-2']) !!} <span class="badge ml-2">{{ __('art_tracker.xp') }}</span>
                <a href="#" class="remove-line btn btn-sm btn-danger ml-2">-</a>
            </div>
            <div class="text-right">
                <a href="#" id="addSubLine" class="btn btn-sm btn-primary m-2">Add Sub Line</a>
            </div>
        </div>
        <!-- Single Line Template -->
        <div class="line-item w-100 d-inline-flex align-items-center justify-content-between p-2">
            {!! Form::text('card[__INDEX__][title]', null, ['class' => 'form-control']) !!}
            {!! Form::number('card[__INDEX__][value]', 1, ['class' => 'form-control w-25 ml-2']) !!} <span class="badge ml-2">{{ __('art_tracker.xp') }}</span>
            <a href="#" class="remove-line btn btn-sm btn-danger ml-2">-</a>
        </div>
    </div>

@endsection

@section('scripts')
    @parent
    @if ($tracker->status == 'Pending')
        @include('js._character_select_js')

        <script>
            $(document).ready(function() {
                var $confirmationModal = $('#confirmationModal');
                var $trackerForm = $('#trackerForm');

                var $approvalButton = $('#approvalButton');
                var $approvalContent = $('#approvalContent');
                var $approvalSubmit = $('#approvalSubmit');

                var $rejectionButton = $('#rejectionButton');
                var $rejectionContent = $('#rejectionContent');
                var $rejectionSubmit = $('#rejectionSubmit');

                var $cancelButton = $('#cancelButton');
                var $cancelContent = $('#cancelContent');
                var $cancelSubmit = $('#cancelSubmit');

                $approvalButton.on('click', function(e) {
                    e.preventDefault();
                    $approvalContent.removeClass('hide');
                    $rejectionContent.addClass('hide');
                    $cancelContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $rejectionButton.on('click', function(e) {
                    e.preventDefault();
                    $rejectionContent.removeClass('hide');
                    $approvalContent.addClass('hide');
                    $cancelContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $cancelButton.on('click', function(e) {
                    e.preventDefault();
                    $cancelContent.removeClass('hide');
                    $rejectionContent.addClass('hide');
                    $approvalContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $approvalSubmit.on('click', function(e) {
                    e.preventDefault();
                    $trackerForm.attr('action', '{{ url()->current() }}/approve');
                    $trackerForm.submit();
                });

                $rejectionSubmit.on('click', function(e) {
                    e.preventDefault();
                    $trackerForm.attr('action', '{{ url()->current() }}/reject');
                    $trackerForm.submit();
                });

                $cancelSubmit.on('click', function(e) {
                    e.preventDefault();
                    $trackerForm.attr('action', '{{ url()->current() }}/cancel');
                    $trackerForm.submit();
                });

                // Tracker editor JS
                $('#addLine').on('click', function(e) {
                    e.preventDefault();
                    var index = $('.line-rows .line-item, .line-rows .line-group').length;
                    var template = $('.template > .line-item').prop('outerHTML').replace(/__INDEX__/g, index);
                    $('.line-rows').append(template);
                });

                $('#addGroup').on('click', function(e) {
                    e.preventDefault();
                    var index = $('.line-rows .line-item, .line-rows .line-group').length;
                    var template = $('.template > .line-group').prop('outerHTML').replace(/__INDEX__/g, index).replace(/__SUB_INDEX__/g, 0);
                    $('.line-rows').append(template);
                });

                $(document).on('click', '#addSubLine', function(e) {
                    e.preventDefault();
                    var $group = $(this).closest('.line-group');
                    var index = $('.line-rows .line-item, .line-rows .line-group').length;
                    var subIndex = $group.find('.line-item').length;
                    var template = $('.template > .line-item').prop('outerHTML')
                        .replace(/__INDEX__/g, index)
                        .replace(/__SUB_INDEX__/g, subIndex);
                    $group.find('.text-right').before(template);
                });

                $(document).on('click', '.remove-line', function(e) {
                    e.preventDefault();
                    $(this).closest('.line-item').remove();
                });

                $(document).on('click', '.remove-group', function(e) {
                    e.preventDefault();
                    $(this).closest('.line-group').remove();
                });

            });
        </script>
    @endif
@endsection

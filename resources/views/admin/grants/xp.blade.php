@extends('admin.layout')

@section('admin-title')
    Grant Character {{ __('art_tracker.xp') }}
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Grant ' . __('art_tracker.xp') => 'admin/grants/xp']) !!}

    <h1>Grant {{ __('art_tracker.xp') }}</h1>

    {!! Form::open(['url' => 'admin/grants/xp']) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('characters[]', 'Character(s)') !!} {!! add_help('You can select up to 10 characters at once.') !!}
        {!! Form::select('characters[]', $characters, null, ['id' => 'characterList', 'class' => 'form-control', 'multiple']) !!}
    </div>

    <div class="form-group">
        <p>Grant the {{ __('art_tracker.experience_points') }} to all characters here. You can either select a status and it will award the minimum amount for that status OR you can enter in a custom amount. If you enter in both the amount will be added
            together.</p>
        <div class="row">
            <div class="col-md-6">
                {!! Form::label('levels', 'Levels(s)') !!} {!! add_help('Select a level to set the selected character(s) to.') !!}
                {!! Form::select('levels', array_flip((array) $levels), null, ['id' => 'levelList', 'class' => 'form-control']) !!}
            </div>
            <div class="col-md-6">
                {!! Form::label('static_xp', 'Add Static ' . __('art_tracker.xp')) !!} {!! add_help('Enter in an amount to grant to the selected character(s).') !!}
                {!! Form::number('static_xp', 0, ['class' => 'form-control w-100', 'placeholder' => 'Amount of ' . __('art_tracker.xp') . ' to add']) !!}
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">Selected Character(s) will gain <span id="total">0</span> {{ __('art_tracker.xp') }}.</div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the logs and in the inventory description.') !!}
        {!! Form::text('data', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
    </div>

    <div class="text-right">
        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <script>
        $(document).ready(function() {
            $('#characterList').selectize({
                maxItems: 100
            });

            var total = {
                level: 0,
                static: 0,
            };

            $('#levelList').on('change', function() {
                var levelPoints = $(this).val();
                total.level = parseFloat(levelPoints);
                updateTotals();
            });
            $('#static_xp').on('change', function() {
                var staticPoints = $(this).val();
                total.static = parseFloat(staticPoints);
                updateTotals();
            });

            function updateTotals() {
                var final = total.static + total.level;
                $('#total').text(final);
            }

        });
    </script>
@endsection

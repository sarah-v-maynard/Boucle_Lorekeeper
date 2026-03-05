@extends('layouts.app')

@section('title')
    Submit {{ __('art_tracker.xp') }} Tracker
@endsection

@section('content')
    {!! breadcrumbs([__('art_tracker.xp') . ' Calculator' => '/submit-xp']) !!}
    <h1>Submit Tracker</h1>
    <p>Fill out the corresponding options for your artwork/literature. Ensure to double check your points on the totals box and select a character. An admin will process your tracker card for approval. Leave fields blank if they do not apply to your
        submission.</p>

    <div class="site-page-content parsed-text">
        <pre style="background-color:#ccc;display:none;">
            {{ print_r($xp_calc_form, true) }}
        </pre>

        {!! Form::open(['url' => 'submit-xp', 'files' => false]) !!}
        <div class="row">
            <div class="col-md-8">
                <div class="tracker_type d-flex mb-4">
                    <div class="card w-100 p-3 rounded border border-secondary mr-3">
                        <input type="radio" checked id="single" name="tracker_type" value="single" selected>
                        <label for="single">
                            <h5>Single Tracker</h5>
                            <p class="mb-0">For tracker cards that contain singular data. Such as artwork with a single image or literature with one stories.</p>
                        </label>
                    </div>
                    <div class="card w-100 p-3 rounded border border-secondary ml-3">
                        <input type="radio" id="multi" name="tracker_type" value="multi">
                        <label for="multi">
                            <h5>Multi Tracker</h5>
                            <p class="mb-0">For more advanced tracker cards. Great for sketch pages with multiple pieces to count or bundled literature stories.</p>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <!-- Calculator Options -->
                <div id="pointValues" class="accordion mb-4">
                    <div class="tracker_0 tracker-item" data-index="0">
                        <div class="card rounded bg-transparent border border-secondary">
                            <div class="card-header bg-dark d-flex justify-content-between align-items-center" id="0"><button class="btn h2 btn-link btn-block text-left" style="text-decoration:none;" type="button" data-toggle="collapse"
                                    data-target="#collapse-0" aria-expanded="true" aria-controls="collapse-0">Tracker #0</button><a href="#" class="remove-tracker multi-only hide btn btn-danger">-</a></div>
                            <div id="collapse-0" class="collapse show" aria-labelledby="0" data-parent="#pointValues">
                                <div class="card-body">
                                    @if ($xp_calc_form)
                                        @foreach ($xp_calc_form as $field)
                                            <div class="card mb-3" data-name="{{ $field->field_name }}">
                                                <h5 class="card-header">{{ $field->field_name }}</h5>
                                                <div class="card-body">
                                                    @if ($field->field_description)
                                                        <p>{!! $field->field_description !!}</p>

                                                        @switch($field->field_type)
                                                            @case('number')
                                                                {!! Form::number('tracker[' . $field->field_name . '][]', null, ['class' => 'form-control', 'placeholder' => 'Enter a number.']) !!}
                                                            @break

                                                            @case('radio')
                                                                @if ($field->field_options)
                                                                    @foreach ($field->field_options as $option)
                                                                        <div class="list-item">
                                                                            {!! Form::radio('tracker[0][' . $field->field_name . '][' . $option->label . '][value]', $option->point_value, false, [
                                                                                'class' => 'mr-2',
                                                                                'label' => $option->label,
                                                                                'id' => 'tracker[' . $field->field_name . '][' . $option->label . '][value]',
                                                                            ]) !!}
                                                                            {!! Form::hidden('tracker[0][' . $field->field_name . '][' . $option->label . '][label]', $option->label) !!}
                                                                            <label for="{!! 'tracker[][' . $field->field_name . '][' . $option->label . '][value]' !!}"><strong>{!! $option->label !!}</strong> ({!! $option->point_value !!} {{ __('art_tracker.xp') }})
                                                                                <br>{!! $option->description !!}</label>
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            @break

                                                            @case('checkboxes')
                                                                @if ($field->field_options)
                                                                    @foreach ($field->field_options as $option)
                                                                        <div class="list-item">
                                                                            {!! Form::checkbox('tracker[0][' . $field->field_name . '][' . $option->label . '][value]', $option->point_value, false, [
                                                                                'class' => 'mr-2',
                                                                                'label' => $option->label,
                                                                                'id' => 'tracker[' . $field->field_name . '][' . $option->label . '][value]',
                                                                            ]) !!}
                                                                            {!! Form::hidden('tracker[0][' . $field->field_name . '][' . $option->label . '][label]', $option->label) !!}
                                                                            <label for="{!! 'tracker[0][' . $field->field_name . '][' . $option->label . '][value]' !!}"><strong>{!! $option->label !!}</strong> <br>{!! $option->description !!}</label>
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            @break

                                                            @default
                                                                <div class="alert alert-danger" role="alert">
                                                                    There was an issue rendering the field.
                                                                </div>
                                                        @endswitch
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if ($lit_settings)
                                        <div class="card mb-3">
                                            <h5 class="card-header">Literature Word Count</h5>
                                            <div class="card-body">
                                                <p>Enter the word count for your work and it will automatically calculate the {{ __('art_tracker.xp') }}.
                                                    @if ($lit_settings->round_to)
                                                        Rounds to the nearest {!! $lit_settings->round_to !!}.
                                                    @endif
                                                </p>
                                                {!! Form::number('tracker[0][word_count]', null, ['class' => 'form-control wordCount', 'rounding' => $lit_settings->round_to ?? '', 'conversion' => $lit_settings->conversion_rate, 'placeholder' => 'Enter the exact word count']) !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="#" class="add-tracker hide btn btn-primary multi-only" data-toggle="tooltip" title="Add a new sub tracker">Add Sub Tracker</a>
            </div>
            <div class="col-md-4">
                <div class="card rounded border border-primary">
                    <div class="card-header">
                        <h3>{{ __('art_tracker.xp') }} Totals</h3>
                    </div>
                    <div class="card-body">
                        <div id="calcTotals"></div>
                        <hr />
                        {!! Form::label('Select Character') !!}
                        {!! Form::select('character_id', $users_character, null, ['class' => 'form-control mr-2 characterSelect', 'placeholder' => 'Select a Character...']) !!}
                        <hr />
                        {!! Form::label('Select Gallery Submission (Optional)') !!}
                        {!! Form::select('gallery_id', $user_galleries, null, ['class' => 'form-control mr-2 gallerySelect', 'placeholder' => 'Select a Gallery...']) !!}
                        <div class="text-center my-2">
                            <h5>OR</h5>
                        </div>
                        {!! Form::url('url', null, ['class' => 'form-control mb-4', 'placeholder' => 'Enter a URL for your artwork or literature']) !!}
                        {!! Form::url('image_url', null, ['class' => 'form-control mb-4', 'placeholder' => 'Enter an image URL if you have artwork.']) !!}

                        {!! Form::label('Other Notes (Optional)') !!} {!! add_help('Let admins know any other details you may have for this card.') !!}
                        {!! Form::textarea('comments', null, ['class' => 'form-control', 'rows' => '4', 'placeholder' => 'Add optional notes to your tracker card.']) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right mt-4">
            {!! Form::submit('Submit Tracker Card for Review', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    </div>
    {!! Form::close() !!}

    </div>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            var totals = {};
            var target = $('#calcTotals');
            var parent = $('#pointValues');
            var trackerTemplate = $('#pointValues .tracker_0').clone();
            var i = 0;

            //On lit word count change
            $('#pointValues').on('change', '.wordCount', function() {
                var wc = $(this).val();
                var index = $(this).closest('.tracker-item').attr('data-index');

                if (wc === 0 || wc === '') {
                    delete totals[index]['Word Count'];
                    updateTotals();
                    return;
                }

                var cr = ($(this).attr('conversion')).split('|');
                var ro = $(this).attr('rounding') ?? 1;

                var raw = (cr[0] * (Math.round(wc / ro) * ro)) / cr[1];
                var total = Math.round(raw * ro) / ro;

                if (!totals[index]) {
                    totals[index] = {};
                }

                totals[index]['Word Count'] = total;
                updateTotals();
            });

            //Update groups on checkbox/radio changes
            function updateCheckRadioGroup($group, $type) {
                var group = $($group).attr('data-name');
                var index = $($group).closest('.tracker-item').attr('data-index');

                var selected = $($group).find('input[type="' + $type + '"]:checked');
                if (selected.length === 0) {
                    delete totals[index][group];
                } else {
                    var $temp_selected = [];
                    selected.each(function(i, element) {
                        $temp_selected[$(this).attr('label')] = $(this).val();
                    });
                    if (!totals[index]) {
                        totals[index] = {};
                    }
                    totals[index][group] = $temp_selected;
                }


                updateTotals();
            }

            //On checkbox input changes
            $('#pointValues').on('change', 'input[type="checkbox"][name]', function() {
                updateCheckRadioGroup($(this).closest('.card')[0], 'checkbox');
            });
            //On radio input changes
            $('#pointValues').on('change', 'input[type="radio"][name]', function() {
                updateCheckRadioGroup($(this).closest('.card')[0], 'radio');
            });

            //On type change
            $('.main-content').on('change', 'input[name="tracker_type"]', function(e) {
                var t = $('input[name="tracker_type"]:checked').val();

                if (t === 'multi') {
                    $('.multi-only').show().removeClass('hide');
                } else {
                    $('.tracker-item:not([data-index="0"])').remove();
                    $('.multi-only').hide().addClass('hide');
                }
            });

            //Add new tracker
            $('.add-tracker').on('click', function(e) {
                e.preventDefault();
                i++;
                addTracker();
            });

            //Remove tracker
            parent.on('click', '.remove-tracker', function(e) {
                e.preventDefault();
                var trackerCount = $('.tracker-item').length;
                if (trackerCount > 1) {
                    $(this).parents('.tracker-item').remove();
                }
            });

            function addTracker() {
                var $clone = trackerTemplate.clone();
                $clone.removeClass('tracker_0').addClass('tracker_' + i);
                $clone.find('#0').attr('id', i);
                $clone.attr('data-index', i);
                $clone.find('button').attr('data-target', '#collapse-' + i).attr('aria-controls', 'collapse-' + i).text('Tracker #' + i);
                $clone.find('#collapse-0').attr('id', 'collapse-' + i).attr('aria-labelledby', i);
                $clone.find('.remove-tracker').removeClass('hide').show();
                $clone.html($clone.html().replace(/tracker\[0\]/g, 'tracker[' + i + ']'));

                parent.append($clone);
            }

            function updateTotals() {
                target.empty();

                console.log(totals);

                $.each(totals, function(i, card) {
                    var item;
                    item = '<div class="card mb-2"><h3 class="card-header">Tracker #' + i + '</h3><div class="card-body">';
                    //Trackers
                    Object.entries(card).forEach(([group, value]) => {
                        //Sub tracker groups
                        if (typeof value === 'object') {
                            item = item + `<div class="mb-2 pb-2 border-bottom border-seconday"><h5>${group}</h5>`;
                            Object.entries(value).forEach(([k, v]) => {
                                //Sub tracker sub lines
                                item = item + `<div class="d-flex justify-content-between"><strong>${k}:</strong> <span>${v}</span></div>`;
                            });
                            item = item + `</div>`;
                        } else {
                            item = item + `<div class="d-flex justify-content-between"><strong>${group}:</strong> <span>${value}</span></div>`;
                        }
                    });
                    item = item + '</div></div>';
                    target.append(item);
                });
            }
        });
    </script>
@endsection

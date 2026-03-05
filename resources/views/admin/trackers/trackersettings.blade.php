@extends('admin.layout')

@section('admin-title')
    Art Tracker Settings
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Art Tracker Settings' => 'admin/tracker-settings/']) !!}

    <h1>Art Tracker Settings</h1>

    <p>Edit all of the art tracker related settings here.</p>

    {!! Form::open(['url' => 'admin/tracker-settings', 'files' => true]) !!}

    <div class="card mb-4">
        <div class="card-header">
            <h3>Levels</h3>
            <p>Edit the leveling benchmarks across all characters. Start from the lowest rank to the highest.</p>
        </div>
        <div class="card-body">
            <div class="form-group">
                {!! Form::label('Levels') !!}
                <div id="levelList">
                    @if ($levels)
                        @foreach ($levels as $name => $val)
                            <div class="level-row mb-2 d-flex">
                                {!! Form::text('level_name[]', $name, ['class' => 'form-control mr-2', 'placeholder' => 'Level Name']) !!}
                                {!! Form::number('level_threshold[]', $val, ['class' => 'form-control mr-2', 'min' => 0, 'placeholder' => 'Minimum Level Threshold']) !!}
                                <a href="#" class="remove-level btn btn-primary" data-toggle="tooltip" title="Remove Level">-</a>
                            </div>
                        @endforeach
                    @else
                        <div class="level-row mb-2 d-flex">
                            {!! Form::text('level_name[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Level Name']) !!}
                            {!! Form::number('level_threshold[]', null, ['class' => 'form-control mr-2', 'min' => 0, 'placeholder' => 'Minimum Level Threshold']) !!}
                            <a href="#" class="remove-level btn btn-primary" data-toggle="tooltip" title="Remove Level">-</a>
                        </div>
                    @endif
                </div>
                <div class="text-right">
                    <a href="#" id="add-level" class="add-level btn btn-primary" data-toggle="tooltip" title="Add Level">Add Level</a>
                </div>
                <div class="level-row hide mb-2">
                    {!! Form::text('level_name[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Level Name']) !!}
                    {!! Form::number('level_threshold[]', null, ['class' => 'form-control mr-2', 'min' => 0, 'placeholder' => 'Minimum Level Threshold']) !!}
                    <a href="#" class="remove-level btn btn-primary" data-toggle="tooltip" title="Remove Level">-</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>{{ __('art_tracker.xp') }} Calculator Settings</h3>
            <p>Configure the {{ __('art_tracker.xp') }} points here. You can group items to allow only one option in a group to be selected.</p>
        </div>
        <div class="card-body">
            <div class="form-group">
                {!! Form::label(__('art_tracker.xp') . ' Calculator Options') !!}
                @if ($xp_calc_data)
                    <div id="calcList">
                        @foreach ($xp_calc_data as $i => $field)
                            <div class="option-row mb-2 p-2 border border-secondary rounded d-flex flex-column" field-id="{{ $i }}">
                                <div class="row-parent mb-2 d-flex">
                                    {!! Form::text('field[' . $i . '][field_name]', $field->field_name, ['class' => 'form-control mr-2', 'style' => 'width:40%', 'placeholder' => 'Field Name']) !!}
                                    {!! Form::select('field[' . $i . '][field_type]', ['radio' => 'Radio Buttons', 'checkboxes' => 'Checkboxes', 'number' => 'Number'], $field->field_type, [
                                        'class' => 'form-control mr-2 ftype',
                                        'style' => 'width:40%',
                                        'placeholder' => 'Please select a Field Type...',
                                    ]) !!}
                                    {!! Form::text('field[' . $i . '][field_description]', $field->field_description, ['class' => 'form-control mr-2', 'placeholder' => 'Description']) !!}
                                    <a href="#" class="remove-field btn btn-primary" data-toggle="tooltip" title="Remove Field">-</a>
                                </div>
                                <div class="optionsList mb-2 {!! $field->field_options ? '' : 'hide' !!} ml-3 border-left pl-3">
                                    <h5>Field Options</h5>
                                    <div class="row-children">
                                        @if ($field->field_options)
                                            @foreach ($field->field_options as $option)
                                                <div class="child-row row mb-2 px-3">
                                                    <div class="col-md-2 px-1">
                                                        {!! Form::number('field[' . $i . '][field_options][' . $i . '][point_value]', $option['point_value'] ?? 0, ['class' => 'form-control w-100', 'placeholder' => 'Point Value']) !!}
                                                    </div>
                                                    <div class="col-md-4 px-1">
                                                        {!! Form::text('field[' . $i . '][field_options][' . $i . '][label]', $option['label'] ?? null, ['class' => 'form-control w-100', 'placeholder' => 'Option Name']) !!}
                                                    </div>
                                                    <div class="col-md-6 px-1 d-flex">
                                                        {!! Form::text('field[' . $i . '][field_options][' . $i . '][description]', gettype($option->description) === 'string' ? $option->description : null, ['class' => 'form-control w-100', 'placeholder' => 'Option Description']) !!}
                                                        <a href="#" class="remove-option ml-2 btn btn-primary" data-toggle="tooltip" title="Remove Option">-</a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <a href="#" id="add-option" class="add-op mt-2 btn btn-primary" data-toggle="tooltip" title="Add Option">Add Option</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- There's a first time for everything -->
                    <div id="calcList">
                        <div class="option-row mb-2 p-2 border border-secondary rounded d-flex flex-column" field-id="0">
                            <div class="row-parent mb-2 d-flex">
                                {!! Form::text('field[0][field_name]', null, ['class' => 'form-control mr-2', 'style' => 'width:40%', 'placeholder' => 'Field Name']) !!}
                                {!! Form::select('field[0][field_type]', ['radio' => 'Radio Buttons', 'checkboxes' => 'Checkboxes', 'number' => 'Number'], null, ['class' => 'form-control mr-2 ftype', 'style' => 'width:40%', 'placeholder' => 'Please select a Field Type...']) !!}
                                {!! Form::text('field[0][field_description]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Description']) !!}
                                <a href="#" class="remove-field btn btn-primary" data-toggle="tooltip" title="Remove Field">-</a>
                            </div>
                            <div class="optionsList mb-2 ml-3 border-left pl-3">
                                <h5>Field Options</h5>
                                <div class="row-children">
                                    <div class="child-row row mb-2 px-3">
                                        <div class="col-md-2 px-1">
                                            {!! Form::number('field[0][field_options][0][value]', null, ['class' => 'form-control w-100', 'placeholder' => 'Point Value']) !!}
                                        </div>
                                        <div class="col-md-4 px-1">
                                            {!! Form::text('field[0][field_options][0][label]', null, ['class' => 'form-control w-100', 'placeholder' => 'Option Name']) !!}
                                        </div>
                                        <div class="col-md-6 px-1 d-flex">
                                            {!! Form::text('field[0][field_options][0][description]', null, ['class' => 'form-control w-100', 'placeholder' => 'Option Description']) !!}
                                            <a href="#" class="remove-option ml-2 btn btn-primary" data-toggle="tooltip" title="Remove Option">-</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <a href="#" id="add-option" class="add-op mt-2 btn btn-primary" data-toggle="tooltip" title="Add Option">Add Option</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="text-right main-buttons">
                    <a href="#" id="add-field" class="add-field btn btn-primary" data-toggle="tooltip" title="Add Field">Add Field</a>
                </div>
                <!-- Field Template Start -->
                <div class="option-row template hide mb-2 p-2 border border-secondary rounded d-flex flex-column">
                    <div class="row-parent mb-2 d-flex">
                        {!! Form::text('field[INDEX][field_name]', null, ['class' => 'form-control mr-2', 'style' => 'width:40%', 'placeholder' => 'Field Name']) !!}
                        {!! Form::select('field[INDEX][field_type]', ['radio' => 'Radio Buttons', 'checkboxes' => 'Checkboxes', 'number' => 'Number'], null, [
                            'class' => 'form-control mr-2 ftype',
                            'style' => 'width:40%',
                            'placeholder' => 'Please select a Field Type...',
                        ]) !!}
                        {!! Form::text('field[INDEX][field_description]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Description']) !!}
                        <a href="#" class="remove-field btn btn-primary" data-toggle="tooltip" title="Remove Field">-</a>
                    </div>
                    <div class="optionsList hide mb-2 ml-3 border-left pl-3">
                        <h5>Field Options</h5>
                        <div class="row-children">
                            <div class="child-row hide row mb-2 px-3">
                                <div class="col-md-2 px-1">
                                    {!! Form::number('field[INDEX][field_options][SUB_INDEX][point_value]', null, ['class' => 'form-control w-100', 'placeholder' => 'Point Value']) !!}
                                </div>
                                <div class="col-md-4 px-1">
                                    {!! Form::text('field[INDEX][field_options][SUB_INDEX][label]', null, ['class' => 'form-control w-100', 'placeholder' => 'Option Name']) !!}
                                </div>
                                <div class="col-md-6 px-1 d-flex">
                                    {!! Form::text('field[INDEX][field_options][SUB_INDEX][description]', null, ['class' => 'form-control w-100', 'placeholder' => 'Option Description']) !!}
                                    <a href="#" class="remove-option ml-2 btn btn-primary" data-toggle="tooltip" title="Remove Option">-</a>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <a href="#" id="add-option" class="add-op mt-2 btn btn-primary" data-toggle="tooltip" title="Add Option">Add Option</a>
                        </div>
                    </div>
                </div>
                <!-- Field Template End -->
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3>Literature {{ __('art_tracker.xp') }} Calculator Settings</h3>
            <p>Configure the {{ __('art_tracker.xp') }} points for literature here. Note that this ONLY applies to word count, the rest is shared with the general {{ __('art_tracker.xp') }} calculator settings. Leave all blank to disable.</p>
        </div>
        <div class="card-body">
            @if ($lit_settings)
                <div class="form-group">
                    {!! Form::label('Word Count Options') !!}
                    <p>This is the rate at which word count is converted to {{ __('art_tracker.xp') }}. To enter the conversion use "Points|Word Count". Example: 1|100 would be 1 {{ __('art_tracker.xp') }} every 100 words.</p>
                    <div class="d-flex mb-2">
                        {!! Form::text('word_count_conversion_rate', $lit_settings->conversion_rate, ['class' => 'form-control mr-2', 'placeholder' => 'Word Count Conversion Rate']) !!}
                        {!! Form::number('round_to', $lit_settings->round_to, ['class' => 'form-control mr-2 roundTo', 'style' => $lit_settings->round_to ? '' : 'display:none;', 'placeholder' => 'Round to the Nearest']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::checkbox('enable_rounding', 1, $lit_settings->round_to ? 1 : 0, ['class' => 'form-check-input enable-rounding', 'data-toggle' => 'toggle']) !!}
                        {!! Form::label('enable_rounding', 'Enable Rounding', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Enable to create a rounding rule for literature word counts.') !!}
                    </div>
                </div>
            @else
                <div class="form-group">
                    {!! Form::label('Word Count Options') !!}
                    <p>This is the rate at which word count is converted to {{ __('art_tracker.xp') }}. To enter the conversion use "Points|Word Count". Example: 1|100 would be 1 {{ __('art_tracker.xp') }} every 100 words.</p>
                    <div class="d-flex mb-2">
                        {!! Form::text('word_count_conversion_rate', 0 | 0, ['class' => 'form-control mr-2', 'placeholder' => 'Word Count Conversion Rate (Use a | to separate)']) !!}
                        {!! Form::number('round_to', null, ['class' => 'form-control mr-2 roundTo', 'style' => 'display:none;', 'placeholder' => 'Round to the Nearest']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::checkbox('enable_rounding', 1, 0, ['class' => 'form-check-input enable-rounding', 'data-toggle' => 'toggle']) !!}
                        {!! Form::label('enable_rounding', 'Enable Rounding', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Enable to create a rounding rule for literature word counts.') !!}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="text-right mt-4">
        {!! Form::submit('Save Settings', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {

            $field_count = {{ $xp_calc_data && count($xp_calc_data) > 0 ? 1 : 0 }};

            $('.enable-rounding').on('change', function() {
                console.log('rounding changed');
                if ($(this).is(':checked')) {
                    console.log('show!');
                    $('.roundTo').show();
                } else {
                    console.log('hide!');
                    $('.roundTo').hide();
                }
            });

            $i = 1;
            //Level Repeater
            $('#add-level').on('click', function(e) {
                e.preventDefault();
                addLevelRow();
            });
            $('.remove-level').on('click', function(e) {
                e.preventDefault();
                removeLevelRow($(this));
            })

            function addLevelRow() {
                var $clone = $('.level-row.hide').clone();
                $('#levelList').append($clone);
                $clone.removeClass('hide');
                $clone.addClass('d-flex');
                $clone.find('.remove-level').on('click', function(e) {
                    e.preventDefault();
                    removeLevelRow($(this));
                });
            }

            function removeLevelRow($trigger) {
                $trigger.parent().remove();
            }

            //Calculator Repeater
            // --- Groups
            $('#calcList').on('click', '#add-option', function(e) {
                e.preventDefault();
                addOption($(this).parent().prev());
            });
            $('.remove-option').on('click', function(e) {
                e.preventDefault();
                removeOption($(this));
            });

            function addOption($parent) {
                var $clone = $('.template .child-row.hide').clone();
                $childLength = $parent.children('.child-row').length + 1;
                console.log('Child Length: ' + $childLength);
                $field_id = $parent.parents('.option-row').attr('field-id');
                $unique_row_id = $field_id + '_' + $childLength;

                //Update the field option indexes
                $clone.html($clone.html().replace(/SUB_INDEX/g, $childLength));
                $clone.html($clone.html().replace(/INDEX/g, $field_id));

                $parent.append($clone);
                $clone.removeClass('hide');
                $clone.attr('count', $i);

                $clone.find('.child-row').on('click', function(e) {
                    e.preventDefault();
                    removeOption($(this));
                });
                $i++;
            }

            function removeOption($trigger) {
                $trigger.parents('.child-row').remove();
            }

            // --- Options
            $('.main-buttons #add-field').on('click', function(e) {
                e.preventDefault();
                addField();
            });

            function addField($selector = '#calcList', $c = null) {
                var $clone = $('.option-row.hide').clone();
                $($selector).append($clone);
                $clone.removeClass('hide');
                $clone.removeClass('template');
                $clone.attr('field-id', $field_count);

                console.log('Adding field with count ' + $field_count);


                // Replace "INDEX" with "0" in the cloned HTML
                $clone.html($clone.html().replace(/INDEX/g, $field_count));
                $clone.html($clone.html().replace(/SUB_INDEX/g, 0));

                $clone.children('.child-row').first().attr('row-id', 0);
                if ($c !== null) {
                    $inputs = $clone.find('input, select');
                    $inputs.each(function(i, input) {
                        $old_name = $(input).attr('name');
                        $new_name = 'g_' + $old_name.replace('[]', '_') + $c + '[]';
                        $(input).attr('name', $new_name);
                    });
                }
                $field_count++;
            }

            $(document).on('click', '.remove-field', function(e) {
                e.preventDefault();
                removeField($(this));
            });

            function removeField($trigger) {
                $trigger.parents('.option-row').remove();
            }

            //On Field Change
            $('#calcList').on('change', 'select.ftype', function() {
                var val = $(this).val();
                console.log('Field type changed to ' + val);
                if (val == 'radio' || val == 'checkboxes') {
                    console.log('show options');
                    $(this).parents('.option-row').find('.optionsList').removeClass('hide');
                } else {
                    $(this).parents('.option-row').find('.optionsList').addClass('hide');
                }
            });

            //Double check for remove option
            $('#calcList').on('click', '.remove-option', function(e) {
                e.preventDefault();
                removeOption($(this));
            });

        });
    </script>
@endsection

@if ($tracker)

    <?php
    if ($tracker->gallery_id) {
        $image_data = [
            'url' => $tracker->gallery->getUrlAttribute(),
            'image' => $tracker->gallery->getThumbnailUrlAttribute() ?? url('/') . '/images/tracker_fallback.png',
            'alt' => $tracker->gallery->title,
        ];
    } else {
        $image_data = [
            'url' => $tracker->url,
            'image' => $tracker->url ?? url('/') . '/images/tracker_fallback.png',
            'alt' => 'Tracker Card Image (#' . $tracker->id . ')',
        ];
    }
    $image_html = '<a href="' . $image_data['url'] . '"><img class="img-fluid mr-3" src="' . $image_data['image'] . '" alt="' . $image_data['alt'] . '"/></a>';
    ?>

    <div class="card">
        <h4 class="card-header justify-content-between d-flex">Tracker Card (#{{ $tracker->id }}) <span class="badge badge-pill badge-{{ $tracker->status === 'Pending' ? 'warning' : 'success' }}">{{ $tracker->status }}</span></h4>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    {!! $image_html !!}
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
        </div>
        <div class="card-footer d-flex justify-content-between">
            <h5>Total</h5><span class="font-weight-bold bg-primary text-white p-2 rounded">TOTAL {{ __('art_tracker.xp') }}</span>
        </div>
    </div>

    @if ($editable)
        <div class="template hide">
            <!-- Grouped Template -->
            <div class="line-group border border-secondary my-2" data-id="__INDEX__">
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
            <div class="line-item w-100 d-inline-flex align-items-center justify-content-between p-2" data-id="__INDEX__">
                {!! Form::text('card[__INDEX__][title]', null, ['class' => 'form-control']) !!}
                {!! Form::number('card[__INDEX__][value]', 1, ['class' => 'form-control w-25 ml-2']) !!} <span class="badge ml-2">{{ __('art_tracker.xp') }}</span>
                <a href="#" class="remove-line btn btn-sm btn-danger ml-2">-</a>
            </div>
        </div>
    @endif
@endif
@section('scripts')
    @parent
    @if ($editable)
        <script>
            $(document).ready(function() {
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
                    var index = $(this).closest('.line-group').data('id');
                    var subIndex = $group.find('.line-item').length;
                    var template = $('.template .line-group .line-item').prop('outerHTML')
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

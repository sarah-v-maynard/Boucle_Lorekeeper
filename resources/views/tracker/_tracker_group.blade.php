@if (gettype($value) === 'array')
    <div class="line-group border border-secondary my-2">
        <div class="line-header p-2">
            <div class="d-flex justify-content-between mb-2">
                <h5>Group</h5>
                <a href="#" class="remove-group btn btn-sm btn-danger ml-2">-</a>
            </div>
            {!! Form::text('card[' . $i . '][title]', $title, ['class' => 'form-control']) !!}
        </div>
        <hr class="my-1 border border-secondary" />
        <?php $si = 0; ?>
        @foreach ($value as $title => $sub_val)
            @if (gettype($sub_val) === 'array')
                @foreach ($sub_val as $sub_title => $sub_value)
                    <div class="line-item w-100 d-inline-flex align-items-center justify-content-between p-2">
                        {!! Form::text('card[' . $i . '][sub_card][' . $si . '][title]', $sub_title, ['class' => 'form-control']) !!}
                        {!! Form::number('card[' . $i . '][sub_card][' . $si . '][value]', $sub_value, ['class' => 'form-control w-25 ml-2']) !!} <span class="badge ml-2">{{ __('art_tracker.xp') }}</span>
                        <a href="#" class="remove-line btn btn-sm btn-danger ml-2">-</a>
                    </div>
                @endforeach
            @else
                <div class="line-item w-100 d-inline-flex align-items-center justify-content-between p-2">
                    {!! Form::text('card[' . $i . '][sub_card][' . $si . '][title]', $title, ['class' => 'form-control']) !!}
                    {!! Form::number('card[' . $i . '][sub_card][' . $si . '][value]', $sub_val, ['class' => 'form-control w-25 ml-2']) !!} <span class="badge ml-2">{{ __('art_tracker.xp') }}</span>
                    <a href="#" class="remove-line btn btn-sm btn-danger ml-2">-</a>
                </div>
            @endif
            <?php
            $total += (float) $sub_val;
            $si++;
            ?>
        @endforeach
        <div class="text-right">
            <a href="#" id="addSubLine" class="btn btn-sm btn-primary m-2">Add Sub Line</a>
        </div>
    </div>
@else
    <div class="line-item w-100 d-inline-flex align-items-center justify-content-between p-2">
        {!! Form::text('card[' . $i . '][title]', $title, ['class' => 'form-control']) !!}
        {!! Form::number('card[' . $i . '][value]', $value, ['class' => 'form-control w-25 ml-2']) !!} <span class="badge ml-2">{{ __('art_tracker.xp') }}</span>
        <a href="#" class="remove-line btn btn-sm btn-danger ml-2">-</a>
    </div>
    <?php $total += $value; ?>
@endif

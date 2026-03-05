@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s Tracker
@endsection

@section('meta-img')
    {{ $character->image->thumbnailUrl }}
@endsection

@section('profile-content')
    @if ($character->is_myo_slot)
        {!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url, __('art_tracker.xp') . ' Tracker' => $character->url . '/tracker']) !!}
    @else
        {!! breadcrumbs([
            $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
            $character->fullName => $character->url,
            __('art_tracker.xp') . ' Tracker' => $character->url . '/tracker',
        ]) !!}
    @endif

    @include('character._header', ['character' => $character])

    <div class="w-100 d-inline-flex justify-content-between">
        <div class="text-left">
            <h3>{{ __('art_tracker.xp') }} Progress</h3>
            <p><strong>Current Rank: </strong>{!! $current_level !!}</p>
        </div>
        <div class="text-right">
            <h5 class="font-weight-bold">{!! $total_accepted !!} {{ __('art_tracker.xp') }}</h5>
            @if ($total_xp !== $total_accepted)
                {!! $total_xp - $total_accepted !!} {{ __('art_tracker.xp') }} Pending
            @endif
        </div>
    </div>

    <div class="px-4 py-2">
        {!! $progress !!}
    </div>

    <hr />

    @if ($tracker_cards)
        {!! $tracker_cards !!}
    @else
        <p>No cards found!</p>
    @endif

    <style>
        .progress-stop {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: var(--gray-800);
            z-index: 1;
        }

        .progress-stop .badge {
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
        }

        .progress-stop .badge::before {
            content: "";
            display: block;
            width: 10px;
            height: 10px;
            background-color: inherit;
            position: absolute;
            z-index: -1;
            top: -5px;
            left: calc(50% - 5px);
            transform: rotate(45deg);
        }
    </style>
@endsection

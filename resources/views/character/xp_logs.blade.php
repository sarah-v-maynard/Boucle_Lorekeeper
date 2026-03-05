@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s {{ __('art_tracker.xp') }} Logs
@endsection

@section('meta-img')
    {{ $character->image->thumbnailUrl }}
@endsection

@section('profile-content')
    {!! breadcrumbs([
        $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
        $character->fullName => $character->url,
        __('art_tracker.xp') . ' Tracker' => $character->url . '/tracker',
        'Logs' => $character->url . '/xp-logs',
    ]) !!}

    @include('character._header', ['character' => $character])

    <h3>{{ __('art_tracker.xp') }} Logs</h3>

    {!! $logs->render() !!}

    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Sender</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Recipient</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">{{ __('art_tracker.xp') }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="logs-table-cell">Log</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Date</div>
                </div>
            </div>
        </div>

        <div class="logs-table-body">
            @foreach ($logs as $log)
                <div class="logs-table-row">

                    <div class="row flex-wrap">
                        <div class="col-6 col-md-2">
                            <div class="logs-table-cell">
                                <i class="{{ $log->xp > 0 ? 'in' : 'out' }}flow bg-{{ $log->xp > 0 ? 'success' : 'danger' }} fas {{ $log->xp > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-2"></i>
                                {!! $log->sender ? $log->sender->displayName : '' !!}
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="logs-table-cell">
                                {!! $log->recipient ? $log->recipient->displayName : '' !!}
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="logs-table-cell">
                                {!! $log->xp !!}
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="logs-table-cell">
                                {!! $log->data !!}
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="logs-table-cell">
                                {!! pretty_date($log->created_at) !!}
                            </div>
                        </div>
                    </div>


                </div>
            @endforeach
        </div>
    </div>
    {!! $logs->render() !!}
@endsection

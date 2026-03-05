<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Tracker\Tracker;
use App\Services\TrackerManager;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackerController extends Controller {
    /**
     * Shows the tracker index page.
     *
     * @param string $status
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTrackerIndex(Request $request, $status = null) {
        $trackers = Tracker::where('status', $status ? ucfirst($status) : 'Pending')->whereNotNull('character_id');
        $data = $request->only(['character_id', 'sort']);
        if (isset($data['character_id']) && $data['character_id'] != null) {
            $trackers->whereHas('character', function ($query) use ($data) {
                $query->where('character_id', $data['character_id']);
            });
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'newest':
                    $trackers->sortNewest();
                    break;
                case 'oldest':
                    $trackers->sortOldest();
                    break;
            }
        } else {
            $trackers->sortOldest();
        }

        return view('admin.trackers.index', [
            'trackers'      => $trackers->paginate(30),
        ]);
    }

    /**
     * Shows the tracker detail page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTrackerCard($id) {
        $tracker = Tracker::whereNotNull('character_id')->where('id', $id)->where('status', '!=', 'Draft')->first();
        if (!$tracker) {
            abort(404);
        }

        return view('admin.trackers.tracker', [
            'tracker'          => $tracker,
            'cardData'         => ($tracker->data_temp ? $tracker->getDataAttribute(true) : $tracker->getDataAttribute()),
            'gallery'          => $tracker->gallery ?? null,
            'characters'       => Character::visible(Auth::check() ? Auth::user() : null)->myo(0)->orderBy('slug', 'DESC')->get()->pluck('fullName', 'slug')->toArray(),
        ] + ($tracker->status == 'Pending' ? [
        ] : []));
    }

    /**
     * Creates a new tracker.
     *
     * @param App\Services\TrackerManager $service
     * @param int                         $id
     * @param string                      $action
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTrackerCard(Request $request, TrackerManager $service, $id, $action) {
        $data = $request->all();

        if ($action == 'reject' && $service->rejectTrackerCard($request->only(['staff_comments']) + ['id' => $id], Auth::user())) {
            flash('Tracker card rejected successfully.')->success();
        } elseif ($action == 'cancel' && $service->cancelTrackerCard($request->only(['staff_comments']) + ['id' => $id], Auth::user())) {
            flash('Tracker card canceled successfully.')->success();

            return redirect()->to('admin/trackers');
        } elseif ($action == 'approve' && $service->approveTrackerCard($data + ['id' => $id], Auth::user())) {
            flash('Tracker card approved successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the tracker settings edit page.
     */
    public function getTrackerSettingsPage(Request $request) {
        $levels = DB::table('site_settings')->where('key', 'xp_levels')->pluck('value');
        $lit_settings = json_decode(DB::table('site_settings')->where('key', 'xp_lit_conversion_options')->pluck('value'));
        if (array_key_exists(0, $lit_settings)) {
            $lit_settings = json_decode($lit_settings[0]);
        } else {
            $lit_settings = null;
        }
        $calc_data = DB::table('site_settings')->where('key', 'xp_calculator')->pluck('value');
        if (count($calc_data) > 0) {
            $calc_data = json_decode($calc_data[0]);
        } else {
            $calc_data = null;
        }

        return view('admin.trackers.trackersettings', [
            'levels'              => isset($levels[0]) ? json_decode($levels[0]) : null,
            'xp_calc_data'        => $calc_data,
            'lit_settings'        => $lit_settings,
        ]);
    }

    /**
     * Gets the tracker settings edit page.
     *
     * @param App\Services\TrackerManager $service
     */
    public function saveTrackerSettings(Request $request, TrackerManager $service) {
        $data = $request->all();

        if ($data && $service->updateTrackerSettings($data)) {
            flash('Art Tracker settings updated successfully.');
        } else {
            if (isset($service->errors()->getMessages()['error'])) {
                foreach ($service->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
            }
        }

        return redirect('/admin/tracker-settings');
    }
}

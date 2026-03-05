<?php

namespace App\Http\Controllers;

use App\Models\Tracker\Tracker;
use App\Models\User\User;
use App\Services\TrackerManager;
use Auth;
use DB;
use Illuminate\Http\Request;

class XPCalcController extends Controller {
    /**
     * Display the XP Calculator page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getXPCalc() {
        // Check if the user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get the current user's data
        $user = Auth::user();
        $users_character = Auth::user()->characters()->with('image')->visible()->whereNotNull('name')->whereNull('trade_id')->pluck('name', 'id')->toArray();
        $user_galleries = Auth::user()->gallerySubmissions()->where('status', 'Approved')->pluck('title', 'id');

        // Get site options for the XP Calculator
        $xp_calc_form = json_decode(DB::table('site_settings')->where('key', 'xp_calculator')->pluck('value')[0]) ?? null;
        $lit_settings = json_decode(DB::table('site_settings')->where('key', 'xp_lit_conversion_options')->pluck('value')[0]) ?? null;

        return view('tracker.xp_calc', [
            'users_character'   => $users_character,
            'user_galleries'    => $user_galleries,
            'xp_calc_form'      => $xp_calc_form,
            'lit_settings'      => $lit_settings,
        ]);
    }

    /**
     * Creates a new tracker card from the xp calculator form.
     *
     * @param App\Services\TrackerManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postXPForm(Request $request, TrackerManager $service) {
        $data = $request->all();

        $trackerCard = $service->createTrackerCard($data, $request->user());

        if ($data && $trackerCard) {
            flash('Created tracker card successfully.')->success();

            return redirect()->to($trackerCard->viewUrl);
        } else {
            if (isset($service->errors()->getMessages()['error'])) {
                foreach ($service->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
            }

            return redirect()->back();
        }
    }
}

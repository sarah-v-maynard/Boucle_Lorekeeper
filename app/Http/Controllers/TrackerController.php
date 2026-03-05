<?php

namespace App\Http\Controllers;

use App\Models\Tracker\Tracker;
use App\Services\TrackerManager;
use Auth;
use Illuminate\Http\Request;

class TrackerController extends Controller {
    /**
     * Shows an individual tracker card.
     *
     * @param mixed $id
     * @param mixed $editable
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTrackerCard($id, $editable = false) {
        $tracker = Tracker::where('id', $id)->first();

        if (!$tracker) {
            abort(404);
        }

        return view('tracker.index', [
            'tracker'          => $tracker,
            'cardData'         => $tracker->getDataAttribute(),
            'editable'         => $editable,
        ]);
    }

    /**
     * Shows the editable version of an individual tracker card.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditableTrackerCard($id) {
        $tracker = Tracker::where('id', $id)->first();
        if ($tracker->user->id !== Auth::user()->id && !Auth::user()->isStaff) {
            return redirect()->route('tracker.index', ['id' => $id])
                ->with('error', 'You do not have permission to edit this tracker card.');
        }

        return $this->getTrackerCard($id, true);
    }

    /**
     * Sends the tracker card back to the queue for an edit.
     *
     * @param App\Services\TrackerManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTrackerCardEditRequest(Request $request, TrackerManager $service) {
        $data = $request->all();

        $tracker = Tracker::where('id', $data['tracker_id'])->first();

        $temp_data = [];
        foreach ($data['card'] as $i => $item) {
            if ($i === '__INDEX__') {
                continue;
            }
            if (isset($item['sub_card']) && $item['sub_card']) {
                $sub_items = [];
                foreach ($item['sub_card'] as $sub_i => $sub_item) {
                    $sub_items[$sub_item['title']] = $sub_item['value'];
                }
                $temp_data[$item['title']] = $sub_items;
            } else {
                $temp_data[$item['title']] = $item['value'];
            }
        }

        $tracker->update([
            'status'    => 'Pending',
            'data_temp' => json_encode($temp_data),
        ]);

        if (!$tracker) {
            abort(404);
        }

        return redirect()->back();
    }
}

<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Tracker\Tracker;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrackerManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Tracker Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of submission data.
    |
    */

    /**
     * Creates a new tracker card.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return mixed
     */
    public function createTrackerCard($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['character_id'])) {
                throw new \Exception('Please select a character.');
            }
            if (!isset($data['tracker']) && count($data['tracker']) == 0) {
                throw new \Exception('There must be at least one line for the tracker card.');
            }
            if (!isset($data['gallery_id']) && !isset($data['url']) && !isset($data['image_url'])) {
                throw new \Exception('There must be a linked gallery or external image/url.');
            }

            $xp = 0;
            $tracker_data = [];
            $ci = 0;

            //Set the tracker limits
            if ($data['tracker_type'] === 'single') {
                $limit = 1;
            } else {
                $limit = 10;
            }

            //Create the tracker array
            foreach ($data['tracker'] as $i => $tracker) {
                if ($limit < $ci) {
                    break;
                }
                foreach ($tracker as $name => $value) {
                    if (gettype($value) == 'array') {
                        $sub_cards = [];
                        foreach ($value as $sub_name => $sub_value) {
                            if (array_key_exists('value', $sub_value) && array_key_exists('label', $sub_value)) {
                                $sub_cards[$sub_value['label']] = floatval($sub_value['value']) ?? 0;
                                $xp += floatval($sub_value['value']) ?? 0;
                            }
                        }
                        if ($sub_cards) {
                            $tracker_data[$ci][$name] = $sub_cards;
                        }
                    } else {
                        if ($value) {
                            $tracker_data[$name] = floatval($value) ?? 0;
                            $xp += floatval($value) ?? 0;
                        }
                    }
                }
                $ci++;
            }

            // Create the tracker card itself.
            $tracker = Tracker::create([
                'user_id'       => $user->id,
                'character_id'  => $data['character_id'],
                'gallery_id'    => $data['gallery_id'] ?? null,
                'image_url'     => $data['image_url'] ?? null,
                'url'           => $data['url'] ?? null,
                'status'        => 'Pending',
                'comments'      => $data['comments'],
                'data'          => json_encode($tracker_data),
            ]);

            if (!$this->createLog($user ? $user->id : null, $data['character_id'] ?? null, 'Pending Tracker Card', $xp)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn($tracker);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edits an existing tracker card.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $tracker
     * @param mixed                 $isSubmit
     *
     * @return mixed
     */
    public function editTrackerCard($tracker, $data, $user, $isSubmit = false) {
        DB::beginTransaction();

        try {
            if ($isSubmit) {
                $tracker->update(['status' => 'Pending']);
            }

            // Modify submission
            $tracker->update([
                'url'           => $data['url'] ?? null,
                'updated_at'    => Carbon::now(),
                'comments'      => $data['comments'],
            ]);

            return $this->commitReturn($tracker);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Cancels a tracker card.
     *
     * @param mixed $data the tracker card data
     * @param mixed $user the user performing the cancellation
     */
    public function cancelTrackerCard($data, $user) {
        DB::beginTransaction();

        try {
            // 1. check that the tracker exists
            // 2. check that the tracker is pending
            if (!isset($data['tracker'])) {
                $tracker = Tracker::where('status', 'Pending')->where('id', $data['id'])->first();
            } elseif ($data['tracker']->status == 'Pending') {
                $tracker = $data['tracker'];
            } else {
                $tracker = null;
            }
            if (!$tracker) {
                throw new \Exception('Invalid tracker card.');
            }

            // Set staff comments
            if (isset($data['staff_comments']) && $data['staff_comments']) {
                $data['parsed_staff_comments'] = parse($data['staff_comments']);
            } else {
                $data['parsed_staff_comments'] = null;
            }

            if ($user->id != $tracker->user_id) {
                // The only things we need to set are:
                // 1. staff comment
                // 2. staff ID
                // 3. status
                $tracker->update([
                    'staff_comments'        => $data['staff_comments'],
                    'updated_at'            => Carbon::now(),
                    'staff_id'              => $user->id,
                    'status'                => 'Draft',
                    'comments'              => null,
                ]);

                Notifications::create('TRACKER_SUBMISSION_CANCELLED', $tracker->user, [
                    'staff_url'         => $user->url,
                    'staff_name'        => $user->name,
                    'tracker_id'        => $tracker->id,
                    'character_name'    => $tracker->character->fullName,
                ]);
            } else {
                // This is when a user cancels their own submission back into draft form
                $tracker->update([
                    'status'     => 'Draft',
                    'updated_at' => Carbon::now(),
                ]);
            }

            return $this->commitReturn($tracker);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Rejects a submission.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return mixed
     */
    public function rejectTrackerCard($data, $user) {
        DB::beginTransaction();

        try {
            // 1. check that the tracker exists
            // 2. check that the tracker is pending
            if (!isset($data['tracker'])) {
                $tracker = Tracker::where('status', 'Pending')->where('id', $data['id'])->first();
            } elseif ($data['tracker']->status == 'Pending') {
                $tracker = $data['tracker'];
            } else {
                $tracker = null;
            }
            if (!$tracker) {
                throw new \Exception('Invalid tracker card.');
            }

            if (isset($data['staff_comments']) && $data['staff_comments']) {
                $data['parsed_staff_comments'] = parse($data['staff_comments']);
            } else {
                $data['parsed_staff_comments'] = null;
            }

            // The only things we need to set are:
            // 1. staff comment
            // 2. staff ID
            // 3. status
            $tracker->update([
                'staff_comments'        => $data['staff_comments'],
                'staff_id'              => $user->id,
                'status'                => 'Rejected',
                'comments'              => null,
            ]);

            Notifications::create('TRACKER_SUBMISSION_REJECTED', $tracker->user, [
                'staff_url'         => $user->url,
                'staff_name'        => $user->name,
                'tracker_id'        => $tracker->id,
                'character_name'    => $tracker->character->fullName,
            ]);

            if (!$this->logAdminAction($user, 'Tracker Rejected', 'Rejected tracker <a href="'.$tracker->viewurl.'">#'.$tracker->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($tracker);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approves a submission.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return mixed
     */
    public function approveTrackerCard($data, $user) {
        DB::beginTransaction();

        try {
            // 1. check that the tracker exists
            // 2. check that the tracker is pending
            $tracker = Tracker::where('status', 'Pending')->where('id', $data['id'])->first();
            if (!$tracker) {
                throw new \Exception('Invalid tracker card.');
            }
            if (!$data['card'] || !is_array($data['card']) || count($data['card']) == 0) {
                throw new \Exception('There must be at least one line for the tracker card.');
            }

            $card_data = [];
            foreach ($data['card'] as $i => $card) {
                if (isset($card['sub_card']) && is_array($card['sub_card']) && count($card['sub_card']) > 0) {
                    $sub_cards = [];
                    foreach ($card['sub_card'] as $j => $sub_card) {
                        if (isset($sub_card['title']) && $sub_card['title'] != '') {
                            $sub_cards[$sub_card['title']] = floatval($sub_card['value']) ?? 0;
                        }
                    }
                    if (count($sub_cards) > 0) {
                        $card_data[$card['title']][] = $sub_cards;
                    }
                } else {
                    if (isset($card['title']) && $card['title'] != '') {
                        $card_data[$card['title']] = floatval($card['value']) ?? 0;
                    }
                }
            }

            if (isset($data['staff_comments']) && $data['staff_comments']) {
                $data['parsed_staff_comments'] = parse($data['staff_comments']);
            } else {
                $data['parsed_staff_comments'] = null;
            }

            // Finally, set:
            // 1. staff comments
            // 2. staff ID
            // 3. status
            if ($tracker->data_temp) {
                $tracker->update([
                    'staff_comments'        => $data['parsed_staff_comments'] ?? null,
                    'staff_id'              => $user->id,
                    'status'                => 'Approved',
                    'data'                  => json_encode($card_data),
                    'data_temp'             => null,
                    'comments'              => null,
                ]);
            } else {
                $tracker->update([
                    'staff_comments'        => $data['parsed_staff_comments'] ?? null,
                    'staff_id'              => $user->id,
                    'status'                => 'Approved',
                    'data'                  => json_encode($card_data),
                    'comments'              => null,
                ]);
            }

            //Check the current character level and update if needed
            $this->checkCharacterLevel($tracker->character_id);

            Notifications::create('TRACKER_SUBMISSION_APPROVED', $tracker->user, [
                'staff_url'         => $user->url,
                'staff_name'        => $user->name,
                'tracker_id'        => $tracker->id,
                'character_name'    => $tracker->character->fullName,
            ]);

            if (!$this->logAdminAction($user, 'Tracker Approved', 'Approved submission <a href="'.$tracker->viewurl.'">#'.$tracker->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($tracker);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a submission.
     *
     * @param mixed $data the data of the submission to be deleted
     * @param mixed $user the user performing the deletion
     */
    public function deleteTrackerCard($data, $user) {
        DB::beginTransaction();
        try {
            // 1. check that the tracker exists
            // 2. check that the tracker is a draft
            if (!isset($data['tracker'])) {
                $tracker = Tracker::where('status', 'Draft')->where('id', $data['id'])->first();
            } elseif ($data['tracker']->status == 'Pending') {
                $tracker = $data['tracker'];
            } else {
                $tracker = null;
            }
            if (!$tracker) {
                throw new \Exception('Invalid tracker card.');
            }
            if ($user->id != $tracker->user_id) {
                throw new \Exception('This is not your tracker card.');
            }
            $tracker->delete();

            return $this->commitReturn($tracker);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Grants XP to a given character.
     *
     * @param mixed $data  the data of the submission to be deleted
     * @param mixed $staff
     */
    public function grantCharacterXP($data, $staff) {
        try {
            if (!$data) {
                throw new \Exception('Something went wrong, data missing.');
            }

            // Process characters
            $characters = Character::find($data['characters']);
            if (count($characters) != count($data['characters'])) {
                throw new \Exception('You must select at least 1 character.');
            }

            $xp = (floatval($data['levels']) ?? 0) + (floatval($data['static_xp']) ?? 0);

            foreach ($characters as $character) {
                $user = User::where('id', $character->user_id)->first();
                if (!$this->logAdminAction($staff, 'XP Grant', 'Granted '.$xp.' XP to '.$character->fullName)) {
                    throw new \Exception('Failed to log admin action.');
                }
                if ($this->creditCharacterXP($staff, $user, 'Staff Grant', $data['data'] ?? '', $character, $xp)) {
                    Notifications::create('XP_GRANT', $user, [
                        'character_name'    => $character->fullName,
                        'xp_points'         => $xp,
                        'url'               => $character->url,
                        'slug'              => $character->slug,
                        'staff_name'        => $staff->name,
                        'staff_url'         => $staff->url,
                    ]);
                } else {
                    throw new \Exception('Failed to credit XP to '.$character->fullName.'.');
                }
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Credit the XP to the character.
     *
     * @param mixed $sender
     * @param mixed $recipient
     * @param mixed $type
     * @param mixed $data
     * @param mixed $character
     * @param mixed $xp
     */
    public function creditCharacterXP($sender, $recipient, $type, $data, $character, $xp) {
        DB::beginTransaction();

        try {
            $xp_data = ['Grant' => ['Manual Staff Grant' => $xp]];

            $temp_tracker = Tracker::create([
                'user_id'       => $recipient->id,
                'staff_id'      => $sender->id,
                'status'        => 'Approved',
                'character_id'  => $character->id,
                'gallery_id'    => null,
                'image_url'     => null,
                'url'           => null,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
                'data'          => json_encode($xp_data),
            ]);
            $temp_tracker->save();
            $data = 'Staff Grant'.($data ? ': '.$data : '');

            if ($type && !$this->createLog($sender ? $sender->id : null, $character ? $character->id : null, $data, $xp)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates an XP log.
     *
     * @param int    $senderId
     * @param string $data
     * @param float  $xp
     * @param mixed  $characterId
     *
     * @return float
     */
    public function createLog($senderId, $characterId, $data, $xp) {
        return DB::table('xp_log')->insert(
            [
                'sender_id'      => $senderId,
                'character_id'   => $characterId,
                'log'            => 'XP Granted',
                'log_type'       => 'XP Grant',
                'data'           => json_encode($data),
                'xp'             => $xp,
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ]
        );
    }

    /**
     * Updates all of the tracker settings to the site_options table.
     *
     * @param mixed $data the data of the submission to be deleted
     */
    public function updateTrackerSettings($data) {
        DB::beginTransaction();

        try {
            if (!$data) {
                throw new \Exception('Invalid data, something went wrong.');
            }

            if (isset($data['level_name'])) {
                $i = 0;
                foreach ($data['level_name'] as $name) {
                    if ($name !== null && $data['level_threshold'][$i] !== null) {
                        $levels[$name] = $data['level_threshold'][$i];
                        $i++;
                    }
                }
                $this->updateSiteOption('xp_levels', $levels);

                //Unset these after updating so we can update the calculator array
                unset($data['level_name']);
                unset($data['level_threshold']);
            }
            if (isset($data['word_count_conversion_rate'])) {
                //Set up the word count conversion rate
                $this->updateSiteOption('xp_lit_conversion_options', [
                    'conversion_rate' => $data['word_count_conversion_rate'],
                    'round_to'        => $data['round_to'],
                ]);

                //Unset after updating
                unset($data['word_count_conversion_rate']);
                unset($data['round_to']);
                unset($data['enable_rounding']);
            }

            $form = [];
            if ($data['field']) {
                $nI = 0;
                foreach ($data['field'] as $i => $field) {
                    // Clean up empty options
                    if ($i !== 'INDEX' && $data['field'][$i]['field_options']) {
                        $sI = 0;
                        $sub_options = [];
                        foreach ($data['field'][$i]['field_options'] as $j => $option) {
                            if (!str_contains($j, 'SUB_')) {
                                $sub_options[$sI] = $option;
                            }
                            $sI++;
                        }
                        $form[$nI] = $field;
                        $form[$nI]['field_options'] = $sub_options;
                    }
                    $nI++;
                }
                $this->updateSiteOption('xp_calculator', $form);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Checks for the character's current level and updates it if needed.
     *
     * @param int   $characterId
     * @param mixed $characterId
     *
     * @return float
     */
    public function checkCharacterLevel($characterId) {
        $character = Character::where('id', $characterId)->first();
        if (!$character) {
            return false;
        }

        $currentLevel = $character->level;

        $levels = Settings::get('xp_levels');
        if (!$levels) {
            return false;
        }
        $levels = (array) json_decode($levels);
        ksort($levels);

        $newLevel = $currentLevel;
        foreach ($levels as $levelName => $threshold) {
            if ($character->total_xp >= $threshold) {
                $newLevel = $levelName;
            }
        }

        if ($newLevel != $currentLevel) {
            $character->update(['level' => $newLevel]);

            Notifications::create('CHARACTER_LEVEL_UP', $character, [
                'new_level'         => $newLevel,
                'url'               => $character->url,
                'slug'              => $character->slug,
                'character_name'    => $character->fullName,
            ]);

            return true;
        }

        return false;
    }

    private function updateSiteOption($key, $value) {
        if ($value) {
            $exists = DB::table('site_settings')->where('key', $key)->first();
            $value = json_encode($value);
            if ($exists) {
                //Update
                if ($exists->value !== $value) {
                    DB::table('site_settings')->where('key', $key)->update(['value' => $value]);
                }
            } else {
                //Create
                DB::table('site_settings')->insert(['key' => $key, 'value' => $value, 'description' => 'Auto-Generated']);
            }
        }
    }

    /**************************************************************************************************************
     *
     * PRIVATE FUNCTIONS
     *
     **************************************************************************************************************/

    /**
     * Helper function to remove all empty/zero/falsey values.
     *
     * @param array $value
     *
     * @return array
     */
    private function innerNull($value) {
        return array_values(array_filter($value));
    }

    /**************************************************************************************************************
     *
     * ATTACHMENT FUNCTIONS
     *
     **************************************************************************************************************/
}

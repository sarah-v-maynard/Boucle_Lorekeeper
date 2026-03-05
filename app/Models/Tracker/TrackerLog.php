<?php

namespace App\Models\Tracker;

use App\Models\Character\Character;
use App\Models\Model;
use App\Models\User\User;

class TrackerLog extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'character_id',
        'log', 'log_type', 'data',
        'xp',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'xp_log';
    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who initiated the logged action.
     */
    public function sender() {
        if ($this->sender_type == 'User') {
            return $this->belongsTo(User::class, 'sender_id');
        }

        return $this->belongsTo(Character::class, 'sender_id');
    }

    /**
     * Get the user who received the logged action.
     */
    public function recipient() {
        return $this->belongsTo(Character::class, 'character_id');
    }

    /**
     * Get the character that is the target of the action.
     */
    public function character() {
        return $this->belongsTo(Character::class);
    }
}

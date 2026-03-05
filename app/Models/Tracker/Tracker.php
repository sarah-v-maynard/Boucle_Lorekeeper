<?php

namespace App\Models\Tracker;

use App\Models\Character\Character;
use App\Models\Gallery\GallerySubmission;
use App\Models\Model;
use App\Models\User\User;
use Carbon\Carbon;
use DB;

class Tracker extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'user_id', 'staff_id', 'gallery_id',
        'image_url', 'url', 'comments', 'staff_comments',
        'status', 'data', 'data_temp',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'art_tracker';
    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for tracker creation.
     *
     * @var array
     */
    public static $createRules = [
        'character_id'  => 'required',
        'user_id'       => 'required',
        'staff_id'      => 'required',
        'gallery_id'    => 'nullable',
        'image_url'     => 'nullable|url',
        'url'           => 'nullable|url',
        'staff_comments'=> 'nullable',
        'data'          => 'nullable',
        'data_temp'     => 'nullable',
    ];

    /**
     * Validation rules for tracker updating.
     *
     * @var array
     */
    public static $updateRules = [
        'character_id'  => 'required',
        'user_id'       => 'required',
        'staff_id'      => 'required',
        'gallery_id'    => 'nullable',
        'image_url'     => 'nullable|url',
        'url'           => 'nullable|url',
        'staff_comments'=> 'nullable',
        'data'          => 'nullable',
        'data_temp'     => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the character this tracker is for.
     */
    public function character() {
        return $this->belongsTo(Character::class, 'character_id');
    }

    /**
     * Get the gallery item this tracker is for.
     */
    public function gallery() {
        return $this->belongsTo(GallerySubmission::class, 'gallery_id');
    }

    /**
     * Get the user who made the tracker.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the staff who processed the tracker.
     */
    public function staff() {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include pending trackers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope a query to only include drafted trackers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDrafts($query) {
        return $query->where('status', 'Drafts');
    }

    /**
     * Scope a query to only include viewable trackers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeViewable($query, $user = null) {
        $forbiddenSubmissions = $this
            ->whereHas('tracker', function ($q) {
                $q->where('hide_trackers', 1)->whereNotNull('end_at')->where('end_at', '>', Carbon::now());
            })
            ->orWhereHas('tracker', function ($q) {
                $q->where('hide_trackers', 2);
            })
            ->orWhere('status', '!=', 'Approved')->pluck('id')->toArray();

        if ($user && $user->hasPower('manage_submissions')) {
            return $query;
        } else {
            return $query->where(function ($query) use ($user, $forbiddenSubmissions) {
                if ($user) {
                    $query->whereNotIn('id', $forbiddenSubmissions)->orWhere('user_id', $user->id);
                } else {
                    $query->whereNotIn('id', $forbiddenSubmissions);
                }
            });
        }
    }

    /**
     * Scope a query to sort trackers oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to sort trackers by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @param mixed $temp
     *
     * @return array
     */
    public function getDataAttribute($temp = false) {
        return json_decode(($temp ? $this->attributes['data_temp'] : $this->attributes['data']), true);
    }

    /**
     * Get the viewing URL of the tracker.
     *
     * @return string
     */
    public function getViewUrlAttribute() {
        return url('tracker/'.$this->id);
    }

    /**
     * Get the admin URL (for processing purposes) of the tracker.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/trackers/edit/'.$this->id);
    }

    /**
     * Gets all card data for a character.
     *
     * @param mixed $character_id
     *
     * @return array (cards|total_points)
     */
    public static function renderAllCards($character_id) {
        $tracker_cards = self::where('character_id', $character_id)->whereIn('status', ['Approved', 'Pending'])->get();

        $cards = [];
        $total = 0;
        $accepted_total = 0;
        foreach ($tracker_cards as $card) {
            $temp = $card->renderCard();
            $cards[] = $temp['card'];
            $total += floatval($temp['total_points']);
            $accepted_total += floatval($temp['accepted_points']);
        }

        return [
            'cards'           => $cards ? implode('', $cards) : null,
            'accepted_points' => $accepted_total,
            'total_points'    => $total,
        ];
    }

    /**
     * Renders the data for a single card into HTML.
     *
     * @param mixed $editable
     *
     * @return array (cards|total_points)
     */
    public function renderCard($editable = false) {
        $data = $this->getDataAttribute();
        $total = 0;
        $accepted_points = 0;
        $cards = [];

        $line_template = '<div class="line-item w-100 d-inline-flex justify-content-between p-2"><h5 class="lh-1 m-0">$title</h5><p class="lh-1 m-0">$value XP</p></div>';

        if (count($data) > 1) {
            //Multi-card
            $accordion = true;
        } else {
            //Sinle-card
            $accordion = false;
        }

        //Format each multi-level card - this also applies to single though it won't create an accordion
        foreach ($data as $i => $card) {
            $ci = 0;
            foreach ($card as $title => $value) {
                if (gettype($value) === 'array') {
                    $sub_line = [];
                    //If this is a group rather than a single line
                    foreach ($value as $sub_title => $sub_val) {
                        $sub_line[] = str_replace(['$card_id', '$title', '$value'], [$this->id, $sub_title, $sub_val], $line_template);
                        if ($this->status === 'Approved') {
                            $accepted_points += $sub_val;
                            $total += $sub_val;
                        } else {
                            $total += $sub_val;
                        }
                    }
                    $hContent = '<div class="line-group border border-secondary my-2"> <h4 class="line-header text-uppercase font-weight-bold p-2">'.$title.'</h4>'.implode('', $sub_line).'</div>';
                    if ($accordion) {
                        $hContent = '
                        <div class="card">
                            <div class="card-header" id="card-'.$this->id.'-'.$i.'-heading">
                                <h4 class="m-0">
                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse-card-'.$this->id.'-'.$i.'" aria-expanded="'.($i === 0 ? 'true' : 'false').'" aria-controls="collapse-card-'.$this->id.'-'.$i.'">
                                        Sub-Tracker #'.($i + 1).'
                                    </button>
                                </h4>
                            </div>
                            <div id="collapse-card-'.$this->id.'-'.$i.'" class="collapse'.($i === 0 ? ' show' : '').'" aria-labelledby="card-'.$this->id.'-'.$i.'-heading" data-parent="#card-'.$this->id.'-accordion">
                                <div class="card-body p-0">
                                    '.$hContent.'
                                </div>
                            </div>
                        </div>
                        ';
                    }

                    $cards[] = $hContent;
                } else {
                    $cards[] = str_replace(['$title', '$value'], [$title, $value], $line_template);
                    if ($this->status === 'Approved') {
                        $accepted_points += $value;
                        $total += $value;
                    } else {
                        $total += $value;
                    }
                }
                $ci++;
            }

            $card_badge = '<span class="badge badge-pill badge-'.($this->status === 'Pending' ? 'warning' : 'success').'">'.$this->status.'</span>';

            if ($this->gallery_id) {
                $image_data = [
                    'url'   => $this->gallery->getUrlAttribute(),
                    'image' => $this->gallery->getThumbnailUrlAttribute() ?? url('/').'/images/tracker_fallback.png',
                    'alt'   => $this->gallery->title,
                ];
            } else {
                $image_data = [
                    'url'   => $this->url,
                    'image' => $this->url ?? url('/').'/images/tracker_fallback.png',
                    'alt'   => 'Tracker Card Image (#'.$this->id.')',
                ];
            }

            $image_html = '<a href="'.$image_data['url'].'"><img class="img-fluid mr-3" src="'.$image_data['image'].'" alt="'.$image_data['alt'].'"/></a>';

            $card_html = '
                <div class="card my-4">
                    <h4 class="card-header justify-content-between d-flex"><a href="'.$this->getViewUrlAttribute().'">Tracker Card (#'.$this->id.')</a>'.$card_badge.'</h4>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                '.$image_html.'
                            </div>
                            <div class="col-md-9">
                                '.($accordion ? '<div class="accordion sub-tracker" id="card-'.$this->id.'-accordion">' : '').'
                                '.implode('', $cards).'
                                '.($accordion ? '</div>' : '').'
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <h5>Total</h5><span class="font-weight-bold bg-primary text-white p-2 rounded">'.$total.' XP</span>
                    </div>
                </div>';
        }

        return [
            'accepted_points' => $accepted_points,
            'total_points'    => $total,
            'card'            => $card_html,
        ];
    }

    /**
     * Get the current XP level of the tracker.
     *
     * @param mixed $current_points
     *
     * @return int
     */
    public static function getCurrentLevel($current_points = 0) {
        $levels = json_decode(DB::table('site_settings')->where('key', 'xp_levels')->pluck('value')->first(), true);

        $level_names = array_keys($levels);

        if ($levels) {
            foreach ($levels as $level_name => $xp_required) {
                $current_pos = array_search($level_name, $level_names);
                $previous_key = $level_names[$current_pos - 1] ?? array_key_first($levels);
                if ($current_points < $xp_required) {
                    return $previous_key;
                }
            }

            return array_key_first($levels);
        }
    }

    public static function getXpProgressBar($current_points = 0, $current_level = null) {
        $levels = json_decode(DB::table('site_settings')->where('key', 'xp_levels')->pluck('value')->first(), true);
        $level_names = array_keys($levels);

        $min = min(array_values($levels));
        $max = max(array_values($levels));

        if (!$current_level) {
            $current_level = self::getCurrentLevel($current_points);
        }

        $current_percentage = ($current_points > 0 ? $current_points / $max * 100 : 0);

        $stops = [];
        foreach ($levels as $name => $threshold) {
            $percentage = ($threshold > 0 ? $threshold / $max * 100 : 0);
            $currentKeyPos = array_search($name, $level_names);
            $targetKeyPos = array_search($current_level, $level_names);
            if ($currentKeyPos !== false && $targetKeyPos !== false) {
                $is_checked = ($currentKeyPos < $targetKeyPos ? true : false);
            }
            if ($name === $current_level) {
                $is_checked = true;
            }
            $stops[] = [
                'name'       => $name,
                'threshold'  => $threshold,
                'percentage' => $percentage,
                'is_checked' => $is_checked ?? false,
            ];
        }

        $stop_html = [];
        foreach ($stops as $stop) {
            $stop_html[] = '<div class="progress-stop" style="left: '.$stop['percentage'].'%;"><h6><span class="badge badge-pill '.($stop['is_checked'] ? 'badge-primary' : 'badge-secondary').'">'.$stop['name'].'<br><small>'.$stop['threshold'].' XP</small></span></h6></div>';
        }

        return '
            <div class="progress mt-2 mb-4" style="position: relative; overflow:visible;">
                <div class="progress-bar progress-bar-striped" role="progressbar" style="width: '.$current_percentage.'%;" aria-valuenow="'.$current_percentage.'" aria-valuemin="0" aria-valuemax="100"></div>
                '.implode('', $stop_html).'
            </div>
        ';
    }
}

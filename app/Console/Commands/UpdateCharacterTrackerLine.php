<?php

namespace App\Console\Commands;

use App\Models\Character\Character;
use App\Models\Tracker\Tracker;
use Illuminate\Console\Command;

class UpdateCharacterTrackerLine extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-character-tracker-line';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the character tracker line for all characters. May take time if there are many trackers to update.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info('******************');
        $this->info('* UPDATE CHARACTER TRACKER LINES *');
        $this->info('******************'."\n");

        // 1. Ask what the user wants to update via the name and the amount of points to change it to.
        $line_item = $this->ask('What tracker line item do you want to update? Please ensure the spelling and capitalization are correct. If you need to update a specific sub-item in the tracker use: "Group>Line Item to Update" (e.g., "Quest Points")');
        $points = $this->ask('What do you want to change the points to? Please enter a numeric value.');
        $group = null;
        if ($points === null || !is_numeric($points)) {
            $this->error('Invalid points value entered. Please enter a numeric value.');

            return;
        }
        if (str_contains($line_item, '>')) {
            $group = explode('>', $line_item)[0];
            $line_item = explode('>', $line_item)[1];
            $search_in_sublevel = true;
        }

        // 2. Fetch all characters that have trackers with the given name.
        $trackers = Tracker::where('data', 'LIKE', '%'.$line_item.'%')->get();
        $count = $trackers->count();
        $this->info("Found {$count} trackers with the line item '{$line_item}'.");

        if ($count === 0) {
            $this->info('No trackers found to update. Exiting command.');

            return;
        }

        // 3. Confirm the action.
        if ($this->confirm('Are you sure you want to update all '.$count.' character tracker lines? This may take a while.')) {
            // 4. Loop through each character and update their tracker line.
            $this->line("Updating character tracker lines...this may take some time.\n");
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            for ($i = 0; $i < $count; $i++) {
                //START: Card level
                $tracker = $trackers[$i];
                $data = $tracker->getDataAttribute();
                $updated = false;

                if (!$search_in_sublevel) {
                    // Loop through each line item in the data.
                    foreach ($data as $name => &$value) {
                        if (isset($value['sub_card'])) {
                            // If it's a sub-card, loop through those items.
                            foreach ($value['sub_card'] as $sub_name => &$sub_value) {
                                if ($sub_name === $line_item) {
                                    $sub_value = (int) $points;
                                    $updated = true;
                                }
                            }
                        } else {
                            if ($name === $line_item) {
                                $value = (int) $points;
                                $updated = true;
                            }
                        }
                    }
                } else {
                    foreach ($data as $name => &$value) {
                        if (isset($value['sub_card'])) {
                            // If it's a sub-card, loop through those items.
                            foreach ($value['sub_card'] as $sub_name => &$sub_value) {
                                if (gettype($sub_value) !== 'array') {
                                    if ($sub_name === $line_item) {
                                        $sub_value = (int) $points;
                                        $updated = true;
                                    }
                                } else {
                                    //If this is still an array, loop deeper.
                                    foreach ($sub_value as $deep_name => &$deep_value) {
                                        if ($deep_name === $line_item) {
                                            $deep_value = (int) $points;
                                            $updated = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // If an update was made, save the tracker.
                if ($updated) {
                    $tracker->data = json_encode($data);
                    $tracker->save();
                }

                $bar->advance();
            }

            $bar->finish();
            $this->info("\n");
        } else {
            $this->info('Command cancelled. No changes were made.');
        }

        $this->info("\n".'******************');
        $this->info('* COMPLETE *');
    }
}

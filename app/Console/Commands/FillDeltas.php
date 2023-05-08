<?php

namespace App\Console\Commands;

use App\DataPoint;
use App\DataPointDelta;
use App\Petition;
use Illuminate\Console\Command;

class FillDeltas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'petitions:filldeltas {petitionId : The ID of the petition - this is the remote ID, not the local ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills in missing deltas for petition data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $petitionId = $this->argument('petitionId');
        $petition = Petition::where('remote_id', $petitionId)->first();
        if (empty($petition)) {
            $this->error('Petition is not registered for data collection');
        } else {
            $this->info('Updating deltas for petition ID ' . $petitionId . ' (Local ID: ' . $petition->id . ')');
            $this->info('Petition has description: ' . $petition->description);

            $dataPoints = DataPoint::where('petition_id', $petition->id)->get();
            // Clean out the deltas - it's easier to recreate them all!
            $existingDeltas = DataPointDelta::where('petition_id', $petition->id)->delete();

            $this->info('There are ' . count($dataPoints) . ' data points');
            $this->info('There were ' . count($existingDeltas) . ' existing deltas, but I have cleaned them out and will rebuild everything');

            $bar = $this->output->createProgressBar(count($dataPoints));

            // The first delta is null. We need to remember the previous data point and delta
            $previousDataPoint = $dataPoints->shift();

            $newDelta = new DataPointDelta;
            $newDelta->delta_timestamp = $previousDataPoint->data_timestamp;
            $newDelta->petition_id = $petition->id;
            $newDelta->delta = 0;
            $newDelta->save();

            $bar->advance();

            foreach ($dataPoints as $thisDataPoint) {

                $newDelta = new DataPointDelta;
                $newDelta->delta_timestamp = $previousDataPoint->data_timestamp;
                $newDelta->petition_id = $petition->id;
                $newDelta->delta = $thisDataPoint->count - $previousDataPoint->count;
                $newDelta->save();

                $previousDataPoint = $thisDataPoint;

                $bar->advance();
            }

            $bar->finish();

            $this->info('Done!');
        }

        return 0;
    }
}

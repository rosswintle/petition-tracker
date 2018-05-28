<?php

namespace App\Jobs;

use App\DataPoint;
use App\DataPointDelta;
use App\Http\Controllers\PetitionController;
use App\Jobs\Job;
use App\Petition;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UpdatePetitionData extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $petitionId;

    /**
     * Create a new job instance.
     *
     * @param $petitionId
     */
    public function __construct( $petitionId )
    {
        $this->petitionId = $petitionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle( PetitionController $controller )
    {

        $petition = Petition::find( $this->petitionId );

        if ( empty( $petition ) ) {

            Log::info( 'Could not find a petition entry in the database for petition ID ' . $this->petitionId );
            return;

        }

        try {

            $json = $controller->fetchPetitionJson($petition->remote_id);

        } catch (\Exception $e) {

            Log::info('Error while fetching petition data for petition ID ' . $petition->remote_id);
            // Something failed - retry later
            $controller->dispatchPetitionJob($petition->id);
            return;

        }

        try {

            $petitionData = \GuzzleHttp\json_decode($json);

        } catch (\Exception $e) {

            Log::info('Error while decoding petition JSON for petition ID ' . $petition->remote_id);
            // Something failed - retry later
            $controller->dispatchPetitionJob($petition->id);
            return;

        }

        if (empty($petitionData)) {

            Log::info('Petition data for petition ID ' . $petition->remote_id . ' was empty for some reason');
            // Something failed - retry later
            $controller->dispatchPetitionJob($petition->id);
            return;

        }

        $petitionAttributes = $petitionData->data->attributes;

        $date = date("Y-m-d H:i:s");

        // Fetch the last value for creating the delta later
        $lastDataPoint = DataPoint::where('petition_id', $this->petitionId)->orderBy('id', 'desc')->first();

        // Always add a data point. If petition is now closed then
        // this is a final data point.
        $dataPoint = new DataPoint();
        $dataPoint->data_timestamp = $date;
        $dataPoint->petition_id = $this->petitionId;
        $dataPoint->count = $petitionAttributes->signature_count;
        $dataPoint->save();


        $delta = new DataPointDelta();
        $delta->delta_timestamp = $date;
        $delta->petition_id = $this->petitionId;
        if (!empty($lastDataPoint)) {
            $delta->delta = $petitionAttributes->signature_count - $lastDataPoint->count;
        } else {
            $delta->delta = 0;
        }
        $delta->save();

        // Set the latest count on the petition too
        $petition->last_count = $petitionAttributes->signature_count;
        $petition->last_count_timestamp = $date;

        if ('open' == $petitionAttributes->state) {

            // Still open - set the next job
            $controller->dispatchPetitionJob($petition->id);

        } else {

            $petition->status = $petitionAttributes->state;

        }

        // Save any updates to the petition
        $petition->save();

    }
}

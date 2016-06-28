<?php

namespace App\Jobs;

use App\DataPoint;
use App\Http\Controllers\PetitionController;
use App\Jobs\Job;
use App\Petition;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        if ( ! empty( $petition ) ) {

            $json = $controller->fetchPetitionJson($petition->remote_id);

            try {

                $petitionData = \GuzzleHttp\json_decode($json);

            } catch (Exception $e) {

                // TODO: Error handling
                // return response( 'Error decoding the petition. It may have expired or been removed.', '503');

            }

            $petitionAttributes = $petitionData->data->attributes;

            // Always add a data point. If petition is now closed then
            // this is a final data point.
            $dataPoint = new DataPoint();
            $dataPoint->data_timestamp = date("Y-m-d H:i:s");
            $dataPoint->petition_id = $this->petitionId;
            $dataPoint->count = $petitionAttributes->signature_count;
            $dataPoint->save();

            // Set the latest count on the petition too
            $petition->last_count = $petitionAttributes->signature_count;
            $petition->last_count_timestamp = date("Y-m-d H:i:s");

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
}

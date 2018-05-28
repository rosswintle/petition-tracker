<?php

namespace App\Http\Controllers;

use App\DataPoint;
use App\DataPointDelta;
use App\Jobs\UpdatePetitionData;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Petition;
use Illuminate\Support\Facades\Log;

class PetitionController extends Controller
{

    public function fetchPetitionJson( $petitionId ) {
        $guzzle = new \GuzzleHttp\Client([
            'allow_redirects' => false,
        ]);

        try {
            $result = $guzzle->request('GET', 'https://petition.parliament.uk/petitions/' . $petitionId . '.json');
        } catch (\Exception $e) {
            Log::info('An exception occurred while fetching JSON for petition ' . $petitionId );
            return '';
        }

        if ( $result->getStatusCode() != 200 ) {
            Log::info('HTTP response code for fetching petition data for petition ID ' . $petitionId . ' was ' . $result->getStatusCode() );
            return '';
        }

        $json = (string) $result->getBody();
        return $json;
    }

    public function check( Request $request, $petitionId = null ) {

        if (is_null($petitionId)) {
            if ($request->has('petitionId')) {

                $petitionId = $request->input('petitionId');

            } else {

                return response('No petition specified', '503');

            }
        }

        $petition = Petition::firstOrNew([
            'remote_id' => $petitionId
        ]);

        if ( ! isset($petition->description) || empty($petition->description) ) {

            try {

                $json = $this->fetchPetitionJson( $petitionId );

            } catch ( Exception $e ) {



            }


            try {

                $petitionData = \GuzzleHttp\json_decode($json);

            } catch ( Exception $e ) {

                return response( 'Error decoding the petition. It may have expired or been removed.', '503');

            }

            $petitionAttributes = $petitionData->data->attributes;
            $petition->description = $petitionAttributes->action;
            $petition->status = $petitionAttributes->state;
            $petition->last_count = $petitionAttributes->signature_count;
            $petition->last_count_timestamp = date("Y-m-d H:i:s");

            $petition->save();

            if ('open' == $petition->status) {

                $this->dispatchPetitionJob($petition->id);

            }
        }

        //dd($petition);

        $dataPoints = DataPoint::where('petition_id', $petition->id)
            ->get();
        $deltas = DataPointDelta::where('petition_id', $petition->id)
            ->get();

        $chartDataLabels = array_pluck($dataPoints, 'data_timestamp');
        $chartDataValues = array_pluck($dataPoints, 'count');
        $chartDeltaValues = array_pluck($deltas, 'delta');

        return view('petitions.check', [
            'petitionId' => $petitionId,
            'petition' => $petition,
            'chartDataLabels' => $chartDataLabels,
            'chartDataValues' => $chartDataValues,
            'chartDeltaValues' => $chartDeltaValues,
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return null
     */
    public function update($id)
    {

        $petition = Petition::find( $id );

        if ( empty( $petition ) ) {

            Log::info( 'Could not find a petition entry in the database for petition ID ' . $this->petitionId );
            return;

        }

        try {

            $json = $this->fetchPetitionJson($petition->remote_id);

        } catch (\Exception $e) {

            Log::info('Error while fetching petition data for petition ID ' . $petition->remote_id);
            // Something failed - retry later
            $this->dispatchPetitionJob($petition->id);
            return;

        }

        try {

            $petitionData = \GuzzleHttp\json_decode($json);

        } catch (\Exception $e) {

            Log::info('Error while decoding petition JSON for petition ID ' . $petition->remote_id);
            // Something failed - retry later
            $this->dispatchPetitionJob($petition->id);
            return;

        }

        if (empty($petitionData)) {

            Log::info('Petition data for petition ID ' . $petition->remote_id . ' was empty for some reason');
            // Something failed - retry later
            $this->dispatchPetitionJob($petition->id);
            return;

        }

        $petitionAttributes = $petitionData->data->attributes;

        $date = date("Y-m-d H:i:s");

        // Fetch the last value for creating the delta later
        $lastDataPoint = DataPoint::where('petition_id', $id)->orderBy('id', 'desc')->first();

        // Always add a data point. If petition is now closed then
        // this is a final data point.
        $dataPoint = new DataPoint();
        $dataPoint->data_timestamp = $date;
        $dataPoint->petition_id = $id;
        $dataPoint->count = $petitionAttributes->signature_count;
        $dataPoint->save();


        $delta = new DataPointDelta();
        $delta->delta_timestamp = $date;
        $delta->petition_id = $id;
        if (!empty($lastDataPoint)) {
            $delta->delta = $petitionAttributes->signature_count - $lastDataPoint->count;
        } else {
            $delta->delta = 0;
        }
        $delta->save();

        // Set the latest count on the petition too
        $petition->last_count = $petitionAttributes->signature_count;
        $petition->last_count_timestamp = $date;

        if ('open' != $petitionAttributes->state) {

            $petition->status = $petitionAttributes->state;

        }

        // Save any updates to the petition
        $petition->save();

        return;

    }

    public function updateAll() {

        $activePetitions = Petition::where('status', 'open')->get();

        $activePetitions->pluck('id')->map( [ $this, 'update' ] );

    }

    /**
     * Adds a data collection job for the specified
     *
     * @param $id
     */
    public function dispatchPetitionJob($id)
    {
        // Stick the job on the queue
        $job = (new UpdatePetitionData($id))->delay(60 * 5);
        $this->dispatch($job);
    }

    public function checkJobs() {
        $petitions = Petition::where('status', 'open')->get();
        dd($petitions);
    }

}

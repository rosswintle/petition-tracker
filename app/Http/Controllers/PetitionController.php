<?php

namespace App\Http\Controllers;

use App\DataPoint;
use App\DataPointDelta;
use App\Jobs\UpdatePetitionData;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Petition;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PetitionController extends Controller
{

    public function fetchPetitionJson( $petitionId ) {
        $guzzle = new \GuzzleHttp\Client([
            'allow_redirects' => true,
        ]);

        try {
            $result = $guzzle->request('GET', 'https://petition.parliament.uk/petitions/' . $petitionId . '.json');
        } catch (\Exception $e) {
            Log::info('An exception occurred while fetching JSON for petition ' . $petitionId );
            Log::info($e->getMessage());
            return '';
        }

        if ( $result->getStatusCode() != 200 ) {
            Log::info('HTTP response code for fetching petition data for petition ID ' . $petitionId . ' was ' . $result->getStatusCode() );
            return '';
        }

        $json = (string) $result->getBody();
        return $json;
    }

    public function check( Request $request, $petitionId = null, $startTime = null, $timeFrameLabel = null ) {

        if (is_null($petitionId)) {
            if ($request->has('petitionId')) {

                $petitionId = $request->input('petitionId');

                $petition = Petition::where('remote_id', $petitionId);

                if ( $petition ) {
                    return redirect()->route( 'check-petition', [ 'petition_id' => $petitionId ] );
                }

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

            } catch ( \Exception $e ) {

                // TODO: Handle error on initial fetch

            }


            try {

                $petitionData = \GuzzleHttp\json_decode($json);

            } catch ( \Exception $e ) {

                return response( 'Error decoding the petition. It may have expired or been removed.', '503');

            }

            $petitionAttributes = $petitionData->data->attributes;
            $petition->description = $petitionAttributes->action;
            $petition->status = $petitionAttributes->state;
            $petition->last_count = $petitionAttributes->signature_count;
            $petition->last_count_timestamp = date("Y-m-d H:i:s");

            $petition->save();

            // This was a new petition, probably. So redirect to the petition URL
            return redirect()->route('check-petition', $petitionId);

        }

        if (!is_null($startTime)) {
            $dataPoints = DataPoint::where('petition_id', $petition->id)
                ->where('data_timestamp', '>', $startTime)
                ->get();
            $deltas = DataPointDelta::where('petition_id', $petition->id)
                ->where('delta_timestamp', '>', $startTime)
                ->get();
        } else {
            $dataPoints = DataPoint::where('petition_id', $petition->id)
                ->get();
            $deltas = DataPointDelta::where('petition_id', $petition->id)
                ->get();
        }

        $chartDataXY = $dataPoints->map(function ($dataPoint) {
            $timestamp = Carbon::parse($dataPoint->data_timestamp)->getTimestamp();
            return ['x' => $timestamp, 'y' => $dataPoint->count];
        });
        $chartDeltaXY = $deltas->map(function ($dataPoint) {
            $timestamp = Carbon::parse($dataPoint->delta_timestamp)->getTimestamp();
            return ['x' => $timestamp, 'y' => $dataPoint->delta];
        });

        return view('petitions.check', [
            'petitionId' => $petitionId,
            'petition' => $petition,
            'chartDataValues' => $chartDataXY,
            'chartDeltaValues' => $chartDeltaXY,
            'timeFrameLabel' => $timeFrameLabel,
        ]);

    }

    public function checkMonth(Request $request, $petitionId = null)
    {
        return $this->check($request, $petitionId, Carbon::now()->subDays(30)->toDateTimeString(), 'month');
    }

    public function checkWeek(Request $request, $petitionId = null)
    {
        return $this->check($request, $petitionId, Carbon::now()->subDays(7)->toDateTimeString(), 'week');
    }

    public function checkDay(Request $request, $petitionId = null)
    {
        return $this->check($request, $petitionId, Carbon::now()->subHours(24)->toDateTimeString(), 'day');
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
            $petition->markError();
            return;

        }

        try {

            $petitionData = \GuzzleHttp\json_decode($json);

        } catch (\Exception $e) {

            Log::info('Error while decoding petition JSON for petition ID ' . $petition->remote_id);
            $petition->markError();
            return;

        }

        if (empty($petitionData)) {

            Log::info('Petition data for petition ID ' . $petition->remote_id . ' was empty for some reason');
            $petition->markMissing();
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

        // Save any updates to the petition
        $petition->save();

        $petition->updateStatus($petitionAttributes->state);

        return;

    }

    public function updateAll() {

        $activePetitions = Petition::whereIn('status', ['open', 'error'])->get();

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

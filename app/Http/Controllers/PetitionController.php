<?php

namespace App\Http\Controllers;

use App\Models\DataPoint;
use App\Models\DataPointDelta;
use App\Models\Petition;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PetitionController extends Controller
{
    public function fetchPetitionJson(string $petitionId): string
    {
        $response = Http::withOptions(
            [
                'allow_redirects' => true,
            ]
        )->get('https://petition.parliament.uk/petitions/'.$petitionId.'.json');

        if ($response->failed()) {
            Log::info('An exception occurred while fetching JSON for petition '.$petitionId);
            Log::info($response->body());

            return '';
        }

        if ($response->status() != 200) {
            Log::info('HTTP response code for fetching petition data for petition ID '.$petitionId.' was '.$response->status());

            return '';
        }

        return (string) $response->body();
    }

    public function check(Request $request, ?string $petitionId = null, ?string $startTime = null, ?string $timeFrameLabel = null)
    {
        if (is_null($petitionId)) {

            if ($request->has('petitionId')) {

                $petitionId = $request->input('petitionId');
                if (! Str::isMatch('/\d+/', $petitionId)) {
                    return response('Invalid petition id.', 400);
                }

                $petition = Petition::where('remote_id', $petitionId);

                if ($petition) {
                    return redirect()->route('check-petition', ['petition_id' => $petitionId]);
                }

            } else {

                return response('No petition specified', '503');

            }
        }

        $petition = Petition::firstOrNew(
            [
                'remote_id' => $petitionId,
            ]
        );

        if (empty($petition->description)) {

            $json = $this->fetchPetitionJson($petitionId);

            $petitionData = json_decode($json);

            if (is_null($petitionData)) {
                return response('Error decoding the petition. It may have expired or been removed.', '503');
            }

            $petitionAttributes = $petitionData->data->attributes;
            $petition->description = $petitionAttributes->action;
            $petition->status = $petitionAttributes->state;
            $petition->last_count = $petitionAttributes->signature_count;
            $petition->last_count_timestamp = date('Y-m-d H:i:s');

            $petition->save();

            // This was a new petition, probably. So redirect to the petition URL
            return redirect()->route('check-petition', $petitionId);

        }

        if (! is_null($startTime)) {
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

        $chartDataXY = $dataPoints->map(
            function ($dataPoint) {
                $timestamp = Carbon::parse($dataPoint->data_timestamp)->getTimestamp();

                return ['x' => $timestamp, 'y' => $dataPoint->count];
            }
        );
        $chartDeltaXY = $deltas->map(
            function ($dataPoint) {
                $timestamp = Carbon::parse($dataPoint->delta_timestamp)->getTimestamp();

                return ['x' => $timestamp, 'y' => $dataPoint->delta];
            }
        );

        return view(
            'petitions.check', [
                'petitionId' => $petitionId,
                'petition' => $petition,
                'chartDataValues' => $chartDataXY,
                'chartDeltaValues' => $chartDeltaXY,
                'timeFrameLabel' => $timeFrameLabel,
            ]
        );

    }

    public function checkMonth(Request $request, ?string $petitionId = null)
    {
        return $this->check($request, $petitionId, Carbon::now()->subDays(30)->toDateTimeString(), 'month');
    }

    public function checkWeek(Request $request, ?string $petitionId = null)
    {
        return $this->check($request, $petitionId, Carbon::now()->subDays(7)->toDateTimeString(), 'week');
    }

    public function checkDay(Request $request, ?string $petitionId = null)
    {
        return $this->check($request, $petitionId, Carbon::now()->subHours(24)->toDateTimeString(), 'day');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id)
    {
        $petition = Petition::find($id);

        if (empty($petition)) {
            Log::info('Could not find a petition entry in the database for petition ID '.$this->petitionId);
            return;
        }

        $json = $this->fetchPetitionJson($petition->remote_id);

        $petitionData = json_decode($json);

        if (is_null($petitionData)) {
            $petition->markError();
            return response('Error while decoding petition JSON for petition ID '.$petition->remote_id, '503');
        }

        if (empty($petitionData)) {
            $petition->markMissing();
            return response('Petition data for petition ID '.$petition->remote_id.' was empty for some reason', 503);
        }

        $petitionAttributes = $petitionData->data->attributes;

        $date = date('Y-m-d H:i:s');

        // Fetch the last value for creating the delta later
        $lastDataPoint = DataPoint::where('petition_id', $id)->orderBy('id', 'desc')->first();

        // Always add a data point. If petition is now closed then
        // this is a final data point.
        $dataPoint = new DataPoint;
        $dataPoint->data_timestamp = $date;
        $dataPoint->petition_id = $id;
        $dataPoint->count = $petitionAttributes->signature_count;
        $dataPoint->save();

        $delta = new DataPointDelta;
        $delta->delta_timestamp = $date;
        $delta->petition_id = $id;
        if (! empty($lastDataPoint)) {
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
    }

    public function updateAll()
    {
        $activePetitions = Petition::whereIn('status', ['open', 'error'])->get();
        $activePetitions->pluck('id')->map([$this, 'update']);
    }

    /**
     * Adds a data collection job for the specified
     */
    public function dispatchPetitionJob($id)
    {
        // Stick the job on the queue
        $job = (new UpdatePetitionData($id))->delay(60 * 5);
        $this->dispatch($job);
    }

    public function checkJobs()
    {
        $petitions = Petition::where('status', 'open')->get();
        dd($petitions);
    }
}

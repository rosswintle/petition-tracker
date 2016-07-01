<?php

namespace App\Http\Controllers;

use App\DataPoint;
use App\DataPointDelta;
use App\Jobs\UpdatePetitionData;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Petition;

class PetitionController extends Controller
{

    public function fetchPetitionJson( $petitionId ) {
        $guzzle = new \GuzzleHttp\Client();
        $result = $guzzle->request('GET', 'https://petition.parliament.uk/petitions/' . $petitionId . '.json');
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

        if ( ! isset($petition->description) ) {

            $json = $this->fetchPetitionJson( $petitionId );

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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

}

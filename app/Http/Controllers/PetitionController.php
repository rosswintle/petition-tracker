<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class PetitionController extends Controller
{

    public function check( Request $request, $petitionId = null ) {

        if (is_null($petitionId) && $request->has('petitionId')) {

            $petitionId = $request->input('petitionId');

        } else {

            return response( 'No petition specified', '503' );

        }

        $guzzle = new \GuzzleHttp\Client();
        $result = $guzzle->request('GET', 'https://petition.parliament.uk/petitions/' . $petitionId . '.json');
        $json = (string) $result->getBody();

        try {

            $petitionData = \GuzzleHttp\json_decode($json);

        } catch {

            return response( 'Error decoding the petition. It may have expired or been removed.', '503');

        }

        //dd($petitionData);

        return view('petitions.check', [
            'petitionId' => $petitionId,
            'petitionData' => $petitionData ]);

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
}

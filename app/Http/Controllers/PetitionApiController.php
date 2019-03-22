<?php

namespace App\Http\Controllers;

use App\DataPoint;
use App\Petition;
use Illuminate\Http\Request;

class PetitionApiController extends Controller
{
    public function show($petitionId)
    {
        $petition = Petition::where([
            'remote_id' => $petitionId
        ])->firstOrFail();

        $dataPoints = DataPoint::where('petition_id', $petition->id)
            ->get();
//        $deltas = DataPointDelta::where('petition_id', $petition->id)
//            ->get();

        return $dataPoints;
    }
}

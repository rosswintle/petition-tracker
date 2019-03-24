<?php

namespace App\Http\Controllers;

use App\DataPoint;
use App\Petition;
use Illuminate\Http\Request;
use League\Csv\Writer;

class PetitionApiController extends Controller
{
    public function show($petitionId)
    {
        $petition = Petition::where([
            'remote_id' => $petitionId
        ])->firstOrFail();

        $dataPoints = DataPoint::where('petition_id', $petition->id)
            ->get();

        return $dataPoints;
    }

    public function showCsv($petitionId)
    {
        $petition = Petition::where([
            'remote_id' => $petitionId
        ])->firstOrFail();

        $dataPoints = DataPoint::where('petition_id', $petition->id)
            ->get();

        $csv = Writer::createFromString('');
        $csv->insertOne(['timestamp', 'signatures']);

        $dataPoints->each(function ($item, $key) use ($csv) {
            $csv->insertOne([$item->data_timestamp, $item->count]);
        });

        $csv->output($petition->remote_id . '.csv');
        die();
    }

}

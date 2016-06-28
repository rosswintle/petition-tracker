<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use App\Petition;

Route::get('/', function () {
    $petitions = Petition::where('status','open')
        ->get();
    return view('welcome', ['petitions' => $petitions]);
});

Route::get('/test/{petition_id}', function ( $petitionId ) {
    $guzzle = new \GuzzleHttp\Client();
    $result = $guzzle->request('GET', 'https://petition.parliament.uk/petitions/' . $petitionId . '.json');
    $output = (string) $result->getBody();
    dd($output);
});

Route::get('/check-petition/{petition_id}', 'PetitionController@Check')->name('check-petition');
Route::get('/check-petition/', 'PetitionController@Check');
Route::post('/check-petition/', 'PetitionController@Check');

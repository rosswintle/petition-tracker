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
    $petitions = Petition::whereIn('status',['open','error'])
        ->orderBy('remote_id', 'desc')
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
Route::get('/check-petition/{petition_id}/month/', 'PetitionController@CheckMonth')->name('check-petition-month');
Route::get('/check-petition/{petition_id}/week/', 'PetitionController@CheckWeek')->name('check-petition-week');
Route::get('/check-petition/{petition_id}/day/', 'PetitionController@CheckDay')->name('check-petition-day');
Route::get('/check-petition/', 'PetitionController@Check');
Route::post('/check-petition/', 'PetitionController@Check');

Route::group(['prefix' => '/api/v1'], function () {
    Route::get('/petition/{petition_id}', 'PetitionApiController@show');
    Route::get('/petition/{petition_id}/csv', 'PetitionApiController@showCsv');
});

Route::get('/check-jobs/', 'PetitionController@checkJobs');
Route::get('/update-all/', 'PetitionController@updateAll');

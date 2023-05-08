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

use App\Http\Controllers\PetitionApiController;
use App\Http\Controllers\PetitionController;
use Illuminate\Support\Facades\Route;
use App\Petition;

Route::get('/', function () {
    $petitions = Petition::whereIn('status', ['open', 'error'])
        ->orderBy('remote_id', 'desc')
        ->get();
    return view('welcome', ['petitions' => $petitions]);
});

Route::get('/test/{petition_id}', function ($petitionId) {
    $guzzle = new \GuzzleHttp\Client();
    $result = $guzzle->request('GET', 'https://petition.parliament.uk/petitions/' . $petitionId . '.json');
    $output = (string) $result->getBody();
    dd($output);
});

Route::get('/check-petition/{petition_id}', [PetitionController::class, 'check'])->name('check-petition');
Route::get('/check-petition/{petition_id}/month/', [PetitionController::class, 'checkMonth'])->name('check-petition-month');
Route::get('/check-petition/{petition_id}/week/', [PetitionController::class, 'checkWeek'])->name('check-petition-week');
Route::get('/check-petition/{petition_id}/day/', [PetitionController::class, 'checkDay'])->name('check-petition-day');
Route::get('/check-petition/', [PetitionController::class, 'check'])->name('check-petition-get');
Route::post('/check-petition/', [PetitionController::class, 'check'])->name('check-petition-post');

Route::group(['prefix' => '/api/v1'], function () {
    Route::get('/petition/{petition_id}', [PetitionApiController::class, 'show']);
    Route::get('/petition/{petition_id}/csv', [PetitionApiController::class, 'showCsv']);
});

Route::get('/check-jobs/', [PetitionController::class, 'checkJobs']);
Route::get('/update-all/', [PetitionController::class, 'updateAll']);

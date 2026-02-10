<?php

use App\Http\Controllers\PetitionController;
// use App\Http\Controllers\PetitionApiController;
use App\Models\Petition;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $petitions = Petition::whereIn('status', ['open', 'error'])
        ->orderBy('remote_id', 'desc')
        ->get();

    return view('welcome', ['petitions' => $petitions]);
});

Route::get('/test/{petition_id}', function ($petitionId) {
    $p = new PetitionController;
    $result = $p->fetchPetitionJson($petitionId);
    $json = json_decode($result, true);
    dd($json);
});

Route::get('/check-petition/{petition_id}', [PetitionController::class, 'check'])->name('check-petition');
Route::get('/check-petition/{petition_id}/month/', [PetitionController::class, 'checkMonth'])->name('check-petition-month');
Route::get('/check-petition/{petition_id}/week/', [PetitionController::class, 'checkWeek'])->name('check-petition-week');
Route::get('/check-petition/{petition_id}/day/', [PetitionController::class, 'checkDay'])->name('check-petition-day');
Route::get('/check-petition/', [PetitionController::class, 'check'])->name('check-petition-get');
Route::post('/check-petition/', [PetitionController::class, 'check'])->name('check-petition-post');

Route::group(['prefix' => '/api/v1'], function () {
//    Route::get('/petition/{petition_id}', [PetitionApiController::class, 'show']);
//    Route::get('/petition/{petition_id}/csv', [PetitionApiController::class, 'showCsv']);
});

Route::get('/check-jobs/', [PetitionController::class, 'checkJobs']);
Route::get('/update-all/', [PetitionController::class, 'updateAll']);

/**
 * ADMIN ROUTES
 */
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';

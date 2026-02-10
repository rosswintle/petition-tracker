<?php

use Illuminate\Support\Facades\Schedule;

Schedule::call(
    function () {
        resolve('\App\Http\Controllers\PetitionController')->updateAll();
    }
)->everyFiveMinutes()->name('update-petitions')->withoutOverlapping();

<?php

use Illuminate\Support\Facades\Route;
use Lareon\Modules\Ticketing\App\Http\Controllers\Web\Panel\Tickets\TicketsController;

Route::prefix('ticketing')->name('tickets.')->group(function () {
    Route::get('/', [TicketsController::class ,'index'])->name('index');
    Route::post('/', [TicketsController::class ,'store'])->name('store');
    Route::get('/create', [TicketsController::class ,'create'])->name('create');
});

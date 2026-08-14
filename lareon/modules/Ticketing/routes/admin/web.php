<?php

use Illuminate\Support\Facades\Route;
use Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\Tickets\TicketsController;

Route::prefix('ticketing')->name('tickets.')->group(function () {
    Route::get('/', [TicketsController::class ,'index'])->name('index');
});

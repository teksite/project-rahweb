<?php

use Illuminate\Support\Facades\Route;
use Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\Tickets\TicketsController;

Route::prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', [TicketsController::class ,'index'])->name('index');
    Route::get('/{ticket}', [TicketsController::class ,'edit'])->name('edit');
    Route::patch('/{ticket}', [TicketsController::class ,'update'])->name('update');
    Route::delete('/{ticket}', [TicketsController::class ,'destroy'])->name('destroy');
});

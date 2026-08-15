<?php

use Illuminate\Support\Facades\Route;
use Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\Log\TicketLogsController;
use Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\Tickets\TicketsController;

Route::resource('tickets', TicketsController::class)->except(['create', 'store']);
Route::patch('/tickets', [TicketsController::class , 'bulk'])->name('tickets.bulk');
Route::patch('/tickets/log', [TicketsController::class , 'index'])->name('tickets.logs.index');
Route::patch('/tickets/log/{item}', [TicketLogsController::class , 'show'])->name('tickets.logs.show');

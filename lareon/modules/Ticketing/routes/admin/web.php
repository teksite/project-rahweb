<?php

use Illuminate\Support\Facades\Route;
use Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\requests\TicketApiRequestsController;
use Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\Tickets\TicketsController;

Route::get('/tickets/requests/{item}', [TicketApiRequestsController::class , 'show'])->name('tickets.requests.show');
Route::get('/tickets/requests', [TicketApiRequestsController::class , 'index'])->name('tickets.requests.index');
Route::resource('tickets', TicketsController::class)->except(['create', 'store']);
Route::patch('/tickets', [TicketsController::class , 'bulk'])->name('tickets.bulk');

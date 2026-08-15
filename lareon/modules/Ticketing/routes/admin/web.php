<?php

use Illuminate\Support\Facades\Route;
use Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\Tickets\TicketsController;

Route::resource('tickets', TicketsController::class)->except(['create', 'store']);

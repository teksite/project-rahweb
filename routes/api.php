<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/endpoint', function (Request $request) {
    if (random_int(0, 1) === 0) {
        return response()->json([
            'success' => false,
            'message' => 'Internal server error',
        ], 500);
    }

    return response()->json([
        'success' => true,
        'message' => 'Ticket received successfully',
    ], 200);
});

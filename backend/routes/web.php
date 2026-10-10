<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/health', function () {
    return response()->json([
        'message' => 'Hello from TicketFlow API',
    ]);
});

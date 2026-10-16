<?php

use App\Http\Controllers\Api\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/tickets', [TicketController::class, 'index']);

Route::middleware('auth:sanctum')->get('/tickets/{ticket}', [TicketController::class, 'show']);

Route::middleware('auth:sanctum')->post('/tickets', [TicketController::class, 'store']);

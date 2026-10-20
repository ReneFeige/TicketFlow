<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TicketCommentController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/tickets', [TicketController::class, 'index']);

Route::middleware('auth:sanctum')->get('/tickets/{ticket}', [TicketController::class, 'show']);

Route::middleware('auth:sanctum')->post('/tickets', [TicketController::class, 'store']);

Route::middleware('auth:sanctum')->post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store']);

Route::middleware('auth:sanctum')->get('/tickets/{ticket}/comments', [TicketCommentController::class, 'index']);

Route::middleware('auth:sanctum')->get('/categories', [CategoryController::class, 'index']);

Route::middleware('auth:sanctum')->patch('/tickets/{ticket}', [TicketController::class, 'update']);

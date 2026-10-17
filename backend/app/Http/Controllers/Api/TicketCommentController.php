<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TicketCommentController extends Controller
{
    public function store(
        StoreCommentRequest $request,
        Ticket $ticket
    ): JsonResponse {
        $comment = $ticket->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return response()->json($comment, 201);
    }

    public function index(Ticket $ticket): JsonResponse
    {
        Gate::authorize('view', $ticket);

        $comments = $ticket->comments()
            ->with('user:id,name')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return response()->json($comments);
    }
}

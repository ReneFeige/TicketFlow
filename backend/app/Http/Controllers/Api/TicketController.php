<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = Ticket::create([
            ...$request->validated(),
            'customer_id' => $request->user()->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        return response()->json($ticket, 201);
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Ticket::class);

        $validated = $request->validate([
            'sort' => ['sometimes', 'string', Rule::in(['newest', 'priority'])],
        ]);

        $sort = $validated['sort'] ?? 'newest';

        $user = $request->user();

        $query = Ticket::query();

        if ($user->role === 'customer') {
            $query->where('customer_id', $user->id);
        }

        if ($sort === 'priority') {
            $query->orderByRaw("
            CASE priority
                WHEN 'high' THEN 1
                WHEN 'medium' THEN 2
                WHEN 'low' THEN 3
                ELSE 4
            END
        ");
        }

        $tickets = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return response()->json($tickets);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        Gate::authorize('view', $ticket);

        return response()->json($ticket);
    }

    public function update(
        UpdateTicketRequest $request,
        Ticket $ticket
    ): JsonResponse {
        $ticket->update($request->validated());

        return response()->json($ticket);
    }
}

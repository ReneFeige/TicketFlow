<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Ticket::class);

        $user = $request->user();

        $query = Ticket::query();

        if ($user->role === 'customer') {
            $query->where('customer_id', $user->id);
        }

        $tickets = $query->get();

        return response()->json($tickets);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        Gate::authorize('view', $ticket);

        return response()->json($ticket);
    }
}

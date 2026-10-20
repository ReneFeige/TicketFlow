<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Ticket::class);

        $user = $request->user();

        $query = Ticket::query();

        if ($user->role === 'customer') {
            $query->where('customer_id', $user->id);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
        ];

        $recentTickets = (clone $query)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'title', 'status', 'priority', 'created_at']);

        return response()->json([
            'stats' => $stats,
            'recent_tickets' => $recentTickets,
        ]);
    }
}

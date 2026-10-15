<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Ticket::query();

        if ($user->role === 'customer') {
            $query->where('customer_id', $user->id);
        } elseif (! in_array($user->role, ['agent', 'admin'], true)) {
            abort(403);
        }

        $tickets = $query->get();

        return response()->json($tickets);
    }
}

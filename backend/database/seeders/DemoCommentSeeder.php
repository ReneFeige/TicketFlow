<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoCommentSeeder extends Seeder
{
    public function run(): void
    {
        $agent = User::where('email', 'agent@ticketflow.demo')->first();

        if (! $agent) {
            throw new RuntimeException('Demo agent does not exist. Run DemoUserSeeder first.');
        }

        $ticket = Ticket::where('title', 'Laptop does not start')
            ->whereHas('customer', fn ($query) => $query->where('email', 'customer@ticketflow.demo'))
            ->first();

        if (! $ticket) {
            throw new RuntimeException('Demo ticket does not exist. Run DemoTicketSeeder first.');
        }

        $comments = [
            [
                'user_id' => $ticket->customer_id,
                'body' => 'I have already tried restarting the laptop several times.',
            ],
            [
                'user_id' => $agent->id,
                'body' => 'Thanks for the information. Does the power indicator light up?',
            ],
            [
                'user_id' => $ticket->customer_id,
                'body' => 'No, the power indicator stays off.',
            ],
        ];

        foreach ($comments as $commentData) {
            Comment::firstOrCreate(
                [
                    'ticket_id' => $ticket->id,
                    'user_id' => $commentData['user_id'],
                    'body' => $commentData['body'],
                ],
            );
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketRelationshipsTest extends TestCase
{
    public function test_ticket_category_and_customer_relationships(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test Customer',
            'email' => 'customer@example.test',
            'password' => 'test-password',
            'role' => 'customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Technical Issue',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ticketId = DB::table('tickets')->insertGetId([
            'customer_id' => $userId,
            'category_id' => $categoryId,
            'title' => 'Login problem',
            'description' => 'Unable to log in.',
            'status' => 'open',
            'priority' => 'medium',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ticket = Ticket::findOrFail($ticketId);

        $this->assertSame($categoryId, $ticket->category->id);
        $this->assertSame('Technical Issue', $ticket->category->name);

        $category = Category::findOrFail($categoryId);

        $this->assertCount(1, $category->tickets);
        $this->assertSame($ticketId, $category->tickets->first()->id);

        // Ticket -> Customer
        $this->assertSame($userId, $ticket->customer->id);

        // User -> Tickets
        $user = User::findOrFail($userId);

        $this->assertCount(1, $user->tickets);
        $this->assertSame($ticketId, $user->tickets->first()->id);

        $commentId = DB::table('comments')->insertGetId([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'body' => 'I am experiencing the same issue.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $comment = Comment::findOrFail($commentId);

        // Comment -> Ticket
        $this->assertSame($ticketId, $comment->ticket->id);

        // Comment -> User
        $this->assertSame($userId, $comment->user->id);

        // Ticket -> Comments
        $this->assertCount(1, $ticket->comments);
        $this->assertSame($commentId, $ticket->comments->first()->id);

        // User -> Comments
        $this->assertCount(1, $user->comments);
        $this->assertSame($commentId, $user->comments->first()->id);
    }
}

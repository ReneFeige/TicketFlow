<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TicketCommentStoreTest extends TestCase
{
    public function test_customer_can_comment_on_own_open_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($customer)
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'I have already tried resetting my password.',
            ]);

        $response->assertCreated()
            ->assertJsonPath('body', 'I have already tried resetting my password.')
            ->assertJsonPath('user.id', $customer->id)
            ->assertJsonPath('user.name', $customer->name)
            ->assertJsonMissingPath('user.email');

        $response->assertJsonFragment([
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'body' => 'I have already tried resetting my password.',
        ]);

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'body' => 'I have already tried resetting my password.',
        ]);
    }

    public function test_customer_cannot_comment_on_another_customers_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $anotherCustomer = User::factory()->create([
            'role' => 'customer',
        ]);

        $ticket = Ticket::factory()->create([
            'customer_id' => $anotherCustomer->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($customer)
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'I should not be allowed to comment here.',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseCount('comments', 0);
    }

    #[DataProvider('commentRoles')]
    public function test_user_cannot_comment_on_closed_ticket(string $role): void
    {
        $user = User::factory()->create([
            'role' => $role,
        ]);

        $ticket = Ticket::factory()->create([
            'customer_id' => $user->id,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'Can you help me again?',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseCount('comments', 0);
    }

    public static function commentRoles(): array
    {
        return [
            'customer' => ['customer'],
            'agent' => ['agent'],
            'admin' => ['admin'],
        ];
    }

    #[DataProvider('supportRoles')]
    public function test_support_staff_can_comment_on_customer_ticket(string $role): void
    {
        $user = User::factory()->create([
            'role' => $role,
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'We are investigating your issue.',
            ]);

        $response->assertCreated();

        $response->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.name', $user->name);

        $response->assertJsonPath('ticket_id', $ticket->id);

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => 'We are investigating your issue.',
        ]);
    }

    public static function supportRoles(): array
    {
        return [
            'agent' => ['agent'],
            'admin' => ['admin'],
        ];
    }

    public function test_customer_cannot_override_comment_author_or_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $anotherCustomer = User::factory()->create([
            'role' => 'customer',
        ]);

        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'open',
        ]);

        $anotherTicket = Ticket::factory()->create([
            'customer_id' => $anotherCustomer->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($customer)
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'This is my comment.',
                'user_id' => $anotherCustomer->id,
                'ticket_id' => $anotherTicket->id,
            ]);

        $response->assertCreated();

        $response->assertJsonPath('user_id', $customer->id);
        $response->assertJsonPath('ticket_id', $ticket->id);

        $this->assertDatabaseHas('comments', [
            'id' => $response->json('id'),
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'body' => 'This is my comment.',
        ]);
    }

    #[DataProvider('invalidCommentBodies')]
    public function test_customer_cannot_create_comment_with_invalid_body(
        array $invalidData
    ): void {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($customer)
            ->postJson("/api/tickets/{$ticket->id}/comments", $invalidData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['body']);

        $this->assertDatabaseCount('comments', 0);
    }

    public static function invalidCommentBodies(): array
    {
        return [
            'body missing' => [
                [],
            ],
            'body empty' => [
                ['body' => ''],
            ],
            'body is an array' => [
                ['body' => ['invalid', 'comment']],
            ],
            'body too long' => [
                ['body' => str_repeat('A', 5001)],
            ],
        ];
    }

    public function test_guest_cannot_create_comment(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => 'open',
        ]);

        $response = $this->postJson(
            "/api/tickets/{$ticket->id}/comments",
            [
                'body' => 'This should not be allowed.',
            ]
        );

        $response->assertUnauthorized();

        $this->assertDatabaseCount('comments', 0);
    }
}

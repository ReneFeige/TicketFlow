<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TicketCommentIndexTest extends TestCase
{
    public function test_customer_can_view_comments_on_own_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $agent = User::factory()->create([
            'role' => 'agent',
        ]);

        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'closed',
        ]);

        $ticket->comments()->create([
            'user_id' => $customer->id,
            'body' => 'I cannot log in.',
        ]);

        $ticket->comments()->create([
            'user_id' => $agent->id,
            'body' => 'We have resolved the issue.',
        ]);

        $response = $this->actingAs($customer)
            ->getJson("/api/tickets/{$ticket->id}/comments");

        $response->assertOk();

        $response->assertJsonCount(2);

        $response->assertJsonPath('0.body', 'I cannot log in.');
        $response->assertJsonPath('0.user.id', $customer->id);
        $response->assertJsonPath('0.user.name', $customer->name);

        $response->assertJsonPath('1.body', 'We have resolved the issue.');
        $response->assertJsonPath('1.user.id', $agent->id);
        $response->assertJsonPath('1.user.name', $agent->name);

        $response->assertJsonMissingPath('0.user.email');
        $response->assertJsonMissingPath('1.user.email');
    }

    public function test_customer_cannot_view_comments_on_another_customers_ticket(): void
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

        $ticket->comments()->create([
            'user_id' => $anotherCustomer->id,
            'body' => 'This is a private comment.',
        ]);

        $response = $this->actingAs($customer)
            ->getJson("/api/tickets/{$ticket->id}/comments");

        $response->assertForbidden();
    }

    #[DataProvider('supportRoles')]
    public function test_support_staff_can_view_comments_on_customer_ticket(
        string $role
    ): void {
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

        $ticket->comments()->create([
            'user_id' => $customer->id,
            'body' => 'I need help with my account.',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/tickets/{$ticket->id}/comments");

        $response->assertOk();

        $response->assertJsonCount(1);

        $response->assertJsonPath(
            '0.body',
            'I need help with my account.'
        );

        $response->assertJsonPath('0.user.id', $customer->id);
        $response->assertJsonPath('0.user.name', $customer->name);
    }

    public static function supportRoles(): array
    {
        return [
            'agent' => ['agent'],
            'admin' => ['admin'],
        ];
    }

    public function test_guest_cannot_view_ticket_comments(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => 'open',
        ]);

        $response = $this->getJson(
            "/api/tickets/{$ticket->id}/comments"
        );

        $response->assertUnauthorized();
    }
}

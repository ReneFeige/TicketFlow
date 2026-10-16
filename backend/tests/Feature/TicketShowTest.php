<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

class TicketShowTest extends TestCase
{
    public function test_customer_can_view_own_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $response = $this->actingAs($customer)
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $ticket->id,
            'title' => $ticket->title,
        ]);
    }

    public function test_customer_cannot_view_another_customers_ticket(): void
    {
        $customerA = User::factory()->create([
            'role' => 'customer',
        ]);

        $customerB = User::factory()->create([
            'role' => 'customer',
        ]);

        $ticket = Ticket::factory()->create([
            'customer_id' => $customerB->id,
        ]);

        $response = $this->actingAs($customerA)
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertForbidden();
    }

    public function test_agent_can_view_any_ticket(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent',
        ]);

        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($agent)
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $ticket->id,
            'title' => $ticket->title,
        ]);
    }

    public function test_admin_can_view_any_ticket(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($admin)
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $ticket->id,
            'title' => $ticket->title,
        ]);
    }

    public function test_guest_cannot_view_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->getJson("/api/tickets/{$ticket->id}");

        $response->assertUnauthorized();
    }

    public function test_nonexistent_ticket_returns_not_found(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)
            ->getJson('/api/tickets/999999');

        $response->assertNotFound();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

class TicketIndexTest extends TestCase
{
    public function test_customer_can_only_see_their_own_tickets(): void
    {
        $customerA = User::factory()->create([
            'role' => 'customer',
        ]);

        $customerB = User::factory()->create([
            'role' => 'customer',
        ]);

        $ticketA = Ticket::factory()->create([
            'customer_id' => $customerA->id,
        ]);

        $ticketB = Ticket::factory()->create([
            'customer_id' => $customerB->id,
        ]);

        $response = $this->actingAs($customerA)
            ->getJson('/api/tickets');

        $response->assertOk();

        $response->assertJsonCount(1);

        $response->assertJsonFragment([
            'id' => $ticketA->id,
        ]);

        $response->assertJsonMissing([
            'id' => $ticketB->id,
        ]);
    }

    public function test_agent_can_see_all_tickets(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent',
        ]);

        $ticketA = Ticket::factory()->create();
        $ticketB = Ticket::factory()->create();

        $response = $this->actingAs($agent)
            ->getJson('/api/tickets');

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['id' => $ticketA->id]);
        $response->assertJsonFragment(['id' => $ticketB->id]);
    }

    public function test_admin_can_see_all_tickets(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $ticketA = Ticket::factory()->create();
        $ticketB = Ticket::factory()->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/tickets');

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['id' => $ticketA->id]);
        $response->assertJsonFragment(['id' => $ticketB->id]);
    }

    public function test_guest_cannot_view_tickets(): void
    {
        $response = $this->getJson('/api/tickets');

        $response->assertUnauthorized();
    }

    public function test_tickets_are_sorted_by_newest_first_by_default(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $olderTicket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDays(2),
        ]);

        $newerTicket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($customer)
            ->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonPath('0.id', $newerTicket->id)
            ->assertJsonPath('1.id', $olderTicket->id);
    }

    public function test_tickets_can_be_sorted_by_newest_first(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $olderTicket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDays(2),
        ]);

        $newerTicket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($customer)
            ->getJson('/api/tickets?sort=newest')
            ->assertOk()
            ->assertJsonPath('0.id', $newerTicket->id)
            ->assertJsonPath('1.id', $olderTicket->id);
    }

    public function test_tickets_can_be_sorted_by_priority(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $lowTicket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'priority' => 'low',
        ]);

        $highTicket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'priority' => 'high',
        ]);

        $mediumTicket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'priority' => 'medium',
        ]);

        $this->actingAs($customer)
            ->getJson('/api/tickets?sort=priority')
            ->assertOk()
            ->assertJsonPath('0.id', $highTicket->id)
            ->assertJsonPath('1.id', $mediumTicket->id)
            ->assertJsonPath('2.id', $lowTicket->id);
    }

    public function test_tickets_with_same_priority_are_sorted_by_newest_first(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $olderTicket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'priority' => 'high',
            'created_at' => now()->subDays(2),
        ]);

        $newerTicket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'priority' => 'high',
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($customer)
            ->getJson('/api/tickets?sort=priority')
            ->assertOk()
            ->assertJsonPath('0.id', $newerTicket->id)
            ->assertJsonPath('1.id', $olderTicket->id);
    }

    public function test_invalid_sort_value_is_rejected(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->getJson('/api/tickets?sort=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');
    }

    public function test_customer_only_sees_own_tickets_when_sorting_by_priority(): void
    {
        $customerA = User::factory()->create([
            'role' => 'customer',
        ]);

        $customerB = User::factory()->create([
            'role' => 'customer',
        ]);

        $lowTicket = Ticket::factory()->create([
            'customer_id' => $customerA->id,
            'priority' => 'low',
        ]);

        $highTicket = Ticket::factory()->create([
            'customer_id' => $customerA->id,
            'priority' => 'high',
        ]);

        $otherTicket = Ticket::factory()->create([
            'customer_id' => $customerB->id,
            'priority' => 'high',
        ]);

        $this->actingAs($customerA)
            ->getJson('/api/tickets?sort=priority')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', $highTicket->id)
            ->assertJsonPath('1.id', $lowTicket->id)
            ->assertJsonMissing(['id' => $otherTicket->id]);
    }
}

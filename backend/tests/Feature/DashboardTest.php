<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_guest_cannot_view_dashboard(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertUnauthorized();
    }

    public function test_customer_dashboard_only_counts_own_tickets(): void
    {
        $customerA = User::factory()->create([
            'role' => 'customer',
        ]);

        $customerB = User::factory()->create([
            'role' => 'customer',
        ]);

        Ticket::factory()->create([
            'customer_id' => $customerA->id,
            'status' => 'open',
        ]);

        Ticket::factory()->create([
            'customer_id' => $customerA->id,
            'status' => 'in_progress',
        ]);

        Ticket::factory()->create([
            'customer_id' => $customerB->id,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($customerA)
            ->getJson('/api/dashboard');

        $response->assertOk();

        $response->assertJsonPath('stats.total', 2);
        $response->assertJsonPath('stats.open', 1);
        $response->assertJsonPath('stats.in_progress', 1);
        $response->assertJsonPath('stats.closed', 0);
    }

    public function test_agent_dashboard_counts_all_tickets(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent',
        ]);

        $customerA = User::factory()->create([
            'role' => 'customer',
        ]);

        $customerB = User::factory()->create([
            'role' => 'customer',
        ]);

        Ticket::factory()->create([
            'customer_id' => $customerA->id,
            'status' => 'open',
        ]);

        Ticket::factory()->create([
            'customer_id' => $customerB->id,
            'status' => 'in_progress',
        ]);

        Ticket::factory()->create([
            'customer_id' => $customerB->id,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($agent)
            ->getJson('/api/dashboard');

        $response->assertOk();

        $response->assertJsonPath('stats.total', 3);
        $response->assertJsonPath('stats.open', 1);
        $response->assertJsonPath('stats.in_progress', 1);
        $response->assertJsonPath('stats.closed', 1);
    }

    public function test_admin_dashboard_counts_all_tickets(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Ticket::factory()->create([
            'status' => 'open',
        ]);

        Ticket::factory()->create([
            'status' => 'closed',
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/dashboard');

        $response->assertOk();

        $response->assertJsonPath('stats.total', 2);
        $response->assertJsonPath('stats.open', 1);
        $response->assertJsonPath('stats.in_progress', 0);
        $response->assertJsonPath('stats.closed', 1);
    }

    public function test_dashboard_returns_zero_counts_when_no_tickets_exist(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)
            ->getJson('/api/dashboard');

        $response->assertOk();

        $response->assertJsonPath('stats.total', 0);
        $response->assertJsonPath('stats.open', 0);
        $response->assertJsonPath('stats.in_progress', 0);
        $response->assertJsonPath('stats.closed', 0);
    }

    public function test_customer_dashboard_only_shows_own_recent_tickets(): void
    {
        $customerA = User::factory()->create([
            'role' => 'customer',
        ]);

        $customerB = User::factory()->create([
            'role' => 'customer',
        ]);

        $ownTicket = Ticket::factory()->create([
            'customer_id' => $customerA->id,
            'title' => 'My ticket',
        ]);

        $otherTicket = Ticket::factory()->create([
            'customer_id' => $customerB->id,
            'title' => 'Another customer ticket',
        ]);

        $response = $this->actingAs($customerA)
            ->getJson('/api/dashboard');

        $response->assertOk();

        $response->assertJsonCount(1, 'recent_tickets');

        $response->assertJsonPath(
            'recent_tickets.0.id',
            $ownTicket->id
        );

        $response->assertJsonMissing([
            'id' => $otherTicket->id,
            'title' => $otherTicket->title,
        ]);
    }

    public function test_dashboard_returns_only_five_most_recent_tickets(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent',
        ]);

        $tickets = collect();

        for ($i = 1; $i <= 7; $i++) {
            $tickets->push(Ticket::factory()->create([
                'created_at' => now()->subMinutes(7 - $i),
            ]));
        }

        $response = $this->actingAs($agent)
            ->getJson('/api/dashboard');

        $response->assertOk();
        $response->assertJsonCount(5, 'recent_tickets');

        $expectedIds = $tickets
            ->reverse()
            ->take(5)
            ->pluck('id')
            ->values()
            ->all();

        $actualIds = collect($response->json('recent_tickets'))
            ->pluck('id')
            ->all();

        $this->assertSame($expectedIds, $actualIds);
    }
}

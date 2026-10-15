<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class TicketIndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (
            ! app()->environment('testing') ||
            DB::connection()->getDatabaseName() !== 'ticketflow_test'
        ) {
            throw new RuntimeException('Unsafe test database configuration.');
        }

        DB::beginTransaction();
    }

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

    protected function tearDown(): void
    {
        try {
            DB::rollBack();
        } finally {
            parent::tearDown();
        }
    }
}

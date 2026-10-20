<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TicketUpdateTest extends TestCase
{
    #[DataProvider('supportRoles')]
    public function test_support_staff_can_update_ticket_status_and_priority(string $role): void
    {
        $user = User::factory()->create([
            'role' => $role,
        ]);

        $ticket = Ticket::factory()->create([
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($user)
            ->patchJson("/api/tickets/{$ticket->id}", [
                'status' => 'in_progress',
                'priority' => 'high',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'in_progress')
            ->assertJsonPath('priority', 'high');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
            'priority' => 'high',
        ]);
    }

    public static function supportRoles(): array
    {
        return [
            'agent' => ['agent'],
            'admin' => ['admin'],
        ];
    }

    public function test_customer_cannot_update_own_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($customer)
            ->patchJson("/api/tickets/{$ticket->id}", [
                'status' => 'closed',
                'priority' => 'high',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);
    }

    public function test_guest_cannot_update_ticket(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->patchJson("/api/tickets/{$ticket->id}", [
            'status' => 'closed',
            'priority' => 'low',
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);
    }

    #[DataProvider('invalidTicketUpdates')]
    public function test_support_staff_cannot_update_ticket_with_invalid_data(
        array $invalidData
    ): void {
        $agent = User::factory()->create([
            'role' => 'agent',
        ]);

        $ticket = Ticket::factory()->create([
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($agent)
            ->patchJson("/api/tickets/{$ticket->id}", $invalidData);

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors(
            array_keys($invalidData)
        );

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);
    }

    public static function invalidTicketUpdates(): array
    {
        return [
            'invalid status' => [
                ['status' => 'pending'],
            ],
            'invalid priority' => [
                ['priority' => 'urgent'],
            ],
            'status null' => [
                ['status' => null],
            ],
            'priority null' => [
                ['priority' => null],
            ],
            'status array' => [
                ['status' => ['open']],
            ],
            'priority array' => [
                ['priority' => ['high']],
            ],
        ];
    }

    public function test_updating_priority_does_not_change_status(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent',
        ]);

        $ticket = Ticket::factory()->create([
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($agent)
            ->patchJson("/api/tickets/{$ticket->id}", [
                'priority' => 'high',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'open')
            ->assertJsonPath('priority', 'high');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'open',
            'priority' => 'high',
        ]);
    }

    public function test_updating_status_does_not_change_priority(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent',
        ]);

        $ticket = Ticket::factory()->create([
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($agent)
            ->patchJson("/api/tickets/{$ticket->id}", [
                'status' => 'in_progress',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'in_progress')
            ->assertJsonPath('priority', 'medium');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);
    }

    public function test_support_staff_cannot_override_other_ticket_fields(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $anotherCustomer = User::factory()->create([
            'role' => 'customer',
        ]);

        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'title' => 'Original title',
            'description' => 'Original description',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($agent)
            ->patchJson("/api/tickets/{$ticket->id}", [
                'customer_id' => $anotherCustomer->id,
                'title' => 'Manipulated title',
                'description' => 'Manipulated description',
                'status' => 'in_progress',
            ]);

        $response->assertOk()
            ->assertJsonPath('customer_id', $customer->id)
            ->assertJsonPath('title', 'Original title')
            ->assertJsonPath('description', 'Original description')
            ->assertJsonPath('status', 'in_progress');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'customer_id' => $customer->id,
            'title' => 'Original title',
            'description' => 'Original description',
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);
    }
}

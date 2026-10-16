<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TicketStoreTest extends TestCase
{
    public function test_customer_can_create_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $category = Category::factory()->create();

        $response = $this->actingAs($customer)
            ->postJson('/api/tickets', [
                'title' => 'Cannot log in',
                'description' => 'Login fails with an error.',
                'category_id' => $category->id,
            ]);

        $response->assertCreated();

        $response->assertJsonFragment([
            'title' => 'Cannot log in',
            'customer_id' => $customer->id,
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Cannot log in',
            'customer_id' => $customer->id,
            'category_id' => $category->id,
            'priority' => 'medium',
            'status' => 'open',
        ]);
    }

    public function test_customer_cannot_override_owner_status_or_priority(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $anotherCustomer = User::factory()->create([
            'role' => 'customer',
        ]);

        $category = Category::factory()->create();

        $response = $this->actingAs($customer)
            ->postJson('/api/tickets', [
                'title' => 'Login problem',
                'description' => 'Cannot access my account.',
                'category_id' => $category->id,
                'priority' => 'high',
                'customer_id' => $anotherCustomer->id,
                'status' => 'closed',
            ]);

        $response->assertCreated();

        $response->assertJsonPath('customer_id', $customer->id);
        $response->assertJsonPath('priority', 'medium');
        $response->assertJsonPath('status', 'open');

        $this->assertDatabaseHas('tickets', [
            'id' => $response->json('id'),
            'customer_id' => $customer->id,
            'priority' => 'medium',
            'status' => 'open',
        ]);
    }

    public function test_customer_cannot_create_ticket_without_required_fields(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)
            ->postJson('/api/tickets', []);

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors([
            'title',
            'description',
            'category_id',
        ]);

        $this->assertDatabaseCount('tickets', 0);
    }

    #[DataProvider('invalidTicketData')]
    public function test_customer_cannot_create_ticket_with_invalid_data(
        array $invalidData,
        string $invalidField
    ): void {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $category = Category::factory()->create();

        $data = [
            'title' => 'Login problem',
            'description' => 'Cannot access my account.',
            'category_id' => $category->id,
            ...$invalidData,
        ];

        $response = $this->actingAs($customer)
            ->postJson('/api/tickets', $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([$invalidField]);

        $this->assertDatabaseCount('tickets', 0);
    }

    public static function invalidTicketData(): array
    {
        return [
            'title too long' => [
                ['title' => str_repeat('A', 256)],
                'title',
            ],
            'title is numeric' => [
                ['title' => 12345],
                'title',
            ],
            'category does not exist' => [
                ['category_id' => 999999],
                'category_id',
            ],
            'description is not a string' => [
                ['description' => ['invalid', 'description']],
                'description',
            ],
        ];
    }

    #[DataProvider('unauthorizedRoles')]
    public function test_non_customer_cannot_create_ticket(string $role): void
    {
        $user = User::factory()->create([
            'role' => $role,
        ]);

        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/tickets', [
                'title' => 'Login problem',
                'description' => 'Cannot access my account.',
                'category_id' => $category->id,
            ]);

        $response->assertForbidden();

        $this->assertDatabaseCount('tickets', 0);
    }

    public static function unauthorizedRoles(): array
    {
        return [
            'agent' => ['agent'],
            'admin' => ['admin'],
        ];
    }

    public function test_guest_cannot_create_ticket(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/tickets', [
            'title' => 'Login problem',
            'description' => 'Cannot access my account.',
            'category_id' => $category->id,
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('tickets', 0);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class CategoryIndexTest extends TestCase
{
    public function test_authenticated_user_can_list_categories(): void
    {
        $user = User::factory()->create();

        $hardware = Category::factory()->create([
            'name' => 'Hardware',
        ]);

        $software = Category::factory()->create([
            'name' => 'Software',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment([
                'id' => $hardware->id,
                'name' => 'Hardware',
            ])
            ->assertJsonFragment([
                'id' => $software->id,
                'name' => 'Software',
            ]);
    }

    public function test_categories_are_sorted_alphabetically(): void
    {
        $user = User::factory()->create();

        Category::factory()->create(['name' => 'Software']);
        Category::factory()->create(['name' => 'Hardware']);
        Category::factory()->create(['name' => 'Account']);

        $response = $this->actingAs($user)
            ->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonPath('0.name', 'Account')
            ->assertJsonPath('1.name', 'Hardware')
            ->assertJsonPath('2.name', 'Software');
    }

    public function test_guest_cannot_list_categories(): void
    {
        Category::factory()->create([
            'name' => 'Hardware',
        ]);

        $response = $this->getJson('/api/categories');

        $response->assertUnauthorized();
    }
}

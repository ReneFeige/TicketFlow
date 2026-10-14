<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_user_can_log_in_with_valid_credentials(): void
    {
        if (
            ! app()->environment('testing') ||
            DB::connection()->getDatabaseName() !== 'ticketflow_test'
        ) {
            throw new RuntimeException('Unsafe test database configuration.');
        }

        DB::beginTransaction();

        try {
            $user = User::factory()->create([
                'password' => 'password123',
            ]);

            $response = $this->postJson('/login', [
                'email' => $user->email,
                'password' => 'password123',
            ]);

            $response->assertOk();
            $response->assertJson([
                'message' => 'Login successful.',
            ]);

            $this->assertAuthenticatedAs($user);
        } finally {
            DB::rollBack();
        }
    }

    public function test_user_cannot_log_in_with_invalid_credentials(): void
    {
        if (
            ! app()->environment('testing') ||
            DB::connection()->getDatabaseName() !== 'ticketflow_test'
        ) {
            throw new RuntimeException('Unsafe test database configuration.');
        }

        DB::beginTransaction();

        try {
            $user = User::factory()->create([
                'password' => 'password123',
            ]);

            $response = $this->postJson('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['email']);

            $this->assertGuest();
        } finally {
            DB::rollBack();
        }
    }

    public function test_authenticated_user_can_access_profile(): void
    {
        if (
            ! app()->environment('testing') ||
            DB::connection()->getDatabaseName() !== 'ticketflow_test'
        ) {
            throw new RuntimeException('Unsafe test database configuration.');
        }

        DB::beginTransaction();

        try {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->getJson('/api/user');

            $response->assertOk();
            $response->assertJson([
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ]);

            $response->assertJsonMissing([
                'password' => $user->password,
            ]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_authenticated_user_can_log_out(): void
    {
        if (
            ! app()->environment('testing') ||
            DB::connection()->getDatabaseName() !== 'ticketflow_test'
        ) {
            throw new RuntimeException('Unsafe test database configuration.');
        }

        DB::beginTransaction();

        try {
            $user = User::factory()->create();

            $this->actingAs($user);

            $response = $this->postJson('/logout');

            $response->assertOk();
            $response->assertJson([
                'message' => 'Logout successful.',
            ]);

            $this->assertGuest('web');

            $this->getJson('/api/user')->assertUnauthorized();
        } finally {
            DB::rollBack();
        }
    }
}

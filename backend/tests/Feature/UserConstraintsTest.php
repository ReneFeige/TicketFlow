<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class UserConstraintsTest extends TestCase
{
    public function test_invalid_user_role_is_rejected(): void
    {
        if (
            ! app()->environment('testing') ||
            DB::connection()->getDatabaseName() !== 'ticketflow_test'
        ) {
            throw new RuntimeException('Unsafe test database configuration.');
        }

        DB::beginTransaction();

        try {
            try {
                DB::table('users')->insert([
                    'name' => 'Invalid User',
                    'email' => 'invalid@example.test',
                    'password' => 'test-password',
                    'role' => 'superadmin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->fail('Expected PostgreSQL to reject the invalid role.');
            } catch (QueryException $exception) {
                $this->assertSame('23514', $exception->errorInfo[0]);
            }
        } finally {
            DB::rollBack();
        }
    }
}

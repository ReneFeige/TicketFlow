<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserConstraintsTest extends TestCase
{
    public function test_invalid_user_role_is_rejected(): void
    {
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
    }
}

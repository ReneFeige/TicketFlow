<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class TicketConstraintsTest extends TestCase
{
    #[DataProvider('invalidTicketValues')]
    public function test_invalid_ticket_values_are_rejected(
        string $column,
        string $invalidValue
    ): void {
        if (
            ! app()->environment('testing') ||
            DB::connection()->getDatabaseName() !== 'ticketflow_test'
        ) {
            throw new RuntimeException('Unsafe test database configuration.');
        }

        DB::beginTransaction();

        try {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Test Customer',
                'email' => 'customer@example.test',
                'password' => 'test-password',
                'role' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $categoryId = DB::table('categories')->insertGetId([
                'name' => 'Technical Issue',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                DB::table('tickets')->insert([
                    'customer_id' => $userId,
                    'category_id' => $categoryId,
                    'title' => 'Test Ticket',
                    'description' => 'Testing invalid ticket status.',
                    'status' => 'open',
                    'priority' => 'medium',
                    $column => $invalidValue,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->fail("Expected PostgreSQL to reject invalid {$column}.");
            } catch (QueryException $exception) {
                $this->assertSame('23514', $exception->errorInfo[0]);
            }
        } finally {
            DB::rollBack();
        }
    }

    public static function invalidTicketValues(): array
    {
        return [
            'invalid status' => ['status', 'invalid'],
            'invalid priority' => ['priority', 'invalid'],
        ];
    }

    public function test_ticket_with_nonexistent_category_is_rejected(): void
    {
        if (
            ! app()->environment('testing') ||
            DB::connection()->getDatabaseName() !== 'ticketflow_test'
        ) {
            throw new RuntimeException('Unsafe test database configuration.');
        }

        DB::beginTransaction();

        try {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Test Customer',
                'email' => 'customer@example.test',
                'password' => 'test-password',
                'role' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                DB::table('tickets')->insert([
                    'customer_id' => $userId,
                    'category_id' => 999999,
                    'title' => 'Test Ticket',
                    'description' => 'Testing an invalid category reference.',
                    'status' => 'open',
                    'priority' => 'medium',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->fail('Expected PostgreSQL to reject the invalid category.');
            } catch (QueryException $exception) {
                $this->assertSame('23503', $exception->errorInfo[0]);
            }
        } finally {
            DB::rollBack();
        }
    }
}

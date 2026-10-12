<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    public function test_uses_dedicated_postgresql_database(): void
    {
        $this->assertSame(
            'pgsql',
            config('database.default')
        );

        $this->assertSame(
            'ticketflow_test',
            config('database.connections.pgsql.database')
        );

        $this->assertSame('testing', app()->environment());
        $this->assertSame('pgsql', config('database.default'));

        $this->assertSame(
            'ticketflow_test',
            \Illuminate\Support\Facades\DB::connection()->getDatabaseName()
        );
    }
}

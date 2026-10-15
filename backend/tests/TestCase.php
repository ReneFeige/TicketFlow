<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Tests\Support\TestDatabaseGuard;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions {
        beginDatabaseTransaction as protected startDatabaseTransactions;
    }

    public function beginDatabaseTransaction(): void
    {
        TestDatabaseGuard::assertSafe(
            app()->environment(),
            DB::connection()->getDriverName(),
            DB::connection()->getDatabaseName()
        );

        $this->startDatabaseTransactions();
    }
}

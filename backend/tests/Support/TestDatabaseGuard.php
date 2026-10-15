<?php

namespace Tests\Support;

use RuntimeException;

final class TestDatabaseGuard
{
    public static function assertSafe(
        string $environment,
        string $driver,
        string $database
    ): void {
        if (
            $environment !== 'testing' ||
            $driver !== 'pgsql' ||
            $database !== 'ticketflow_test'
        ) {
            throw new RuntimeException('Unsafe test database configuration.');
        }
    }
}

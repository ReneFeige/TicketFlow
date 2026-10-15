<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\TestDatabaseGuard;

class TestDatabaseGuardTest extends TestCase
{
    #[DataProvider('unsafeConfigurations')]
    public function test_rejects_unsafe_configuration(
        string $environment,
        string $driver,
        string $database
    ): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsafe test database configuration.');

        TestDatabaseGuard::assertSafe(
            $environment,
            $driver,
            $database
        );
    }

    public static function unsafeConfigurations(): array
    {
        return [
            'wrong environment' => ['local', 'pgsql', 'ticketflow_test'],
            'wrong driver' => ['testing', 'sqlite', 'ticketflow_test'],
            'development database' => ['testing', 'pgsql', 'ticketflow'],
        ];
    }

    public function test_accepts_safe_configuration(): void
    {
        TestDatabaseGuard::assertSafe(
            'testing',
            'pgsql',
            'ticketflow_test'
        );

        $this->addToAssertionCount(1);
    }
}

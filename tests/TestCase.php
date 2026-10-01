<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Refuse to run any test that would migrate, seed or truncate against a
     * database we were not explicitly told to destroy.
     *
     * This project is configured against the live production database, so a
     * class using RefreshDatabase (which drops and re-migrates every table)
     * would wipe real data. The suites under tests/Feature/Auth plus
     * CustomerAboutTest, ProfileTest and ExampleTest all use it.
     *
     * Set ALLOW_DESTRUCTIVE_TESTS=1 to override, and only ever do that against a
     * throwaway database.
     */
    protected function setUp(): void
    {
        if (env('ALLOW_DESTRUCTIVE_TESTS')) {
            parent::setUp();

            return;
        }

        foreach ($this->destructiveTraits() as $trait) {
            $this->fail(sprintf(
                '%s uses %s, which drops and re-migrates tables. This project points at a live database, '
                .'so this test is blocked. Set ALLOW_DESTRUCTIVE_TESTS=1 only against a throwaway database.',
                static::class,
                $trait
            ));
        }

        parent::setUp();
    }

    /**
     * Traits that clear, migrate or seed the database.
     *
     * @return array<int, string>
     */
    private function destructiveTraits(): array
    {
        $traits = class_uses_recursive(static::class);
        $destructive = [
            \Illuminate\Foundation\Testing\RefreshDatabase::class,
            \Illuminate\Foundation\Testing\DatabaseMigrations::class,
            \Illuminate\Foundation\Testing\DatabaseTruncation::class,
        ];

        return array_values(array_filter(
            $destructive,
            fn (string $trait) => in_array($trait, $traits, true)
        ));
    }

    /**
     * Guard for suites that are meant to be read-only. Called from setUp by the
     * opt-in tests so an accidental write fails loudly instead of persisting.
     */
    protected function assertNotProductionWrite(string $table, int $before): void
    {
        $after = DB::table($table)->count();

        $this->assertSame(
            $before,
            $after,
            "Row count for {$table} changed from {$before} to {$after}; a test leaked a write."
        );
    }
}

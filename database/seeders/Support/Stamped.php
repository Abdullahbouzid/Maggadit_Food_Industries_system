<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Query-builder writes that automatically fill created_at / updated_at.
 *
 * DB::table()->insert() bypasses Eloquent, so timestamps are never set, which
 * later breaks views that call ->format() on created_at. Every seeder write
 * should go through this helper instead.
 *
 * Columns are looked up once per table and cached, because the column list is
 * the same for every row in a batch and the lookup is a remote round-trip.
 */
final class Stamped
{
    /** @var array<string, array{created: bool, updated: bool}> */
    private static array $columns = [];

    /**
     * Equivalent to DB::table($table)->updateOrInsert($attributes, $values).
     */
    public static function upsert(string $table, array $attributes, array $values = []): bool
    {
        return self::query($table)->updateOrInsert($attributes, self::stamp($table, $values));
    }

    public static function insert(string $table, array $rows): bool
    {
        if ($rows === []) {
            return true;
        }

        // A single associative row is accepted for convenience.
        if (! array_is_list($rows)) {
            $rows = [$rows];
        }

        $stamped = [];
        foreach ($rows as $row) {
            $stamped[] = self::stamp($table, $row);
        }

        return self::query($table)->insert($stamped);
    }

    public static function insertGetId(string $table, array $row): int
    {
        return (int) self::query($table)->insertGetId(self::stamp($table, $row));
    }

    public static function insertOrIgnore(string $table, array $rows): bool
    {
        if ($rows === []) {
            return true;
        }

        if (! array_is_list($rows)) {
            $rows = [$rows];
        }

        $stamped = [];
        foreach ($rows as $row) {
            $stamped[] = self::stamp($table, $row);
        }

        return self::query($table)->insertOrIgnore($stamped);
    }

    /**
     * Add created_at / updated_at only for columns the table actually has,
     * and never overwrite a value the caller set explicitly.
     */
    private static function stamp(string $table, array $row): array
    {
        [$hasCreated, $hasUpdated] = self::columns($table);

        if (! $hasCreated && ! $hasUpdated) {
            return $row;
        }

        $now = now();

        if ($hasCreated && ! array_key_exists('created_at', $row)) {
            $row['created_at'] = $now;
        }

        if ($hasUpdated && ! array_key_exists('updated_at', $row)) {
            $row['updated_at'] = $now;
        }

        return $row;
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private static function columns(string $table): array
    {
        if (isset(self::$columns[$table])) {
            return self::$columns[$table];
        }

        $columns = Schema::hasTable($table)
            ? Schema::getColumnListing($table)
            : [];

        return self::$columns[$table] = [
            in_array('created_at', $columns, true),
            in_array('updated_at', $columns, true),
        ];
    }

    private static function query(string $table): \Illuminate\Database\Query\Builder
    {
        return DB::table($table);
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fills NULL created_at / updated_at values left behind by seeders that wrote
 * through DB::table() (which bypasses Eloquent's automatic timestamps).
 *
 * Views that call ->format() on these columns return HTTP 500 while they are
 * null, so this is safe to run at any time.
 */
class BackfillTimestampsSeeder extends Seeder
{
    public function run(): void
    {
        $tables = DB::select(
            'SELECT table_name AS t FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_type = "BASE TABLE"
             ORDER BY table_name'
        );

        $fixed = 0;

        foreach ($tables as $row) {
            $table = $row->t;

            $updates = [];

            if (Schema::hasColumn($table, 'created_at')) {
                $count = DB::table($table)->whereNull('created_at')->count();
                if ($count > 0) {
                    DB::table($table)->whereNull('created_at')->update([
                        'created_at' => now()->subDay(),
                    ]);
                    $updates[] = "created_at ({$count})";
                }
            }

            if (Schema::hasColumn($table, 'updated_at')) {
                $count = DB::table($table)->whereNull('updated_at')->count();
                if ($count > 0) {
                    DB::table($table)->whereNull('updated_at')->update([
                        'updated_at' => now()->subDay(),
                    ]);
                    $updates[] = "updated_at ({$count})";
                }
            }

            if ($updates !== []) {
                $fixed++;
                $this->command?->info('  '.$table.': '.implode(', ', $updates));
            }
        }

        $this->command?->info('✅ Backfilled timestamps in '.$fixed.' tables.');
    }
}
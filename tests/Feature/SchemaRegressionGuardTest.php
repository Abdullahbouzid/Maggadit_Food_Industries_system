<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Static regression guards for two production 500s that no runtime test caught:
 *
 *   1. A Blade comment placed inside a @php block (e.g.
 *      resources/views/dashboard/deliveries/index.blade.php) is not compiled by
 *      Blade, leaks into the generated PHP, and raises a ParseError. Because the
 *      failure happens at compile time it took down the whole page regardless of
 *      data.
 *
 *   2. A model with $timestamps = false, or UPDATED_AT = null, that maps to a
 *      table whose created_at/updated_at column is NOT NULL with no default.
 *      Eloquent then omits the column and the insert fails. This broke every
 *      order creation through App\Models\AuditLog.
 *
 * Both checks are read-only, so unlike InvoiceCollectionFlowTest they are safe
 * to run against any database. They are still opt-in because the schema check
 * reads information_schema, which sqlite (the default phpunit.xml) does not
 * expose in the same way.
 */
class SchemaRegressionGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! env('INVOICE_FLOW_TESTS')) {
            $this->markTestSkipped('Set INVOICE_FLOW_TESTS=1 to run tests against the configured database.');
        }
    }

    public function test_no_blade_comment_sits_inside_a_php_block(): void
    {
        $offenders = [];

        foreach ($this->bladeFiles() as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES);

            if ($lines === false) {
                continue;
            }

            $depth = 0;

            foreach ($lines as $index => $line) {
                // Track block nesting. @php($expr) is a self-closing inline form
                // that must not be counted as opening a block, otherwise every
                // following line looks nested and we report false positives.
                $this->adjustDepth($line, $depth);

                if ($depth > 0 && preg_match('/\{\{--|--\}\}/', $line) === 1) {
                    $offenders[] = sprintf('%s:%d', $this->relative($file), $index + 1);
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Blade comments inside @php blocks leak into the compiled PHP and cause a ParseError:\n"
            .implode("\n", $offenders)
        );
    }

    public function test_every_view_compiles_and_is_valid_php(): void
    {
        $this->assertSame(0, Artisan::call('view:clear'));

        $exitCode = Artisan::call('view:cache');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode, "view:cache failed:\n" . $output);
        $this->assertStringContainsString('cached successfully', $output);
    }

    public function test_compiled_views_have_no_php_syntax_errors(): void
    {
        // Ensure a compiled cache exists even if run in isolation.
        if (View::getFinder()->getPaths() === []) {
            $this->markTestSkipped('No view paths configured.');
        }

        Artisan::call('view:clear');
        Artisan::call('view:cache');

        $compiled = glob(storage_path('framework/views/*.php')) ?: [];

        $this->assertNotEmpty($compiled, 'No compiled views were produced by view:cache.');

        $failures = [];

        foreach ($compiled as $file) {
            $result = $this->lint($file);

            if ($result !== null) {
                $failures[] = $this->relative($file) . ': ' . $result;
            }
        }

        $this->assertSame([], $failures, "Compiled views failed to parse:\n" . implode("\n", $failures));
    }

    public function test_no_model_disables_timestamps_for_a_not_null_column(): void
    {
        $offenders = [];

        foreach ($this->modelFiles() as $file) {
            $source = file_get_contents($file);

            if ($source === false) {
                continue;
            }

            $class = 'App\\Models\\' . basename($file, '.php');

            if (! class_exists($class)) {
                continue;
            }

            $model = new $class;

            // Only eager models write rows; a model that never touches the table
            // cannot produce a failing insert.
            if (! $model instanceof \Illuminate\Database\Eloquent\Model) {
                continue;
            }

            $timestampsDisabled = preg_match('/public\s+\$timestamps\s*=\s*false/', $source) === 1;
            $noUpdatedAt = preg_match('/UPDATED_AT\s*=\s*null/i', $source) === 1;

            if (! $timestampsDisabled && ! $noUpdatedAt) {
                continue;
            }

            $table = $model->getTable();

            foreach ($this->timestampColumns($table) as $column) {
                $column = (array) $column;

                if ($column['Null'] !== 'NO' || $column['Default'] !== null) {
                    continue;
                }

                if (stripos($column['Extra'], 'auto_increment') !== false) {
                    continue;
                }

                // With $timestamps = false Eloquent writes neither column; with
                // UPDATED_AT = null it writes created_at but never updated_at.
                $written = ! $timestampsDisabled && ($column['Field'] !== 'updated_at' || ! $noUpdatedAt);

                if (! $written) {
                    $offenders[] = sprintf(
                        '%s (%s): %s is NOT NULL with no default but is never populated',
                        $class,
                        $table,
                        $column['Field']
                    );
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These models omit a NOT NULL timestamp column and will fail on insert:\n"
            .implode("\n", $offenders)
        );
    }

    /**
     * Add/subtract one for each block-opening or block-closing directive.
     */
    private function adjustDepth(string $line, int &$depth): void
    {
        if (preg_match_all('/@php(?![A-Za-z(])(.*)$/m', $line, $starts) === false) {
            $starts = [1 => []];
        }

        // @php($a = 1) is self-closing: the inner ")" ends it, so never open a block.
        $opens = count($starts[1] ?? []);
        $closes = preg_match_all('/@endphp\b/', $line);

        $depth = max(0, $depth + $opens - $closes);
    }

    /**
     * @return array<int, object>
     */
    private function timestampColumns(string $table): array
    {
        $known = DB::select(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME IN ('created_at','updated_at')",
            [$table]
        );

        if ($known === []) {
            return [];
        }

        // SHOW COLUMNS returns every column; keep only the timestamp ones we check.
        return array_values(array_filter(
            DB::select('SHOW COLUMNS FROM `' . str_replace('`', '', $table) . '`'),
            fn ($column) => in_array($column->Field, ['created_at', 'updated_at'], true)
        ));
    }

    /**
     * Run php -l on a file, returning the error message or null when it parses.
     */
    private function lint(string $file): ?string
    {
        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

        $process = proc_open(
            sprintf('%s -l %s', escapeshellarg(PHP_BINARY), escapeshellarg($file)),
            $descriptors,
            $pipes
        );

        if (! is_resource($process)) {
            return 'could not start php -l';
        }

        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $status = proc_close($process);

        if ($status === 0 && str_contains($stdout, 'No syntax errors')) {
            return null;
        }

        $message = trim($stderr) !== '' ? trim($stderr) : trim($stdout);

        return $message !== '' ? $message : 'exit code ' . $status;
    }

    /**
     * @return array<int, string>
     */
    private function bladeFiles(): array
    {
        $files = glob(resource_path('views/**/*.blade.php')) ?: [];

        return $this->flattenGlob($files);
    }

    /**
     * @return array<int, string>
     */
    private function modelFiles(): array
    {
        $files = glob(app_path('Models/*.php')) ?: [];

        sort($files);

        return $files;
    }

    /**
     * glob() does not recurse with ** on every platform, so walk the tree.
     *
     * @param  array<int, string>  $files
     * @return array<int, string>
     */
    private function flattenGlob(array $files): array
    {
        $collected = [];

        foreach ($files as $file) {
            if (is_dir($file)) {
                continue;
            }

            $collected[] = $file;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $entry) {
            if ($entry->isFile() && str_ends_with($entry->getFilename(), '.blade.php')) {
                $path = $entry->getPathname();

                if (! in_array($path, $collected, true)) {
                    $collected[] = $path;
                }
            }
        }

        sort($collected);

        return $collected;
    }

    private function relative(string $path): string
    {
        $base = base_path() . DIRECTORY_SEPARATOR;

        return str_starts_with($path, $base) ? substr($path, strlen($base)) : $path;
    }
}

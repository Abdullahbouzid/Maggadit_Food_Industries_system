<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Diagnoses the PDF pipeline (barryvdh/laravel-snappy + wkhtmltopdf) so a
 * misconfigured server can be checked without guessing.
 */
class PdfCheck extends Command
{
    protected $signature = 'pdf:check';

    protected $description = 'Check whether PDF generation is working on this server';

    public function handle(): int
    {
        $this->line('<info>PDF generation check</info>');
        $this->newLine();

        $ok = true;

        // 1. Composer package
        if (class_exists(\Barryvdh\Snappy\Facades\SnappyPdf::class)) {
            $this->line('  [OK]   barryvdh/laravel-snappy is installed');
        } else {
            $ok = false;
            $this->error('  [FAIL] barryvdh/laravel-snappy is missing -> run: composer install --no-dev --optimize-autoloader');
        }

        // 2. Configured binary
        $binary = config('snappy.pdf.binary');
        $this->line('  Binary path: ' . ($binary ?: '(empty)'));

        if (is_string($binary) && $binary !== '' && is_file($binary) && is_executable($binary)) {
            $this->line('  [OK]   binary exists and is executable');
        } else {
            $ok = false;
            $this->error('  [FAIL] binary is missing or not executable');
            $this->line('         Install it, then set WKHTMLTOPDF_BINARY in .env to its real path.');
        }

        // 3. Binary actually runs
        if ($ok && is_string($binary) && $binary !== '') {
            $process = new Process([$binary, '--version']);
            $process->setTimeout(30);

            try {
                $process->run();

                if ($process->isSuccessful()) {
                    $this->line('  [OK]   ' . trim($process->getOutput()));
                } else {
                    $ok = false;
                    $this->error('  [FAIL] binary could not run: ' . trim($process->getErrorOutput()));
                }
            } catch (\Throwable $e) {
                $ok = false;
                $this->error('  [FAIL] could not execute binary: ' . $e->getMessage());
            }
        }

        // 4. End-to-end render
        if ($ok) {
            $this->newLine();
            $this->line('  Rendering a test PDF...');

            try {
                $output = \Barryvdh\Snappy\Facades\SnappyPdf::loadHTML('<h1>test</h1>')
                    ->setPaper('a4', 'portrait', ['encoding' => 'UTF-8'])
                    ->output();

                if (str_starts_with($output, '%PDF-')) {
                    $this->line('  [OK]   test render succeeded (' . number_format(strlen($output)) . ' bytes)');
                } else {
                    $ok = false;
                    $this->error('  [FAIL] render did not return a PDF');
                }
            } catch (\Throwable $e) {
                $ok = false;
                $this->error('  [FAIL] render failed: ' . $e->getMessage());
            }
        }

        $this->newLine();
        $this->line($ok ? '<info>PDF generation is working.</info>' : '<error>PDF generation is NOT working.</error>');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}

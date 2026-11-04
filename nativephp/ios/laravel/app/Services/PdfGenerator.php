<?php

namespace App\Services;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PdfGenerator
{
    /**
     * Render a PDF view and return it as a download response.
     *
     * Guards the hard dependency on barryvdh/laravel-snappy and the wkhtmltopdf
     * binary so a misconfigured environment produces a readable Arabic error
     * for the user instead of an unhandled 500 with a stack trace.
     */
    public static function download(string $view, array $data, string $filename): Response|RedirectResponse
    {
        try {
            return self::build($view, $data)->download($filename);
        } catch (RuntimeException $e) {
            Log::error('PDF generation failed: '.$e->getMessage(), ['view' => $view]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Render a PDF view and return it for inline display.
     */
    public static function inline(string $view, array $data, string $filename): Response|RedirectResponse
    {
        try {
            return self::build($view, $data)->inline($filename);
        } catch (RuntimeException $e) {
            Log::error('PDF generation failed: '.$e->getMessage(), ['view' => $view]);

            return back()->with('error', $e->getMessage());
        }
    }

    private static function build(string $view, array $data)
    {
        if (! class_exists(SnappyPdf::class)) {
            throw new RuntimeException(
                'تعذر توليد ملف PDF: مكتبة barryvdh/laravel-snappy غير مثبتة. '.
                'يرجى تنفيذ composer install على الخادم.'
            );
        }

        if (! self::binaryAvailable()) {
            throw new RuntimeException(
                'تعذر توليد ملف PDF: أداة wkhtmltopdf غير متوفرة على الخادم'
                .' (المسار: '.(self::binaryPath() ?: 'غير محدد').'). '
                .'ثبّتها أو اضبط WKHTMLTOPDF_BINARY في ملف .env.'
            );
        }

        return SnappyPdf::loadView($view, $data)
            ->setPaper('a4', 'portrait', [
                'encoding' => 'UTF-8',
                'enable-local-file-access' => true,
            ]);
    }

    private static function binaryAvailable(): bool
    {
        $binary = self::binaryPath();

        if ($binary === '') {
            return false;
        }

        return is_file($binary) && is_executable($binary);
    }

    private static function binaryPath(): string
    {
        $binary = config('snappy.pdf.binary');

        return is_string($binary) ? $binary : '';
    }
}
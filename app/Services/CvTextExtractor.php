<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CvTextExtractor
{
    public function extractFromPublicPath(string $relativePath): ?string
    {
        $absolutePath = Storage::disk('public')->path($relativePath);

        if (! is_file($absolutePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        try {
            return match ($extension) {
                'docx' => $this->extractDocx($absolutePath),
                'pdf' => $this->extractPdf($absolutePath),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::warning('CV text extraction failed', [
                'path' => $relativePath,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function extractDocx(string $absolutePath): ?string
    {
        if (! class_exists(\ZipArchive::class)) {
            return null;
        }

        $zip = new \ZipArchive();
        if ($zip->open($absolutePath) !== true) {
            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! is_string($xml) || $xml === '') {
            return null;
        }

        $text = strip_tags($xml);
        $text = preg_replace("/\\s+/u", ' ', $text ?? '') ?? '';
        $text = trim($text);

        return $text !== '' ? $text : null;
    }

    private function extractPdf(string $absolutePath): ?string
    {
        $pdftotext = trim((string) @shell_exec('where pdftotext 2>NUL'));
        if ($pdftotext === '') {
            return null;
        }

        $escapedPath = escapeshellarg($absolutePath);
        $out = @shell_exec("pdftotext -layout -nopgbrk {$escapedPath} - 2>NUL");
        $out = is_string($out) ? trim($out) : '';

        return $out !== '' ? $out : null;
    }
}


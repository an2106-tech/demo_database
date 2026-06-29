<?php

namespace App\Support;

class CvUpload
{
    public const EXTENSIONS = ['pdf', 'doc', 'docx'];

    /**
     * @var array<string, array<int, string>>
     */
    private const MIMES_BY_EXTENSION = [
        'pdf' => [
            'application/pdf',
            'application/x-pdf',
            'application/acrobat',
            'applications/vnd.pdf',
            'text/pdf',
            'text/x-pdf',
            'application/octet-stream',
        ],
        'doc' => [
            'application/msword',
            'application/octet-stream',
        ],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/octet-stream',
        ],
    ];

    public static function isAllowed(mixed $file): bool
    {
        if (! is_object($file) || ! method_exists($file, 'getClientOriginalExtension')) {
            return false;
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($extension, self::EXTENSIONS, true)) {
            return false;
        }

        $mime = method_exists($file, 'getMimeType') ? strtolower((string) $file->getMimeType()) : '';
        $clientMime = method_exists($file, 'getClientMimeType') ? strtolower((string) $file->getClientMimeType()) : '';
        $detectedMimes = array_filter([$mime, $clientMime]);

        if ($detectedMimes === []) {
            return true;
        }

        return collect($detectedMimes)
            ->contains(fn (string $detectedMime): bool => in_array($detectedMime, self::MIMES_BY_EXTENSION[$extension], true));
    }

    public static function acceptAttribute(): string
    {
        return implode(',', [
            '.pdf',
            '.doc',
            '.docx',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
}

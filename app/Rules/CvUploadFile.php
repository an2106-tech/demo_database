<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class CvUploadFile implements ValidationRule
{
    private const EXTENSIONS = ['pdf', 'doc', 'docx'];

    private const MIME_TYPES = [
        'application/pdf',
        'application/x-pdf',
        'application/acrobat',
        'application/vnd.pdf',
        'text/pdf',
        'text/x-pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    private const GENERIC_MIME_TYPES = [
        '',
        'application/octet-stream',
        'application/zip',
        'application/x-zip',
        'application/x-zip-compressed',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('CV tải lên không hợp lệ.');

            return;
        }

        $extension = strtolower((string) $value->getClientOriginalExtension());
        $mimeType = strtolower((string) $value->getMimeType());
        $clientMimeType = strtolower((string) $value->getClientMimeType());

        if (in_array($mimeType, self::MIME_TYPES, true) || in_array($clientMimeType, self::MIME_TYPES, true)) {
            return;
        }

        if (in_array($extension, self::EXTENSIONS, true) && (
            in_array($mimeType, self::GENERIC_MIME_TYPES, true)
            || in_array($clientMimeType, self::GENERIC_MIME_TYPES, true)
        )) {
            return;
        }

        $fail('CV chỉ hỗ trợ định dạng PDF, DOC hoặc DOCX.');
    }
}

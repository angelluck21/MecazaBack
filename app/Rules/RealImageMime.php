<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class RealImageMime implements ValidationRule
{
    private const ALLOWED = [
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile || !$value->isValid()) {
            $fail('El archivo no es válido.');
            return;
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($value->getRealPath());

        if (!in_array($realMime, self::ALLOWED, true)) {
            $fail('El archivo debe ser una imagen real (JPEG, PNG o GIF).');
        }
    }
}

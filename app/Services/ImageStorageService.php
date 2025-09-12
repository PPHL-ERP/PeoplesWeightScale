<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;

class ImageStorageService
{
    /**
     * Save raw image bytes to configured disk and return metadata.
     * Optionally convert to webp if configured/possible.
     *
     * @param string $bytes
     * @param string $transactionId
     * @param string $cameraNo
     * @param \DateTimeInterface $capturedAt
     * @param string|null $contentType
     * @return array
     */
    public function saveBytes(string $bytes, string $identity, string $cameraNo, \DateTimeInterface $capturedAt, ?string $contentType = null, ?string $origChecksum = null, ?string $mode = null): array
    {
        $date = Carbon::instance($capturedAt)->format('Y-m-d');
        if (!$origChecksum) {
            $origChecksum = hash('sha256', $bytes);
        }
        // Choose extension based on content type (prefer PNG/JPEG to match frontend expectations)
        $ext = 'png';
        if ($contentType === 'image/jpeg' || $contentType === 'image/jpg') {
            $ext = 'jpg';
        }

        // Build filename per frontend convention: <identity>_<mode>_<cameraNo>.<ext>
        $modePart = $mode ? ($mode . '_') : '';
        $fileName = sprintf('%s_%s%s.%s', $identity, $modePart, $cameraNo, $ext);

        $path = "pictures/{$date}/{$identity}/{$fileName}";

        // ensure directory exists and write raw bytes
        Storage::disk('public')->put($path, $bytes);
        $size = Storage::disk('public')->size($path);

        return [
            'path' => $path,
            'size' => $size,
            'checksum' => $origChecksum,
            'content_type' => $contentType ?? ($ext === 'jpg' ? 'image/jpeg' : 'image/png')
        ];
    }
}

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
        // Always convert to webp and save as .webp per frontend requirement
        $modePart = $mode ? ($mode . '_') : '';
        $fileName = sprintf('%s_%s%s.webp', $identity, $modePart, $cameraNo);
        $path = "pictures/{$date}/{$identity}/{$fileName}";

        try {
            $img = Image::make($bytes);
            // encode to webp with default quality (80)
            $webpContents = (string) $img->encode('webp', 80);

            Storage::disk('public')->put($path, $webpContents);
            $size = Storage::disk('public')->size($path);

            return [
                'path' => $path,
                'size' => $size,
                'checksum' => $origChecksum,
                'content_type' => 'image/webp'
            ];
        } catch (\Exception $e) {
            // fail hard so caller returns 500 and frontend can retry
            throw new \Exception('webp_conversion_failed: ' . $e->getMessage());
        }
    }
}

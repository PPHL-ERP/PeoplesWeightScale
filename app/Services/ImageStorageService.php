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
    public function saveBytes(string $bytes, string $transactionId, string $cameraNo, \DateTimeInterface $capturedAt, ?string $contentType = null, ?string $origChecksum = null): array
    {
        $date = Carbon::instance($capturedAt)->format('Y-m-d');
        if (!$origChecksum) {
            $origChecksum = hash('sha256', $bytes);
        }

        // Base filename (without extension)
        $baseName = sprintf('%s_%s_%s_%s', $transactionId, $cameraNo, $capturedAt->format('His_u'), substr($origChecksum, 0, 8));

        // Prefer storing a webp - encode using Intervention Image and write webp only.
        try {
            $img = Image::make($bytes);
            // encode to webp with reasonable default quality (configurable later)
            $webpContents = (string) $img->encode('webp', 80);

            $webpName = $baseName . '.webp';
            $webpPath = "images/{$date}/{$transactionId}/{$webpName}";

            Storage::disk('public')->put($webpPath, $webpContents);
            $size = Storage::disk('public')->size($webpPath);

            return [
                'path' => $webpPath,
                'webp_path' => $webpPath,
                'size' => $size,
                'checksum' => $origChecksum, // return checksum of original bytes for dedup
                'content_type' => 'image/webp'
            ];
        } catch (\Exception $e) {
            // Conversion failed: fall back to storing original bytes to avoid losing the upload.
            // This branch returns the original file path and signals that webp conversion failed.
            $ext = 'png';
            if ($contentType === 'image/jpeg') {
                $ext = 'jpg';
            }

            $origName = $baseName . '.' . $ext;
            $origPath = "images/{$date}/{$transactionId}/{$origName}";

            Storage::disk('public')->put($origPath, $bytes);
            $size = Storage::disk('public')->size($origPath);

            return [
                'path' => $origPath,
                'webp_path' => null,
                'size' => $size,
                'checksum' => $origChecksum,
                'content_type' => $contentType ?? 'application/octet-stream',
                'warning' => 'webp_conversion_failed'
            ];
        }
    }
}

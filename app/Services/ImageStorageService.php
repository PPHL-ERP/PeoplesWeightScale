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
    public function saveBytes(string $bytes, string $transactionId, string $cameraNo, \DateTimeInterface $capturedAt, ?string $contentType = null): array
    {
        $date = Carbon::instance($capturedAt)->format('Y-m-d');
        $checksum = hash('sha256', $bytes);
        $ext = 'png';
        if ($contentType === 'image/jpeg') {
            $ext = 'jpg';
        }

        // generate filename
        $filename = sprintf('%s_%s_%s_%s.%s', $transactionId, $cameraNo, $capturedAt->format('His_u'), substr($checksum, 0, 8), $ext);
        $path = "images/{$date}/{$transactionId}/{$filename}";

        // ensure directory
        Storage::disk('public')->put($path, $bytes);
        $size = Storage::disk('public')->size($path);

        // attempt webp conversion if Intervention available and GD supports webp
        // do NOT overwrite original; store alongside
        try {
            if (extension_loaded('gd') || extension_loaded('imagick')) {
                $webpName = preg_replace('/\.[^.]+$/', '.webp', $filename);
                $webpPath = "images/{$date}/{$transactionId}/{$webpName}";
                $img = Image::make($bytes);
                // you can set quality via config
                $img->encode('webp', 80);
                Storage::disk('public')->put($webpPath, (string)$img);
            } else {
                $webpPath = null;
            }
        } catch (\Exception $e) {
            // conversion failed; ignore and continue
            $webpPath = null;
        }

        return [
            'path' => $path,
            'webp_path' => $webpPath,
            'size' => $size,
            'checksum' => $checksum,
            'content_type' => $contentType ?? 'image/png'
        ];
    }
}

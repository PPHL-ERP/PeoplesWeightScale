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
    public function saveBytes(string $bytes, string $identity, string $cameraNo, \DateTimeInterface $capturedAt, ?string $contentType = null, ?string $origChecksum = null, ?string $mode = null, ?int $sectorId = null): array
    {
        // Use UTC date for directory naming to avoid timezone mismatch
        $date = Carbon::instance($capturedAt)->utc()->format('Y-m-d');
        if (!$origChecksum) {
            $origChecksum = hash('sha256', $bytes);
        }
        // Decide segment (sector-first). If sectorId provided use it, otherwise use identity (weighing_id or transaction_id)
        $segment = $sectorId ? (string)$sectorId : (string)$identity;
        $modePart = $mode ? ($mode . '_') : '';
        // Filename should be based on weighing_id/identity and mode, matching frontend
        $fileName = sprintf('%s_%s%s.png', $identity, $modePart, $cameraNo);
        // New layout: pictures/{sector}/{date}/{weighing_id}/{fileName}
        $path = "pictures/{$segment}/{$date}/{$identity}/{$fileName}";

        // Ensure directory exists for local disk: Storage::disk('public') maps to storage/app/public
        $disk = Storage::disk('public');

        // Determine whether to save raw PNG or convert to webp (configurable)
        $convertToWebp = config('image.convert_to_webp', false);

        try {
            if ($convertToWebp) {
                $img = Image::make($bytes);
                $contents = (string) $img->encode('webp', 80);
                $contentTypeSaved = 'image/webp';
                // adjust filename for webp if converting
                $fileName = preg_replace('/\.png$/', '.webp', $fileName);
                // Use same sector-first layout for webp
                $path = "pictures/{$segment}/{$date}/{$identity}/{$fileName}";
            } else {
                // store original bytes; try to infer extension from contentType
                $contents = $bytes;
                $contentTypeSaved = $contentType ?? 'image/png';
                // preserve extension if provided via content type
                if ($contentTypeSaved === 'image/jpeg') {
                    $fileName = preg_replace('/\.png$/', '.jpg', $fileName);
                } elseif ($contentTypeSaved === 'image/webp') {
                    $fileName = preg_replace('/\.png$/', '.webp', $fileName);
                }
                $path = "pictures/{$segment}/{$date}/{$identity}/{$fileName}";
            }

            // Atomic write for local disk: write to a temp path then move
            $tempPath = $path . '.tmp';
            $fullTemp = storage_path('app/public/' . $tempPath);
            $fullFinal = storage_path('app/public/' . $path);

            // Ensure directory exists
            $dir = dirname($fullFinal);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            // write temp file
            file_put_contents($fullTemp, $contents);
            // flush to disk
            if (function_exists('fflush')) {
                // not required; keep simple
            }
            // atomic replace
            rename($fullTemp, $fullFinal);

            // Use Storage to report size
            $size = filesize($fullFinal);

            return [
                'path' => $path,
                'size' => $size,
                'checksum' => $origChecksum,
                'content_type' => $contentTypeSaved
            ];
        } catch (\Exception $e) {
            throw new \Exception('image_store_failed: ' . $e->getMessage());
        }
    }
}

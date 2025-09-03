<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadImageRequest;
use App\Models\TransactionImage;
use App\Services\ImageStorageService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ImageUploadController extends Controller
{
    protected ImageStorageService $storageService;

    public function __construct(ImageStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function upload(UploadImageRequest $request): JsonResponse
    {
        $data = $request->validated();

        // parse captured datetime
        if (!empty($data['capture_datetime'])) {
            $capturedAt = Carbon::parse($data['capture_datetime']);
        } elseif (!empty($data['capture_date']) && !empty($data['capture_time'])) {
            $capturedAt = Carbon::parse($data['capture_date'] . ' ' . $data['capture_time']);
        } else {
            return response()->json(['message' => 'capture_datetime or capture_date+capture_time required'], 400);
        }

        $transactionId = $data['transaction_id'];
        $cameraNo = $data['camera_no'];

        // decode base64
        $b64 = $data['image_base64'];
        if (preg_match('/^data:(.*);base64,/', $b64)) {
            $b64 = substr($b64, strpos($b64, ',') + 1);
        }
        $bytes = base64_decode($b64, true);
        if ($bytes === false) {
            return response()->json(['message' => 'Invalid base64 image'], 400);
        }

        $size = strlen($bytes);
        $max = config('image.max_bytes', 5 * 1024 * 1024);
        if ($size > $max) {
            return response()->json(['message' => 'Image too large'], 413);
        }

        // infer mime if not provided
        $contentType = $data['content_type'] ?? null;
        if (!$contentType) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $contentType = $finfo->buffer($bytes) ?: 'image/png';
        }
        if (!in_array($contentType, ['image/png', 'image/jpeg'])) {
            return response()->json(['message' => 'Unsupported image type: ' . $contentType], 400);
        }

        $checksum = $data['checksum'] ?? hash('sha256', $bytes);

        // idempotency: check existing by txn+cam+time
        $existing = TransactionImage::where('transaction_id', $transactionId)
            ->where('camera_no', $cameraNo)
            ->where('captured_at', $capturedAt)
            ->first();
        if ($existing) {
            return response()->json([
                'id' => $existing->id,
                'image_path' => $existing->image_path
            ], 200);
        }

        // save bytes via service
        try {
            $meta = $this->storageService->saveBytes($bytes, $transactionId, $cameraNo, $capturedAt, $contentType);
        } catch (\Exception $e) {
            Log::error('Image save failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to save image'], 500);
        }

        // insert DB record
        $rec = TransactionImage::create([
            'transaction_id' => $transactionId,
            'camera_no' => $cameraNo,
            'captured_at' => $capturedAt,
            'image_path' => $meta['path'],
            'storage_backend' => 'local',
            'content_type' => $meta['content_type'],
            'size_bytes' => $meta['size'],
            'checksum_sha256' => $meta['checksum'],
            'ingest_status' => 'stored'
        ]);

        $url = Storage::disk('public')->url($meta['path']);

        return response()->json([
            'id' => $rec->id,
            'image_path' => $meta['path'],
            'url' => $url
        ], 201);
    }
}

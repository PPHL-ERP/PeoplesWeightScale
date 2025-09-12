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
use Illuminate\Support\Str;
class ImageUploadController extends Controller
{
    protected ImageStorageService $storageService;

    public function __construct(ImageStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function uploadold(UploadImageRequest $request): JsonResponse
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

    $weighingId = $data['weighing_id'] ?? null;
    $transactionId = $data['transaction_id'] ?? null;
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

        $checksum = $data['checksum'] ?? null;
        if (!$checksum) {
            $logger->warning('upload.missing_checksum', $baseCtx);
            return response()->json(['status' => 'error', 'error' => 'missing_checksum', 'message' => 'checksum is required'], 400);
        }

        // verify checksum matches raw bytes
        $computed = hash('sha256', $bytes);
        if (!hash_equals($computed, $checksum)) {
            $logger->warning('upload.checksum_mismatch', $baseCtx + ['provided_prefix' => substr($checksum,0,12), 'computed_prefix'=>substr($computed,0,12)]);
            return response()->json(['status' => 'error', 'error' => 'checksum_mismatch', 'message' => 'checksum does not match image bytes'], 400);
        }

        // idempotency/deduplication: prefer (weighing_id, checksum) uniqueness if weighing_id supplied
        if ($weighingId) {
            $existing = TransactionImage::where('weighing_id', $weighingId)
                ->where('checksum_sha256', $checksum)
                ->first();
        } elseif ($transactionId) {
            $existing = TransactionImage::where('transaction_id', $transactionId)
                ->where('checksum_sha256', $checksum)
                ->first();
        } else {
            // fallback global checksum check
            $existing = TransactionImage::where('checksum_sha256', $checksum)->first();
        }
        if ($existing) {
            return response()->json([
                'id' => $existing->id,
                'image_path' => $existing->image_path
            ], 200);
        }

        // save bytes via service
        try {
            $identity = $weighingId ? (string)$weighingId : ($transactionId ?? 'unknown');
            $mode = $data['mode'] ?? null;
            $meta = $this->storageService->saveBytes($bytes, $identity, $cameraNo, $capturedAt, $contentType, $checksum, $mode);
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
    public function upload(UploadImageRequest $request): JsonResponse
{
    $t0 = microtime(true);
    $rid = (string) Str::uuid();

    // Use a dedicated channel (configure below). Fallback to default if you prefer: Log::
    $logger = Log::channel('upload');

    $baseCtx = [
        'request_id'     => $rid,
        'user_id'        => optional($request->user())->id,
        'ip'             => $request->ip(),
        'ua'             => $request->userAgent(),
        'transaction_id' => $request->input('transaction_id'),
        'camera_no'      => $request->input('camera_no'),
    ];

    $logger->info('upload.start', $baseCtx + [
        'payload_keys' => array_keys($request->all()),
        'has_capture_datetime' => $request->filled('capture_datetime'),
        'has_capture_date'     => $request->filled('capture_date'),
        'has_capture_time'     => $request->filled('capture_time'),
        'has_image_base64'     => $request->filled('image_base64'),
        'content_type_provided'=> (bool) $request->input('content_type'),
    ]);

    try {
        $data = $request->validated();

        // parse captured datetime
        if (!empty($data['capture_datetime'])) {
            $capturedAt = Carbon::parse($data['capture_datetime']);
        } elseif (!empty($data['capture_date']) && !empty($data['capture_time'])) {
            $capturedAt = Carbon::parse($data['capture_date'] . ' ' . $data['capture_time']);
        } else {
            $logger->warning('upload.missing_capture_time', $baseCtx);
            return response()->json(['message' => 'capture_datetime or capture_date+capture_time required'], 400);
        }
        $logger->debug('upload.captured_time_parsed', $baseCtx + [
            'captured_at_iso' => $capturedAt->toIso8601String(),
        ]);

        $transactionId = $data['transaction_id'];
        $cameraNo = $data['camera_no'];

        // decode base64 (never log raw bytes)
        $b64 = $data['image_base64'];
        $hadDataPrefix = false;
        if (preg_match('/^data:(.*);base64,/', $b64)) {
            $hadDataPrefix = true;
            $b64 = substr($b64, strpos($b64, ',') + 1);
        }
        $logger->debug('upload.base64_received', $baseCtx + [
            'had_data_url_prefix' => $hadDataPrefix,
            'base64_length'       => strlen($data['image_base64']),
        ]);

        $bytes = base64_decode($b64, true);
        if ($bytes === false) {
            $logger->warning('upload.invalid_base64', $baseCtx);
            return response()->json(['message' => 'Invalid base64 image'], 400);
        }

        $size = strlen($bytes);
        $max  = config('image.max_bytes', 5 * 1024 * 1024);
        $logger->debug('upload.size_check', $baseCtx + [
            'bytes' => $size,
            'max'   => $max,
        ]);
        if ($size > $max) {
            $logger->warning('upload.too_large', $baseCtx + ['bytes' => $size, 'max' => $max]);
            return response()->json(['message' => 'Image too large'], 413);
        }

        // infer mime if not provided
        $contentType = $data['content_type'] ?? null;
        if (!$contentType) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $contentType = $finfo->buffer($bytes) ?: 'image/png';
            $logger->debug('upload.mime_inferred', $baseCtx + ['content_type' => $contentType]);
        } else {
            $logger->debug('upload.mime_provided', $baseCtx + ['content_type' => $contentType]);
        }

        if (!in_array($contentType, ['image/png', 'image/jpeg'])) {
            $logger->warning('upload.unsupported_type', $baseCtx + ['content_type' => $contentType]);
            return response()->json(['message' => 'Unsupported image type: ' . $contentType], 400);
        }

        $checksum = $data['checksum'] ?? hash('sha256', $bytes);
        $logger->debug('upload.checksum_ready', $baseCtx + [
            'checksum_sha256_prefix' => substr($checksum, 0, 12),
        ]);

        // idempotency: check existing by txn+cam+time
        $existing = TransactionImage::where('transaction_id', $transactionId)
            ->where('camera_no', $cameraNo)
            ->where('captured_at', $capturedAt)
            ->first();

        if ($existing) {
            $logger->info('upload.idempotent_hit', $baseCtx + [
                'existing_id'   => $existing->id,
                'existing_path' => $existing->image_path,
            ]);
            $durationMs = (int) round((microtime(true) - $t0) * 1000);
            $logger->info('upload.done', $baseCtx + ['status' => 200, 'duration_ms' => $durationMs]);
            return response()->json([
                'status' => 'success',
                'image_id' => $existing->id,
                'weighing_id' => $existing->weighing_id,
                'transaction_id' => $existing->transaction_id,
                'url' => Storage::disk('public')->url($existing->image_path),
                'checksum' => $existing->checksum_sha256,
                'already_exists' => true
            ], 200);
        }

        // save bytes via service
        try {
            $logger->debug('upload.storage_save_attempt', $baseCtx);
            $identity = $transactionId ?? 'unknown';
            $mode = $data['mode'] ?? null;
            $meta = $this->storageService->saveBytes($bytes, $identity, $cameraNo, $capturedAt, $contentType, null, $mode);
            $logger->info('upload.storage_saved', $baseCtx + [
                'path'         => $meta['path'] ?? null,
                'size'         => $meta['size'] ?? null,
                'content_type' => $meta['content_type'] ?? null,
                'checksum_pref'=> isset($meta['checksum']) ? substr($meta['checksum'], 0, 12) : null,
            ]);
        } catch (\Exception $e) {
            $logger->error('upload.storage_failed', $baseCtx + [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Failed to save image'], 500);
        }

        // determine association state: link to weighing if exists, else store pending
        $weighingExists = false;
        if ($weighingId) {
            // check existence of weighing record (use WeightTransaction model)
            try {
                $weighingExists = (bool) \App\Models\WeightTransaction::find($weighingId);
            } catch (\Throwable $e) {
                $weighingExists = false;
            }
        }

        $rec = TransactionImage::create([
            'weighing_id'      => $weighingId,
            'transaction_id'   => $transactionId,
            'camera_no'        => $cameraNo,
            'captured_at'      => $capturedAt,
            'image_path'       => $meta['path'],
            'storage_backend'  => 'local',
            'content_type'     => $meta['content_type'],
            'size_bytes'       => $meta['size'],
            'checksum_sha256'  => $meta['checksum'],
            'ingest_status'    => $weighingExists ? 'linked' : 'pending',
            'extra_meta'       => $data['metadata'] ?? null,
        ]);
        $logger->info('upload.db_created', $baseCtx + [
            'record_id'  => $rec->id,
            'image_path' => $rec->image_path,
        ]);

        $url = Storage::disk('public')->url($meta['path']);
        $durationMs = (int) round((microtime(true) - $t0) * 1000);
        $logger->info('upload.done', $baseCtx + ['status' => 201, 'duration_ms' => $durationMs]);

        if ($weighingExists) {
            return response()->json([
                'status' => 'success',
                'image_id' => $rec->id,
                'weighing_id' => $weighingId,
                'transaction_id' => $transactionId,
                'url' => $url,
                'checksum' => $rec->checksum_sha256,
                'already_exists' => false
            ], 201);
        }

        return response()->json([
            'status' => 'accepted',
            'image_id' => $rec->id,
            'weighing_id' => null,
            'transaction_id' => $transactionId,
            'info' => 'Saved; weighing not found. Will link when weighing record becomes available.'
        ], 202);

    } catch (\Throwable $e) {
        $durationMs = (int) round((microtime(true) - $t0) * 1000);
        $logger->error('upload.unhandled_exception', $baseCtx + [
            'error'       => $e->getMessage(),
            'duration_ms' => $durationMs,
        ]);
        throw $e; // Let Laravel handle (or convert to a JSON 500 if you prefer)
    }
}

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class TransactionImage extends Model
{
    use HasFactory;

    protected $table = 'transaction_images';

    protected $fillable = [
    'weighing_id',
        'transaction_id',
        'sector_id',
        'mode',
        'camera_no',
        'captured_at',
        'image_path',
        'storage_backend',
        'content_type',
        'size_bytes',
        'checksum_sha256',
        'ingest_status',
        'extra_meta',
        'isSynced',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'extra_meta' => 'array'
    ];

     protected static function booted()
    {
        static::saving(function ($img) {
            if ($img->image_path) {
                $parsed = self::parseFromPath($img->image_path);
                if (!$img->sector_id && $parsed['sector_id'])   $img->sector_id   = $parsed['sector_id'];
                if (!$img->weighing_id && $parsed['weighing_id']) $img->weighing_id = $parsed['weighing_id'];
            }
        });
    }

    public static function parseFromPath(string $path): array
    {
        $out = ['sector_id'=>null,'date'=>null,'weighing_id'=>null];
        if (preg_match('#^pictures/(\d+)/(\d{4}-\d{2}-\d{2})/(\d+)/#', $path, $m)) {
            $out['sector_id']   = (int)$m[1];
            $out['date']        = $m[2];
            $out['weighing_id'] = (int)$m[3];
        }
        return $out;
    }

    public function transaction()
    {
        return $this->belongsTo(WeightTransaction::class, 'weighing_id', 'id');
    }

    // URL accessor
    public function getUrlAttribute(): ?string
    {
        $path = $this->image_path;
        if (!$path) return null;
        $disk = $this->storage_backend ?: 'public';
        try {
            return Storage::disk($disk)->url($path); // e.g. /storage/pictures/...
        } catch (\Throwable) {
            if ($disk === 'public') return url('/storage/'.ltrim($path,'/'));
            return null;
        }
    }
}

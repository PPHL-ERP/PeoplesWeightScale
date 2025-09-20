<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'extra_meta'
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'extra_meta' => 'array'
    ];
}

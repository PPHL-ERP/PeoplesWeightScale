<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Models\TransactionImage;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_base64_upload_saves_png()
    {
        Storage::fake('public');

        $b64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Yq7hJwAAAAASUVORK5CYII='; // 1x1 PNG
        $payload = [
            'transaction_id' => 'CH-2025-001',
            'camera_no' => 'cam1',
            'capture_datetime' => '2025-09-12T06:20:13.123456Z',
            'image_base64' => $b64,
            'content_type' => 'image/png',
            'checksum' => hash('sha256', base64_decode($b64)),
            'mode' => 'gross',
            'weighing_id' => null,
            'sector_id' => 45
        ];

        $resp = $this->postJson('/api/v1/images/upload', $payload);
        $resp->assertStatus(201)->assertJsonStructure(['status','data'=>['id','weighing_id','filename','url','checksum']]);

        $this->assertDatabaseCount('transaction_images', 1);
        $rec = TransactionImage::first();
        $this->assertStringContainsString('pictures/2025-09-12/45/', $rec->image_path);
        Storage::disk('public')->assertExists($rec->image_path);
    }

    public function test_multipart_upload_saves_png()
    {
        Storage::fake('public');

        $tmp = tmpfile();
        $meta = stream_get_meta_data($tmp);
        $tmpfname = $meta['uri'];
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Yq7hJwAAAAASUVORK5CYII=');
        file_put_contents($tmpfname, $png);

        $response = $this->post('/api/v1/images/upload', [
            'transaction_id' => 'CH-2025-002',
            'camera_no' => 'cam2',
            'capture_datetime' => '2025-09-12T06:20:13.123456Z',
            'image_file' => new \Illuminate\Http\UploadedFile($tmpfname, 'one.png', 'image/png', null, true),
            'content_type' => 'image/png',
            'checksum' => hash('sha256', $png),
            'mode' => 'tare',
            'sector_id' => 99
        ]);

        $response->assertStatus(201)->assertJson(['status' => 'created']);
        $this->assertDatabaseCount('transaction_images', 1);
        $rec = TransactionImage::first();
        $this->assertStringContainsString('pictures/2025-09-12/99/', $rec->image_path);
        Storage::disk('public')->assertExists($rec->image_path);
    }
}

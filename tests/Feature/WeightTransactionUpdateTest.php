<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\WeightTransaction;

class WeightTransactionUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_weight_transaction_with_vendor_name()
    {
        // Create a transaction
        $transaction = WeightTransaction::factory()->create();

        $payload = [
            'vehicle_no' => 'ABC-123',
            'vendor_name' => 'MS Trading',
        ];

        $response = $this->putJson(route('weight_transactions.update', ['id' => $transaction->id]), $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('weight_transactions', [
            'id' => $transaction->id,
            'vehicle_no' => 'ABC-123',
            'vendor_name' => 'MS Trading',
        ]);
    }
}

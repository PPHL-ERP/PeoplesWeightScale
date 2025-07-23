<?php

namespace App\Services;

use App\Models\Sms;
use Illuminate\Support\Facades\Http;

class AddSmsService
{
    public function sendCustomSms($phone, $message)
{
    $sms = Sms::where('status', 'active')->first();

    if (!$sms) {
        throw new \Exception('No SMS gateway configured.');
    }

    $baseUrl = trim($sms->url);
        $cleanPhone = $this->cleanPhoneNumber($phone);

        $params = [
            'api_key'  => $sms->apiKey,
            'type'     => $sms->type ?? 'text',
            'contacts' => $cleanPhone,
            'senderid' => $sms->nmSenderId,
            'msg'      => $message
        ];

        $response = Http::get($baseUrl, $params);

        if ($response->failed()) {
            throw new \Exception('SMS failed: ' . $response->body());
        }

        return $response->body();
    }
    private function cleanPhoneNumber(string $rawPhone): string
{
    // Remove all non-numeric characters
    $cleaned = preg_replace('/[^0-9]/', '', $rawPhone);

    // Validate length
    if (strlen($cleaned) === 11 && str_starts_with($cleaned, '01')) {
        return '88' . $cleaned; // Add Bangladesh country code
    }

    if (strlen($cleaned) === 13 && str_starts_with($cleaned, '8801')) {
        return $cleaned; // Already in correct format
    }

    throw new \Exception("Invalid phone number: $rawPhone");
}

}

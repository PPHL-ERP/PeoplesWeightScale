<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait SendsSMS
{
    protected $smsApiUrl = 'https://msg.elitbuzz-bd.com/smsapi';
    protected $smsApiKey = 'C200894566cb511a0eee98.43166964'; // Replace with your API Key

    public function sendSms($number, $message, $senderId, $type = 'text', $label = 'transactional')
    {
        try {
            $response = Http::get($this->smsApiUrl, [
                'api_key' => $this->smsApiKey,
                'type' => $type,
                'contacts' => $number,
                'senderid' => $senderId,
                'msg' => urlencode($message),
                'label' => $label,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => $response->body()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function checkSmsBalance()
    {
        try {
            $response = Http::get($this->smsApiUrl, [
                'api_key' => $this->smsApiKey,
                'type' => 'balance',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => $response->body()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

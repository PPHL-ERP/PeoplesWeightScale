<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\SendsSMS;
use Illuminate\Http\Request;
class SendSmsController extends Controller
{
    use SendsSMS;

    public function sendSmsNotification()
    {
        $number = '8801624032691'; // Replace with the recipient's number
        $message = 'This is a test SMS.';
        $senderId = 'Kamrul'; // Replace with your approved sender ID
        $response = $this->sendSms($number, $message, $senderId);

        return response()->json($response);
    }

    public function checkBalance()
    {
        $response = $this->checkSmsBalance();

        return response()->json($response);
    }
}

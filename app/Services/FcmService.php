<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;

class FcmService
{
    /**
     * @param string $token
     * @param string $title
     * @param string $body
     * @param array $data
     */
    public static function send($token, $title, $body, $data = [])
    {
        try {
            $messaging = app('firebase.messaging');
            $stringData = array_map('strval', $data);
            $message = CloudMessage::fromArray([
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $stringData,
            ]);

            $messaging->send($message);

            return true;
        } catch (\Exception $e) {
            Log::error('FCM Send Error: ' . $e->getMessage());
            return false;
        }
    }
}

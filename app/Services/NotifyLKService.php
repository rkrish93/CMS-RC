<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotifyLKService
{
    public static function send($phone, $message)
    {
        $userId = env('NOTIFY_USER_ID', env('NOTIFYLK_USER_ID'));
        $apiKey = env('NOTIFY_API_KEY', env('NOTIFYLK_API_KEY'));
        $senderId = env('NOTIFY_SENDER_ID', env('NOTIFYLK_SENDER_ID'));

        return Http::withoutVerifying()->get(
            'https://app.notify.lk/api/v1/send',
            [
                'user_id'   => $userId,
                'api_key'   => $apiKey,
                'sender_id' => $senderId,
                'to'        => $phone,
                'message'   => $message,
            ]
        );
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotifyLKService
{
    public static function send($phone, $message)
    {
        
        return Http::withoutVerifying()->get(
            'https://app.notify.lk/api/v1/send',
            [
                'user_id'   => env('NOTIFY_USER_ID'),
                'api_key'   => env('NOTIFY_API_KEY'),
                'sender_id' => env('NOTIFY_SENDER_ID'),
                'to'        => $phone,
                'message'   => $message,
            ]
        );
    }
}

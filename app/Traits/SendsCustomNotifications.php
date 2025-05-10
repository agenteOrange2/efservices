<?php

namespace App\Traits;

trait SendsCustomNotifications
{
    protected function sendNotification($type, $message, $details = null){
        return [
            'notification' => [
                'type' => $type,
                'message' => $message,
                'details' => $details
                ]
            ];
    }
}

<?php

namespace App\Services\Notification;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendEmail(User $user, string $subject, string $message)
    {
        try {

            Mail::raw($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                    ->subject($subject);
            });

            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'email',
                'purpose' => $subject,
                'recipient' => $user->email,
                'message' => $message,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {

            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'email',
                'purpose' => $subject,
                'recipient' => $user->email,
                'message' => $message,
                'status' => 'failed',
                'provider_response' => $e->getMessage(),
            ]);

        }
    }

    public function sendSMS(User $user, string $message)
    {
        NotificationLog::create([
            'user_id' => $user->id,
            'channel' => 'sms',
            'purpose' => 'system_notification',
            'recipient' => $user->phone,
            'message' => $message,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
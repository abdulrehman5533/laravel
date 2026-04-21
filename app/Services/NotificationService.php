<?php

namespace App\Services;

use App\Models\NotificationHistory;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification based on a trigger event.
     */
    public function notify(string $event, User $user, array $data = [])
    {
        $template = NotificationTemplate::where('trigger_event', $event)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            Log::warning("No notification template found for event: {$event}");

            return;
        }

        $body = $this->parseTemplate($template->body, $data);
        $subject = $this->parseTemplate($template->subject ?? '', $data);

        return $this->sendOmnichannel($user, $body, $template->channels, $subject, $event);
    }

    /**
     * Parse template placeholders.
     */
    protected function parseTemplate(string $text, array $data)
    {
        foreach ($data as $key => $value) {
            $text = str_replace("{{{$key}}}", $value, $text);
        }

        return $text;
    }

    public function sendOmnichannel($user, $message, $channels = ['email', 'sms', 'whatsapp'], $subject = '', $event = 'general')
    {
        $results = [];

        foreach ($channels as $channel) {
            $status = 'sent';
            $error = null;

            try {
                match ($channel) {
                    'email' => $this->sendEmail($user->email, $message, $subject),
                    'sms' => $this->sendSms($user->phone, $message),
                    'whatsapp' => $this->sendWhatsApp($user->phone, $message),
                    'in_app' => $this->sendInApp($user->id, $message),
                    default => throw new \Exception("Unsupported channel: {$channel}"),
                };
            } catch (\Exception $e) {
                $status = 'failed';
                $error = $e->getMessage();
            }

            NotificationHistory::create([
                'user_id' => $user->id,
                'channel' => $channel,
                'event_type' => $event,
                'status' => $status,
                'error_message' => $error,
            ]);

            $results[$channel] = $status;
        }

        return $results;
    }

    protected function sendEmail($email, $message, $subject)
    {
        // Integration with Mailgun/SES
        return true;
    }

    protected function sendSms($phone, $message)
    {
        // Integration with Twilio/Nexmo
        return true;
    }

    protected function sendWhatsApp($phone, $message)
    {
        // Integration with Twilio WhatsApp API
        return true;
    }

    protected function sendInApp($userId, $message)
    {
        $user = User::find($userId);
        if (!$user) return false;

        // Using Laravel's built-in database notifications
        // This assumes a Notification class exists or we use a generic one
        // For now, we'll log it as a history entry which is already handled in sendOmnichannel
        // But we could also fire an event for real-time Pusher/Socket.io
        
        return true;
    }
}

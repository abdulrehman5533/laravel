<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationHistory;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    /**
     * Send email notification
     */
    public function sendEmail($to, $subject, $message, $type = 'info')
    {
        try {
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)
                    ->subject($subject);
            });

            $this->logNotification('email', $to, $subject, 'sent');
            return ['status' => 'success', 'message' => 'Email بھیجی گئی'];
        } catch (\Exception $e) {
            Log::error('Email error: ' . $e->getMessage());
            $this->logNotification('email', $to, $subject, 'failed', $e->getMessage());
            return ['status' => 'error', 'message' => 'Email بھیجنے میں خرابی'];
        }
    }

    /**
     * Send SMS notification
     */
    public function sendSMS($phone, $message)
    {
        try {
            $apiKey = env('SMS_API_KEY');
            
            if (!$apiKey) {
                return ['status' => 'success', 'message' => 'SMS API key نہیں ہے'];
            }

            // مثال کے طور پر - اپنی SMS service استعمال کریں
            $response = Http::post('https://api.sms-service.com/send', [
                'api_key' => $apiKey,
                'phone' => $phone,
                'message' => $message
            ]);

            $this->logNotification('sms', $phone, $message, 'sent');
            return ['status' => 'success', 'message' => 'SMS بھیجی گئی'];
        } catch (\Exception $e) {
            Log::error('SMS error: ' . $e->getMessage());
            $this->logNotification('sms', $phone, $message, 'failed', $e->getMessage());
            return ['status' => 'error', 'message' => 'SMS بھیجنے میں خرابی'];
        }
    }

    /**
     * Send in-app notification
     */
    public function sendInAppNotification($userId, $title, $message, $type = 'info', $actionUrl = null)
    {
        try {
            $user = User::find($userId);
            
            if (!$user) {
                return ['status' => 'error', 'message' => 'صارف نہیں ملا'];
            }

            // Database میں notification save کریں
            $notification = NotificationHistory::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'action_url' => $actionUrl,
                'is_read' => false,
                'created_at' => now()
            ]);

            $this->logNotification('in-app', $userId, $title, 'sent');
            return ['status' => 'success', 'message' => 'In-app notification بھیجی گئی'];
        } catch (\Exception $e) {
            Log::error('In-app notification error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Notification بھیجنے میں خرابی'];
        }
    }

    /**
     * Send WhatsApp notification
     */
    public function sendWhatsApp($phone, $message)
    {
        try {
            $apiKey = env('WHATSAPP_API_KEY');
            
            if (!$apiKey) {
                return ['status' => 'success', 'message' => 'WhatsApp API key نہیں ہے'];
            }

            // مثال کے طور پر - Twilio یا دوسری service استعمال کریں
            $response = Http::post('https://api.whatsapp.com/send', [
                'api_key' => $apiKey,
                'phone' => $phone,
                'message' => $message
            ]);

            $this->logNotification('whatsapp', $phone, $message, 'sent');
            return ['status' => 'success', 'message' => 'WhatsApp message بھیجی گئی'];
        } catch (\Exception $e) {
            Log::error('WhatsApp error: ' . $e->getMessage());
            $this->logNotification('whatsapp', $phone, $message, 'failed', $e->getMessage());
            return ['status' => 'error', 'message' => 'WhatsApp message بھیجنے میں خرابی'];
        }
    }

    /**
     * Send bulk notifications
     */
    public function sendBulkNotifications($userIds, $title, $message, $type = 'info')
    {
        $results = [];
        
        foreach ($userIds as $userId) {
            $result = $this->sendInAppNotification($userId, $title, $message, $type);
            $results[] = $result;
        }

        return [
            'status' => 'success',
            'message' => count($results) . ' notifications بھیجی گئیں',
            'results' => $results
        ];
    }

    /**
     * Send alert notification
     */
    public function sendAlert($title, $message, $severity = 'info')
    {
        try {
            // تمام admins کو alert بھیجیں
            $admins = User::where('role_id', 1)->get(); // Admin role

            foreach ($admins as $admin) {
                $this->sendInAppNotification($admin->id, $title, $message, $severity);
                
                // Email بھی بھیجیں
                if ($severity === 'critical') {
                    $this->sendEmail($admin->email, $title, $message, $severity);
                }
            }

            return ['status' => 'success', 'message' => 'Alert بھیجی گئی'];
        } catch (\Exception $e) {
            Log::error('Alert error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Alert بھیجنے میں خرابی'];
        }
    }

    /**
     * Log notification
     */
    private function logNotification($type, $recipient, $subject, $status, $error = null)
    {
        try {
            NotificationHistory::create([
                'type' => $type,
                'recipient' => $recipient,
                'subject' => $subject,
                'status' => $status,
                'error_message' => $error,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Notification logging error: ' . $e->getMessage());
        }
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 10)
    {
        try {
            $notifications = NotificationHistory::where('user_id', $userId)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            return [
                'status' => 'success',
                'data' => $notifications
            ];
        } catch (\Exception $e) {
            Log::error('Get notifications error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Notifications حاصل کرنے میں خرابی'];
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        try {
            $notification = NotificationHistory::find($notificationId);
            
            if (!$notification) {
                return ['status' => 'error', 'message' => 'Notification نہیں ملی'];
            }

            $notification->update(['is_read' => true]);
            return ['status' => 'success', 'message' => 'Notification پڑھی گئی'];
        } catch (\Exception $e) {
            Log::error('Mark as read error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'خرابی'];
        }
    }

    /**
     * Send AI Agent notification
     */
    public function sendAIAgentNotification($userId, $message, $actionType = 'info')
    {
        return $this->sendInAppNotification(
            $userId,
            'AI Agent',
            $message,
            $actionType,
            '/ai-agent/chat'
        );
    }
}

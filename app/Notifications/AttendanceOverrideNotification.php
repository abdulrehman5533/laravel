<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class AttendanceOverrideNotification extends Notification
{
    use Queueable;

    public $attendance;
    public $status;
    public $reason;

    public function __construct($attendance, $status, $reason)
    {
        $this->attendance = $attendance;
        $this->status = $status;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Attendance Override Update')
            ->line('Your attendance record has been updated by admin.')
            ->line('Status: ' . $this->status)
            ->line('Reason: ' . $this->reason);
    }

    public function toArray($notifiable)
    {
        return [
            'attendance_id' => $this->attendance->id,
            'status' => $this->status,
            'reason' => $this->reason,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

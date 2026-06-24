<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HrAccountApprovedNotification extends Notification
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tài khoản nhà tuyển dụng đã được duyệt')
            ->greeting('Xin chào ' . ($notifiable->name ?? '') . ',')
            ->line('Tài khoản nhà tuyển dụng của bạn đã được kích hoạt.')
            ->action('Đăng nhập nhà tuyển dụng', route('employers.login'))
            ->line('Bạn có thể đăng nhập và bắt đầu sử dụng khu vực nhà tuyển dụng.');
    }
}

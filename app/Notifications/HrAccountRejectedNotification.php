<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HrAccountRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $reason)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reason = trim($this->reason) !== ''
            ? trim($this->reason)
            : 'Không ghi rõ.';

        return (new MailMessage)
            ->subject('Yêu cầu tài khoản nhà tuyển dụng bị từ chối')
            ->greeting('Xin chào ' . ($notifiable->name ?? '') . ',')
            ->line('Yêu cầu kích hoạt tài khoản nhà tuyển dụng của bạn chưa được duyệt.')
            ->line('Lý do: ' . $reason)
            ->action('Quay lại trang nhà tuyển dụng', route('employers.portal'))
            ->line('Vui lòng kiểm tra lại thông tin hoặc liên hệ quản trị viên nếu cần hỗ trợ.');
    }
}

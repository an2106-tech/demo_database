<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\HrAccountApprovedNotification;
use App\Notifications\HrAccountRejectedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class HrAccountNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_hr_notification_contains_login_link(): void
    {
        $user = User::factory()->make([
            'name' => 'Employer Owner',
            'email' => 'approved-hr@example.com',
        ]);

        $mail = (new HrAccountApprovedNotification())->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('Tài khoản nhà tuyển dụng đã được duyệt', $mail->subject);
        $this->assertSame(route('employers.login'), $mail->actionUrl);
    }

    public function test_rejected_hr_notification_contains_reason(): void
    {
        $user = User::factory()->make([
            'name' => 'Employer Owner',
            'email' => 'rejected-hr@example.com',
        ]);

        $mail = (new HrAccountRejectedNotification('Thiếu thông tin chi nhánh.'))->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('Yêu cầu tài khoản nhà tuyển dụng bị từ chối', $mail->subject);
        $this->assertSame(route('employers.portal'), $mail->actionUrl);
        $this->assertContains('Lý do: Thiếu thông tin chi nhánh.', $mail->introLines);
    }
}

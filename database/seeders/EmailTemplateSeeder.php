<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('email_templates')) {
            return;
        }

        $creatorId = User::query()->value('id');

        if (! $creatorId) {
            return;
        }

        EmailTemplate::query()->updateOrCreate(
            ['type' => 'auto_reply', 'name' => 'Xác nhận nhận hồ sơ ứng tuyển'],
            [
                'subject' => 'Xác nhận đã nhận hồ sơ ứng tuyển',
                'body' => implode("\n", [
                    '<p>Chào {{candidate_name}},</p>',
                    '<p>Hệ thống đã nhận được hồ sơ ứng tuyển của bạn cho vị trí <strong>{{job_title}}</strong>.</p>',
                    '<p>Thông tin ghi nhận:</p>',
                    '<ul>',
                    '<li>Mã hồ sơ ứng tuyển: #{{application_id}}</li>',
                    '<li>Vị trí: {{job_title}}</li>',
                    '<li>Thời gian nộp: {{applied_at}}</li>',
                    '<li>Email ứng tuyển: {{candidate_email}}</li>',
                    '</ul>',
                    '<p>Bộ phận tuyển dụng sẽ xem xét hồ sơ và liên hệ với bạn nếu phù hợp với nhu cầu tuyển dụng hiện tại.</p>',
                    '<p>Trân trọng,<br>{{app_name}}</p>',
                ]),
                'is_active' => true,
                'created_by' => $creatorId,
            ],
        );

        EmailTemplate::query()->updateOrCreate(
            ['type' => 'interview_invite', 'name' => 'Thư mời phỏng vấn'],
            [
                'subject' => 'Lịch phỏng vấn - {{candidate_name}} - {{job_title}}',
                'body' => implode("\n", [
                    '<p>Xin chào,</p>',
                    '<p>Hệ thống đã sắp xếp lịch phỏng vấn cho hồ sơ ứng tuyển.</p>',
                    '<p><strong>Thông tin lịch phỏng vấn</strong></p>',
                    '<ul>',
                    '<li>Ứng viên: {{candidate_name}}</li>',
                    '<li>Vị trí: {{job_title}}</li>',
                    '<li>Thời gian: {{scheduled_at}}</li>',
                    '<li>Hình thức: {{interview_type}}</li>',
                    '<li>Địa điểm / link: {{interview_location}}</li>',
                    '<li>Người phỏng vấn: {{interviewer_name}}</li>',
                    '</ul>',
                    '<p><strong>Ghi chú:</strong> {{interview_notes}}</p>',
                    '<p>File lịch định dạng calendar (.ics) đã được đính kèm để bạn có thể thêm vào lịch làm việc.</p>',
                    '<p>Trân trọng,<br>{{app_name}}</p>',
                ]),
                'is_active' => true,
                'created_by' => $creatorId,
            ],
        );

        EmailTemplate::query()->updateOrCreate(
            ['type' => 'rejection', 'name' => 'Thông báo từ chối hồ sơ'],
            [
                'subject' => 'Thông báo kết quả hồ sơ ứng tuyển',
                'body' => implode("\n", [
                    '<p>Chào {{candidate_name}},</p>',
                    '<p>Cảm ơn bạn đã quan tâm và ứng tuyển vào vị trí <strong>{{job_title}}</strong>.</p>',
                    '<p>Sau quá trình xem xét, hiện tại chúng tôi chưa thể tiếp tục hồ sơ của bạn cho vị trí này.</p>',
                    '<p>Thông tin hồ sơ:</p>',
                    '<ul>',
                    '<li>Mã hồ sơ ứng tuyển: #{{application_id}}</li>',
                    '<li>Vị trí: {{job_title}}</li>',
                    '<li>Thời gian cập nhật: {{updated_at}}</li>',
                    '</ul>',
                    '<p><strong>Lý do:</strong> {{rejected_reason}}</p>',
                    '<p>Chúng tôi sẽ lưu thông tin của bạn cho các cơ hội phù hợp hơn trong tương lai.</p>',
                    '<p>Trân trọng,<br>{{app_name}}</p>',
                ]),
                'is_active' => true,
                'created_by' => $creatorId,
            ],
        );
    }
}

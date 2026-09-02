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
                'subject' => '[{{app_name}}] - Xác nhận tiếp nhận hồ sơ ứng tuyển vị trí {{job_title}}',
                'body' => implode("\n", [
                    '<p>Thân gửi <strong>{{candidate_name}}</strong>,</p>',
                    '<p>Cảm ơn bạn đã quan tâm và gửi hồ sơ ứng tuyển vị trí <strong>{{job_title}}</strong> tại <strong>{{app_name}}</strong>.</p>',
                    '<p>Chúng tôi xác nhận đã tiếp nhận hồ sơ của bạn với thông tin sau:</p>',
                    '<div class="info-card">',
                    '    <div class="info-item"><span>Mã hồ sơ</span><span class="info-value">#{{application_id}}</span></div>',
                    '    <div class="info-item"><span>Vị trí</span><span class="info-value">{{job_title}}</span></div>',
                    '    <div class="info-item"><span>Thời gian nộp</span><span class="info-value">{{applied_at}}</span></div>',
                    '    <div class="info-item"><span>Email ứng tuyển</span><span class="info-value">{{candidate_email}}</span></div>',
                    '</div>',
                    '<p>Bộ phận tuyển dụng sẽ xem xét hồ sơ và liên hệ với bạn nếu hồ sơ phù hợp với nhu cầu tuyển dụng.</p>',
                    '<p>Trân trọng,<br><strong>Đội ngũ Tuyển dụng - {{app_name}}</strong></p>',
                ]),
                'is_active' => true,
                'created_by' => $creatorId,
            ],
        );

        EmailTemplate::query()->updateOrCreate(
            ['type' => 'interview_invite', 'name' => 'Thư mời phỏng vấn'],
            [
                'subject' => 'Thư mời tham gia {{round_name}} - {{job_title}} - {{app_name}}',
                'body' => implode("\n", [
                    '<p>Chào <strong>{{candidate_name}}</strong>,</p>',
                    '<p>Cảm ơn bạn đã ứng tuyển vào vị trí <strong>{{job_title}}</strong>. Chúng tôi trân trọng mời bạn tham dự buổi phỏng vấn theo thông tin dưới đây.</p>',
                    '<div class="info-card">',
                    '    <div class="info-item"><span>Vòng phỏng vấn</span><span class="info-value">{{round_name}}</span></div>',
                    '    <div class="info-item"><span>Vị trí ứng tuyển</span><span class="info-value">{{job_title}}</span></div>',
                    '    <div class="info-item"><span>Thời gian</span><span class="info-value">{{scheduled_at}}</span></div>',
                    '    <div class="info-item"><span>Thời lượng</span><span class="info-value">{{duration_minutes}} phút</span></div>',
                    '    <div class="info-item"><span>Hình thức</span><span class="info-value">{{interview_type}}</span></div>',
                    '    <div class="info-item"><span>Địa điểm / Link</span><span class="info-value">{{interview_location}}</span></div>',
                    '    <div class="info-item"><span>Người phụ trách</span><span class="info-value">{{interviewer_name}}</span></div>',
                    '</div>',
                    '<p><strong>Ghi chú:</strong> {{interview_notes}}</p>',
                    '<p>File lịch hẹn (.ics) đã được đính kèm để bạn thuận tiện lưu hoặc cập nhật lịch cá nhân.</p>',
                    '<p>Trân trọng,<br><strong>Phòng Tuyển dụng - {{app_name}}</strong></p>',
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
                    '<p>Hồ sơ hiện chưa phù hợp với nhu cầu tuyển dụng tại thời điểm này.</p>',
                    '<p>Chúng tôi sẽ lưu thông tin của bạn cho các cơ hội phù hợp hơn trong tương lai.</p>',
                    '<p>Trân trọng,<br>{{app_name}}</p>',
                ]),
                'is_active' => true,
                'created_by' => $creatorId,
            ],
        );

        EmailTemplate::query()
            ->where('type', 'offer')
            ->where('name', '<>', 'Đề nghị tuyển dụng')
            ->update(['is_active' => false]);

        EmailTemplate::query()->updateOrCreate(
            ['type' => 'offer', 'name' => 'Đề nghị tuyển dụng'],
            [
                'subject' => 'Đề nghị tuyển dụng - {{job_title}} - {{app_name}}',
                'body' => implode("\n", [
                    '<p>Thân gửi <strong>{{candidate_name}}</strong>,</p>',
                    '<p><strong>{{app_name}}</strong> trân trọng gửi đến bạn đề nghị tuyển dụng cho vị trí <strong>{{job_title}}</strong>.</p>',
                    '<div class="info-card">',
                    '    <div class="info-item"><span>Mã đề nghị</span><span class="info-value">#{{offer_id}}</span></div>',
                    '    <div class="info-item"><span>Mức lương đề nghị</span><span class="info-value">{{salary_offered}}</span></div>',
                    '    <div class="info-item"><span>Ngày bắt đầu dự kiến</span><span class="info-value">{{start_date}}</span></div>',
                    '    <div class="info-item"><span>Thời gian thử việc</span><span class="info-value">{{probation_months}}</span></div>',
                    '    <div class="info-item"><span>Hạn phản hồi</span><span class="info-value">{{expiration_date}}</span></div>',
                    '</div>',
                    '<p>Vui lòng xem file PDF đính kèm để biết chi tiết nội dung đề nghị tuyển dụng.</p>',
                    '<div>{{offer_content}}</div>',
                    '{{offer_response_actions}}',
                    '<p>Đề nghị này không thay thế hợp đồng lao động chính thức. Sau khi bạn xác nhận đồng ý, bộ phận tuyển dụng sẽ liên hệ để hướng dẫn các thủ tục tiếp theo.</p>',
                    '<p>Trân trọng,<br>Bộ phận Tuyển dụng - {{app_name}}</p>',
                ]),
                'is_active' => true,
                'created_by' => $creatorId,
            ],
        );
    }
}

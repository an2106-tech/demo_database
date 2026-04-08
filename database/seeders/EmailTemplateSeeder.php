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
    }
}

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
            ['type' => 'auto_reply', 'name' => 'Xac nhan nhan ho so ung tuyen'],
            [
                'subject' => 'Xac nhan da nhan ho so ung tuyen',
                'body' => implode("\n", [
                    '<p>Chao {{candidate_name}},</p>',
                    '<p>He thong da nhan duoc ho so ung tuyen cua ban cho vi tri <strong>{{job_title}}</strong>.</p>',
                    '<p>Thong tin ghi nhan:</p>',
                    '<ul>',
                    '<li>Ma ho so ung tuyen: #{{application_id}}</li>',
                    '<li>Vi tri: {{job_title}}</li>',
                    '<li>Thoi gian nop: {{applied_at}}</li>',
                    '<li>Email ung tuyen: {{candidate_email}}</li>',
                    '</ul>',
                    '<p>Bo phan tuyen dung se xem xet ho so va lien he voi ban neu phu hop voi nhu cau tuyen dung hien tai.</p>',
                    '<p>Tran trong,<br>{{app_name}}</p>',
                ]),
                'is_active' => true,
                'created_by' => $creatorId,
            ],
        );

        EmailTemplate::query()->updateOrCreate(
            ['type' => 'interview_invite', 'name' => 'Thu moi phong van'],
            [
                'subject' => 'Lich phong van - {{candidate_name}} - {{job_title}}',
                'body' => implode("\n", [
                    '<p>Xin chao,</p>',
                    '<p>He thong da sap xep lich phong van cho ho so ung tuyen.</p>',
                    '<p><strong>Thong tin lich phong van</strong></p>',
                    '<ul>',
                    '<li>Ung vien: {{candidate_name}}</li>',
                    '<li>Vi tri: {{job_title}}</li>',
                    '<li>Thoi gian: {{scheduled_at}}</li>',
                    '<li>Hinh thuc: {{interview_type}}</li>',
                    '<li>Dia diem / link: {{interview_location}}</li>',
                    '<li>Nguoi phong van: {{interviewer_name}}</li>',
                    '</ul>',
                    '<p><strong>Ghi chu:</strong> {{interview_notes}}</p>',
                    '<p>File lich dinh dang calendar (.ics) da duoc dinh kem de ban co the them vao lich lam viec.</p>',
                    '<p>Tran trong,<br>{{app_name}}</p>',
                ]),
                'is_active' => true,
                'created_by' => $creatorId,
            ],
        );

        EmailTemplate::query()->updateOrCreate(
            ['type' => 'rejection', 'name' => 'Thong bao tu choi ho so'],
            [
                'subject' => 'Thong bao ket qua ho so ung tuyen',
                'body' => implode("\n", [
                    '<p>Chao {{candidate_name}},</p>',
                    '<p>Cam on ban da quan tam va ung tuyen vao vi tri <strong>{{job_title}}</strong>.</p>',
                    '<p>Sau qua trinh xem xet, hien tai chung toi chua the tiep tuc ho so cua ban cho vi tri nay.</p>',
                    '<p>Thong tin ho so:</p>',
                    '<ul>',
                    '<li>Ma ho so ung tuyen: #{{application_id}}</li>',
                    '<li>Vi tri: {{job_title}}</li>',
                    '<li>Thoi gian cap nhat: {{updated_at}}</li>',
                    '</ul>',
                    '<p><strong>Ly do:</strong> {{rejected_reason}}</p>',
                    '<p>Chung toi se luu thong tin cua ban cho cac co hoi phu hop hon trong tuong lai.</p>',
                    '<p>Tran trong,<br>{{app_name}}</p>',
                ]),
                'is_active' => true,
                'created_by' => $creatorId,
            ],
        );
    }
}

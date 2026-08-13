<?php

namespace Database\Seeders;

use App\Models\OfferLetterTemplate;
use Illuminate\Database\Seeder;

class OfferLetterTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $official = <<<'HTML'
<p>FPT Education trân trọng ghi nhận năng lực và sự phù hợp của bạn trong quá trình tuyển dụng cho vị trí <strong>{{job_title}}</strong> tại <strong>{{branch_name}}</strong>.</p>
<p>Các thông tin về mức lương, ngày nhận việc và thời gian thử việc được thể hiện trong thư mời này.</p>
<p>Đề nghị có hiệu lực đến <strong>{{expiration_date}}</strong>. Sau khi xác nhận, bộ phận nhân sự sẽ liên hệ để hướng dẫn thủ tục nhận việc.</p>
<p>Trân trọng,<br/><strong>Khối Nhân sự FPT Education</strong></p>
HTML;

        $probation = <<<'HTML'
<p>FPT Education trân trọng mời bạn tham gia giai đoạn thử việc cho vị trí <strong>{{job_title}}</strong> tại <strong>{{branch_name}}</strong>.</p>
<p>Các điều khoản về mức lương, ngày bắt đầu và thời gian thử việc được thể hiện trong bảng thông tin của thư mời này.</p>
<p>Đề nghị có hiệu lực đến <strong>{{expiration_date}}</strong>. Sau thời gian thử việc, hai bên sẽ trao đổi các bước tiếp theo theo quy định của đơn vị tuyển dụng.</p>
<p>Trân trọng,<br/><strong>Khối Nhân sự FPT Education</strong></p>
HTML;

        OfferLetterTemplate::query()->updateOrCreate(
            ['slug' => 'official-employee'],
            [
                'name' => 'Thư mời nhận việc',
                'body_html' => $official,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        OfferLetterTemplate::query()->updateOrCreate(
            ['slug' => 'probation'],
            [
                'name' => 'Thư mời thử việc',
                'body_html' => $probation,
                'is_active' => true,
                'sort_order' => 2,
            ],
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\OfferLetterTemplate;
use Illuminate\Database\Seeder;

class OfferLetterTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $official = <<<'HTML'
<p>Kính gửi <strong>{{candidate_name}}</strong>,</p>
<p>Công ty <strong>{{app_name}}</strong> trân trọng thông báo: bạn đã được tuyển chọn cho vị trí <strong>{{job_title}}</strong>
tại chi nhánh <strong>{{branch_name}}</strong> (hoặc theo phân công của công ty).</p>
<p>Điều kiện chính:</p>
<ul>
<li>Mức lương đề nghị: <strong>{{salary_offered}}</strong></li>
<li>Ngày bắt đầu dự kiến: <strong>{{start_date}}</strong></li>
<li>Thời gian thử việc: <strong>{{probation_months}} tháng</strong></li>
</ul>
<p>Vui lòng xác nhận nhận việc và hoàn tất các thủ tục theo hướng dẫn của bộ phận nhân sự.</p>
<p>Trân trọng,<br/>Bộ phận Tuyển dụng — {{app_name}}</p>
HTML;

        $probation = <<<'HTML'
<p>Kính gửi <strong>{{candidate_name}}</strong>,</p>
<p>Chúng tôi xin mời bạn làm việc với vai trò <strong>{{job_title}}</strong> theo hình thức <strong>hợp đồng thử việc</strong>.</p>
<p>Thông tin nhanh:</p>
<ul>
<li>Lương thử việc: <strong>{{salary_offered}}</strong></li>
<li>Bắt đầu từ: <strong>{{start_date}}</strong></li>
<li>Thời hạn thử việc: <strong>{{probation_months}} tháng</strong></li>
</ul>
<p>Sau thời gian thử việc, hai bên sẽ đánh giá và ký hợp đồng chính thức nếu đạt yêu cầu.</p>
<p>Trân trọng,<br/>{{app_name}}</p>
HTML;

        OfferLetterTemplate::query()->updateOrCreate(
            ['slug' => 'official-employee'],
            [
                'name' => 'Offer nhân viên chính thức',
                'body_html' => $official,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        OfferLetterTemplate::query()->updateOrCreate(
            ['slug' => 'probation'],
            [
                'name' => 'Offer thử việc',
                'body_html' => $probation,
                'is_active' => true,
                'sort_order' => 2,
            ],
        );
    }
}

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
<p><strong>{{app_name}}</strong> trân trọng gửi đến bạn đề nghị tuyển dụng cho vị trí <strong>{{job_title}}</strong>
tại <strong>{{branch_name}}</strong>.</p>
<p>Dựa trên kết quả trao đổi và đánh giá trong quá trình tuyển dụng, chúng tôi tin rằng kinh nghiệm và định hướng nghề nghiệp của bạn phù hợp với nhu cầu của vị trí này.</p>
<p><strong>Thông tin đề nghị tuyển dụng:</strong></p>
<ul>
<li>Mã đề nghị: <strong>#{{offer_id}}</strong></li>
<li>Ngày phát hành: <strong>{{issued_date}}</strong></li>
<li>Mức lương đề nghị: <strong>{{salary_offered}}</strong></li>
<li>Ngày bắt đầu dự kiến: <strong>{{start_date}}</strong></li>
<li>Thời gian thử việc: <strong>{{probation_months}} tháng</strong></li>
<li>Hạn phản hồi: <strong>{{expiration_date}}</strong></li>
</ul>
<p>Đề nghị này có hiệu lực đến hạn phản hồi nêu trên. Sau khi bạn xác nhận đồng ý, bộ phận tuyển dụng sẽ liên hệ để hướng dẫn các thủ tục tiếp theo.</p>
<p><em>Lưu ý: Văn bản này là đề nghị tuyển dụng và không thay thế hợp đồng lao động chính thức.</em></p>
<p>Trân trọng,<br/>Bộ phận Tuyển dụng - {{app_name}}</p>
HTML;

        $probation = <<<'HTML'
<p>Kính gửi <strong>{{candidate_name}}</strong>,</p>
<p><strong>{{app_name}}</strong> trân trọng gửi đến bạn đề nghị tham gia giai đoạn thử việc cho vị trí <strong>{{job_title}}</strong>
tại <strong>{{branch_name}}</strong>.</p>
<p><strong>Thông tin đề nghị thử việc:</strong></p>
<ul>
<li>Mã đề nghị: <strong>#{{offer_id}}</strong></li>
<li>Ngày phát hành: <strong>{{issued_date}}</strong></li>
<li>Mức lương đề nghị: <strong>{{salary_offered}}</strong></li>
<li>Ngày bắt đầu dự kiến: <strong>{{start_date}}</strong></li>
<li>Thời gian thử việc: <strong>{{probation_months}} tháng</strong></li>
<li>Hạn phản hồi: <strong>{{expiration_date}}</strong></li>
</ul>
<p>Sau thời gian thử việc, hai bên sẽ đánh giá kết quả làm việc và mức độ phù hợp để thống nhất các bước tiếp theo.</p>
<p><em>Lưu ý: Văn bản này là đề nghị thử việc và không thay thế hợp đồng lao động chính thức.</em></p>
<p>Trân trọng,<br/>Bộ phận Tuyển dụng - {{app_name}}</p>
HTML;

        OfferLetterTemplate::query()->updateOrCreate(
            ['slug' => 'official-employee'],
            [
                'name' => 'Đề nghị tuyển dụng chính thức',
                'body_html' => $official,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        OfferLetterTemplate::query()->updateOrCreate(
            ['slug' => 'probation'],
            [
                'name' => 'Đề nghị thử việc',
                'body_html' => $probation,
                'is_active' => true,
                'sort_order' => 2,
            ],
        );
    }
}

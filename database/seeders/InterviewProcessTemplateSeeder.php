<?php

namespace Database\Seeders;

use App\Models\InterviewProcessTemplate;
use App\Models\ScorecardTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

class InterviewProcessTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = User::query()
            ->whereIn('role', ['admin', 'director', 'hr'])
            ->value('id')
            ?? User::query()->value('id');

        $scorecards = ScorecardTemplate::query()
            ->whereIn('name', [
                'Mẫu đánh giá chung',
                'Giảng viên / Trợ giảng FPT Education',
                'Tuyển sinh / Tư vấn học viên FPT Education',
                'Khối vận hành / Chuyên môn FPT Education',
            ])
            ->pluck('id', 'name');

        $templates = [
            [
                'code' => 'fpt-education-lecturer',
                'name' => 'Giảng viên / Trợ giảng FPT Education',
                'description' => 'Đánh giá chuyên môn, năng lực giảng dạy và mức độ phù hợp với đơn vị đào tạo.',
                'is_default' => true,
                'rounds' => [
                    [
                        'name' => 'Chuyên môn và năng lực giảng dạy',
                        'candidate_label' => 'Phỏng vấn chuyên môn và giảng thử',
                        'objective' => 'Làm rõ kiến thức chuyên môn, khả năng truyền đạt và xử lý tình huống học tập.',
                        'scorecard' => 'Giảng viên / Trợ giảng FPT Education',
                        'evaluator_roles' => ['pm', 'hr'],
                    ],
                    [
                        'name' => 'Phù hợp đơn vị và thống nhất tuyển dụng',
                        'candidate_label' => 'Trao đổi với đơn vị tuyển dụng',
                        'objective' => 'Xác nhận mức độ phù hợp với môi trường giáo dục, định hướng đơn vị và điều kiện tiếp nhận.',
                        'scorecard' => 'Mẫu đánh giá chung',
                        'evaluator_roles' => ['director', 'hr'],
                    ],
                ],
            ],
            [
                'code' => 'fpt-education-operations',
                'name' => 'Nhân sự chuyên môn / Vận hành FPT Education',
                'description' => 'Đánh giá năng lực theo vị trí và khả năng phối hợp trong môi trường giáo dục.',
                'is_default' => false,
                'rounds' => [
                    [
                        'name' => 'Năng lực chuyên môn',
                        'candidate_label' => 'Phỏng vấn chuyên môn',
                        'objective' => 'Đánh giá kinh nghiệm, năng lực xử lý công việc và khả năng phối hợp theo yêu cầu vị trí.',
                        'scorecard' => 'Khối vận hành / Chuyên môn FPT Education',
                        'evaluator_roles' => ['pm', 'hr'],
                    ],
                    [
                        'name' => 'Phù hợp đơn vị',
                        'candidate_label' => 'Trao đổi với đơn vị tuyển dụng',
                        'objective' => 'Thống nhất về mức độ phù hợp, định hướng phát triển và điều kiện tiếp nhận tại đơn vị.',
                        'scorecard' => 'Mẫu đánh giá chung',
                        'evaluator_roles' => ['director', 'hr'],
                    ],
                ],
            ],
            [
                'code' => 'fpt-education-admissions',
                'name' => 'Tuyển sinh / Tư vấn học viên FPT Education',
                'description' => 'Đánh giá năng lực tư vấn, xử lý tình huống và khả năng làm việc theo mục tiêu tuyển sinh.',
                'is_default' => false,
                'rounds' => [
                    [
                        'name' => 'Năng lực tư vấn và xử lý tình huống',
                        'candidate_label' => 'Phỏng vấn nghiệp vụ và tình huống',
                        'objective' => 'Đánh giá giao tiếp, khai thác nhu cầu, tư vấn lộ trình và xử lý tình huống thực tế.',
                        'scorecard' => 'Tuyển sinh / Tư vấn học viên FPT Education',
                        'evaluator_roles' => ['pm', 'hr'],
                    ],
                    [
                        'name' => 'Phù hợp mục tiêu và đơn vị',
                        'candidate_label' => 'Trao đổi với đơn vị tuyển dụng',
                        'objective' => 'Xác nhận khả năng phối hợp, tinh thần trách nhiệm và mức độ phù hợp với mục tiêu tuyển sinh.',
                        'scorecard' => 'Mẫu đánh giá chung',
                        'evaluator_roles' => ['director', 'hr'],
                    ],
                ],
            ],
            [
                'code' => 'fpt-education-management',
                'name' => 'Cán bộ quản lý FPT Education',
                'description' => 'Quy trình ba vòng dành cho vị trí quản lý hoặc vai trò có phạm vi ảnh hưởng cấp đơn vị.',
                'is_default' => false,
                'rounds' => [
                    [
                        'name' => 'Năng lực chuyên môn',
                        'candidate_label' => 'Phỏng vấn chuyên môn',
                        'objective' => 'Đánh giá nền tảng chuyên môn, kinh nghiệm vận hành và kết quả công việc nổi bật.',
                        'scorecard' => 'Khối vận hành / Chuyên môn FPT Education',
                        'evaluator_roles' => ['pm', 'hr'],
                    ],
                    [
                        'name' => 'Năng lực quản lý',
                        'candidate_label' => 'Phỏng vấn năng lực quản lý',
                        'objective' => 'Đánh giá tư duy tổ chức, quản trị đội ngũ, phối hợp liên đơn vị và xử lý tình huống quản lý.',
                        'scorecard' => 'Mẫu đánh giá chung',
                        'evaluator_roles' => ['director', 'pm'],
                    ],
                    [
                        'name' => 'Thống nhất tuyển dụng',
                        'candidate_label' => 'Trao đổi cuối với đơn vị tuyển dụng',
                        'objective' => 'Xác nhận mức độ phù hợp chiến lược, phạm vi trách nhiệm và điều kiện tiếp nhận cuối cùng.',
                        'scorecard' => 'Mẫu đánh giá chung',
                        'evaluator_roles' => ['director', 'hr'],
                    ],
                ],
            ],
        ];

        DB::transaction(function () use ($templates, $scorecards, $creatorId): void {
            foreach ($templates as $definition) {
                $template = InterviewProcessTemplate::query()->updateOrCreate(
                    ['code' => $definition['code']],
                    [
                        'name' => $definition['name'],
                        'description' => $definition['description'],
                        'is_default' => $definition['is_default'],
                        'is_active' => true,
                        'created_by' => $creatorId,
                    ]
                );

                $roundNumbers = [];

                foreach ($definition['rounds'] as $index => $round) {
                    $roundNumber = $index + 1;
                    $roundNumbers[] = $roundNumber;
                    $scorecardId = $scorecards->get($round['scorecard']);

                    if (! $scorecardId) {
                        throw new LogicException("Không tìm thấy mẫu đánh giá [{$round['scorecard']}].");
                    }

                    $template->rounds()->updateOrCreate(
                        ['round_number' => $roundNumber],
                        [
                            'name' => $round['name'],
                            'candidate_label' => $round['candidate_label'],
                            'objective' => $round['objective'],
                            'scorecard_template_id' => $scorecardId,
                            'evaluator_roles' => $round['evaluator_roles'],
                        ]
                    );
                }

                $template->rounds()
                    ->whereNotIn('round_number', $roundNumbers)
                    ->delete();
            }
        });
    }
}

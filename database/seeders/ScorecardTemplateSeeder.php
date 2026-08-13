<?php

namespace Database\Seeders;

use App\Models\ScorecardTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class ScorecardTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::query()
            ->whereIn('role', ['admin', 'hr', 'director'])
            ->first()
            ?? User::query()->first();

        if (! $creator) {
            return;
        }

        ScorecardTemplate::query()->updateOrCreate(
            ['name' => 'Mẫu đánh giá chung'],
            [
                'created_by' => $creator->id,
                'is_default' => true,
                'criteria' => [
                    ['name' => 'Kinh nghiệm phù hợp vị trí', 'score' => null, 'note' => null],
                    ['name' => 'Kỹ năng chuyên môn', 'score' => null, 'note' => null],
                    ['name' => 'Tư duy giải quyết vấn đề', 'score' => null, 'note' => null],
                    ['name' => 'Kỹ năng giao tiếp', 'score' => null, 'note' => null],
                    ['name' => 'Thái độ và mức độ phù hợp văn hóa', 'score' => null, 'note' => null],
                ],
            ]
        );

        ScorecardTemplate::query()->updateOrCreate(
            ['name' => 'Mẫu đánh giá kỹ thuật / IT'],
            [
                'created_by' => $creator->id,
                'is_default' => false,
                'criteria' => [
                    ['name' => 'Kiến thức nền tảng', 'score' => null, 'note' => null],
                    ['name' => 'Khả năng giải quyết vấn đề', 'score' => null, 'note' => null],
                    ['name' => 'Kinh nghiệm dự án', 'score' => null, 'note' => null],
                    ['name' => 'Tư duy hệ thống', 'score' => null, 'note' => null],
                    ['name' => 'Khả năng học hỏi công nghệ mới', 'score' => null, 'note' => null],
                ],
            ]
        );

        ScorecardTemplate::query()->updateOrCreate(
            ['name' => 'Giảng viên / Trợ giảng FPT Education'],
            [
                'created_by' => $creator->id,
                'is_default' => false,
                'criteria' => [
                    ['name' => 'Nền tảng chuyên môn theo môn học', 'score' => null, 'note' => null],
                    ['name' => 'Năng lực truyền đạt và tương tác với người học', 'score' => null, 'note' => null],
                    ['name' => 'Thiết kế bài giảng, học liệu hoặc hoạt động thực hành', 'score' => null, 'note' => null],
                    ['name' => 'Xử lý tình huống lớp học và đồng hành người học', 'score' => null, 'note' => null],
                    ['name' => 'Phối hợp chuyên môn và phù hợp môi trường giáo dục', 'score' => null, 'note' => null],
                ],
            ]
        );

        ScorecardTemplate::query()->updateOrCreate(
            ['name' => 'Tuyển sinh / Tư vấn học viên FPT Education'],
            [
                'created_by' => $creator->id,
                'is_default' => false,
                'criteria' => [
                    ['name' => 'Giao tiếp, lắng nghe và xây dựng tin cậy', 'score' => null, 'note' => null],
                    ['name' => 'Khai thác nhu cầu và tư vấn lộ trình phù hợp', 'score' => null, 'note' => null],
                    ['name' => 'Hiểu biết về chương trình đào tạo và dịch vụ người học', 'score' => null, 'note' => null],
                    ['name' => 'Xử lý tình huống với phụ huynh, người học hoặc khách hàng', 'score' => null, 'note' => null],
                    ['name' => 'Tính chủ động, kỷ luật và phối hợp theo mục tiêu tuyển sinh', 'score' => null, 'note' => null],
                ],
            ]
        );

        ScorecardTemplate::query()->updateOrCreate(
            ['name' => 'Khối vận hành / Chuyên môn FPT Education'],
            [
                'created_by' => $creator->id,
                'is_default' => false,
                'criteria' => [
                    ['name' => 'Năng lực chuyên môn theo vị trí', 'score' => null, 'note' => null],
                    ['name' => 'Tư duy giải quyết vấn đề và cải tiến quy trình', 'score' => null, 'note' => null],
                    ['name' => 'Khả năng phối hợp liên phòng ban', 'score' => null, 'note' => null],
                    ['name' => 'Tinh thần chủ động, trách nhiệm và kỷ luật', 'score' => null, 'note' => null],
                    ['name' => 'Phù hợp văn hóa và môi trường giáo dục', 'score' => null, 'note' => null],
                ],
            ]
        );
    }
}

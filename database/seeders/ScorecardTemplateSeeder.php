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
    }
}

<?php

namespace App\Filament\Resources\RecruitmentJobs\Schemas;

use App\Models\RecruitmentJob;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecruitmentJobInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tình trạng tuyển dụng')
                ->description('Thông tin cần nắm nhanh để quyết định thao tác tiếp theo.')
                ->icon('heroicon-o-chart-bar-square')
                ->compact()
                ->columns([
                    'default' => 1,
                    'sm' => 2,
                    'xl' => 4,
                ])
                ->schema([
                    TextEntry::make('status')
                        ->label('Tình trạng tin')
                        ->badge(),
                    TextEntry::make('deadline_context')
                        ->label('Hạn nhận hồ sơ')
                        ->state(fn (RecruitmentJob $record): string => static::deadlineContext($record))
                        ->color(fn (RecruitmentJob $record): string => static::deadlineColor($record))
                        ->weight('semibold'),
                    TextEntry::make('applications_count')
                        ->label('Hồ sơ tiếp nhận')
                        ->state(fn (RecruitmentJob $record): string => $record->applications_count.' hồ sơ')
                        ->badge()
                        ->color(fn (RecruitmentJob $record): string => $record->applications_count > 0 ? 'info' : 'gray'),
                    TextEntry::make('positions_count')
                        ->label('Chỉ tiêu tuyển')
                        ->suffix(' vị trí'),
                ])
                ->columnSpanFull(),
            Grid::make(['default' => 1, 'xl' => 12])
                ->schema([
                    Section::make('Nội dung công khai')
                        ->description('Thông tin ứng viên nhìn thấy khi mở tin tuyển dụng.')
                        ->icon('heroicon-o-document-text')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('description')
                                ->hiddenLabel()
                                ->html()
                                ->prose()
                                ->placeholder('Chưa có nội dung mô tả.')
                                ->columnSpanFull(),
                            TextEntry::make('categories.name')
                                ->label('Danh mục tuyển dụng')
                                ->badge()
                                ->color('info')
                                ->placeholder('Chưa gắn danh mục'),
                            TextEntry::make('skills.name')
                                ->label('Kỹ năng yêu cầu')
                                ->badge()
                                ->color('gray')
                                ->placeholder('Chưa gắn kỹ năng'),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 8]),
                    Section::make('Thông tin quản lý')
                        ->description('Phạm vi tuyển dụng và dấu vết cập nhật nội bộ.')
                        ->icon('heroicon-o-building-office-2')
                        ->compact()
                        ->columns(1)
                        ->schema([
                            TextEntry::make('formatted_salary')
                                ->label('Mức lương'),
                            TextEntry::make('branch.name')
                                ->label('Chi nhánh')
                                ->placeholder('Chưa xác định'),
                            TextEntry::make('department.name')
                                ->label('Phòng ban')
                                ->placeholder('Chưa xác định'),
                            TextEntry::make('workplace.name')
                                ->label('Nơi làm việc')
                                ->placeholder('Chưa xác định'),
                            TextEntry::make('process_lock')
                                ->label('Thiết lập phỏng vấn')
                                ->state(fn (RecruitmentJob $record): string => $record->applications_count > 0
                                    ? 'Đã cố định khi nhận hồ sơ'
                                    : 'Có thể thiết lập trước khi nhận hồ sơ')
                                ->badge()
                                ->color(fn (RecruitmentJob $record): string => $record->applications_count > 0 ? 'success' : 'warning'),
                            TextEntry::make('creator.name')
                                ->label('Người tạo')
                                ->placeholder('-'),
                            TextEntry::make('created_at')
                                ->label('Ngày tạo')
                                ->dateTime('H:i, d/m/Y')
                                ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')),
                            TextEntry::make('updated_at')
                                ->label('Cập nhật gần nhất')
                                ->dateTime('H:i, d/m/Y')
                                ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 4]),
                ])
                ->columnSpanFull(),
            Section::make('Quy trình phỏng vấn')
                ->description(fn (RecruitmentJob $record): string => static::processDescription($record))
                ->icon('heroicon-o-arrows-right-left')
                ->schema([
                    TextEntry::make('process_name')
                        ->label('Quy trình áp dụng')
                        ->state(fn (RecruitmentJob $record): string => (string) ($record->resolvedInterviewProcess()['name'] ?? 'Quy trình phỏng vấn tiêu chuẩn'))
                        ->weight('semibold'),
                    RepeatableEntry::make('process_rounds')
                        ->label('Các vòng đánh giá')
                        ->state(fn (RecruitmentJob $record): array => array_values((array) ($record->resolvedInterviewProcess()['rounds'] ?? [])))
                        ->contained(false)
                        ->grid([
                            'default' => 1,
                            'lg' => 2,
                        ])
                        ->columns([
                            'default' => 1,
                            'md' => 2,
                        ])
                        ->schema([
                            TextEntry::make('round_number')
                                ->label('Vòng')
                                ->formatStateUsing(fn (mixed $state): string => 'Vòng '.max(1, (int) $state))
                                ->badge()
                                ->color('warning'),
                            TextEntry::make('name')
                                ->label('Tên vòng')
                                ->weight('semibold'),
                            TextEntry::make('evaluator_roles')
                                ->label('Vai trò đánh giá')
                                ->formatStateUsing(fn (mixed $state): string => static::roleLabels((array) $state))
                                ->placeholder('Chưa cấu hình'),
                            TextEntry::make('scorecard_template.name')
                                ->label('Mẫu đánh giá')
                                ->placeholder('Mẫu mặc định'),
                            TextEntry::make('objective')
                                ->label('Mục tiêu đánh giá')
                                ->placeholder('Chưa có mô tả')
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    protected static function deadlineContext(RecruitmentJob $record): string
    {
        if (! $record->deadline) {
            return 'Không giới hạn';
        }

        $days = (int) now()->startOfDay()->diffInDays($record->deadline->copy()->startOfDay(), false);
        $date = $record->deadline->format('d/m/Y');

        return match (true) {
            $days < 0 => $date.' · quá hạn '.abs($days).' ngày',
            $days === 0 => $date.' · hết hạn hôm nay',
            $days === 1 => $date.' · còn 1 ngày',
            default => $date.' · còn '.$days.' ngày',
        };
    }

    protected static function deadlineColor(RecruitmentJob $record): string
    {
        if (! $record->deadline) {
            return 'gray';
        }

        $days = (int) now()->startOfDay()->diffInDays($record->deadline->copy()->startOfDay(), false);

        return match (true) {
            $days < 0 => 'danger',
            $days <= 3 => 'warning',
            default => 'gray',
        };
    }

    protected static function processDescription(RecruitmentJob $record): string
    {
        $process = $record->resolvedInterviewProcess();
        $roundCount = count((array) ($process['rounds'] ?? []));

        return $record->applications_count > 0
            ? "Quy trình {$roundCount} vòng đã được cố định từ khi tin nhận hồ sơ đầu tiên."
            : "Quy trình dự kiến gồm {$roundCount} vòng và có thể thiết lập trước khi nhận hồ sơ.";
    }

    protected static function roleLabels(array $roles): string
    {
        return collect($roles)
            ->map(fn (mixed $role): string => match ((string) $role) {
                'hr' => 'Nhân sự tuyển dụng',
                'pm' => 'Quản lý chuyên môn',
                'director' => 'Giám đốc chi nhánh',
                'admin', 'super_admin' => 'Quản trị hệ thống',
                default => strtoupper((string) $role),
            })
            ->filter()
            ->join(', ');
    }
}

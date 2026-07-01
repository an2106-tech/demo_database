<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StatusApplicationEnum: string implements HasIcon, HasColor, HasLabel
{
    case CV_REVIEWING = 'cv_reviewing';
    case SCREENING = 'screening';
    case INTERVIEW_SCHEDULED = 'interview_scheduled';
    case INTERVIEWING = 'interview';
    case OFFERED = 'offer';
    case HIRED = 'hired';
    case REJECTED = 'rejected';

    // Backward-compatible aliases for older workflow code paths.
    public const NEW = self::CV_REVIEWING;
    public const INTERVIEW = self::INTERVIEWING;
    public const OFFER = self::OFFERED;

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::CV_REVIEWING => 'heroicon-o-eye',
            self::SCREENING => 'heroicon-o-phone',
            self::INTERVIEW_SCHEDULED => 'heroicon-o-calendar',
            self::INTERVIEWING => 'heroicon-o-user-group',
            self::OFFERED => 'heroicon-o-hand-raised',
            self::HIRED => 'heroicon-o-check-badge',
            self::REJECTED => 'heroicon-o-x-circle',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CV_REVIEWING => 'gray',
            self::SCREENING => 'info',
            self::INTERVIEW_SCHEDULED => 'warning',
            self::INTERVIEWING => 'primary',
            self::OFFERED => 'success',
            self::HIRED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::CV_REVIEWING => 'Chờ sàng lọc CV',
            self::SCREENING => 'Sơ tuyển',
            self::INTERVIEW_SCHEDULED => 'Đã lên lịch phỏng vấn',
            self::INTERVIEWING => 'Chờ đánh giá phỏng vấn',
            self::OFFERED => 'Đề nghị tuyển dụng',
            self::HIRED => 'Đã tuyển',
            self::REJECTED => 'Từ chối',
        };
    }

    public function getPipelineStageKey(): string
    {
        return match ($this) {
            self::CV_REVIEWING => 'applied',
            self::SCREENING => 'screening',
            self::INTERVIEW_SCHEDULED, self::INTERVIEWING => 'interview',
            self::OFFERED => 'offer',
            self::HIRED => 'hired',
            self::REJECTED => 'rejected',
        };
    }

    public function getPipelineStageLabel(): string
    {
        return match ($this->getPipelineStageKey()) {
            'applied' => 'Ứng tuyển',
            'screening' => 'Sơ tuyển',
            'interview' => 'Phỏng vấn',
            'offer' => 'Đề nghị tuyển dụng',
            'hired' => 'Đã tuyển',
            'rejected' => 'Từ chối',
        };
    }

    public function getPipelineStageIcon(): string
    {
        return match ($this->getPipelineStageKey()) {
            'applied' => 'heroicon-o-inbox',
            'screening' => 'heroicon-o-document-magnifying-glass',
            'interview' => 'heroicon-o-user-group',
            'offer' => 'heroicon-o-envelope-open',
            'hired' => 'heroicon-o-check-badge',
            'rejected' => 'heroicon-o-x-circle',
        };
    }

    public function getPipelineStageColor(): string
    {
        return match ($this->getPipelineStageKey()) {
            'applied' => 'gray',
            'screening' => 'info',
            'interview' => 'warning',
            'offer' => 'success',
            'hired' => 'success',
            'rejected' => 'danger',
        };
    }

    /**
     * @return array<string, array{label: string, icon: string, color: string, statuses: array<int, self>}>
     */
    public static function pipelineStages(): array
    {
        return [
            'applied' => [
                'label' => 'Ứng tuyển',
                'icon' => 'heroicon-o-inbox',
                'color' => 'gray',
                'statuses' => [self::CV_REVIEWING],
            ],
            'screening' => [
                'label' => 'Sơ tuyển',
                'icon' => 'heroicon-o-document-magnifying-glass',
                'color' => 'info',
                'statuses' => [self::SCREENING],
            ],
            'interview' => [
                'label' => 'Phỏng vấn',
                'icon' => 'heroicon-o-user-group',
                'color' => 'warning',
                'statuses' => [self::INTERVIEW_SCHEDULED, self::INTERVIEWING],
            ],
            'offer' => [
                'label' => 'Đề nghị tuyển dụng',
                'icon' => 'heroicon-o-envelope-open',
                'color' => 'success',
                'statuses' => [self::OFFERED],
            ],
            'hired' => [
                'label' => 'Đã tuyển',
                'icon' => 'heroicon-o-check-badge',
                'color' => 'success',
                'statuses' => [self::HIRED],
            ],
            'rejected' => [
                'label' => 'Từ chối',
                'icon' => 'heroicon-o-x-circle',
                'color' => 'danger',
                'statuses' => [self::REJECTED],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function pipelineStageOptions(): array
    {
        return collect(self::pipelineStages())
            ->mapWithKeys(fn (array $stage, string $key): array => [$key => $stage['label']])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function statusValuesForPipelineStage(string $stageKey): array
    {
        $stage = self::pipelineStages()[$stageKey] ?? null;

        if (! $stage) {
            return [];
        }

        return array_map(fn (self $status): string => $status->value, $stage['statuses']);
    }
}
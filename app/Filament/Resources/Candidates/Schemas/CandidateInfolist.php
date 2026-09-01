<?php

namespace App\Filament\Resources\Candidates\Schemas;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\Candidate;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class CandidateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->compact()
                ->columnSpanFull()
                ->schema([
                    View::make('filament.resources.candidates.infolists.candidate-summary')
                        ->viewData(fn (Candidate $record): array => static::summaryData($record)),
                ]),
            Grid::make(['default' => 1, 'xl' => 12])
                ->schema([
                    Section::make('Hồ sơ hiện tại')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            View::make('filament.resources.candidates.infolists.candidate-profile')
                                ->viewData(fn (Candidate $record): array => static::profileData($record)),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 7]),
                    Section::make('Thông tin quản lý')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            View::make('filament.resources.candidates.infolists.candidate-management')
                                ->viewData(fn (Candidate $record): array => static::managementData($record)),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 5]),
                ])
                ->columnSpanFull(),
            Section::make('Lịch sử ứng tuyển')
                ->description('Các hồ sơ tuyển dụng gần nhất trong phạm vi được phân quyền.')
                ->icon('heroicon-o-briefcase')
                ->schema([
                    View::make('filament.resources.candidates.infolists.candidate-applications')
                        ->viewData(fn (Candidate $record): array => static::applicationsData($record)),
                ])
                ->columnSpanFull(),
        ]);
    }

    /** @return array<string, mixed> */
    protected static function summaryData(Candidate $record): array
    {
        $status = static::statusEnum($record->latestVisibleApplication);

        return [
            'candidateName' => $record->name,
            'profileTitle' => $record->resume?->profile_title ?: 'Chưa cập nhật chức danh',
            'experience' => is_numeric($record->experience_years) ? $record->experience_years.' năm kinh nghiệm' : 'Chưa cập nhật kinh nghiệm',
            'applicationCount' => (int) ($record->visible_applications_count ?? 0),
            'latestStage' => $status?->getPipelineStageLabel() ?: 'Chưa có ứng tuyển',
            'latestStageColor' => $status?->getPipelineStageColor() ?: 'gray',
            'isRestricted' => (bool) $record->blacklist,
        ];
    }

    /** @return array<string, mixed> */
    protected static function profileData(Candidate $record): array
    {
        $resume = $record->resume;

        return [
            'email' => $record->email ?: '-',
            'phone' => $record->phone ?: '-',
            'careerObjective' => trim((string) $resume?->career_objective) ?: 'Chưa cập nhật',
            'skills' => collect((array) $resume?->skills)
                ->map(fn (mixed $skill): ?string => is_array($skill) ? ($skill['name'] ?? null) : (is_string($skill) ? $skill : null))
                ->filter()
                ->take(12)
                ->values()
                ->all(),
            'experienceCount' => collect((array) $resume?->experiences)->filter()->count(),
            'educationCount' => collect((array) $resume?->educations)->filter()->count(),
            'currentCvName' => $record->cv_file ? basename($record->cv_file) : null,
            'currentCvUrl' => $record->currentCvUrl(),
        ];
    }

    /** @return array<string, mixed> */
    protected static function managementData(Candidate $record): array
    {
        return [
            'accountStatus' => $record->user ? 'Đã liên kết tài khoản' : 'Chưa liên kết tài khoản',
            'accountEmail' => $record->user?->email,
            'isRestricted' => (bool) $record->blacklist,
            'restrictionReason' => $record->blacklist_reason,
            'restrictedBy' => $record->blacklistedBy?->name,
            'restrictedAt' => static::formatDateTime($record->blacklisted_at),
            'createdAt' => static::formatDateTime($record->created_at),
            'updatedAt' => static::formatDateTime($record->updated_at),
        ];
    }

    /** @return array<string, mixed> */
    protected static function applicationsData(Candidate $record): array
    {
        $applications = $record->relationLoaded('visibleApplications')
            ? $record->getRelation('visibleApplications')
            : collect([$record->latestVisibleApplication])->filter();

        return [
            'applications' => $applications->map(function (Application $application): array {
                $status = static::statusEnum($application);

                return [
                    'code' => '#'.str_pad((string) $application->id, 4, '0', STR_PAD_LEFT),
                    'jobTitle' => $application->job?->title ?: 'Vị trí không còn tồn tại',
                    'branchName' => $application->job?->branch?->name ?: 'Chưa xác định chi nhánh',
                    'stage' => $status?->getPipelineStageLabel() ?: 'Chưa xác định',
                    'stageColor' => $status?->getPipelineStageColor() ?: 'gray',
                    'status' => $status?->getLabel() ?: 'Chưa xác định',
                    'appliedAt' => static::formatDateTime($application->applied_at),
                    'cvName' => $application->submittedCvName(),
                    'cvUrl' => $application->submittedCvUrl(),
                    'detailUrl' => ApplicationResource::getUrl('view', ['record' => $application]),
                ];
            })->values()->all(),
            'total' => (int) ($record->visible_applications_count ?? $applications->count()),
        ];
    }

    protected static function statusEnum(?Application $application): ?StatusApplicationEnum
    {
        if (! $application) {
            return null;
        }

        return $application->status instanceof StatusApplicationEnum
            ? $application->status
            : StatusApplicationEnum::tryFrom((string) $application->status);
    }

    protected static function formatDateTime(mixed $value): string
    {
        if (! $value) {
            return '-';
        }

        return $value->copy()
            ->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
            ->format('H:i, d/m/Y');
    }
}

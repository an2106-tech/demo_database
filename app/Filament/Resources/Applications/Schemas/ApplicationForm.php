<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 12])
                    ->schema([
                        Section::make('Thông tin ứng tuyển')
                            ->compact()
                            ->schema([
                                Grid::make(['default' => 1, 'md' => 1])
                                    ->schema([
                                        Select::make('job_id')
                                            ->label('Công việc')
                                            ->options(function (): array {
                                                $query = RecruitmentJob::query();

                                                if (\Illuminate\Support\Facades\Auth::user()?->branchScopeId()) {
                                                    $query->where('branch_id', \Illuminate\Support\Facades\Auth::user()?->branchScopeId());
                                                }

                                                return $query
                                                    ->orderByDesc('id')
                                                    ->limit(500)
                                                    ->get()
                                                    ->mapWithKeys(fn (RecruitmentJob $job) => [
                                                        $job->id => "#{$job->id} - {$job->title}",
                                                    ])
                                                    ->all();
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->rules(['required', 'integer', 'exists:recruitment_jobs,id']),

                                        Select::make('candidate_id')
                                            ->label('Ứng viên')
                                            ->options(fn () => Candidate::query()
                                                ->orderByDesc('id')
                                                ->limit(500)
                                                ->get()
                                                ->mapWithKeys(fn (Candidate $candidate) => [
                                                    $candidate->id => "#{$candidate->id} - {$candidate->name}",
                                                ])
                                                ->all())
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                                            ->dehydrated()
                                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                                ? 'Ứng viên chỉ được chọn khi tạo mới hồ sơ ứng tuyển.'
                                                : null)
                                            ->afterStateUpdated(function ($state, callable $set): void {
                                                if (blank($state)) {
                                                    $set('cv_path', null);

                                                    return;
                                                }

                                                $cvFile = Candidate::query()
                                                    ->whereKey($state)
                                                    ->value('cv_file');

                                                $set('cv_path', $cvFile ?: null);
                                            })
                                            ->required()
                                            ->rules(['required', 'integer', 'exists:candidates,id']),

                                        TextInput::make('candidate_cv_link')
                                            ->label('CV từ hồ sơ ứng viên')
                                            ->readOnly()
                                            ->dehydrated(false)
                                            ->placeholder('CV sẽ tự động lấy từ hồ sơ ứng viên khi lưu')
                                            ->formatStateUsing(function (callable $get): ?string {
                                                $candidateId = $get('candidate_id');

                                                if (empty($candidateId)) {
                                                    return null;
                                                }

                                                $cvFile = Candidate::query()
                                                    ->whereKey($candidateId)
                                                    ->value('cv_file');

                                                return $cvFile ? asset('storage/' . ltrim($cvFile, '/')) : 'Ứng viên chưa có CV';
                                            })
                                            ->columnSpanFull(),

                                        Select::make('source')
                                            ->label('Nguồn')
                                            ->options([
                                                'website' => 'Website',
                                                'facebook' => 'Facebook',
                                                'linkedin' => 'LinkedIn',
                                                'referral' => 'Giới thiệu',
                                                'other' => 'Khác',
                                            ])
                                            ->default('website')
                                            ->live()
                                            ->required(),

                                        Select::make('status')
                                            ->label('Trạng thái')
                                            ->options(fn (?Application $record): array => static::getAllowedStatusOptions($record))
                                            ->default(StatusApplicationEnum::NEW)
                                            ->live()
                                            ->required()
                                            ->enum(StatusApplicationEnum::class),

                                        DateTimePicker::make('applied_at')
                                            ->label('Ngày ứng tuyển')
                                            ->default(now())
                                            ->seconds(false)
                                            ->required(),

                                        Grid::make(['default' => 1, 'md' => 2])
                                            ->schema([
                                                TextInput::make('salary_expected_min')
                                                    ->label('Lương mong muốn từ')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->suffix('VNĐ')
                                                    ->formatStateUsing(function ($state, callable $get): ?string {
                                                        if ($state !== null) {
                                                            return (string) $state;
                                                        }

                                                        $salary = $get('salary_expected');

                                                        if (is_array($salary) && isset($salary['min'])) {
                                                            return (string) $salary['min'];
                                                        }

                                                        return null;
                                                    })
                                                    ->dehydrated(false),

                                                TextInput::make('salary_expected_max')
                                                    ->label('Lương mong muốn đến')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->suffix('VNĐ')
                                                    ->formatStateUsing(function ($state, callable $get): ?string {
                                                        if ($state !== null) {
                                                            return (string) $state;
                                                        }

                                                        $salary = $get('salary_expected');

                                                        if (is_array($salary) && isset($salary['max'])) {
                                                            return (string) $salary['max'];
                                                        }

                                                        return null;
                                                    })
                                                    ->dehydrated(false),
                                            ]),

                                        Hidden::make('salary_expected')
                                            ->dehydrateStateUsing(function ($state, callable $get): ?array {
                                                $min = $get('salary_expected_min');
                                                $max = $get('salary_expected_max');

                                                if (blank($min) && blank($max)) {
                                                    return null;
                                                }

                                                return [
                                                    'min' => filled($min) ? (float) $min : null,
                                                    'max' => filled($max) ? (float) $max : null,
                                                ];
                                            }),

                                        Textarea::make('rejected_reason')
                                            ->label('Lý do từ chối')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->visible(function (callable $get): bool {
                                                $status = $get('status');

                                                if ($status instanceof StatusApplicationEnum) {
                                                    return $status === StatusApplicationEnum::REJECTED;
                                                }

                                                return $status === StatusApplicationEnum::REJECTED->value;
                                            }),

                                        Select::make('referral_user_id')
                                            ->label('Người giới thiệu')
                                            ->options(fn () => User::query()
                                                ->orderByDesc('id')
                                                ->limit(500)
                                                ->get()
                                                ->mapWithKeys(fn (User $user) => [
                                                    $user->id => "#{$user->id} - {$user->name}" . ($user->email ? " ({$user->email})" : ''),
                                                ])
                                                ->all())
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->columnSpanFull()
                                            ->visible(fn (callable $get): bool => $get('source') === 'referral')
                                            ->rules(['nullable', 'integer', 'exists:users,id']),

                                        TextInput::make('utm_source')
                                            ->label('UTM Source')
                                            ->maxLength(255)
                                            ->nullable(),

                                        TextInput::make('utm_medium')
                                            ->label('UTM Medium')
                                            ->maxLength(255)
                                            ->nullable(),

                                        TextInput::make('utm_campaign')
                                            ->label('UTM Campaign')
                                            ->maxLength(255)
                                            ->nullable(),
                                    ]),
                            ])
                            ->columnSpan(['default' => 'full', 'lg' => 5]),

                        Section::make('CV ứng viên')
                            ->compact()
                            ->schema([
                                FileUpload::make('cv_path')
                                    ->label('File CV')
                                    ->disk('public')
                                    ->directory('cvs')
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    ])
                                    ->maxSize(10 * 1024)
                                    ->nullable(),

                                Section::make('Xem trước CV')
                                    ->columnSpanFull()
                                    ->extraAttributes([
                                        'style' => 'position: sticky; top: 1rem; align-self: start;',
                                    ])
                                    ->schema([
                                        ViewField::make('cv_path_preview')
                                            ->label('')
                                            ->view('filament.forms.components.cv-preview')
                                            ->dehydrated(false)
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(['default' => 'full', 'lg' => 7]),
                    ]),
            ])
            ->columns(1);
    }

    public static function getAllowedStatusOptions(?Application $record = null): array
    {
        $currentStatus = $record?->status instanceof StatusApplicationEnum
            ? $record->status
            : (is_string($record?->status) ? StatusApplicationEnum::tryFrom($record->status) : null);

        $currentIndex = static::getStatusIndex($currentStatus ?? StatusApplicationEnum::NEW);

        return collect(StatusApplicationEnum::cases())
            ->filter(fn (StatusApplicationEnum $status): bool => static::getStatusIndex($status) >= $currentIndex)
            ->mapWithKeys(fn (StatusApplicationEnum $status): array => [
                $status->value => (string) $status->getLabel(),
            ])
            ->all();
    }

    public static function canMoveStatusForwardOnly(
        StatusApplicationEnum|string|null $from,
        StatusApplicationEnum|string|null $to,
    ): bool {
        $fromEnum = $from instanceof StatusApplicationEnum ? $from : StatusApplicationEnum::tryFrom((string) $from);
        $toEnum = $to instanceof StatusApplicationEnum ? $to : StatusApplicationEnum::tryFrom((string) $to);

        if (! $toEnum) {
            return false;
        }

        if (! $fromEnum) {
            return true;
        }

        return static::getStatusIndex($toEnum) >= static::getStatusIndex($fromEnum);
    }

    protected static function getStatusIndex(StatusApplicationEnum $status): int
    {
        return match ($status) {
            StatusApplicationEnum::NEW => 10,
            StatusApplicationEnum::SCREENING => 20,
            StatusApplicationEnum::INTERVIEW => 30,
            StatusApplicationEnum::OFFER => 40,
            StatusApplicationEnum::HIRED => 50,
            StatusApplicationEnum::REJECTED => 60,
        };
    }
}
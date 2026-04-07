<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Outer 3-column grid: left form inputs (2/3) + right CV preview (1/3) ──
                Grid::make(['default' => 1, 'md'=>6, 'lg' => 12])
                    ->schema([
                        Section::make('Thông tin ứng tuyển')
                            ->columns(1)
                            ->schema([
                                Select::make('job_id')
                                    ->label('Vị trí tuyển dụng')
                                    ->options(fn () => RecruitmentJob::query()
                                        ->select(['id', 'title'])
                                        ->orderByDesc('id')
                                        ->limit(500)
                                        ->get()
                                        ->mapWithKeys(fn ($job) => [$job->id => "#{$job->id} — {$job->title}"])
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull()
                                    ->rules(['required', 'integer', 'exists:recruitment_jobs,id']),

                                Select::make('candidate_id')
                                    ->label('Ứng viên')
                                    ->options(fn () => DB::table('candidates')
                                        ->select(['id', 'name'])
                                        ->orderByDesc('id')
                                        ->limit(500)
                                        ->get()
                                        ->mapWithKeys(fn ($candidate) => [
                                            $candidate->id => "#{$candidate->id} — {$candidate->name}",
                                        ])
                                        ->all())
                                    ->reactive()
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull()
                                    ->rules(['required', 'integer', 'exists:candidates,id']),

                                TextInput::make('candidate_cv_link')
                                    ->label('Link CV')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->placeholder('Chọn ứng viên để xem link CV')
                                    ->formatStateUsing(function (callable $get): ?string {
                                        $candidateId = $get('candidate_id');

                                        if (empty($candidateId)) {
                                            return null;
                                        }

                                        $cvFile = Candidate::query()
                                            ->whereKey($candidateId)
                                            ->value('cv_file');

                                        return $cvFile ? asset('storage/' . $cvFile) : 'Ứng viên chưa có CV';
                                    })
                                    ->columnSpanFull(),

                                Select::make('source')
                                    ->label('Nguồn ứng tuyển')
                                    ->options([
                                        'website' => 'Website',
                                        'facebook' => 'Facebook',
                                        'linkedin' => 'LinkedIn',
                                        'referral' => 'Giới thiệu',
                                        'other' => 'Khác',
                                    ])
                                    ->default('website')
                                    ->reactive()
                                    ->required()
                                    ->rules([
                                        'required',
                                        Rule::in(['website', 'facebook', 'linkedin', 'referral', 'other']),
                                    ]),

                                DateTimePicker::make('applied_at')
                                    ->label('Ngày ứng tuyển')
                                    ->seconds(false)
                                    ->default(now())
                                    ->required(),

                                Select::make('status')
                                    ->label('Trạng thái')
                                    ->options([
                                        'new' => 'Mới',
                                        'screening' => 'Sàng lọc',
                                        'interview' => 'Phỏng vấn',
                                        'offer' => 'Offer',
                                        'hired' => 'Đã tuyển',
                                        'rejected' => 'Từ chối',
                                    ])
                                    ->default('new')
                                    ->reactive()
                                    ->required()
                                    ->rules([
                                        'required',
                                        Rule::in(['new', 'screening', 'interview', 'offer', 'rejected', 'hired']),
                                    ]),

                                Grid::make(2)
                                    ->columnSpan(1)
                                    ->schema([
                                        TextInput::make('salary_expected.min')
                                            ->label('Lương mong muốn (từ)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->nullable(),

                                        TextInput::make('salary_expected.max')
                                            ->label('Lương mong muốn (đến)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->nullable(),
                                    ]),

                                Textarea::make('rejected_reason')
                                    ->label('Lý do từ chối')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->nullable()
                                    ->visible(fn (callable $get): bool => $get('status') === 'rejected'),

                                Select::make('referral_user_id')
                                    ->label('Người giới thiệu')
                                    ->options(fn () => User::query()
                                        ->select(['id', 'name', 'email'])
                                        ->orderByDesc('id')
                                        ->limit(500)
                                        ->get()
                                        ->mapWithKeys(fn ($user) => [
                                            $user->id => "#{$user->id} — {$user->name}" . ($user->email ? " ({$user->email})" : ''),
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
                                    ->columnSpanFull()
                                    ->nullable(),
                            ])
                            ->columnSpan(['default' => 'full', 'lg' => 4]),

                        Section::make('CV ứng viên')
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
                                    ->downloadable()
                                    ->openable()
                                    ->nullable(),
                                Section::make('Xem trước CV')
                                    ->columnSpan(['default' => 'full'])
                                    ->extraAttributes(['style' => 'position: sticky; top: 1rem; align-self: start;'])
                                    ->schema([
                                        ViewField::make('cv_path_preview')
                                            ->label('')
                                            ->view('filament.forms.components.cv-preview')
                                            ->dehydrated(false),
                                    ]),
                            ])->columnSpan(['default' => 'full', 'lg' => 8]),

                    ]), // end outer Grid
            ])->columns(1);
    }
}

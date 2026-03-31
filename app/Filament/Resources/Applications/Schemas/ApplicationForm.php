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

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Outer 3-column grid: left form inputs (2/3) + right CV preview (1/3) ──
                Grid::make(['default' => 1, 'md'=>6, 'lg' => 12])
                    ->schema([

                Select::make('candidate_id')
                    ->label('Ứng viên')
                    ->options(fn () => DB::table('candidates')
                        ->select(['id', 'name'])
                        ->orderByDesc('id')
                        ->limit(500)
                        ->get()
                        ->mapWithKeys(fn ($candidate) => [
                            $candidate->id => "#{$candidate->id} - {$candidate->name}",
                        ])
                        ->all())
                    ->reactive()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->rules(['required', 'integer', 'exists:candidates,id']),
                TextInput::make('cv_path')
                    ->label('Link CV')
                    ->readOnly()
                    ->dehydrated(false)
                    ->placeholder('CV sẽ tự lấy từ hồ sơ ứng viên khi lưu')
                    ->formatStateUsing(function (callable $get): ?string {
                        $candidateId = $get('candidate_id');

                        if (empty($candidateId)) {
                            return null;
                        }

                        $cvFile = Candidate::query()
                            ->whereKey($candidateId)
                            ->value('cv_file');

                        return $cvFile ? asset('storage/' . $cvFile) : 'Ứng viên chưa có CV';
                    }),

                                Textarea::make('rejected_reason')
                                    ->label('Lý do từ chối')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->nullable()
                                    ->visible(fn(callable $get): bool => $get('status') === 'rejected'),
                                Select::make('referral_user_id')
                                    ->label('Người giới thiệu')
                                    ->options(fn() => User::query()
                                        ->select(['id', 'name', 'email'])
                                        ->orderByDesc('id')
                                        ->limit(500)
                                        ->get()
                                        ->mapWithKeys(fn($user) => [
                                            $user->id => "#{$user->id} — {$user->name}" . ($user->email ? " ({$user->email})" : ''),
                                        ])
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->columnSpanFull()
                                    ->visible(fn(callable $get): bool => $get('source') === 'referral')
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
                            ])->columnSpan(['default' => 'full', 'lg' => 4]),

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

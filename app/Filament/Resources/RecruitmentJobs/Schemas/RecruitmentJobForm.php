<?php

namespace App\Filament\Resources\RecruitmentJobs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RecruitmentJobForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Tiêu đề tuyển dụng')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        fn($state, $set) =>
                        $set('slug', Str::slug($state))
                    )
                    ->dehydrateStateUsing(fn($state) => trim($state)),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'recruitment_jobs', column: 'slug', ignoreRecord: true)
                    ->dehydrateStateUsing(fn($state) => trim($state)),

                Select::make('branch_id')
                    ->label('Chi nhánh')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),

                Select::make('department_id')
                    ->label('Phòng ban')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->nullable(),

                Select::make('workplace_id')
                    ->label('Nơi làm việc')
                    ->relationship('workplace', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->nullable(),

                Textarea::make('description')
                    ->label('Mô tả công việc')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull()
                    ->dehydrateStateUsing(fn($state) => trim($state)),

                Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'draft' => 'Bản nháp',
                        'published' => 'Đang tuyển',
                        'closed' => 'Đã đóng',
                        'archived' => 'Lưu trữ',
                    ])
                    ->default('draft')
                    ->required(),

                TextInput::make('salary_min')
                    ->label('Lương tối thiểu')
                    ->numeric()
                    ->nullable(),

                TextInput::make('salary_max')
                    ->label('Lương tối đa')
                    ->numeric()
                    ->nullable(),

                Hidden::make('salary_range')
                    ->dehydrateStateUsing(
                        fn($state, $get) =>
                        $get('salary_min') || $get('salary_max')
                            ? [
                                'min' => $get('salary_min'),
                                'max' => $get('salary_max'),
                            ]
                            : null
                    ),

                DatePicker::make('deadline')
                    ->label('Hạn nộp')
                    ->nullable(),

                TextInput::make('positions_count')
                    ->label('Số lượng tuyển')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required(),

                TextInput::make('public_url')
                    ->label('Link công khai')
                    ->url()
                    ->unique(table: 'recruitment_jobs', column: 'public_url', ignoreRecord: true)
                    ->nullable(),

                FileUpload::make('thumbnail')
                    ->label('Ảnh đại diện')
                    ->image()
                    ->directory('jobs')
                    ->nullable(),

                Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }
}

<?php

namespace App\Filament\Resources\RecruitmentJobs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
                    ->rules(['required', 'string', 'max:255'])
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
                    ->rules([
                        'required',
                        'string',
                        'max:255',
                        // allow letters/numbers and dashes (typical slug)
                        'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    ])
                    ->dehydrateStateUsing(fn($state) => trim($state)),

                Select::make('branch_id')
                    ->label('Chi nhánh')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required()
                    ->rules(['required']),

                Select::make('department_id')
                    ->label('Phòng ban')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->nullable()
                    ->rules(['nullable']),

                Select::make('workplace_id')
                    ->label('Nơi làm việc')
                    ->relationship('workplace', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->nullable()
                    ->rules(['nullable']),

                Textarea::make('description')
                    ->label('Mô tả công việc')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull()
                    ->rules(['required', 'string'])
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
                    ->required()
                    ->rules([
                        'required',
                        Rule::in(['draft', 'published', 'closed', 'archived']),
                    ]),

                TextInput::make('salary_min')
                    ->label('Lương tối thiểu')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->rules(function (callable $get) {
                        $max = $get('salary_max');

                        return [
                            'nullable',
                            'numeric',
                            'min:0',
                            function (string $attribute, $value, $fail) use ($max) {
                                if ($value === null || $max === null) {
                                    return;
                                }

                                if ((float) $value > (float) $max) {
                                    $fail('Lương tối thiểu không được lớn hơn lương tối đa.');
                                }
                            },
                        ];
                    }),

                TextInput::make('salary_max')
                    ->label('Lương tối đa')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->rules(function (callable $get) {
                        $min = $get('salary_min');

                        return [
                            'nullable',
                            'numeric',
                            'min:0',
                            function (string $attribute, $value, $fail) use ($min) {
                                if ($value === null || $min === null) {
                                    return;
                                }

                                if ((float) $value < (float) $min) {
                                    $fail('Lương tối đa không được nhỏ hơn lương tối thiểu.');
                                }
                            },
                        ];
                    }),

                Hidden::make('salary_range')
                    ->afterStateHydrated(function ($state, $set) {
                        if (empty($state)) {
                            return;
                        }

                        if (is_array($state)) {
                            $min = $state['min'] ?? $state[0] ?? null;
                            $max = $state['max'] ?? $state[1] ?? null;

                            $set('salary_min', $min);
                            $set('salary_max', $max);

                            return;
                        }

                        if (is_string($state) && str_contains($state, ',')) {
                            $parts = array_map('trim', explode(',', $state, 2));
                            $set('salary_min', $parts[0] !== '' ? $parts[0] : null);
                            $set('salary_max', ($parts[1] ?? '') !== '' ? $parts[1] : null);
                        }
                    })
                    ->dehydrateStateUsing(
                        fn($state, $get) => (function () use ($get) {
                            $min = $get('salary_min');
                            $max = $get('salary_max');

                            // Normalize empty string to null (sometimes numeric inputs return '' while editing)
                            $min = $min === '' ? null : $min;
                            $max = $max === '' ? null : $max;

                            if ($min === null && $max === null) {
                                return null;
                            }

                            return [
                                'min' => $min,
                                'max' => $max,
                            ];
                        })()
                    ),

                DatePicker::make('deadline')
                    ->label('Hạn nộp')
                    ->nullable()
                    ->rules(['nullable', 'date', 'after_or_equal:today']),

                TextInput::make('positions_count')
                    ->label('Số lượng tuyển')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->rules(['required', 'integer', 'min:1']),

                TextInput::make('public_url')
                    ->label('Link công khai')
                    ->url()
                    ->unique(table: 'recruitment_jobs', column: 'public_url', ignoreRecord: true)
                    ->nullable()
                    ->rules(['nullable', 'url', 'max:2048']),

                FileUpload::make('thumbnail')
                    ->label('Ảnh đại diện')
                    ->image()
                    ->directory('jobs')
                    ->nullable()
                    ->rules(['nullable']),

                Hidden::make('created_by')
                    ->default(fn () => Auth::id()),
            ]);
    }
}

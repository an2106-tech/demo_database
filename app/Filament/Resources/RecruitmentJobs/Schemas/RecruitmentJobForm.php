<?php

namespace App\Filament\Resources\RecruitmentJobs\Schemas;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Workplace;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class RecruitmentJobForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin')
                    ->compact()
                    ->extraAttributes(['class' => 'min-h-[650px]'])
                    ->schema([
                        Fieldset::make('Thông tin chung')
                            ->columns(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Tiêu đề tuyển dụng')
                                    ->required()
                                    ->maxLength(255)
                                    ->rules(['required', 'string', 'max:255'])
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state)))
                                    ->dehydrateStateUsing(fn ($state) => trim($state)),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(table: 'recruitment_jobs', column: 'slug', ignoreRecord: true)
                                    ->rules([
                                        'required',
                                        'string',
                                        'max:255',
                                        'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                                    ])
                                    ->dehydrateStateUsing(fn ($state) => trim($state)),
                                Select::make('status')
                                    ->label('Trạng thái')
                                    ->options(StatusRecruitmentJobsEnum::class)
                                    ->default(StatusRecruitmentJobsEnum::DRAFT)
                                    ->required()
                                    ->enum(StatusRecruitmentJobsEnum::class),
                            ]),
                        Fieldset::make('Tổ chức')
                            ->columns(2)
                            ->schema([
                                Select::make('branch_id')
                                    ->label('Chi nhánh')
                                    ->options(function (): array {
                                        /** @var \App\Models\User|null $user */
                                        $user = Auth::user();

                                        if ($user?->branchScopeId()) {
                                            return Branch::query()
                                                ->whereKey($user->branchScopeId())
                                                ->pluck('name', 'id')
                                                ->all();
                                        }

                                        return Branch::query()->orderBy('name')->pluck('name', 'id')->all();
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set): void {
                                        $set('department_id', null);
                                        $set('workplace_id', null);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->default(fn () => Auth::user()?->branchScopeId())
                                    ->disabled(fn (): bool => (bool) Auth::user()?->branchScopeId())
                                    ->required()
                                    ->rules(['required']),
                                Select::make('department_id')
                                    ->label('Phòng ban')
                                    ->options(function (callable $get) {
                                        $branchId = $get('branch_id');

                                        if (empty($branchId)) {
                                            return [];
                                        }

                                        return Department::query()
                                            ->where('branch_id', $branchId)
                                            ->orderBy('name')
                                            ->limit(500)
                                            ->pluck('name', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->disabled(fn (callable $get): bool => empty($get('branch_id')))
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('workplace_id', null))
                                    ->nullable()
                                    ->rules(['nullable', 'integer']),
                                Placeholder::make('branch_image_preview')
                                    ->label('Ảnh chi nhánh')
                                    ->columnSpan(1)
                                    ->visible(fn (callable $get): bool => ! empty($get('branch_id')))
                                    ->content(function (callable $get): HtmlString|string {
                                        $branchId = $get('branch_id');

                                        if (empty($branchId)) {
                                            return '-';
                                        }

                                        $branchImage = Branch::query()
                                            ->whereKey($branchId)
                                            ->value('image');

                                        if (empty($branchImage)) {
                                            return 'Chi nhánh chưa có hình ảnh.';
                                        }

                                        $url = '/storage/' . ltrim($branchImage, '/');

                                        return new HtmlString(
                                            '<img src="' . e($url) . '" alt="Ảnh chi nhánh" style="max-height: 120px; border-radius: 8px; object-fit: cover;" />'
                                        );
                                    }),
                                Select::make('workplace_id')
                                    ->label('Nơi làm việc')
                                    ->options(function (callable $get) {
                                        $branchId = $get('branch_id');

                                        if (empty($branchId)) {
                                            return [];
                                        }

                                        return Workplace::query()
                                            ->where('branch_id', $branchId)
                                            ->orderBy('name')
                                            ->limit(500)
                                            ->pluck('name', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->disabled(fn (callable $get): bool => empty($get('department_id')))
                                    ->nullable()
                                    ->rules(['nullable', 'integer']),
                            ]),
                    ]),
                Section::make('Chi tiết')
                    ->compact()
                    ->schema([
                        Textarea::make('description')
                            ->label('Mô tả công việc')
                            ->required()
                            ->rows(10)
                            ->columnSpanFull()
                            ->rules(['required', 'string'])
                            ->dehydrateStateUsing(fn ($state) => trim($state)),
                        Fieldset::make('Lương & Hạn')
                            ->columns(4)
                            ->schema([
                                TextInput::make('salary_min')
                                    ->label('Lương tối thiểu')
                                    ->numeric()
                                    ->minValue(0)
                                    ->nullable(),
                                TextInput::make('salary_max')
                                    ->label('Lương tối đa')
                                    ->numeric()
                                    ->minValue(0)
                                    ->nullable(),
                                DatePicker::make('deadline')
                                    ->label('Hạn nộp')
                                    ->columnSpan(2)
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->nullable(),
                                TextInput::make('positions_count')
                                    ->label('Số lượng tuyển')
                                    ->columnSpan(2)
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->rules(['required', 'integer', 'min:1']),
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
                                        }
                                    })
                                    ->dehydrateStateUsing(fn ($state, $get) => (function () use ($get) {
                                        $min = $get('salary_min');
                                        $max = $get('salary_max');

                                        $min = $min === '' ? null : $min;
                                        $max = $max === '' ? null : $max;

                                        if ($min === null && $max === null) {
                                            return null;
                                        }

                                        return ['min' => $min, 'max' => $max];
                                    })()),
                            ]),
                        Fieldset::make('Phân loại & Kỹ năng')
                            ->columns(2)
                            ->schema([
                                Select::make('categories')
                                    ->label('Danh mục nghề nghiệp')
                                    ->relationship('categories', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->multiple()
                                    ->required()
                                    ->rules(['required']),
                                Select::make('skills')
                                    ->label('Kỹ năng')
                                    ->relationship('skills', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->multiple()
                                    ->required()
                                    ->rules(['required'])
                                    ->pivotData(fn (callable $get) => [
                                        'level' => $get('skills_level') ?? 'mid',
                                        'is_required' => true,
                                    ]),
                                Select::make('skills_level')
                                    ->label('Trình độ kỹ năng')
                                    ->options([
                                        'junior' => 'Junior',
                                        'mid' => 'Mid',
                                        'senior' => 'Senior',
                                    ])
                                    ->default('mid')
                                    ->required()
                                    ->rules(['required'])
                                    ->dehydrated(false),
                                Placeholder::make('public_link_display')
                                    ->label('🔗 Link công khai cho ứng viên')
                                    ->columnSpanFull()
                                    ->content(function ($record): HtmlString|string {
                                        if (! $record || ! filled($record->slug)) {
                                            return new HtmlString(
                                                '<span style="color:#94a3b8;font-size:13px;">'
                                                . '💡 Lưu tin tuyển dụng để hệ thống tự tạo link chia sẻ'
                                                . '</span>'
                                            );
                                        }

                                        $url = route('jobs.public', ['slug' => $record->slug]);

                                        return new HtmlString(
                                            '<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">'
                                            . '<a href="' . e($url) . '" target="_blank" '
                                            . 'style="color:#2563eb;word-break:break-all;font-size:13px;flex:1;min-width:200px;">'
                                            . e($url)
                                            . '</a>'
                                            . '<button type="button" '
                                            . 'onclick="navigator.clipboard.writeText(\'' . e($url) . '\');'
                                            . 'this.innerHTML=\'✅ Đã copy!\';'
                                            . 'setTimeout(()=>this.innerHTML=\'📋 Copy link\',2500)"'
                                            . ' style="white-space:nowrap;padding:6px 14px;background:#f0fdf4;'
                                            . 'border:1.5px solid #86efac;border-radius:8px;cursor:pointer;'
                                            . 'font-size:12px;font-weight:600;color:#15803d;">'
                                            . '📋 Copy link'
                                            . '</button>'
                                            . '</div>'
                                        );
                                    }),
                            ]),
                        Hidden::make('created_by')
                            ->default(fn () => Auth::id()),
                    ]),
            ]);
    }
}
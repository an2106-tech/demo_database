<?php

namespace App\Filament\Resources\RecruitmentJobs\Tables;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Skill;
use App\Models\Workplace;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Slider;
use Filament\Schemas\Components\Grid as FormGrid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class RecruitmentJobsTable
{
    public static function configure(Table $table): Table
    {
        // Tính khoảng lương thực tế từ CSDL
        $salaryRow = DB::table('recruitment_jobs')
            ->whereNotNull('salary_range')
            ->whereNull('deleted_at')
            ->selectRaw("
                FLOOR(MIN(CAST(JSON_EXTRACT(salary_range, '$.min') AS DECIMAL(20,2))) / 1000000) as sal_min,
                CEIL(MAX(CAST(JSON_EXTRACT(salary_range, '$.max') AS DECIMAL(20,2))) / 1000000) as sal_max
            ")
            ->first();

        $sliderMin = max(0, (int) ($salaryRow?->sal_min ?? 0));
        $sliderMax = max($sliderMin + 1, (int) ($salaryRow?->sal_max ?? 100));

        return $table
            ->columns([
                TextColumn::make('title')->label('Tiêu đề')->searchable()->sortable(),
                TextColumn::make('branch.image')
                    ->label('Ảnh chi nhánh')
                    ->html()
                    ->formatStateUsing(fn(?string $state): HtmlString => new HtmlString(
                        $state
                            ? '<img src="/storage/' . e($state) . '" alt="Ảnh chi nhánh" style="width:36px;height:36px;border-radius:9999px;object-fit:cover;" />'
                            : '<span style="color:#94a3b8;">-</span>'
                    )),
                TextColumn::make('branch.name')->label('Chi nhánh')->searchable(),
                TextColumn::make('department.name')->label('Phòng ban')->searchable(),
                TextColumn::make('workplace.name')->label('Nơi làm việc')->searchable(),

                // * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
                TextColumn::make('deadline')
                    ->date('d/m/Y')->label('Ngày hết hạn')->searchable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->sortable(),
                TextColumn::make('salary_range')
                    ->label('Mức lương')
                    ->state(function ($record): string {
                        $range = $record->salary_range;
                        if (empty($range)) {
                            return '-';
                        }
                        $min = isset($range['min']) ? number_format((float) $range['min']) : null;
                        $max = isset($range['max']) ? number_format((float) $range['max']) : null;
                        if ($min !== null && $max !== null) {
                            return "{$min} - {$max}";
                        }
                        return $min ?? $max ?? '-';
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // ══ Nhóm 1: Phân loại ══════════════════════════════════════

                // Chi nhánh (chỉ super_admin/admin thấy)
                SelectFilter::make('branch_id')
                    ->label('Chi nhánh')
                    ->options(fn() => Branch::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->visible(function (): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
                    }),

                SelectFilter::make('department_id')
                    ->label('Phòng ban')
                    ->options(fn() => Department::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),

                SelectFilter::make('workplace_id')
                    ->label('Nơi làm việc')
                    ->options(fn() => Workplace::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),

                SelectFilter::make('skills')
                    ->label('Kỹ năng')
                    ->options(fn() => Skill::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->multiple()
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['values'] ?? null)) {
                            return $query;
                        }
                        return $query->whereHas(
                            'skills',
                            fn(Builder $q) => $q->whereIn('skills.id', $data['values'])
                        );
                    }),

                // ══ Nhóm 2: Mức lương ══════════════════════════════════════
                // Filter::make('salary_range')
                //     ->label('Mức lương')
                //     ->columnSpan(2)
                //     ->form([
                //         Slider::make('salary_slider')
                //             ->label('Khoảng lương (triệu VNĐ)')
                //             ->range($sliderMin, $sliderMax)
                //             ->step(1)
                //             ->default([$sliderMin, $sliderMax])
                //             ->tooltips(true),
                //     ])
                //     ->query(function (Builder $query, array $data) use ($sliderMin, $sliderMax): Builder {
                //         $vals = $data['salary_slider'] ?? null;
                //         if (! is_array($vals) || count($vals) < 2) {
                //             return $query;
                //         }
                //         [$min, $max] = [(float) $vals[0] * 1_000_000, (float) $vals[1] * 1_000_000];
                //         // Bỏ qua khi slider ở toàn bộ khoảng
                //         if ($min <= $sliderMin * 1_000_000 && $max >= $sliderMax * 1_000_000) {
                //             return $query;
                //         }
                //         return $query
                //             ->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(salary_range, '$.min')) AS DECIMAL(20,2)) <= ?", [$max])
                //             ->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(salary_range, '$.max')) AS DECIMAL(20,2)) >= ?", [$min]);
                //     }),

                // ══ Nhóm 3: Thời gian đăng tuyển ══════════════════════════
                Filter::make('deadline_range')
                    ->label('Hạn nộp hồ sơ')
                    ->columnSpan(2)
                    ->form([
                        FormGrid::make(2)
                            ->schema([
                                DatePicker::make('deadline_from')
                                    ->label('Ngày hết hạn từ')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->default(now()->startOfMonth()->toDateString()),
                                DatePicker::make('deadline_until')
                                    ->label('Ngày hết hạn đến')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->default(now()->endOfMonth()->toDateString()),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from  = $data['deadline_from']  ?? null;
                        $until = $data['deadline_until'] ?? null;

                        if (filled($from)) {
                            $query->where(function (Builder $q) use ($from): void {
                                $q->whereDate('deadline', '>=', $from)
                                    ->orWhereNull('deadline');
                            });
                        }

                        if (filled($until)) {
                            $query->where(function (Builder $q) use ($until): void {
                                $q->whereDate('deadline', '<=', $until)
                                    ->orWhereNull('deadline');
                            });
                        }

                        return $query;
                    }),
            ])
            ->filtersFormColumns(4)
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->recordActions([
                ViewAction::make()
                    ->label('Xem'),
                EditAction::make()
                    ->label('Sửa'),
                DeleteAction::make()
                    ->label('Xóa'),
            ])
            ->recordActionsColumnLabel('Thao tác')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

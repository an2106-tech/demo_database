<?php

namespace App\Filament\Resources\RecruitmentJobs\Tables;

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
use Filament\Schemas\Components\Grid as FormGrid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class RecruitmentJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('branch.image')
                    ->label('Ảnh chi nhánh')
                    ->html()
                    ->formatStateUsing(fn (?string $state): HtmlString => new HtmlString(
                        $state
                            ? '<img src="/storage/' . e($state) . '" alt="Ảnh chi nhánh" style="width:36px;height:36px;border-radius:9999px;object-fit:cover;" />'
                            : '<span style="color:#94a3b8;">-</span>'
                    )),
                TextColumn::make('branch.name')
                    ->label('Chi nhánh')
                    ->searchable(),
                TextColumn::make('department.name')
                    ->label('Phòng ban')
                    ->searchable(),
                TextColumn::make('workplace.name')
                    ->label('Nơi làm việc')
                    ->searchable(),
                TextColumn::make('deadline')
                    ->label('Ngày hết hạn')
                    ->date('d/m/Y')
                    ->searchable(),
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
                SelectFilter::make('branch_id')
                    ->label('Chi nhánh')
                    ->options(fn () => Branch::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->visible(fn (): bool => (bool) Auth::user()?->hasRole('super_admin')),
                SelectFilter::make('department_id')
                    ->label('Phòng ban')
                    ->options(fn () => Department::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                SelectFilter::make('workplace_id')
                    ->label('Nơi làm việc')
                    ->options(fn () => Workplace::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                SelectFilter::make('skills')
                    ->label('Kỹ năng')
                    ->options(fn () => Skill::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->multiple()
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['values'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'skills',
                            fn (Builder $skillQuery) => $skillQuery->whereIn('skills.id', $data['values'])
                        );
                    }),
                Filter::make('deadline_range')
                    ->label('Hạn nộp hồ sơ')
                    ->columnSpan(2)
                    ->form([
                        FormGrid::make(2)
                            ->schema([
                                DatePicker::make('deadline_from')
                                    ->label('Ngày hết hạn từ')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                                DatePicker::make('deadline_until')
                                    ->label('Ngày hết hạn đến')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['deadline_from'] ?? null;
                        $until = $data['deadline_until'] ?? null;

                        if (filled($from)) {
                            $query->where(function (Builder $nestedQuery) use ($from): void {
                                $nestedQuery->whereDate('deadline', '>=', $from)
                                    ->orWhereNull('deadline');
                            });
                        }

                        if (filled($until)) {
                            $query->where(function (Builder $nestedQuery) use ($until): void {
                                $nestedQuery->whereDate('deadline', '<=', $until)
                                    ->orWhereNull('deadline');
                            });
                        }

                        return $query;
                    }),
            ])
            ->filtersFormColumns(4)
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->recordActions([
                ViewAction::make()->label('Xem'),
                EditAction::make()->label('Sửa'),
                DeleteAction::make()->label('Xóa'),
            ])
            ->recordActionsColumnLabel('Thao tác')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

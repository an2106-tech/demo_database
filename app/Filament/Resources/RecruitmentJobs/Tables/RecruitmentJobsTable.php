<?php

namespace App\Filament\Resources\RecruitmentJobs\Tables;

use App\Models\Branch;
use App\Models\Category;
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
use Filament\Tables\Columns\SelectColumn;
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
                    ->sortable()
                    ->wrap(),
                TextColumn::make('categories.name')
                    ->label('Danh mục')
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('branch.image')
                    ->label('Logo')
                    ->html()
                    ->formatStateUsing(fn (?string $state): HtmlString => new HtmlString(
                        $state
                            ? '<img src="/storage/' . e($state) . '" alt="Logo" style="width:32px;height:32px;border-radius:6px;object-fit:cover;" />'
                            : '<span style="color:#94a3b8;">-</span>'
                    ))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('branch.name')
                    ->label('Chi nhánh')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Phòng ban')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('workplace.name')
                    ->label('Nơi làm việc')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deadline')
                    ->label('Hạn nộp')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->sortable()
                    ->action(
                        \Filament\Actions\Action::make('updateStatus')
                            ->label('Cập nhật trạng thái')
                            ->modalHeading('Xác nhận thay đổi trạng thái')
                            ->modalDescription('Bạn có chắc chắn muốn thay đổi trạng thái cho tin tuyển dụng này?')
                            ->form([
                                \Filament\Forms\Components\Select::make('status')
                                    ->label('Chọn trạng thái mới')
                                    ->options(\App\Enums\StatusRecruitmentJobsEnum::class)
                                    ->required()
                                    ->default(fn ($record) => $record->status),
                            ])
                            ->action(function ($record, array $data): void {
                                $record->update(['status' => $data['status']]);
                                \Filament\Notifications\Notification::make()
                                    ->title('Thành công')
                                    ->body('Trạng thái đã được cập nhật.')
                                    ->success()
                                    ->send();
                            })
                            ->modalSubmitActionLabel('Cập nhật')
                    ),
                TextColumn::make('salary_range')
                    ->label('Mức lương')
                    ->toggleable(isToggledHiddenByDefault: true)
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
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('categories')
                    ->label('Danh mục')
                    ->options(fn () => Category::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->multiple()
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['values'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'categories',
                            fn (Builder $categoryQuery) => $categoryQuery->whereIn('categories.id', $data['values'])
                        );
                    }),
            ])
            ->filtersFormColumns(4)
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->recordActions([
                \Filament\Actions\Action::make('updateStatus')
                    ->label('Đổi trạng thái')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->modalHeading('Xác nhận thay đổi trạng thái')
                    ->modalDescription('Bạn có chắc chắn muốn thay đổi trạng thái cho tin tuyển dụng này?')
                    ->form([
                        \Filament\Forms\Components\Select::make('status')
                            ->label('Chọn trạng thái mới')
                            ->options(\App\Enums\StatusRecruitmentJobsEnum::class)
                            ->required()
                            ->default(fn ($record) => $record->status),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update(['status' => $data['status']]);
                        \Filament\Notifications\Notification::make()
                            ->title('Thành công')
                            ->body('Trạng thái đã được cập nhật.')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel('Cập nhật'),
                \Filament\Actions\Action::make('viewPublicLink')
                    ->label('🔗 Link ứng viên')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->url(fn ($record) => filled($record->slug) ? route('jobs.public', ['slug' => $record->slug]) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->slug))
                    ->tooltip('Mở trang ứng tuyển công khai'),
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

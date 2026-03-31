<?php

namespace App\Filament\Resources\RecruitmentJobs\Tables;


use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class RecruitmentJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Tiêu đề')->searchable()->sortable(),
                TextColumn::make('branch.image')
                    ->label('Ảnh chi nhánh')
                    ->html()
                    ->formatStateUsing(fn (?string $state): HtmlString => new HtmlString(
                        $state
                            ? '<img src="/storage/' . e($state) . '" alt="Ảnh chi nhánh" style="width:36px;height:36px;border-radius:9999px;object-fit:cover;" />'
                            : '<span style="color:#94a3b8;">-</span>'
                    )),
                TextColumn::make('branch.name')->label('Chi nhánh')->searchable(),
                TextColumn::make('department.name')->label('Phòng ban')->searchable(),
                TextColumn::make('workplace.name')->label('Nơi làm việc')->searchable(),
                TextColumn::make('status')->label('Trạng thái')->sortable(),
                TextColumn::make('salary_range')
                    ->label('Mức lương')
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return '-';
                        }

                        if (is_array($state)) {
                            $min = $state['min'] ?? $state[0] ?? null;
                            $max = $state['max'] ?? $state[1] ?? null;

                            if (is_numeric($min) && is_numeric($max)) {
                                return number_format((float) $min) . ' - ' . number_format((float) $max);
                            }

                            if (is_numeric($min)) {
                                return number_format((float) $min);
                            }

                            if (is_numeric($max)) {
                                return number_format((float) $max);
                            }
                        }

                        if (is_string($state) && str_contains($state, ',')) {
                            $parts = array_filter(array_map('trim', explode(',', $state)));
                            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                                return number_format((float) $parts[0]) . ' - ' . number_format((float) $parts[1]);
                            }
                        }

                        return is_numeric($state) ? number_format((float) $state) : (string) $state;
                    }),

            ])
            ->filters([
                //
            ])
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

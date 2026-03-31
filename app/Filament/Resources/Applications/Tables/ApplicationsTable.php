<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\StatusApplicationEnum;
use App\Models\Branch;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('job.title')
                    ->label('Công việc')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('job.branch.name')
                    ->label('Chi nhánh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('candidate.name')
                    ->label('Ứng viên')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('cv_path')
                    ->label('CV')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Tải CV' : '-')
                    ->url(fn ($record) => $record->cv_path ? asset('storage/' . $record->cv_path) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('source')
                    ->label('Nguồn')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->colors([
                        'primary' => 'website',
                        'info' => 'linkedin',
                        'success' => 'referral',
                        'warning' => 'facebook',
                        'gray' => 'other',
                    ]),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge(),
                TextColumn::make('applied_at')
                    ->label('Ngày ứng tuyển')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch')
                    ->label('Chi nhánh')
                    ->options(fn() => Branch::orderBy('name')->pluck('name', 'id'))
                    ->query(
                        fn(Builder $query, array $data) =>
                        filled($data['value'])
                            ? $query->whereHas('job', fn(Builder $q) => $q->where('branch_id', $data['value']))
                            : $query
                    )
                    ->searchable()
                    ->visible(fn() => (bool) Auth::user()?->hasAnyRole(['super_admin', 'admin'])),
                SelectFilter::make('source')
                    ->label('Nguồn')
                    ->options([
                        'website' => 'Website',
                        'facebook' => 'Facebook',
                        'linkedin' => 'LinkedIn',
                        'referral' => 'Giới thiệu',
                        'other' => 'Khác',
                    ]),
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->searchable()
                    ->options(StatusApplicationEnum::class),
                TrashedFilter::make(),
            ])
            
            ->recordActions([
                ViewAction::make()
                    ->modal()
                    ->modalWidth('7xl')
                    ->label('Xem'),
                EditAction::make()
                    ->modal()
                    ->label('Sửa')
                    ->modalWidth('7xl')
                    ->modalSubmitActionLabel('Lưu'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        $statusOptions = collect(StatusApplicationEnum::cases())
            ->mapWithKeys(fn (StatusApplicationEnum $status) => [$status->value => $status->getLabel()])
            ->all();

        return $table
            ->defaultSort('applied_at', 'desc')
            ->searchPlaceholder('Tìm theo ID ứng tuyển, ứng viên hoặc công việc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('job.title')
                    ->label('Công việc')
                    ->sortable(),
                TextColumn::make('job.branch.name')
                    ->label('Chi nhánh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('candidate.name')
                    ->label('Ứng viên')
                    ->sortable(),
                TextColumn::make('cv_path')
                    ->label('CV')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Mở CV' : '-')
                    ->url(fn ($record) => $record->cv_path ? asset('storage/' . ltrim($record->cv_path, '/')) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('source')
                    ->label('Nguồn')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'website' => 'Website',
                        'facebook' => 'Facebook',
                        'linkedin' => 'LinkedIn',
                        'referral' => 'Giới thiệu',
                        'other' => 'Khác',
                        default => $state ?? '-',
                    })
                    ->colors([
                        'primary' => 'website',
                        'info' => 'linkedin',
                        'success' => 'referral',
                        'warning' => 'facebook',
                        'gray' => 'other',
                    ]),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->sortable(),
                TextColumn::make('salary_expected')
                    ->label('Lương mong muốn')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function ($state): string {
                        if (! is_array($state) || (($state['min'] ?? null) === null && ($state['max'] ?? null) === null)) {
                            return '-';
                        }

                        $min = isset($state['min']) && $state['min'] !== null
                            ? number_format((float) $state['min'], 0, ',', '.')
                            : null;
                        $max = isset($state['max']) && $state['max'] !== null
                            ? number_format((float) $state['max'], 0, ',', '.')
                            : null;

                        return match (true) {
                            $min && $max => "{$min} - {$max} VNĐ",
                            $min !== null => "Từ {$min} VNĐ",
                            $max !== null => "Đến {$max} VNĐ",
                            default => '-',
                        };
                    }),
                TextColumn::make('applied_at')
                    ->label('Ngày ứng tuyển')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Chi nhánh')
                    ->options(fn () => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('job', fn (Builder $q) => $q->where('branch_id', $data['value']));
                    })
                    ->visible(fn () => (bool) Auth::user()?->hasRole('super_admin')),
                SelectFilter::make('job_id')
                    ->label('Công việc')
                    ->options(fn () => RecruitmentJob::query()->orderBy('title')->limit(500)->pluck('title', 'id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null)
                            ? $query->where('job_id', $data['value'])
                            : $query;
                    }),
                SelectFilter::make('candidate_id')
                    ->label('Ứng viên')
                    ->options(fn () => Candidate::query()->orderBy('name')->limit(500)->get()->mapWithKeys(fn (Candidate $candidate) => [
                        $candidate->id => "#{$candidate->id} - {$candidate->name}" . ($candidate->email ? " ({$candidate->email})" : ''),
                    ])->all())
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null)
                            ? $query->where('candidate_id', $data['value'])
                            : $query;
                    }),
                SelectFilter::make('source')
                    ->label('Nguồn')
                    ->options([
                        'website' => 'Website',
                        'facebook' => 'Facebook',
                        'linkedin' => 'LinkedIn',
                        'referral' => 'Giới thiệu',
                        'other' => 'Khác',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null)
                            ? $query->where('source', $data['value'])
                            : $query;
                    }),
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options($statusOptions)
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null)
                            ? $query->where('status', $data['value'])
                            : $query;
                    }),
                SelectFilter::make('cv_state')
                    ->label('Tình trạng CV')
                    ->options([
                        'has_cv' => 'Đã có CV',
                        'missing_cv' => 'Chưa có CV',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'has_cv' => $query->whereNotNull('cv_path')->where('cv_path', '!=', ''),
                            'missing_cv' => $query->where(function (Builder $q): void {
                                $q->whereNull('cv_path')->orWhere('cv_path', '');
                            }),
                            default => $query,
                        };
                    }),
                Filter::make('applied_at_range')
                    ->label('Ngày ứng tuyển')
                    ->form([
                        DatePicker::make('applied_from')
                            ->label('Từ ngày')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('applied_until')
                            ->label('Đến ngày')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['applied_from'] ?? null),
                                fn (Builder $q) => $q->whereDate('applied_at', '>=', $data['applied_from'])
                            )
                            ->when(
                                filled($data['applied_until'] ?? null),
                                fn (Builder $q) => $q->whereDate('applied_at', '<=', $data['applied_until'])
                            );
                    }),
                TrashedFilter::make()
                    ->label('Bản ghi đã xóa'),
            ])
            ->filtersFormColumns(3)
            ->recordActions([
                ViewAction::make()
                    ->modal()
                    ->modalWidth('7xl')
                    ->label('Xem'),
                EditAction::make()
                    ->label('Sửa')
                    ->url(fn ($record): string => ApplicationResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make()
                    ->label('Xóa'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Xóa đã chọn'),
                    ForceDeleteBulkAction::make()->label('Xóa vĩnh viễn'),
                    RestoreBulkAction::make()->label('Khôi phục'),
                ]),
            ]);
    }
}

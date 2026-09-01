<?php

namespace App\Filament\Resources\Candidates\Tables;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Candidates\CandidateResource;
use App\Models\Candidate;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CandidatesTable
{
    public static function configure(Table $table): Table
    {
        $recordActions = CandidateResource::canAdministerCandidates()
            ? [
                ActionGroup::make([
                    EditAction::make()
                        ->label('Điều chỉnh thông tin')
                        ->visible(fn (Candidate $record): bool => CandidateResource::canEdit($record)),
                    CandidateResource::restrictAction(),
                    CandidateResource::clearRestrictionAction(),
                ])
                    ->label('Thao tác')
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->tooltip('Thao tác khác')
                    ->iconButton(),
            ]
            : [];

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ứng viên')
                    ->description(fn (Candidate $record): string => $record->resume?->profile_title ?: 'Chưa cập nhật chức danh')
                    ->searchable(['name', 'email', 'phone'])
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),
                TextColumn::make('email')
                    ->label('Liên hệ')
                    ->description(fn (Candidate $record): string => $record->phone ?: 'Chưa có số điện thoại')
                    ->placeholder('Chưa có email')
                    ->copyable()
                    ->wrap(),
                TextColumn::make('latestVisibleApplication.job.title')
                    ->label('Ứng tuyển gần nhất')
                    ->description(fn (Candidate $record): string => static::latestApplicationContext($record))
                    ->placeholder('Chưa có hồ sơ trong phạm vi')
                    ->wrap()
                    ->limit(48),
                TextColumn::make('latestVisibleApplication.status')
                    ->label('Giai đoạn hiện tại')
                    ->badge()
                    ->placeholder('Chưa xác định'),
                TextColumn::make('visible_applications_count')
                    ->label('Lượt ứng tuyển')
                    ->formatStateUsing(fn (int|string|null $state): string => ((int) $state).' lượt')
                    ->badge()
                    ->color(fn (int|string|null $state): string => (int) $state > 0 ? 'info' : 'gray')
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('blacklist')
                    ->label('Lưu ý')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->tooltip(fn (Candidate $record): string => $record->blacklist
                        ? ($record->blacklist_reason ?: 'Hồ sơ đang được đánh dấu cần lưu ý')
                        : 'Không có hạn chế tuyển dụng')
                    ->alignCenter(),
                TextColumn::make('latest_visible_applied_at')
                    ->label('Hoạt động gần nhất')
                    ->state(fn (Candidate $record): mixed => $record->latestVisibleApplication?->applied_at)
                    ->dateTime('H:i, d/m/Y')
                    ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(fn (Candidate $record): string => CandidateResource::getUrl('view', ['record' => $record]))
            ->defaultSort('latest_visible_applied_at', 'desc')
            ->filters([
                SelectFilter::make('application_status')
                    ->label('Trạng thái ứng tuyển')
                    ->options(collect(StatusApplicationEnum::cases())
                        ->mapWithKeys(fn (StatusApplicationEnum $status): array => [$status->value => (string) $status->getLabel()])
                        ->all())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'applications',
                            fn (Builder $applicationQuery): Builder => CandidateResource::scopeVisibleApplications($applicationQuery)
                                ->where('status', $data['value']),
                        );
                    }),
                TernaryFilter::make('has_cv')
                    ->label('CV hiện tại')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đã có CV')
                    ->falseLabel('Chưa có CV')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('cv_file'),
                        false: fn (Builder $query): Builder => $query->whereNull('cv_file'),
                    ),
                TernaryFilter::make('blacklist')
                    ->label('Hạn chế tuyển dụng')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang hạn chế')
                    ->falseLabel('Không hạn chế'),
            ])
            ->recordActions($recordActions)
            ->recordActionsColumnLabel('Thao tác')
            ->paginationPageOptions([10, 25, 50]);
    }

    protected static function latestApplicationContext(Candidate $record): string
    {
        $application = $record->latestVisibleApplication;

        if (! $application) {
            return 'Chưa có hoạt động tuyển dụng';
        }

        return collect([
            $application->job?->branch?->name,
            $application->applied_at?->copy()
                ->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                ->format('d/m/Y'),
        ])->filter()->implode(' · ');
    }
}

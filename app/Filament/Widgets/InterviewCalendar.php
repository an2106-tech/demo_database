<?php

namespace App\Filament\Widgets;

use App\Models\Interview;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InterviewCalendar extends TableWidget
{
    protected static ?string $heading = 'Lich phong van';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultSort('scheduled_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('application.candidate.name')
                    ->label('Ung vien')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('application.job.title')
                    ->label('Cong viec')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('interviewer.name')
                    ->label('Phong van vien')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label('Thoi gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('round_number')
                    ->label('Vong')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Hinh thuc')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'online' => 'Online',
                        'offline' => 'Offline',
                        default => $state ?? '-',
                    })
                    ->colors([
                        'info' => 'online',
                        'gray' => 'offline',
                    ]),
                TextColumn::make('result')
                    ->label('Ket qua')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pass' => 'Dat',
                        'fail' => 'Rot',
                        'pending' => 'Cho xu ly',
                        default => $state ?? '-',
                    })
                    ->colors([
                        'success' => 'pass',
                        'danger' => 'fail',
                        'warning' => 'pending',
                    ]),
                TextColumn::make('workplace.name')
                    ->label('Dia diem')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
                TextColumn::make('meeting_link')
                    ->label('Link')
                    ->limit(30)
                    ->url(fn (Interview $record): ?string => filled($record->meeting_link) ? $record->meeting_link : null)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')
                    ->label('Ghi chu')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('result')
                    ->label('Ket qua')
                    ->options([
                        'pending' => 'Cho xu ly',
                        'pass' => 'Dat',
                        'fail' => 'Rot',
                    ]),
                SelectFilter::make('type')
                    ->label('Hinh thuc')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                    ]),
                SelectFilter::make('interviewer_id')
                    ->label('Phong van vien')
                    ->options($this->getInterviewerOptions()),
            ], layout: FiltersLayout::AboveContent)
            ->paginationPageOptions([10, 25, 50])
            ->striped();
    }

    protected function getTableQuery(): Builder
    {
        /** @var User|null $user */
        $user = Auth::user();

        $query = Interview::query()
            ->with(['application.candidate', 'application.job', 'interviewer', 'workplace']);

        if ($user?->branchScopeId()) {
            $query->whereHas('application.job', function (Builder $jobQuery) use ($user): void {
                $jobQuery->where('branch_id', $user->branchScopeId());
            });
        }

        return $query;
    }

    protected function getInterviewerOptions(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        $query = User::query()
            ->where('is_active', true)
            ->whereHas('roles', function (Builder $query): void {
                $query->whereIn('name', ['director', 'pm', 'hr']);
            })
            ->orderBy('name');

        if ($user?->branchScopeId()) {
            $query->where('branch_id', $user->branchScopeId());
        }

        return $query
            ->pluck('name', 'id')
            ->all();
    }
}

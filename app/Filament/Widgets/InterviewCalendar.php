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
    protected static ?string $heading = "L\u{1ECB}ch ph\u{1ECF}ng v\u{1EA5}n";

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
                    ->label("\u{1EE8}ng vi\u{00EA}n")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('application.job.title')
                    ->label("C\u{00F4}ng vi\u{1EC7}c")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('interviewer.name')
                    ->label("Ph\u{1ECF}ng v\u{1EA5}n vi\u{00EA}n")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label("Th\u{1EDD}i gian")
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('round_number')
                    ->label("V\u{00F2}ng")
                    ->sortable(),
                TextColumn::make('type')
                    ->label("H\u{00EC}nh th\u{1EE9}c")
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
                    ->label("K\u{1EBF}t qu\u{1EA3}")
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pass' => "\u{0110}\u{1EA1}t",
                        'fail' => "R\u{1EDB}t",
                        'pending' => "Ch\u{1EDD} x\u{1EED} l\u{00FD}",
                        default => $state ?? '-',
                    })
                    ->colors([
                        'success' => 'pass',
                        'danger' => 'fail',
                        'warning' => 'pending',
                    ]),
                TextColumn::make('workplace.name')
                    ->label("\u{0110}\u{1ECB}a \u{0111}i\u{1EC3}m")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
                TextColumn::make('meeting_link')
                    ->label('Link')
                    ->limit(30)
                    ->url(fn (Interview $record): ?string => filled($record->meeting_link) ? $record->meeting_link : null)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')
                    ->label("Ghi ch\u{00FA}")
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('result')
                    ->label("K\u{1EBF}t qu\u{1EA3}")
                    ->options([
                        'pending' => "Ch\u{1EDD} x\u{1EED} l\u{00FD}",
                        'pass' => "\u{0110}\u{1EA1}t",
                        'fail' => "R\u{1EDB}t",
                    ]),
                SelectFilter::make('type')
                    ->label("H\u{00EC}nh th\u{1EE9}c")
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                    ]),
                SelectFilter::make('interviewer_id')
                    ->label("Ph\u{1ECF}ng v\u{1EA5}n vi\u{00EA}n")
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

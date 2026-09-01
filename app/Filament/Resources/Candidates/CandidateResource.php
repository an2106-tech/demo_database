<?php

namespace App\Filament\Resources\Candidates;

use App\Filament\Resources\Candidates\Pages\EditCandidate;
use App\Filament\Resources\Candidates\Pages\ListCandidates;
use App\Filament\Resources\Candidates\Pages\ViewCandidate;
use App\Filament\Resources\Candidates\Schemas\CandidateForm;
use App\Filament\Resources\Candidates\Schemas\CandidateInfolist;
use App\Filament\Resources\Candidates\Tables\CandidatesTable;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Ứng viên';

    protected static string|\UnitEnum|null $navigationGroup = 'Quản lý tuyển dụng';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'ứng viên';

    protected static ?string $pluralModelLabel = 'ứng viên';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CandidateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CandidatesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CandidateInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCandidates::route('/'),
            'view' => ViewCandidate::route('/{record}'),
            'edit' => EditCandidate::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::prepareQuery(parent::getRecordRouteBindingEloquentQuery());
    }

    public static function getEloquentQuery(): Builder
    {
        return static::prepareQuery(parent::getEloquentQuery());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return $record instanceof Candidate
            && static::canAdministerCandidates();
    }

    public static function canAdministerCandidates(): bool
    {
        $user = Auth::user();

        return static::isSuperAdmin($user)
            && $user->can('Update:Candidate');
    }

    public static function canManageRestrictions(Candidate $candidate): bool
    {
        return static::canAdministerCandidates()
            && ! $candidate->trashed();
    }

    public static function restrictAction(): Action
    {
        return Action::make('restrictRecruitment')
            ->label('Hạn chế tuyển dụng')
            ->icon('heroicon-o-exclamation-triangle')
            ->color('danger')
            ->visible(fn (Candidate $record): bool => ! $record->blacklist && static::canManageRestrictions($record))
            ->modalHeading('Đánh dấu hồ sơ cần hạn chế tuyển dụng')
            ->modalDescription('Lý do sẽ được lưu cùng người thực hiện và thời điểm cập nhật.')
            ->form([
                Textarea::make('reason')
                    ->label('Lý do')
                    ->rows(4)
                    ->maxLength(1000)
                    ->required(),
            ])
            ->action(function (Candidate $record, array $data): void {
                abort_unless(static::canManageRestrictions($record), 403);

                static::recordRestrictionChange($record, true, (string) $data['reason']);

                Notification::make()
                    ->title('Đã ghi nhận hạn chế tuyển dụng')
                    ->body($record->name)
                    ->warning()
                    ->send();
            })
            ->modalSubmitActionLabel('Xác nhận hạn chế');
    }

    public static function clearRestrictionAction(): Action
    {
        return Action::make('clearRecruitmentRestriction')
            ->label('Gỡ hạn chế')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Candidate $record): bool => $record->blacklist && static::canManageRestrictions($record))
            ->modalHeading('Gỡ hạn chế tuyển dụng?')
            ->form([
                Textarea::make('reason')
                    ->label('Lý do gỡ hạn chế')
                    ->rows(3)
                    ->maxLength(1000)
                    ->required(),
            ])
            ->action(function (Candidate $record, array $data): void {
                abort_unless(static::canManageRestrictions($record), 403);

                static::recordRestrictionChange($record, false, (string) $data['reason']);

                Notification::make()
                    ->title('Đã gỡ hạn chế tuyển dụng')
                    ->body($record->name)
                    ->success()
                    ->send();
            })
            ->modalSubmitActionLabel('Gỡ hạn chế');
    }

    public static function scopeVisibleApplications(Builder $query): Builder
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user?->branchScopeId()) {
            $query->whereHas(
                'job',
                fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $user->branchScopeId())
            );
        }

        return $query;
    }

    protected static function prepareQuery(Builder $query): Builder
    {
        static::scopeCandidatesByBranch($query);

        $latestApplicationId = Application::query()
            ->select('applications.id')
            ->whereColumn('applications.candidate_id', 'candidates.id')
            ->latest('applications.applied_at')
            ->latest('applications.id')
            ->limit(1);
        static::scopeVisibleApplications($latestApplicationId);

        $latestAppliedAt = Application::query()
            ->select('applications.applied_at')
            ->whereColumn('applications.candidate_id', 'candidates.id')
            ->latest('applications.applied_at')
            ->latest('applications.id')
            ->limit(1);
        static::scopeVisibleApplications($latestAppliedAt);

        return $query
            ->addSelect([
                'latest_visible_application_id' => $latestApplicationId,
                'latest_visible_applied_at' => $latestAppliedAt,
            ])
            ->with([
                'user',
                'resume',
                'blacklistedBy',
                'latestVisibleApplication.job.branch',
            ])
            ->withCount([
                'applications as visible_applications_count' => fn (Builder $applicationQuery): Builder => static::scopeVisibleApplications($applicationQuery),
            ]);
    }

    protected static function scopeCandidatesByBranch(Builder $query): Builder
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user?->branchScopeId()) {
            $query->whereHas(
                'applications',
                fn (Builder $applicationQuery): Builder => static::scopeVisibleApplications($applicationQuery),
            );
        }

        return $query;
    }

    protected static function recordRestrictionChange(Candidate $record, bool $restricted, string $reason): void
    {
        $reason = trim($reason);
        $metadata = is_array($record->metadata) ? $record->metadata : [];
        $history = collect((array) ($metadata['recruitment_restriction_history'] ?? []))
            ->take(-19)
            ->values()
            ->all();
        $history[] = [
            'action' => $restricted ? 'restricted' : 'cleared',
            'reason' => $reason,
            'actor_id' => Auth::id(),
            'recorded_at' => now()->toIso8601String(),
        ];
        $metadata['recruitment_restriction_history'] = $history;

        $record->update([
            'blacklist' => $restricted,
            'blacklist_reason' => $restricted ? $reason : null,
            'blacklisted_at' => $restricted ? now() : null,
            'blacklisted_by' => $restricted ? Auth::id() : null,
            'metadata' => $metadata,
        ]);
    }

    protected static function isSuperAdmin(?User $user): bool
    {
        return (bool) $user
            && ($user->role === 'admin' || $user->isSuperAdmin());
    }
}

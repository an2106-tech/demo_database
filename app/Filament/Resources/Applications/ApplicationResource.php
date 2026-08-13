<?php

namespace App\Filament\Resources\Applications;

use App\Filament\Resources\Applications\Pages\EditApplication;
use App\Filament\Resources\Applications\Pages\CreateApplication;
use App\Filament\Resources\Applications\Pages\KanbanApplications;
use App\Filament\Resources\Applications\Pages\ListApplications;
use App\Filament\Resources\Applications\Pages\ViewApplication;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Ứng tuyển';

    protected static ?string $modelLabel = 'ứng tuyển';

    protected static ?string $pluralModelLabel = 'ứng tuyển';

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        if (! $record instanceof Application) {
            return null;
        }

        return $record->snapshotCandidateName() ?: 'Hồ sơ #'.$record->id;
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApplicationInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'create' => CreateApplication::route('/create'),
            'kanban' => KanbanApplications::route('/kanban'),
            'view' => ViewApplication::route('/{record}'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        return (bool) $user
            && static::currentUserCanManageHrPipeline($user)
            && $user->can('Create:Application');
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return $record instanceof Application
            && (bool) $user
            && static::currentUserCanManageHrPipeline($user)
            && static::currentUserCanAccessApplicationBranch($user, $record)
            && $user->can('Update:Application');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return (bool) $user
            && static::currentUserCanOverseeRecruitment($user)
            && $user->can('Delete:Application');
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();

        return (bool) $user
            && static::currentUserCanOverseeRecruitment($user)
            && $user->can('DeleteAny:Application');
    }

    public static function canForceDelete(Model $record): bool
    {
        $user = Auth::user();

        return (bool) $user
            && static::currentUserCanOverseeRecruitment($user)
            && $user->can('ForceDelete:Application');
    }

    public static function canForceDeleteAny(): bool
    {
        $user = Auth::user();

        return (bool) $user
            && static::currentUserCanOverseeRecruitment($user)
            && $user->can('ForceDeleteAny:Application');
    }

    public static function canRestore(Model $record): bool
    {
        $user = Auth::user();

        return (bool) $user
            && static::currentUserCanOverseeRecruitment($user)
            && $user->can('Restore:Application');
    }

    public static function canRestoreAny(): bool
    {
        $user = Auth::user();

        return (bool) $user
            && static::currentUserCanOverseeRecruitment($user)
            && $user->can('RestoreAny:Application');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'candidate',
                'cvAttachment',
                'job.branch',
                'job.department',
                'latestInterview.interviewer',
                'latestInterview.workplace',
                'latestOffer.approvedByUser',
                'latestOffer.letterTemplate',
                'latestScorecard.evaluator',
                'latestScorecard.template',
                'latestPreScreening',
                'latestScreeningAiAnalysis',
                'latestInterviewQuestionAiAnalysis',
            ]);

        /** @var User|null $user */
        $user = Auth::user();
        if ($user?->branchScopeId()) {
            $query->whereHas(
                'job',
                fn (Builder $jobQuery) => $jobQuery->where('branch_id', $user->branchScopeId())
            );
        }

        return $query;
    }

    private static function currentUserCanOverseeRecruitment(User $user): bool
    {
        return $user->isSuperAdmin() || $user->role === 'admin';
    }

    private static function currentUserIsHr(User $user): bool
    {
        return $user->role === 'hr' || $user->hasRole('hr');
    }

    private static function currentUserCanManageHrPipeline(User $user): bool
    {
        return static::currentUserCanOverseeRecruitment($user) || static::currentUserIsHr($user);
    }

    private static function currentUserCanAccessApplicationBranch(User $user, Application $record): bool
    {
        if (static::currentUserCanOverseeRecruitment($user)) {
            return true;
        }

        $scopeBranchId = $user->branchScopeId();
        $applicationBranchId = $record->branch_id ?: $record->job?->branch_id;

        return $scopeBranchId !== null
            && $applicationBranchId !== null
            && (int) $scopeBranchId === (int) $applicationBranchId;
    }
}

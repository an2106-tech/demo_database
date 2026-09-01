<?php

namespace App\Filament\Resources\RecruitmentJobs;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Filament\Resources\RecruitmentJobs\Pages\CreateRecruitmentJob;
use App\Filament\Resources\RecruitmentJobs\Pages\EditRecruitmentJob;
use App\Filament\Resources\RecruitmentJobs\Pages\ListRecruitmentJobs;
use App\Filament\Resources\RecruitmentJobs\Pages\ViewRecruitmentJob;
use App\Filament\Resources\RecruitmentJobs\Schemas\RecruitmentJobForm;
use App\Filament\Resources\RecruitmentJobs\Schemas\RecruitmentJobInfolist;
use App\Filament\Resources\RecruitmentJobs\Tables\RecruitmentJobsTable;
use App\Models\RecruitmentJob;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RecruitmentJobResource extends Resource
{
    protected static ?string $model = RecruitmentJob::class;

    protected static ?string $navigationLabel = 'Tin tuyển dụng';

    protected static string|\UnitEnum|null $navigationGroup = 'Quản lý tuyển dụng';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralModelLabel = 'Quản lý tin tuyển dụng';

    protected static ?string $modelLabel = 'tin tuyển dụng';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return RecruitmentJobForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecruitmentJobsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RecruitmentJobInfolist::configure($schema);
    }

    public static function getNavigationBadge(): ?string
    {
        if (! Auth::check()) {
            return null;
        }

        return (string) static::getEloquentQuery()
            ->where('status', 'published')
            ->count();
    }

    public static function canManageLifecycle(RecruitmentJob $record): bool
    {
        return parent::canEdit($record);
    }

    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record)
            && $record instanceof RecruitmentJob
            && $record->status === StatusRecruitmentJobsEnum::DRAFT;
    }

    public static function canDelete(Model $record): bool
    {
        return parent::canDelete($record)
            && $record instanceof RecruitmentJob
            && $record->status === StatusRecruitmentJobsEnum::DRAFT
            && ! $record->applications()->exists();
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecruitmentJobs::route('/'),
            'create' => CreateRecruitmentJob::route('/create'),
            'view' => ViewRecruitmentJob::route('/{record}'),
            'edit' => EditRecruitmentJob::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var User|null $user */
        $user = Auth::user();
        if ($user?->branchScopeId()) {
            $query->where('branch_id', $user->branchScopeId());
        }

        return $query
            ->with([
                'branch',
                'department',
                'workplace',
                'creator',
                'categories',
                'skills',
            ])
            ->withCount('applications');
    }
}

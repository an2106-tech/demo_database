<?php

namespace App\Filament\Resources\RecruitmentJobs;

use App\Filament\Resources\RecruitmentJobs\Pages\CreateRecruitmentJob;
use App\Filament\Resources\RecruitmentJobs\Pages\EditRecruitmentJob;
use App\Filament\Resources\RecruitmentJobs\Pages\ListRecruitmentJobs;
use App\Filament\Resources\RecruitmentJobs\Pages\ViewRecruitmentJob;
use App\Filament\Resources\RecruitmentJobs\Schemas\RecruitmentJobForm;
use App\Filament\Resources\RecruitmentJobs\Tables\RecruitmentJobsTable;
use App\Models\RecruitmentJob;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecruitmentJobResource extends Resource
{
    protected static ?string $model = RecruitmentJob::class;

    protected static ?string $navigationLabel = 'Tin tuyển dụng';

    protected static ?string $pluralModelLabel = 'Quản lý tin tuyển dụng';

    protected static ?string $modelLabel = 'tin tuyển dụng';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return RecruitmentJobForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecruitmentJobsTable::configure($table);
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

        return $query;
    }
}
<?php

namespace App\Filament\Resources\Candidates;

use App\Filament\Resources\Candidates\Pages\EditCandidate;
use App\Filament\Resources\Candidates\Pages\ListCandidates;
use App\Filament\Resources\Candidates\Pages\ViewCandidate;
use App\Filament\Resources\Candidates\Schemas\CandidateForm;
use App\Filament\Resources\Candidates\Tables\CandidatesTable;
use App\Models\Candidate;
use App\Models\User;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Ứng viên';

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
        return $schema->components([
            Section::make('Thông tin ứng viên')
                ->columns(2)
                ->schema([
                    TextEntry::make('name')->label('Họ và tên'),
                    TextEntry::make('email')->label('Email')->placeholder('-'),
                    TextEntry::make('phone')->label('Số điện thoại')->placeholder('-'),
                    TextEntry::make('experience_years')->label('Kinh nghiệm')->formatStateUsing(fn ($state): string => $state ? "{$state} năm" : '-'),
                    TextEntry::make('user.email')->label('Tài khoản liên kết')->placeholder('Chưa liên kết'),
                    TextEntry::make('applications_count')->label('Số lần ứng tuyển'),
                ]),
            Section::make('CV')
                ->columns(1)
                ->schema([
                    TextEntry::make('cv_file')
                        ->label('Đường dẫn CV')
                        ->placeholder('-'),
                    TextEntry::make('metadata.cv_text_excerpt')
                        ->label('Trích xuất CV')
                        ->placeholder('Chưa có dữ liệu trích xuất')
                        ->prose(),
                ]),
        ]);
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
        return static::scopeByBranch(
            parent::getRecordRouteBindingEloquentQuery()
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ])
        );
    }

    public static function getEloquentQuery(): Builder
    {
        return static::scopeByBranch(parent::getEloquentQuery())
            ->withCount('applications');
    }

    protected static function scopeByBranch(Builder $query): Builder
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user?->branchScopeId()) {
            $query->whereHas(
                'applications.job',
                fn (Builder $jobQuery) => $jobQuery->where('branch_id', $user->branchScopeId())
            );
        }

        return $query;
    }
}

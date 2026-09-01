<?php

namespace App\Filament\Resources\Workplaces;

use App\Filament\Resources\Workplaces\Pages\CreateWorkplace;
use App\Filament\Resources\Workplaces\Pages\EditWorkplace;
use App\Filament\Resources\Workplaces\Pages\ListWorkplaces;
use App\Filament\Resources\Workplaces\Pages\ViewWorkplaces;
use App\Filament\Resources\Workplaces\Schemas\WorkplaceForm;
use App\Filament\Resources\Workplaces\Tables\WorkplacesTable;
use App\Models\User;
use App\Models\Workplace;
use BackedEnum;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class WorkplaceResource extends Resource
{
    protected static ?string $model = Workplace::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $navigationLabel = 'Địa điểm làm việc';

    protected static string|\UnitEnum|null $navigationGroup = 'Cơ cấu tổ chức';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'địa điểm làm việc';

    protected static ?string $pluralModelLabel = 'địa điểm làm việc';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WorkplaceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkplacesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin chung')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Tên'),
                        TextEntry::make('code')->label('Mã')->copyable(),
                        TextEntry::make('capacity')->label('Sức chứa'),
                        TextEntry::make('branch.name')->label('Chi nhánh')->placeholder('-'),
                    ]),
                Section::make('Thông tin hệ thống')
                    ->schema([
                        IconEntry::make('is_interview_room')->label('Phòng phỏng vấn')->boolean(),
                        IconEntry::make('is_active')->label('Đang hoạt động')->boolean(),
                        TextEntry::make('created_at')->label('Ngày tạo')->dateTime(),
                    ])->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkplaces::route('/'),
            'create' => CreateWorkplace::route('/create'),
            'edit' => EditWorkplace::route('/{record}/edit'),
            'view' => ViewWorkplaces::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $query = parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        /** @var User|null $user */
        $user = Auth::user();
        if ($user?->branchScopeId()) {
            $query->where('branch_id', $user->branchScopeId());
        }

        return $query;
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

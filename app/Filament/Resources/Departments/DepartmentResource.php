<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Filament\Resources\Departments\Schemas\DepartmentForm;
use App\Filament\Resources\Departments\Tables\DepartmentsTable;
use App\Models\Department;
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

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $navigationLabel = 'Phòng ban';

    protected static string|\UnitEnum|null $navigationGroup = 'Cơ cấu tổ chức';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'phòng ban';

    protected static ?string $pluralModelLabel = 'phòng ban';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DepartmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin phòng ban')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Tên'),
                        TextEntry::make('code')->label('Mã')->copyable(),
                        TextEntry::make('branch.name')->label('Chi nhánh')->placeholder('-'),
                        TextEntry::make('description')->label('Mô tả')->placeholder('-')->prose()->columnSpanFull(),
                    ]),
                Section::make('Hệ thống')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->label('Ngày tạo')->dateTime(),
                        TextEntry::make('updated_at')->label('Cập nhật lần cuối')->dateTime(),
                        TextEntry::make('deleted_at')->label('Đã xóa lúc')->dateTime()
                            ->visible(fn (?Department $record): bool => filled($record?->deleted_at)),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartments::route('/'),
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

<?php

namespace App\Filament\Resources\Workplaces;

use App\Filament\Resources\Workplaces\Pages\CreateWorkplace;
use App\Filament\Resources\Workplaces\Pages\EditWorkplace;
use App\Filament\Resources\Workplaces\Pages\ListWorkplaces;
use App\Filament\Resources\Workplaces\Schemas\WorkplaceForm;
use App\Filament\Resources\Workplaces\Tables\WorkplacesTable;
use App\Models\Workplace;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkplaceResource extends Resource
{
    protected static ?string $model = Workplace::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WorkplaceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkplacesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkplaces::route('/'),
            'create' => CreateWorkplace::route('/create'),
            'edit' => EditWorkplace::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

<?php

namespace App\Filament\Resources\Workplaces;

use App\Filament\Resources\Workplaces\Pages\CreateWorkplace;
use App\Filament\Resources\Workplaces\Pages\EditWorkplace;
use App\Filament\Resources\Workplaces\Pages\ListWorkplaces;
use App\Filament\Resources\Workplaces\Pages\ViewWorkplaces;
use App\Filament\Resources\Workplaces\Schemas\WorkplaceForm;
use App\Filament\Resources\Workplaces\Tables\WorkplacesTable;
use App\Models\Workplace;
use BackedEnum;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkplaceResource extends Resource
{
    protected static ?string $model = Workplace::class;

    protected static string|BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedMapPin;

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
                Section::make('Thông tin Chung')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('code')->label('Code')->copyable(),
                        TextEntry::make('capacity')->label('Capacity'),
                        TextEntry::make('branch.name')->label('Branch')->placeholder('-'),
                    ]),
                Section::make('Thông tin hệ thống')
                    ->schema([
                        IconEntry::make('is_interview_room')->label('Is Interview Room')->boolean(),
                        IconEntry::make('is_active')->label('Is Active')->boolean(),
                        TextEntry::make('created_at')->label('Created At')->dateTime(),
                    ])->columns(2),
            ]);
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
            'view' => ViewWorkplaces::route('/{record}'),
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

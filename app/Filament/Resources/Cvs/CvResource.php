<?php

namespace App\Filament\Resources\Cvs;

use App\Filament\Resources\Cvs\Pages\CreateCv;
use App\Filament\Resources\Cvs\Pages\EditCv;
use App\Filament\Resources\Cvs\Pages\ListCvs;
use App\Filament\Resources\Cvs\Pages\ViewCv;
use App\Filament\Resources\Cvs\Schemas\CvForm;
use App\Filament\Resources\Cvs\Schemas\CvInfolist;
use App\Filament\Resources\Cvs\Tables\CvsTable;
use App\Models\Cv;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CvResource extends Resource
{
    protected static ?string $model = Cv::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return CvForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CvInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CvsTable::configure($table);
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
            'index' => ListCvs::route('/'),
            'create' => CreateCv::route('/create'),
            'view' => ViewCv::route('/{record}'),
            'edit' => EditCv::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(150),

            TextInput::make('code')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true),

            Select::make('branch_id')
                ->relationship('branch', 'name')
                ->searchable()
                ->preload()
                ->nullable(),

            Textarea::make('description')
                ->columnSpanFull()
                ->nullable(),
        ])->columns(1);
    }
}

<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('icon'),
                FileUpload::make('image')
                    ->label('Hình ảnh')
                    ->image()
                    ->directory('categories')
                    ->disk('public')
                    ->visibility('public')
                    ->imagePreviewHeight('150')
                     ->nullable(),
                Toggle::make('status')
                    ->required(),
            ]);
    }
}

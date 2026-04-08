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
                    ->label('Tên danh mục')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                TextInput::make('icon')
                    ->label('Icon'),
                FileUpload::make('image')
                    ->label('Hình ảnh')
                    ->image()
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/svg+xml',
                    ])
                    ->maxSize(5 * 1024)
                    ->directory('categories')
                    ->disk('public')
                    ->visibility('public')
                    ->imagePreviewHeight('150')
                    ->nullable(),
                Toggle::make('status')
                    ->label('Hiển thị')
                    ->required(),
            ]);
    }
}

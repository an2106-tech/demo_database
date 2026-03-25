<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;


class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->label('Branch Name'),
                TextInput::make('code')
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->nullable(),
                TextInput::make('city')
                    ->required()
                    ->maxLength(100),
                TextInput::make('province_code')
                    ->maxLength(10)
                    ->nullable(),
                Textarea::make('address')
                    ->columnSpanFull()
                    ->nullable(),
                TextInput::make('phone')
                    ->maxLength(20)
                    ->nullable(),
                TextInput::make('email_contact')
                    ->email()
                    ->nullable(),
                TextInput::make('latitude')
                    ->numeric()
                    ->nullable(),
                TextInput::make('longitude')
                    ->numeric()
                    ->nullable(),
                Toggle::make('is_headquarters')
                    ->default(false),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}

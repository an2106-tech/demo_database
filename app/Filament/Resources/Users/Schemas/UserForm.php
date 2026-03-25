<?php

namespace App\Filament\Resources\Users\Schemas;

use Dom\Text;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn(?string $state) => $state)
                    ->dehydrated(fn(?string $state) => filled($state))
                    ->required(fn(string $operation) => $operation === 'create'),
                Select::make('role')
                    ->options([
                        'admin' => 'admin',
                        'hr' => 'hr',
                        'director' => 'director',
                        'pm' => 'pm',
                        'leader' => 'leader',
                    ])
                    ->default('pm')
                    ->required(),

                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('avatar')->maxLength(255)->nullable(),

                Toggle::make('is_active')->default(true),

                Textarea::make('metadata')->columnSpanFull()->nullable(),
            ]);
    }
}

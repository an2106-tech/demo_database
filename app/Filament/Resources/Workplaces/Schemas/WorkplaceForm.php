<?php

namespace App\Filament\Resources\Workplaces\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorkplaceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options([
                        'office' => 'office',
                        'meeting_room' => 'meeting_room',
                        'interview_room' => 'interview_room',
                        'remote' => 'remote',
                        'other' => 'other',
                    ])->default('office')->required(),
                TextInput::make('floor')
                    ->maxLength(20)
                    ->nullable(),
                TextInput::make('room')
                    ->maxLength(50)
                    ->nullable(),
                TextInput::make('capacity')
                    ->numeric()
                    ->nullable(),
                Textarea::make('directions')
                    ->columnSpanFull()
                    ->nullable(),
                TextInput::make('map_url')
                    ->maxLength(1000)
                    ->nullable(),

                Toggle::make('is_interview_room')
                    ->default(false),
                Toggle::make('is_active')
                    ->default(true),


            ]);
    }
}

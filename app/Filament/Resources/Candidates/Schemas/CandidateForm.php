<?php

namespace App\Filament\Resources\Candidates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Điều chỉnh thông tin cơ bản')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Họ và tên')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->maxLength(50)
                            ->nullable(),
                        TextInput::make('experience_years')
                            ->label('Số năm kinh nghiệm')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(80)
                            ->nullable(),
                    ]),
            ])
            ->columns(1);
    }
}

<?php

namespace App\Filament\Resources\Users\Schemas;

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
                TextInput::make('name')
                    ->label('Họ và tên')
                    ->required(),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->label('Mật khẩu')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn (?string $state) => $state)
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create'),

                Select::make('role')
                    ->label('Vai trò')
                    ->options([
                        'admin' => 'Super Admin',
                        'hr' => 'Nhân sự',
                        'director' => 'Giám đốc',
                        'pm' => 'Quản lý dự án',
                    ])
                    ->default('pm')
                    ->required(),

                Select::make('branch_id')
                    ->label('Chi nhánh')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('avatar')
                    ->label('Ảnh đại diện (URL)')
                    ->maxLength(255)
                    ->nullable(),

                Toggle::make('is_active')
                    ->label('Đang hoạt động')
                    ->default(true),

                Textarea::make('metadata')
                    ->label('Metadata')
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }
}

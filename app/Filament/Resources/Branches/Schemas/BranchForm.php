<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Enums\VietnamProvince;
use Filament\Forms\Components\Select;
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
                TextInput::make('name')
                ->label('Tên chi nhánh')
                ->placeholder('FPT Polytechnic Cơ sở Cần Thơ')
                ->required()
                ->minLength(3)
                ->maxLength(255)
                ->dehydrateStateUsing(fn ($state) =>trim($state)),

                TextInput::make('code')
                ->label('Mã chi nhánh')
                    ->placeholder('CT01')
                    ->required()
                    ->maxLength(20)
                    ->unique(table: 'branches', column: 'code', ignoreRecord: true)
                    ->dehydrateStateUsing(fn ($state) =>trim($state)),

                Select::make('city')
                    ->label('Thành phố')
                    ->options(VietnamProvince::options())
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('province_code')
                    ->label('Mã tỉnh')
                    ->placeholder('900000')
                    ->maxLength(10)
                    ->dehydrateStateUsing(fn ($state) =>$state ? trim($state) : null),

                Textarea::make('address')
                    ->label('Địa chỉ')
                    ->placeholder('Đường số 22, Phường Cái Răng, TP. Cần Thơ')
                    ->columnSpanFull()
                    ->dehydrateStateUsing(fn ($state) =>$state ? trim($state) : null),

                TextInput::make('phone')
                    ->label('Số điện thoại')
                    ->placeholder('097468XXXX')
                    ->length(10)
                    ->nullable(),

                TextInput::make('email_contact')
                    ->label('Email liên hệ')
                    ->placeholder('example@fpoly.edu.com')
                    ->email()
                    ->maxLength(255)
                    ->unique(table:'branches', column: 'email_contact', ignoreRecord: true)
                    ->nullable(),

                TextInput::make('latitude')
                    ->label('Vĩ độ')
                    ->numeric()
                    ->rule('between:-90,90')
                    ->nullable(),

                TextInput::make('longitude')
                    ->label('Kinh độ')
                    ->numeric()
                    ->rule('between:-180,180')
                    ->nullable(),
                    
                Toggle::make('is_headquarters')
                    ->label('Trụ sở chính')
                    ->default(false),

                Toggle::make('is_active')
                    ->label('Đang hoạt động')
                    ->default(true),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Services\AdminUserManagementGuard;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

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
                    ->options(fn (?User $record): array => app(AdminUserManagementGuard::class)
                        ->roleOptions(Auth::user(), $record))
                    ->default(fn (): string => Auth::user()?->role === 'director' ? 'hr' : 'pm')
                    ->disabled(fn (?User $record): bool => $record?->is(Auth::user()) ?? false)
                    ->live()
                    ->required(),

                Select::make('branch_id')
                    ->label('Chi nhánh')
                    ->options(fn (?User $record): array => app(AdminUserManagementGuard::class)
                        ->branchOptions(Auth::user(), $record))
                    ->searchable()
                    ->preload()
                    ->default(fn () => Auth::user()?->branchScopeId())
                    ->visible(fn (Get $get): bool => in_array($get('role'), AdminUserManagementGuard::BRANCH_ROLES, true))
                    ->required(fn (Get $get): bool => in_array($get('role'), AdminUserManagementGuard::BRANCH_ROLES, true))
                    ->disabled(fn (?User $record): bool => (bool) Auth::user()?->branchScopeId() || ($record?->is(Auth::user()) ?? false))
                    ->dehydrated(),

                TextInput::make('avatar')
                    ->label('Ảnh đại diện (URL)')
                    ->maxLength(255)
                    ->nullable(),

                Toggle::make('is_active')
                    ->label('Đang hoạt động')
                    ->disabled(fn (?User $record): bool => $record?->is(Auth::user()) ?? false)
                    ->default(true),
            ]);
    }
}

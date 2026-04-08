<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Tên phòng ban')
                ->required()
                ->maxLength(150),

            TextInput::make('code')
                ->label('Mã phòng ban')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true),

            Select::make('branch_id')
                ->label('Chi nhánh')
                ->options(function (): array {
                    /** @var \App\Models\User|null $user */
                    $user = Auth::user();

                    if ($user?->branchScopeId()) {
                        return Branch::query()
                            ->whereKey($user->branchScopeId())
                            ->pluck('name', 'id')
                            ->all();
                    }

                    return Branch::query()->orderBy('name')->pluck('name', 'id')->all();
                })
                ->searchable()
                ->preload()
                ->default(fn () => Auth::user()?->branchScopeId())
                ->disabled(fn (): bool => (bool) Auth::user()?->branchScopeId())
                ->nullable(),

            Textarea::make('description')
                ->label('Mô tả')
                ->columnSpanFull()
                ->nullable(),
        ])->columns(1);
    }
}
<?php

namespace App\Filament\Resources\Workplaces\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class WorkplaceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->required(),
                TextInput::make('name')
                    ->label('Tên địa điểm')
                    ->required(),
                Select::make('type')
                    ->label('Loại')
                    ->options([
                        'office' => 'Văn phòng',
                        'meeting_room' => 'Phòng họp',
                        'interview_room' => 'Phòng phỏng vấn',
                        'remote' => 'Làm việc từ xa',
                        'other' => 'Khác',
                    ])->default('office')->required(),
                TextInput::make('floor')
                    ->label('Tầng')
                    ->maxLength(20)
                    ->nullable(),
                TextInput::make('room')
                    ->label('Phòng')
                    ->maxLength(50)
                    ->nullable(),
                TextInput::make('capacity')
                    ->label('Sức chứa')
                    ->numeric()
                    ->nullable(),
                Textarea::make('directions')
                    ->label('Chỉ đường / ghi chú')
                    ->columnSpanFull()
                    ->nullable(),
                TextInput::make('map_url')
                    ->label('Liên kết bản đồ')
                    ->maxLength(1000)
                    ->nullable(),
                Toggle::make('is_interview_room')
                    ->label('Là phòng phỏng vấn')
                    ->default(false),
                Toggle::make('is_active')
                    ->label('Đang hoạt động')
                    ->default(true),
            ]);
    }
}
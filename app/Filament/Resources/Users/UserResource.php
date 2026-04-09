<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Người dùng';

    protected static ?string $modelLabel = 'người dùng';

    protected static ?string $pluralModelLabel = 'người dùng';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make([
                'default' => 1,
                'xl' => 3,
            ])->schema([

                // 👉 AVATAR
                Section::make('Ảnh đại diện')
                    ->columnSpan(1)
                    ->schema([
                        ImageEntry::make('avatar')
                            ->hiddenLabel()
                            ->circular()
                            ->height(120)
                            ->alignCenter()
                            ->state(function (User $record): string {
                                if (! filled($record->avatar)) {
                                    return asset('assets/img/avatar_detail.jpg');
                                }

                                $avatar = (string) $record->avatar;

                                if (str_starts_with($avatar, 'http')) {
                                    return $avatar;
                                }

                                return asset('storage/' . ltrim($avatar, '/'));
                            }),
                    ]),

                // 👉 THÔNG TIN CHÍNH (gộp lại cho gọn)
                Section::make('Thông tin người dùng')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Tên'),
                        TextEntry::make('email')->label('Email'),

                        TextEntry::make('branch.name')
                            ->label('Chi nhánh')
                            ->placeholder('-'),

                        TextEntry::make('role')
                            ->label('Vai trò')
                            ->badge()
                            ->color('warning')
                            ->formatStateUsing(fn($state) => match ($state) {
                                'admin' => 'Super Admin',
                                'hr' => 'Nhân sự',
                                'director' => 'Giám đốc',
                                'pm' => 'Quản lý dự án',
                                default => $state,
                            }),

                        IconEntry::make('is_active')
                            ->label('Hoạt động')
                            ->boolean(),

                        TextEntry::make('created_at')
                            ->label('Ngày tạo')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Cập nhật')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('deleted_at')
                            ->label('Đã xóa')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ]),
            ]),

            // 👉 METADATA (FULL WIDTH)
            Section::make('Xét duyệt & Metadata')
                ->columns(2)
                ->schema([
                    TextEntry::make('metadata.approval_status')
                        ->label('Trạng thái duyệt')
                        ->badge()
                        ->formatStateUsing(fn($state) => match ($state) {
                            'pending' => 'Chờ duyệt',
                            'approved' => 'Đã duyệt',
                            'rejected' => 'Từ chối',
                            default => '-',
                        }),

                    TextEntry::make('metadata.rejected_reason')
                        ->label('Lý do từ chối')
                        ->placeholder('-'),

                    TextEntry::make('metadata.approved_at')
                        ->label('Duyệt lúc')
                        ->formatStateUsing(
                            fn($state) =>
                            blank($state) ? '-' : Carbon::parse($state)->format('d/m/Y H:i')
                        ),

                    TextEntry::make('metadata.rejected_at')
                        ->label('Từ chối lúc')
                        ->formatStateUsing(
                            fn($state) =>
                            blank($state) ? '-' : Carbon::parse($state)->format('d/m/Y H:i')
                        ),

                    TextEntry::make('metadata')
                        ->label('Metadata JSON')
                        ->columnSpanFull()
                        ->prose()
                        ->formatStateUsing(
                            fn($state) =>
                            blank($state)
                                ? '-'
                                : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            'view' => ViewUser::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $query = parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        /** @var User|null $user */
        $user = Auth::user();
        if ($user?->branchScopeId()) {
            $query->where('branch_id', $user->branchScopeId());
        }

        return $query;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var User|null $user */
        $user = Auth::user();
        if ($user?->branchScopeId()) {
            $query->where('branch_id', $user->branchScopeId());
        }

        return $query;
    }
}

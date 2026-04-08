<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        $canApproveHr = static function (): bool {
            $current = Auth::user();
            if (! $current) {
                return false;
            }

            if (method_exists($current, 'hasRole') && $current->hasRole('super_admin')) {
                return true;
            }

            return in_array($current->role, ['admin', 'director'], true);
        };

        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')->label('Họ và tên')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
                TextColumn::make('branch.name')->label('Chi nhánh')->searchable(),
                TextColumn::make('approval')
                    ->label('Xét duyệt')
                    ->badge()
                    ->getStateUsing(static function (User $record): string {
                        if ($record->role !== 'hr') {
                            return '-';
                        }

                        $status = is_array($record->metadata) ? ($record->metadata['approval_status'] ?? null) : null;

                        return match ($status) {
                            'pending' => 'Chờ duyệt',
                            'approved' => 'Đã duyệt',
                            'rejected' => 'Từ chối',
                            default => $record->is_active ? 'Đã duyệt' : 'Chờ duyệt',
                        };
                    })
                    ->colors([
                        'warning' => 'Chờ duyệt',
                        'success' => 'Đã duyệt',
                        'danger' => 'Từ chối',
                        'gray' => '-',
                    ]),
                IconColumn::make('is_active')->label('Hoạt động')->boolean(),
                TextColumn::make('created_at')->label('Ngày tạo')->dateTime()->sortable(),
            ])
            ->filters([
                Filter::make('pending_hr')
                    ->label('HR chờ duyệt')
                    ->query(static function (Builder $query): Builder {
                        return $query
                            ->where('role', 'hr')
                            ->where('is_active', false)
                            ->where(static function (Builder $q): Builder {
                                return $q
                                    ->where('metadata->approval_status', 'pending')
                                    ->orWhereNull('metadata');
                            });
                    }),
                TrashedFilter::make()->label('Đã xóa'),
            ])
            ->recordActions([
                ViewAction::make()->modal()->label('Xem'),

                ActionGroup::make([
                    Action::make('approve_hr')
                        ->label('Duyệt')
                        ->color('success')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->modalHeading('Duyệt tài khoản HR')
                        ->modalDescription('Tài khoản sẽ được kích hoạt và có thể truy cập các chức năng nhà tuyển dụng.')
                        ->visible(static function (User $record) use ($canApproveHr): bool {
                            if (! $canApproveHr()) {
                                return false;
                            }

                            $status = is_array($record->metadata) ? ($record->metadata['approval_status'] ?? null) : null;

                            return $record->role === 'hr' && ! $record->is_active && ($status === 'pending' || $status === null);
                        })
                        ->action(static function (User $record): void {
                            $metadata = is_array($record->metadata) ? $record->metadata : [];
                            $metadata['approval_status'] = 'approved';
                            $metadata['approved_at'] = now()->toISOString();
                            $metadata['approved_by'] = Auth::id();

                            $record->metadata = $metadata;
                            $record->is_active = true;
                            $record->save();
                        }),

                    Action::make('reject_hr')
                        ->label('Từ chối')
                        ->color('danger')
                        ->icon('heroicon-o-x-mark')
                        ->requiresConfirmation()
                        ->modalHeading('Từ chối tài khoản HR')
                        ->form([
                            Textarea::make('reason')
                                ->label('Lý do')
                                ->required()
                                ->rows(3),
                        ])
                        ->visible(static function (User $record) use ($canApproveHr): bool {
                            if (! $canApproveHr()) {
                                return false;
                            }

                            $status = is_array($record->metadata) ? ($record->metadata['approval_status'] ?? null) : null;

                            return $record->role === 'hr' && ! $record->is_active && ($status === 'pending' || $status === null);
                        })
                        ->action(static function (User $record, array $data): void {
                            $metadata = is_array($record->metadata) ? $record->metadata : [];
                            $metadata['approval_status'] = 'rejected';
                            $metadata['rejected_at'] = now()->toISOString();
                            $metadata['rejected_by'] = Auth::id();
                            $metadata['rejected_reason'] = trim((string) ($data['reason'] ?? ''));

                            $record->metadata = $metadata;
                            $record->is_active = false;
                            $record->save();
                        }),
                ])
                    ->label('Xét duyệt HR')
                    ->icon('heroicon-o-shield-check')
                    ->color('gray')
                    ->visible(static function (User $record) use ($canApproveHr): bool {
                        if (! $canApproveHr()) {
                            return false;
                        }

                        if ($record->role !== 'hr') {
                            return false;
                        }

                        if ($record->is_active) {
                            return false;
                        }

                        $status = is_array($record->metadata) ? ($record->metadata['approval_status'] ?? null) : null;

                        return $status === 'pending' || $status === null;
                    }),

                EditAction::make()->modal()->label('Sửa')->modalSubmitActionLabel('Lưu'),
                DeleteAction::make()->label('Xóa'),
            ])
            ->recordActionsColumnLabel('Thao tác')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Xóa'),
                    ForceDeleteBulkAction::make()->label('Xóa vĩnh viễn'),
                    RestoreBulkAction::make()->label('Khôi phục'),
                ]),
            ]);
    }
}


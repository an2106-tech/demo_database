<?php

namespace App\Filament\Resources\Departments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')->label('Tên')->searchable()->sortable(),
                TextColumn::make('code')->label('Mã')->searchable()->sortable(),
                TextColumn::make('branch.name')->label('Chi nhánh')->searchable(),
                TextColumn::make('created_at')->label('Ngày tạo')->dateTime()->sortable(),
            ])
            ->filters([
                TrashedFilter::make()->label('Đã xóa'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modal()
                    ->label('Xem'),
                EditAction::make()
                    ->modal()
                    ->label('Sửa')
                    ->modalSubmitActionLabel('Lưu'),
                DeleteAction::make()
                    ->label('Xóa'),
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
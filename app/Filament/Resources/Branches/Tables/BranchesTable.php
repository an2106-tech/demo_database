<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Enums\VietnamProvince;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('image')
                    ->label('Ảnh chi nhánh')
                    ->html()
                    ->formatStateUsing(fn (?string $state): HtmlString => new HtmlString(
                        $state
                            ? '<img src="/storage/' . e($state) . '" alt="Ảnh chi nhánh" style="width:36px;height:36px;border-radius:9999px;object-fit:cover;" />'
                            : '<span style="color:#94a3b8;">-</span>'
                    )),
                TextColumn::make('name')
                    ->label('Tên chi nhánh')
                    ->getStateUsing(fn($record) => 
                        $record->name . '(' . $record->code . ')'
                    )
                    ->description(fn($record) => VietnamProvince::tryFrom($record->city)?->label() ?? $record->city)
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_headquarters')
                    ->label('Trụ sở chính')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Đang hoạt động')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->modifyQueryUsing(function ($query) {
                return $query->orderByDesc('created_at');
            })
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

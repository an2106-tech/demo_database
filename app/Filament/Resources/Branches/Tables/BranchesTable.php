<?php

namespace App\Filament\Resources\Branches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->getStateUsing(function ($record) {
                    return $record->name.'('.$record->code.')';
                })
                ->description(function ($record) {
                    return $record->name.'('.$record->code.')';
                })
                ->searchable()
                ->sortable(),

                TextColumn::make('code')
                ->copyable()
                ->searchable()
                ->sortable(),

                TextColumn::make('city')
                ->searchable(),

                IconColumn::make('is_headquarters')
                ->boolean(),

                IconColumn::make('is_active')
                ->boolean(),

                TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->modifyQueryUsing(function($query) {
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

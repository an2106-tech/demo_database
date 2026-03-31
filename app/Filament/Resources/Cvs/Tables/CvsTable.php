<?php

namespace App\Filament\Resources\Cvs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CvsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('candidate.name')
                    ->label('Ứng viên')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Tên CV')
                    ->searchable(),
                IconColumn::make('is_default')
                    ->label('CV chính')
                    ->boolean(),
                 TextColumn::make('file')
                    ->label('File CV')
                    ->url(fn ($record) => asset('storage/' . $record->file))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

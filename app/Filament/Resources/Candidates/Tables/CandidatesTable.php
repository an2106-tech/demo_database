<?php

namespace App\Filament\Resources\Candidates\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CandidatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Họ và tên')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('experience_years')
                    ->label('Kinh nghiệm')
                    ->formatStateUsing(fn ($state): string => $state ? "{$state} năm" : '-')
                    ->sortable(),
                TextColumn::make('applications_count')
                    ->label('Số lần ứng tuyển')
                    ->sortable(),
                IconColumn::make('blacklist')
                    ->label('Blacklist')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                ViewAction::make()->label('Xem'),
                EditAction::make()->label('Sửa'),
            ])
            ->recordActionsColumnLabel('Thao tác');
    }
}

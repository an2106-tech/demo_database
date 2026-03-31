<?php

namespace App\Filament\Resources\Cvs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CvForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('candidate_id')
                    ->label('Ứng viên')
                    ->relationship('candidate', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('title')
                    ->label('Tiêu đề CV')
                    ->placeholder('VD: CV Laravel, CV Fresher')
                    ->required(),

                FileUpload::make('file')
                    ->label('File CV')
                    ->directory('cv')
                    ->acceptedFileTypes(['application/pdf'])
                    ->required(),

                Toggle::make('is_default')
                    ->label('CV chính'),
            ]);
    }
}

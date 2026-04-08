<?php

namespace App\Filament\Resources\Candidates\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Họ và tên')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->nullable(),
                TextInput::make('phone')
                    ->label('Số điện thoại')
                    ->nullable(),
                TextInput::make('experience_years')
                    ->label('Số năm kinh nghiệm')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
                FileUpload::make('cv_file')
                    ->label('CV hiện tại')
                    ->disk('public')
                    ->directory('candidates/cv')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->maxSize(10 * 1024)
                    ->nullable(),
                Toggle::make('blacklist')
                    ->label('Đưa vào danh sách đen')
                    ->default(false),
                Textarea::make('blacklist_reason')
                    ->label('Lý do blacklist')
                    ->rows(3)
                    ->visible(fn (callable $get): bool => (bool) $get('blacklist'))
                    ->nullable(),
                Textarea::make('metadata.cv_text_excerpt')
                    ->label('Nội dung CV trích xuất')
                    ->rows(8)
                    ->disabled()
                    ->dehydrated(false),
            ])
            ->columns(2);
    }
}

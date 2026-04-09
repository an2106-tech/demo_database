<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('title')
                    ->label('Tiêu đề')
                    ->placeholder('-'),

                TextEntry::make('slug')
                    ->label('Slug')
                    ->placeholder('-'),

                TextEntry::make('excerpt')
                    ->label('Mô tả ngắn')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('content')
                    ->label('Nội dung')
                    ->placeholder('-')
                    ->columnSpanFull()
                   ,

                ImageEntry::make('image')
                    ->label('Hình đại diện')
                    ->placeholder('-')
                    ->disk('public'),

                TextEntry::make('views')
                    ->label('Lượt xem')
                    ->numeric()
                    ->placeholder('0'),

                TextEntry::make('comments_count')
                    ->label('Số bình luận')
                    ->numeric()
                    ->placeholder('0'),
                IconEntry::make('status')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextEntry::make('published_at')
                    ->label('Ngày đăng')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('Ngày cập nhật')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

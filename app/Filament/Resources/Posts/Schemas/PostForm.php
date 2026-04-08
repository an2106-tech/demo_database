<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([

                    TextInput::make('title')
                        ->label('Tiêu đề')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            fn($state, callable $set) =>
                            $set('slug', Str::slug($state))
                        )
                        ->columnSpanFull()
                        ->helperText('Tiêu đề bài viết. Slug sẽ tự tạo từ đây.'),

                    TextInput::make('slug')
                        ->label('Slug (đường dẫn)')
                        ->columnSpanFull()
                        ->helperText('Có thể chỉnh thủ công nếu muốn'),


                    TextInput::make('excerpt')
                        ->label('Mô tả ngắn')
                        ->columnSpanFull()
                        ->helperText('Hiển thị trong danh sách bài viết hoặc SEO'),


                    RichEditor::make('content')
                        ->label('Nội dung bài viết')
                        ->required()
                        ->columnSpanFull()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'blockquote',
                            'link',
                            'codeBlock',
                            'bulletList',
                            'orderedList',
                            'h2',
                            'h3',
                            'undo',
                            'redo',
                            'attachFiles'
                        ])
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('posts/content'),

                    FileUpload::make('image')
                        ->label('Hình đại diện')
                        ->image()
                        ->directory('posts')
                        ->imagePreviewHeight('200')
                        ->columnSpanFull()
                        ->helperText('Hình nên 800x600px để hiển thị đẹp'),



                    Toggle::make('status')
                        ->label('Trạng thái (Hiển thị)')
                        ->default(true),

                    DateTimePicker::make('published_at')
                        ->label('Ngày đăng')
                        ->seconds(false),

                ]),

                Grid::make(2)->schema([

                    TextInput::make('views')
                        ->label('Lượt xem')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->columnSpanFull(),

                    TextInput::make('comments_count')
                        ->label('Số bình luận')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->columnSpanFull(),

                ]),
            ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kế toán & Tài chính',
                'image' => 'categories/ketoan.jpg'
            ],
            [
                'name' => 'Tư vấn tài chính',
                'image' => 'categories/nhansu.jpg'
            ],
            [
                'name' => 'Kế hoạch kinh doanh',
                'image' => 'categories/nhansu.jpg'
            ],
            [
                'name' => 'Công nghệ thông tin',
                'image' => 'categories/IT.jpg'
            ],
            [
                'name' => 'Marketing',
                'image' => 'categories/maketing.jpg'
            ],
            [
                'name' => 'Thiết kế đồ họa',
                'image' => 'categories/dohoa.jpg'
            ],
            [
                'name' => 'Giáo dục',
                'image' => 'categories/giaoduc.jpg'
            ],
            [
                'name' => 'Nghiên cứu thị trường',
                'image' => 'categories/nhansu.jpg'
            ],
            [
                'name' => 'Bán hàng',
                'image' => 'categories/banhang.jpg'
            ],
            [
                'name' => 'Cơ khí',
                'image' => 'categories/xaydung.jpg'
            ],
            [
                'name' => 'Khác',
                'image' => ''
            ],
        ];
        foreach ($categories as $item) {
            Category::create([
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'icon' => null,
                'image' => $item['image'],
                'status' => 1, 
            ]);
        }
    }
}

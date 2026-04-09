<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kế toán & Tài chính',
                'image' => 'assets/img/categories/ketoan.jpg'
            ],
            [
                'name' => 'Tư vấn tài chính',
                'image' => 'assets/img/categories/nhansu.jpg'
            ],
            [
                'name' => 'Kế hoạch kinh doanh',
                'image' => 'assets/img/categories/nhansu.jpg'
            ],
            [
                'name' => 'Công nghệ thông tin',
                'image' => 'assets/img/categories/IT.jpg'
            ],
            [
                'name' => 'Marketing',
                'image' => 'assets/img/categories/maketing.jpg'
            ],
            [
                'name' => 'Thiết kế đồ họa',
                'image' => 'assets/img/categories/dohoa.jpg'
            ],
            [
                'name' => 'Giáo dục',
                'image' => 'assets/img/categories/giaoduc.jpg'
            ],
            [
                'name' => 'Nghiên cứu thị trường',
                'image' => 'assets/img/categories/nhansu.jpg'
            ],
            [
                'name' => 'Bán hàng',
                'image' => 'assets/img/categories/banhang.jpg'
            ],
            [
                'name' => 'Cơ khí',
                'image' => 'assets/img/categories/xaydung.jpg'
            ],
            [
                'name' => 'Khác',
                'image' => ''
            ],
        ];

        foreach ($categories as $item) {
            $category = Category::withTrashed()->firstOrNew([
                'slug' => Str::slug($item['name']),
            ]);

            $category->fill([
                'name' => $item['name'],
                'icon' => null,
                'image' => $item['image'],
                'status' => true,
            ]);

            if ($category->trashed()) {
                $category->restore();
            }

            $category->save();
        }
    }
}

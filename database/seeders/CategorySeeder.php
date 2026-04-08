<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $categories = [
            'Kế toán & Tài chính',
            'Tư vấn tài chính',
            'Kế hoạch kinh doanh',
            'Công nghệ thông tin',
            'Maketing',
            'Thuyết kế đồ họa',
            'Giáo dục',
            'Nghiên cứu thị trường',
            'Bán hàng',
            'Cơ khí',
            'Khác'
        ];

        foreach ($categories as $item) {
            Category::create([
                'name' => $item,
                'slug' => Str::slug($item)
            ]);
        }
    }
}

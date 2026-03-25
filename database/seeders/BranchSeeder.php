<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::updateOrCreate(
            ['code' => 'HCM-HQ'],
            [
                'name' => 'FPT HCM (HQ)',
                'city' => 'Ho Chi Minh',
                'province_code' => '79',
                'address' => 'Lot T2, D1 Street, Saigon Hi-Tech Park, Thu Duc, HCMC',
                'phone' => '02812345678',
                'email_contact' => 'hr.hcm@fpt.com',
                'latitude' => 10.8515,
                'longitude' => 106.7976,
                'is_headquarters' => true,
                'is_active' => true,
            ],
        );

        Branch::updateOrCreate(
            ['code' => 'HN'],
            [
                'name' => 'FPT Ha Noi',
                'city' => 'Ha Noi',
                'province_code' => '01',
                'address' => 'Cau Giay, Ha Noi',
                'phone' => '02412345678',
                'email_contact' => 'hr.hn@fpt.com',
                'is_headquarters' => false,
                'is_active' => true,
            ],
        );
    }
}

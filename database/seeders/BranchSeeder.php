<?php

namespace Database\Seeders;

use App\Enums\VietnamProvince;
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
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
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
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Cau Giay, Ha Noi',
                'phone' => '02412345678',
                'email_contact' => 'hr.hn@fpt.com',
                'is_headquarters' => false,
                'is_active' => true,
            ],
        );
    }
}

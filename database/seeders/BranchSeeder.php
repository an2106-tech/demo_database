<?php

namespace Database\Seeders;

use App\Enums\VietnamProvince;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $polytechnicImage = $this->storeBranchImageFromAssets(
            '2020-FPTPolytechic-20220408102211.jpg',
            'branches/fpt-polytechnic.jpg'
        );
        $fptUniversityImage = $this->storeBranchImageFromAssets(
            '2021-FPTU-Long-20211213154624.jpg',
            'branches/fpt-university.jpg'
        );
        $fptSchoolsImage = $this->storeBranchImageFromAssets(
            '2021-PhoThongFPT E-20240311104819.png',
            'branches/fpt-schools.png'
        );
        $asiaImage = $this->storeBranchImageFromAssets(
            'asia-university-semiconductor.png',
            'branches/asia-university.png'
        );
        $metropoliaImage = $this->storeBranchImageFromAssets(
            'metropolia-alliance.png',
            'branches/metropolia.png'
        );
        $faiImage = $this->storeBranchImageFromAssets(
            '2017-FAI-01.jpg',
            'branches/fai.jpg'
        );
        $fsbImage = $this->storeBranchImageFromAssets(
            '2017-FSB-Eng-01.jpg',
            'branches/fsb.jpg'
        );
        $fptEducationImage = $this->storeBranchImageFromAssets(
            '2017-FE-01-20210226144936.jpg',
            'branches/fpt-education.jpg'
        );
        $swinburneImage = $this->storeBranchImageFromAssets(
            'Logo_Web_Swinburne.jpg',
            'branches/swinburne.jpg'
        );
        $gachonImage = $this->storeBranchImageFromAssets(
            'Logo Gachon alliance FPT-20260116150216.png',
            'branches/gachon.png'
        );

        // ============================================================
        // Trường Cao đẳng FPT Polytechnic
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'POLY-HCM'],
            [
                'name' => 'Trường Cao đẳng FPT Polytechnic - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345678',
                'email_contact' => 'poly.hcm@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'POLY-HN'],
            [
                'name' => 'Trường Cao đẳng FPT Polytechnic - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345678',
                'email_contact' => 'poly.hn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'POLY-DN'],
            [
                'name' => 'Trường Cao đẳng FPT Polytechnic - Đà Nẵng',
                'city' => VietnamProvince::DA_NANG->value,
                'province_code' => VietnamProvince::DA_NANG->provinceCode(),
                'address' => 'Đà Nẵng',
                'phone' => '02312345678',
                'email_contact' => 'poly.dn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'POLY-CT'],
            [
                'name' => 'Trường Cao đẳng FPT Polytechnic - Cần Thơ',
                'city' => VietnamProvince::CAN_THO->value,
                'province_code' => VietnamProvince::CAN_THO->provinceCode(),
                'address' => 'Cần Thơ',
                'phone' => '02923456789',
                'email_contact' => 'poly.ct@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'POLY-HP'],
            [
                'name' => 'Trường Cao đẳng FPT Polytechnic - Hải Phòng',
                'city' => VietnamProvince::HAI_PHONG->value,
                'province_code' => VietnamProvince::HAI_PHONG->provinceCode(),
                'address' => 'Hải Phòng',
                'phone' => '02253456789',
                'email_contact' => 'poly.hp@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'POLY-QN'],
            [
                'name' => 'Trường Cao đẳng FPT Polytechnic - Quảng Ninh',
                'city' => VietnamProvince::QUANG_NINH->value,
                'province_code' => VietnamProvince::QUANG_NINH->provinceCode(),
                'address' => 'Quảng Ninh',
                'phone' => '02033456789',
                'email_contact' => 'poly.qn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        // ============================================================
        // Trường Đại học FPT
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'FPTU-HCM'],
            [
                'name' => 'Trường Đại học FPT - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Lot E2a-7, D1 Street, Saigon Hi-Tech Park, Thu Duc, HCMC',
                'phone' => '02812345679',
                'email_contact' => 'fptu.hcm@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTU-HN'],
            [
                'name' => 'Trường Đại học FPT - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Khu Công nghệ cao Hòa Lạc, Km29 Đại lộ Thăng Long, Hà Nội',
                'phone' => '02412345679',
                'email_contact' => 'fptu.hn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTU-DN'],
            [
                'name' => 'Trường Đại học FPT - Đà Nẵng',
                'city' => VietnamProvince::DA_NANG->value,
                'province_code' => VietnamProvince::DA_NANG->provinceCode(),
                'address' => 'Khu đô thị FPT City, Ngũ Hành Sơn, Đà Nẵng',
                'phone' => '02312345679',
                'email_contact' => 'fptu.dn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTU-CT'],
            [
                'name' => 'Trường Đại học FPT - Cần Thơ',
                'city' => VietnamProvince::CAN_THO->value,
                'province_code' => VietnamProvince::CAN_THO->provinceCode(),
                'address' => 'Cần Thơ',
                'phone' => '02923456780',
                'email_contact' => 'fptu.ct@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        // ============================================================
        // Hệ thống Trường Phổ thông FPT (FPT Schools)
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'FPTSCHOOLS-HCM'],
            [
                'name' => 'Hệ thống Trường Phổ thông FPT - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345680',
                'email_contact' => 'fptschools.hcm@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTSCHOOLS-HN'],
            [
                'name' => 'Hệ thống Trường Phổ thông FPT - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345680',
                'email_contact' => 'fptschools.hn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTSCHOOLS-DN'],
            [
                'name' => 'Hệ thống Trường Phổ thông FPT - Đà Nẵng',
                'city' => VietnamProvince::DA_NANG->value,
                'province_code' => VietnamProvince::DA_NANG->provinceCode(),
                'address' => 'Đà Nẵng',
                'phone' => '02312345680',
                'email_contact' => 'fptschools.dn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        // ============================================================
        // Greenwich Việt Nam
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'GREENWICH-HCM'],
            [
                'name' => 'Greenwich Việt Nam - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345681',
                'email_contact' => 'greenwich.hcm@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'GREENWICH-HN'],
            [
                'name' => 'Greenwich Việt Nam - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345681',
                'email_contact' => 'greenwich.hn@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'GREENWICH-DN'],
            [
                'name' => 'Greenwich Việt Nam - Đà Nẵng',
                'city' => VietnamProvince::DA_NANG->value,
                'province_code' => VietnamProvince::DA_NANG->provinceCode(),
                'address' => 'Đà Nẵng',
                'phone' => '02312345681',
                'email_contact' => 'greenwich.dn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'GREENWICH-CT'],
            [
                'name' => 'Greenwich Việt Nam - Cần Thơ',
                'city' => VietnamProvince::CAN_THO->value,
                'province_code' => VietnamProvince::CAN_THO->provinceCode(),
                'address' => 'Cần Thơ',
                'phone' => '02923456781',
                'email_contact' => 'greenwich.ct@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        // ============================================================
        // Viện Quản trị & Công nghệ FSB
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'FSB-HCM'],
            [
                'name' => 'Viện Quản trị & Công nghệ FSB - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345682',
                'email_contact' => 'fsb.hcm@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FSB-HN'],
            [
                'name' => 'Viện Quản trị & Công nghệ FSB - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345682',
                'email_contact' => 'fsb.hn@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        // ============================================================
        // Trung tâm Liên kết quốc tế FPT (FAI)
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'FAI-HCM'],
            [
                'name' => 'Trung tâm Liên kết quốc tế FPT (FAI) - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345683',
                'email_contact' => 'fai.hcm@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FAI-HN'],
            [
                'name' => 'Trung tâm Liên kết quốc tế FPT (FAI) - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345683',
                'email_contact' => 'fai.hn@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FAI-DN'],
            [
                'name' => 'Trung tâm Liên kết quốc tế FPT (FAI) - Đà Nẵng',
                'city' => VietnamProvince::DA_NANG->value,
                'province_code' => VietnamProvince::DA_NANG->provinceCode(),
                'address' => 'Đà Nẵng',
                'phone' => '02312345683',
                'email_contact' => 'fai.dn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        // ============================================================
        // Swinburne Việt Nam
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'SWINBURNE-HCM'],
            [
                'name' => 'Swinburne Việt Nam - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345684',
                'email_contact' => 'swinburne.hcm@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'SWINBURNE-HN'],
            [
                'name' => 'Swinburne Việt Nam - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345684',
                'email_contact' => 'swinburne.hn@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'SWINBURNE-DN'],
            [
                'name' => 'Swinburne Việt Nam - Đà Nẵng',
                'city' => VietnamProvince::DA_NANG->value,
                'province_code' => VietnamProvince::DA_NANG->provinceCode(),
                'address' => 'Đà Nẵng',
                'phone' => '02312345684',
                'email_contact' => 'swinburne.dn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        // ============================================================
        // FPT PolySchool
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'POLYSCHOOL-HCM'],
            [
                'name' => 'FPT PolySchool - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345685',
                'email_contact' => 'polyschool.hcm@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'POLYSCHOOL-HN'],
            [
                'name' => 'FPT PolySchool - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345685',
                'email_contact' => 'polyschool.hn@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        // ============================================================
        // Tổ chức Giáo dục FPT
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'FPTEDU-HCM'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345686',
                'email_contact' => 'fptedu.hcm@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-HN'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345686',
                'email_contact' => 'fptedu.hn@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-DN'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Đà Nẵng',
                'city' => VietnamProvince::DA_NANG->value,
                'province_code' => VietnamProvince::DA_NANG->provinceCode(),
                'address' => 'Đà Nẵng',
                'phone' => '02312345686',
                'email_contact' => 'fptedu.dn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-CT'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Cần Thơ',
                'city' => VietnamProvince::CAN_THO->value,
                'province_code' => VietnamProvince::CAN_THO->provinceCode(),
                'address' => 'Cần Thơ',
                'phone' => '02923456782',
                'email_contact' => 'fptedu.ct@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-HP'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Hải Phòng',
                'city' => VietnamProvince::HAI_PHONG->value,
                'province_code' => VietnamProvince::HAI_PHONG->provinceCode(),
                'address' => 'Hải Phòng',
                'phone' => '02253456782',
                'email_contact' => 'fptedu.hp@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-NA'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Nghệ An',
                'city' => VietnamProvince::NGHE_AN->value,
                'province_code' => VietnamProvince::NGHE_AN->provinceCode(),
                'address' => 'Nghệ An',
                'phone' => '02383456789',
                'email_contact' => 'fptedu.na@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-TH'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Thanh Hóa',
                'city' => VietnamProvince::THANH_HOA->value,
                'province_code' => VietnamProvince::THANH_HOA->provinceCode(),
                'address' => 'Thanh Hóa',
                'phone' => '02373456789',
                'email_contact' => 'fptedu.th@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-HUE'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Huế',
                'city' => VietnamProvince::THUA_THIEN_HUE->value,
                'province_code' => VietnamProvince::THUA_THIEN_HUE->provinceCode(),
                'address' => 'Huế',
                'phone' => '02343456789',
                'email_contact' => 'fptedu.hue@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-QNM'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Quảng Nam',
                'city' => VietnamProvince::QUANG_NAM->value,
                'province_code' => VietnamProvince::QUANG_NAM->provinceCode(),
                'address' => 'Quảng Nam',
                'phone' => '02353456789',
                'email_contact' => 'fptedu.qnm@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-KH'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Khánh Hòa',
                'city' => VietnamProvince::KHANH_HOA->value,
                'province_code' => VietnamProvince::KHANH_HOA->provinceCode(),
                'address' => 'Khánh Hòa',
                'phone' => '02583456789',
                'email_contact' => 'fptedu.kh@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-BD'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Bình Dương',
                'city' => VietnamProvince::BINH_DUONG->value,
                'province_code' => VietnamProvince::BINH_DUONG->provinceCode(),
                'address' => 'Bình Dương',
                'phone' => '02743456789',
                'email_contact' => 'fptedu.bd@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-DN2'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Đồng Nai',
                'city' => VietnamProvince::DONG_NAI->value,
                'province_code' => VietnamProvince::DONG_NAI->provinceCode(),
                'address' => 'Đồng Nai',
                'phone' => '02513456789',
                'email_contact' => 'fptedu.dongnai@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-BRVT'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Bà Rịa - Vũng Tàu',
                'city' => VietnamProvince::BA_RIA_VUNG_TAU->value,
                'province_code' => VietnamProvince::BA_RIA_VUNG_TAU->provinceCode(),
                'address' => 'Bà Rịa - Vũng Tàu',
                'phone' => '02543456789',
                'email_contact' => 'fptedu.brvt@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-GL'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Gia Lai',
                'city' => VietnamProvince::GIA_LAI->value,
                'province_code' => VietnamProvince::GIA_LAI->provinceCode(),
                'address' => 'Gia Lai',
                'phone' => '02693456789',
                'email_contact' => 'fptedu.gl@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-DL'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Đắk Lắk',
                'city' => VietnamProvince::DAK_LAK->value,
                'province_code' => VietnamProvince::DAK_LAK->provinceCode(),
                'address' => 'Đắk Lắk',
                'phone' => '02623456789',
                'email_contact' => 'fptedu.dl@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-KG'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Kiên Giang',
                'city' => VietnamProvince::KIEN_GIANG->value,
                'province_code' => VietnamProvince::KIEN_GIANG->provinceCode(),
                'address' => 'Kiên Giang',
                'phone' => '02973456789',
                'email_contact' => 'fptedu.kg@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-HG'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Hậu Giang',
                'city' => VietnamProvince::HAU_GIANG->value,
                'province_code' => VietnamProvince::HAU_GIANG->provinceCode(),
                'address' => 'Hậu Giang',
                'phone' => '02933456789',
                'email_contact' => 'fptedu.hg@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-CM'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Cà Mau',
                'city' => VietnamProvince::CA_MAU->value,
                'province_code' => VietnamProvince::CA_MAU->provinceCode(),
                'address' => 'Cà Mau',
                'phone' => '02903456789',
                'email_contact' => 'fptedu.cm@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-ST'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Sóc Trăng',
                'city' => VietnamProvince::SOC_TRANG->value,
                'province_code' => VietnamProvince::SOC_TRANG->provinceCode(),
                'address' => 'Sóc Trăng',
                'phone' => '02993456789',
                'email_contact' => 'fptedu.st@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-TN'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Tây Ninh',
                'city' => VietnamProvince::TAY_NINH->value,
                'province_code' => VietnamProvince::TAY_NINH->provinceCode(),
                'address' => 'Tây Ninh',
                'phone' => '02763456789',
                'email_contact' => 'fptedu.tn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-BP'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Bình Phước',
                'city' => VietnamProvince::BINH_PHUOC->value,
                'province_code' => VietnamProvince::BINH_PHUOC->provinceCode(),
                'address' => 'Bình Phước',
                'phone' => '02713456789',
                'email_contact' => 'fptedu.bp@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-BG'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Bắc Giang',
                'city' => VietnamProvince::BAC_GIANG->value,
                'province_code' => VietnamProvince::BAC_GIANG->provinceCode(),
                'address' => 'Bắc Giang',
                'phone' => '02043456789',
                'email_contact' => 'fptedu.bg@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-BN'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Bắc Ninh',
                'city' => VietnamProvince::BAC_NINH->value,
                'province_code' => VietnamProvince::BAC_NINH->provinceCode(),
                'address' => 'Bắc Ninh',
                'phone' => '02223456789',
                'email_contact' => 'fptedu.bn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-HNA'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Hà Nam',
                'city' => VietnamProvince::HA_NAM->value,
                'province_code' => VietnamProvince::HA_NAM->provinceCode(),
                'address' => 'Hà Nam',
                'phone' => '02263456789',
                'email_contact' => 'fptedu.hnam@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-NDH'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Nam Định',
                'city' => VietnamProvince::NAM_DINH->value,
                'province_code' => VietnamProvince::NAM_DINH->provinceCode(),
                'address' => 'Nam Định',
                'phone' => '02283456789',
                'email_contact' => 'fptedu.nd@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-NB'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Ninh Bình',
                'city' => VietnamProvince::NINH_BINH->value,
                'province_code' => VietnamProvince::NINH_BINH->provinceCode(),
                'address' => 'Ninh Bình',
                'phone' => '02293456789',
                'email_contact' => 'fptedu.nb@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-PT'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Phú Thọ',
                'city' => VietnamProvince::PHU_THO->value,
                'province_code' => VietnamProvince::PHU_THO->provinceCode(),
                'address' => 'Phú Thọ',
                'phone' => '02103456789',
                'email_contact' => 'fptedu.pt@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-TGN'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Thái Nguyên',
                'city' => VietnamProvince::THAI_NGUYEN->value,
                'province_code' => VietnamProvince::THAI_NGUYEN->provinceCode(),
                'address' => 'Thái Nguyên',
                'phone' => '02083456789',
                'email_contact' => 'fptedu.tng@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-VP'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Vĩnh Phúc',
                'city' => VietnamProvince::VINH_PHUC->value,
                'province_code' => VietnamProvince::VINH_PHUC->provinceCode(),
                'address' => 'Vĩnh Phúc',
                'phone' => '02113456789',
                'email_contact' => 'fptedu.vp@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPTEDU-BDH'],
            [
                'name' => 'Tổ chức Giáo dục FPT - Bình Định',
                'city' => VietnamProvince::BINH_DINH->value,
                'province_code' => VietnamProvince::BINH_DINH->provinceCode(),
                'address' => 'Bình Định',
                'phone' => '02563456789',
                'email_contact' => 'fptedu.bdinh@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        // ============================================================
        // Trung tâm Cao đẳng Quốc tế FPT (FPI)
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'FPI-HCM'],
            [
                'name' => 'Trung tâm Cao đẳng Quốc tế FPT (FPI) - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345687',
                'email_contact' => 'fpi.hcm@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'FPI-HN'],
            [
                'name' => 'Trung tâm Cao đẳng Quốc tế FPT (FPI) - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345687',
                'email_contact' => 'fpi.hn@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        // ============================================================
        // ASIA University
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'ASIA-HCM'],
            [
                'name' => 'ASIA University - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345688',
                'email_contact' => 'asia.hcm@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'ASIA-HN'],
            [
                'name' => 'ASIA University - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345688',
                'email_contact' => 'asia.hn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        // ============================================================
        // Finland Metropolia Vietnam
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'METROPOLIA-HCM'],
            [
                'name' => 'Finland Metropolia Vietnam - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345689',
                'email_contact' => 'metropolia.hcm@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'METROPOLIA-HN'],
            [
                'name' => 'Finland Metropolia Vietnam - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345689',
                'email_contact' => 'metropolia.hn@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        // ============================================================
        // Gachon Vietnam
        // ============================================================
        Branch::updateOrCreate(
            ['code' => 'GACHON-HCM'],
            [
                'name' => 'Gachon Vietnam - Hồ Chí Minh',
                'city' => VietnamProvince::HO_CHI_MINH->value,
                'province_code' => VietnamProvince::HO_CHI_MINH->provinceCode(),
                'address' => 'Hồ Chí Minh',
                'phone' => '02812345690',
                'email_contact' => 'gachon.hcm@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'GACHON-HN'],
            [
                'name' => 'Gachon Vietnam - Hà Nội',
                'city' => VietnamProvince::HA_NOI->value,
                'province_code' => VietnamProvince::HA_NOI->provinceCode(),
                'address' => 'Hà Nội',
                'phone' => '02412345690',
                'email_contact' => 'gachon.hn@fpt.edu.vn',
                'is_headquarters' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'GACHON-DN'],
            [
                'name' => 'Gachon Vietnam - Đà Nẵng',
                'city' => VietnamProvince::DA_NANG->value,
                'province_code' => VietnamProvince::DA_NANG->provinceCode(),
                'address' => 'Đà Nẵng',
                'phone' => '02312345690',
                'email_contact' => 'gachon.dn@fpt.edu.vn',
                'is_headquarters' => false,
                'is_active' => true,
            ]
        );

        $this->applyImageToBranches(
            ['POLY-HCM', 'POLY-HN', 'POLY-DN', 'POLY-CT', 'POLY-HP', 'POLY-QN'],
            $polytechnicImage
        );
        $this->applyImageToBranches(
            ['FPTU-HCM', 'FPTU-HN', 'FPTU-DN', 'FPTU-CT'],
            $fptUniversityImage
        );
        $this->applyImageToBranches(
            ['FPTSCHOOLS-HCM', 'FPTSCHOOLS-HN', 'FPTSCHOOLS-DN'],
            $fptSchoolsImage
        );
        $this->applyImageToBranches(
            ['POLYSCHOOL-HCM', 'POLYSCHOOL-HN'],
            $fptSchoolsImage
        );
        $this->applyImageToBranches(
            ['GREENWICH-HCM', 'GREENWICH-HN', 'GREENWICH-DN', 'GREENWICH-CT'],
            $fptEducationImage
        );
        $this->applyImageToBranches(
            ['FSB-HCM', 'FSB-HN'],
            $fsbImage
        );
        $this->applyImageToBranches(
            ['FAI-HCM', 'FAI-HN', 'FAI-DN'],
            $faiImage
        );
        $this->applyImageToBranches(
            ['SWINBURNE-HCM', 'SWINBURNE-HN', 'SWINBURNE-DN'],
            $swinburneImage
        );
        $this->applyImageToBranches(
            ['FPI-HCM', 'FPI-HN'],
            $fptEducationImage
        );
        $this->applyImageToBranches(
            ['ASIA-HCM', 'ASIA-HN'],
            $asiaImage
        );
        $this->applyImageToBranches(
            ['METROPOLIA-HCM', 'METROPOLIA-HN'],
            $metropoliaImage
        );
        $this->applyImageToBranches(
            [
                'FPTEDU-HCM',
                'FPTEDU-HN',
                'FPTEDU-DN',
                'FPTEDU-CT',
                'FPTEDU-HP',
                'FPTEDU-NA',
                'FPTEDU-TH',
                'FPTEDU-HUE',
                'FPTEDU-QNM',
                'FPTEDU-KH',
                'FPTEDU-BD',
                'FPTEDU-DN2',
                'FPTEDU-BRVT',
                'FPTEDU-GL',
                'FPTEDU-DL',
                'FPTEDU-KG',
                'FPTEDU-HG',
                'FPTEDU-CM',
                'FPTEDU-ST',
                'FPTEDU-TN',
                'FPTEDU-BP',
                'FPTEDU-BG',
                'FPTEDU-BN',
                'FPTEDU-HNA',
                'FPTEDU-NDH',
                'FPTEDU-NB',
                'FPTEDU-PT',
                'FPTEDU-TGN',
                'FPTEDU-VP',
                'FPTEDU-BDH',
            ],
            $fptEducationImage
        );
        $this->applyImageToBranches(
            ['GACHON-HCM', 'GACHON-HN', 'GACHON-DN'],
            $gachonImage
        );
    }

    private function storeBranchImageFromAssets(string $assetFilename, string $destinationPath): ?string
    {
        $sourcePath = public_path('assets/img/' . $assetFilename);

        if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
            return null;
        }

        $destinationPath = str_replace('\\', '/', $destinationPath);
        $disk = Storage::disk('public');

        if (! $disk->exists($destinationPath)) {
            $contents = file_get_contents($sourcePath);
            if ($contents === false) {
                return null;
            }

            $disk->put($destinationPath, $contents);
        }

        return $destinationPath;
    }

    private function applyImageToBranches(array $codes, ?string $imagePath): void
    {
        if (empty($imagePath)) {
            return;
        }

        Branch::query()
            ->whereIn('code', $codes)
            ->update(['image' => $imagePath]);
    }
}

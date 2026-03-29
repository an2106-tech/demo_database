<?php

namespace App\Enums;

enum VietnamProvince: string
{
    case AN_GIANG = 'an_giang';
    case BA_RIA_VUNG_TAU = 'ba_ria_vung_tau';
    case BAC_GIANG = 'bac_giang';
    case BAC_KAN = 'bac_kan';
    case BAC_LIEU = 'bac_lieu';
    case BAC_NINH = 'bac_ninh';
    case BEN_TRE = 'ben_tre';
    case BINH_DINH = 'binh_dinh';
    case BINH_DUONG = 'binh_duong';
    case BINH_PHUOC = 'binh_phuoc';
    case BINH_THUAN = 'binh_thuan';
    case CA_MAU = 'ca_mau';
    case CAN_THO = 'can_tho';
    case CAO_BANG = 'cao_bang';
    case DA_NANG = 'da_nang';
    case DAK_LAK = 'dak_lak';
    case DAK_NONG = 'dak_nong';
    case DIEN_BIEN = 'dien_bien';
    case DONG_NAI = 'dong_nai';
    case DONG_THAP = 'dong_thap';
    case GIA_LAI = 'gia_lai';
    case HA_GIANG = 'ha_giang';
    case HA_NAM = 'ha_nam';
    case HA_NOI = 'ha_noi';
    case HA_TINH = 'ha_tinh';
    case HAI_DUONG = 'hai_duong';
    case HAI_PHONG = 'hai_phong';
    case HAU_GIANG = 'hau_giang';
    case HOA_BINH = 'hoa_binh';
    case HO_CHI_MINH = 'ho_chi_minh';
    case HUNG_YEN = 'hung_yen';
    case KHANH_HOA = 'khanh_hoa';
    case KIEN_GIANG = 'kien_giang';
    case KON_TUM = 'kon_tum';
    case LAI_CHAU = 'lai_chau';
    case LAM_DONG = 'lam_dong';
    case LANG_SON = 'lang_son';
    case LAO_CAI = 'lao_cai';
    case LONG_AN = 'long_an';
    case NAM_DINH = 'nam_dinh';
    case NGHE_AN = 'nghe_an';
    case NINH_BINH = 'ninh_binh';
    case NINH_THUAN = 'ninh_thuan';
    case PHU_THO = 'phu_tho';
    case PHU_YEN = 'phu_yen';
    case QUANG_BINH = 'quang_binh';
    case QUANG_NAM = 'quang_nam';
    case QUANG_NGAI = 'quang_ngai';
    case QUANG_NINH = 'quang_ninh';
    case QUANG_TRI = 'quang_tri';
    case SOC_TRANG = 'soc_trang';
    case SON_LA = 'son_la';
    case TAY_NINH = 'tay_ninh';
    case THAI_BINH = 'thai_binh';
    case THAI_NGUYEN = 'thai_nguyen';
    case THANH_HOA = 'thanh_hoa';
    case THUA_THIEN_HUE = 'thua_thien_hue';
    case TIEN_GIANG = 'tien_giang';
    case TRA_VINH = 'tra_vinh';
    case TUYEN_QUANG = 'tuyen_quang';
    case VINH_LONG = 'vinh_long';
    case VINH_PHUC = 'vinh_phuc';
    case YEN_BAI = 'yen_bai';

    public function label(): string
    {
        return match ($this) {
            self::AN_GIANG => 'An Giang',
            self::BA_RIA_VUNG_TAU => 'Bà Rịa - Vũng Tàu',
            self::BAC_GIANG => 'Bắc Giang',
            self::BAC_KAN => 'Bắc Kạn',
            self::BAC_LIEU => 'Bạc Liêu',
            self::BAC_NINH => 'Bắc Ninh',
            self::BEN_TRE => 'Bến Tre',
            self::BINH_DINH => 'Bình Định',
            self::BINH_DUONG => 'Bình Dương',
            self::BINH_PHUOC => 'Bình Phước',
            self::BINH_THUAN => 'Bình Thuận',
            self::CA_MAU => 'Cà Mau',
            self::CAN_THO => 'Cần Thơ',
            self::CAO_BANG => 'Cao Bằng',
            self::DA_NANG => 'Đà Nẵng',
            self::DAK_LAK => 'Đắk Lắk',
            self::DAK_NONG => 'Đắk Nông',
            self::DIEN_BIEN => 'Điện Biên',
            self::DONG_NAI => 'Đồng Nai',
            self::DONG_THAP => 'Đồng Tháp',
            self::GIA_LAI => 'Gia Lai',
            self::HA_GIANG => 'Hà Giang',
            self::HA_NAM => 'Hà Nam',
            self::HA_NOI => 'Hà Nội',
            self::HA_TINH => 'Hà Tĩnh',
            self::HAI_DUONG => 'Hải Dương',
            self::HAI_PHONG => 'Hải Phòng',
            self::HAU_GIANG => 'Hậu Giang',
            self::HOA_BINH => 'Hòa Bình',
            self::HO_CHI_MINH => 'Hồ Chí Minh',
            self::HUNG_YEN => 'Hưng Yên',
            self::KHANH_HOA => 'Khánh Hòa',
            self::KIEN_GIANG => 'Kiên Giang',
            self::KON_TUM => 'Kon Tum',
            self::LAI_CHAU => 'Lai Châu',
            self::LAM_DONG => 'Lâm Đồng',
            self::LANG_SON => 'Lạng Sơn',
            self::LAO_CAI => 'Lào Cai',
            self::LONG_AN => 'Long An',
            self::NAM_DINH => 'Nam Định',
            self::NGHE_AN => 'Nghệ An',
            self::NINH_BINH => 'Ninh Bình',
            self::NINH_THUAN => 'Ninh Thuận',
            self::PHU_THO => 'Phú Thọ',
            self::PHU_YEN => 'Phú Yên',
            self::QUANG_BINH => 'Quảng Bình',
            self::QUANG_NAM => 'Quảng Nam',
            self::QUANG_NGAI => 'Quảng Ngãi',
            self::QUANG_NINH => 'Quảng Ninh',
            self::QUANG_TRI => 'Quảng Trị',
            self::SOC_TRANG => 'Sóc Trăng',
            self::SON_LA => 'Sơn La',
            self::TAY_NINH => 'Tây Ninh',
            self::THAI_BINH => 'Thái Bình',
            self::THAI_NGUYEN => 'Thái Nguyên',
            self::THANH_HOA => 'Thanh Hóa',
            self::THUA_THIEN_HUE => 'Thừa Thiên Huế',
            self::TIEN_GIANG => 'Tiền Giang',
            self::TRA_VINH => 'Trà Vinh',
            self::TUYEN_QUANG => 'Tuyên Quang',
            self::VINH_LONG => 'Vĩnh Long',
            self::VINH_PHUC => 'Vĩnh Phúc',
            self::YEN_BAI => 'Yên Bái',
        };
    }

    public function provinceCode(): string
    {
        return match ($this) {
            self::HA_NOI => '01',
            self::HA_GIANG => '02',
            self::CAO_BANG => '04',
            self::BAC_KAN => '06',
            self::TUYEN_QUANG => '08',
            self::LAO_CAI => '10',
            self::DIEN_BIEN => '11',
            self::LAI_CHAU => '12',
            self::SON_LA => '14',
            self::YEN_BAI => '15',
            self::HOA_BINH => '17',
            self::THAI_NGUYEN => '19',
            self::LANG_SON => '20',
            self::QUANG_NINH => '22',
            self::BAC_GIANG => '24',
            self::PHU_THO => '25',
            self::VINH_PHUC => '26',
            self::BAC_NINH => '27',
            self::HAI_DUONG => '30',
            self::HAI_PHONG => '31',
            self::HUNG_YEN => '33',
            self::THAI_BINH => '34',
            self::HA_NAM => '35',
            self::NAM_DINH => '36',
            self::NINH_BINH => '37',
            self::THANH_HOA => '38',
            self::NGHE_AN => '40',
            self::HA_TINH => '42',
            self::QUANG_BINH => '44',
            self::QUANG_TRI => '45',
            self::THUA_THIEN_HUE => '46',
            self::DA_NANG => '48',
            self::QUANG_NAM => '49',
            self::QUANG_NGAI => '51',
            self::BINH_DINH => '52',
            self::PHU_YEN => '54',
            self::KHANH_HOA => '56',
            self::NINH_THUAN => '58',
            self::BINH_THUAN => '60',
            self::KON_TUM => '62',
            self::GIA_LAI => '64',
            self::DAK_LAK => '66',
            self::DAK_NONG => '67',
            self::LAM_DONG => '68',
            self::BINH_PHUOC => '70',
            self::TAY_NINH => '72',
            self::BINH_DUONG => '74',
            self::DONG_NAI => '75',
            self::BA_RIA_VUNG_TAU => '77',
            self::HO_CHI_MINH => '79',
            self::LONG_AN => '80',
            self::TIEN_GIANG => '82',
            self::BEN_TRE => '83',
            self::TRA_VINH => '84',
            self::VINH_LONG => '86',
            self::DONG_THAP => '87',
            self::AN_GIANG => '89',
            self::KIEN_GIANG => '91',
            self::CAN_THO => '92',
            self::HAU_GIANG => '93',
            self::SOC_TRANG => '94',
            self::BAC_LIEU => '95',
            self::CA_MAU => '96',
        };
    }

    /**
     * Filament-friendly options: [value => label]
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $province) {
            $options[$province->value] = $province->label();
        }

        return $options;
    }
}


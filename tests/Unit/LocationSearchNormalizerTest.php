<?php

namespace Tests\Unit;

use App\Enums\VietnamProvince;
use App\Services\LocationSearchNormalizer;
use PHPUnit\Framework\TestCase;

class LocationSearchNormalizerTest extends TestCase
{
    public function test_it_matches_common_city_aliases_and_unaccented_inputs(): void
    {
        $normalizer = new LocationSearchNormalizer();

        $cases = [
            'Hồ Chí Minh' => VietnamProvince::HO_CHI_MINH->value,
            'ho chi minh' => VietnamProvince::HO_CHI_MINH->value,
            'hcm' => VietnamProvince::HO_CHI_MINH->value,
            'TP.HCM' => VietnamProvince::HO_CHI_MINH->value,
            'Sài Gòn' => VietnamProvince::HO_CHI_MINH->value,
            'Hà Nội' => VietnamProvince::HA_NOI->value,
            'ha noi' => VietnamProvince::HA_NOI->value,
            'hn' => VietnamProvince::HA_NOI->value,
            'Cần Thơ' => VietnamProvince::CAN_THO->value,
            'can tho' => VietnamProvince::CAN_THO->value,
            'ct' => VietnamProvince::CAN_THO->value,
            'Đà Nẵng' => VietnamProvince::DA_NANG->value,
            'da nang' => VietnamProvince::DA_NANG->value,
            'dn' => VietnamProvince::DA_NANG->value,
        ];

        foreach ($cases as $input => $provinceValue) {
            $this->assertContains(
                $provinceValue,
                $normalizer->normalize($input)['province_values'],
                "Failed asserting [{$input}] matches [{$provinceValue}]."
            );
        }
    }
}

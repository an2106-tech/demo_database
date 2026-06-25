<?php

namespace App\Services;

use App\Enums\VietnamProvince;
use Illuminate\Support\Str;

class LocationSearchNormalizer
{
    /**
     * @return array{term: string, province_values: array<int, string>, keywords: array<int, string>}
     */
    public function normalize(string $input): array
    {
        $term = $this->normalizeText($input);

        if ($term === '') {
            return [
                'term' => '',
                'province_values' => [],
                'keywords' => [],
            ];
        }

        $provinceValues = $this->matchedProvinceValues($term);

        return [
            'term' => $term,
            'province_values' => $provinceValues,
            'keywords' => $this->keywords($input, $term),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function matchedProvinceValues(string $term): array
    {
        $lookup = $this->provinceLookup();

        return array_values(array_unique($lookup[$term] ?? []));
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function provinceLookup(): array
    {
        $lookup = [];

        foreach (VietnamProvince::cases() as $province) {
            foreach ($this->provinceKeys($province) as $key) {
                $lookup[$key][] = $province->value;
            }
        }

        foreach ($this->aliases() as $alias => $provinceValue) {
            $lookup[$alias][] = $provinceValue;
        }

        return $lookup;
    }

    /**
     * @return array<int, string>
     */
    private function provinceKeys(VietnamProvince $province): array
    {
        $label = $this->normalizeText($province->label());
        $value = $this->normalizeText(str_replace('_', ' ', $province->value));

        return array_values(array_unique([
            $label,
            $this->compact($label),
            $value,
            $this->compact($value),
            $province->value,
        ]));
    }

    /**
     * @return array<string, string>
     */
    private function aliases(): array
    {
        return [
            'hn' => VietnamProvince::HA_NOI->value,
            'hanoi' => VietnamProvince::HA_NOI->value,
            'hcm' => VietnamProvince::HO_CHI_MINH->value,
            'tphcm' => VietnamProvince::HO_CHI_MINH->value,
            'tp hcm' => VietnamProvince::HO_CHI_MINH->value,
            'tp ho chi minh' => VietnamProvince::HO_CHI_MINH->value,
            'sai gon' => VietnamProvince::HO_CHI_MINH->value,
            'saigon' => VietnamProvince::HO_CHI_MINH->value,
            'dn' => VietnamProvince::DA_NANG->value,
            'danang' => VietnamProvince::DA_NANG->value,
            'ct' => VietnamProvince::CAN_THO->value,
            'cantho' => VietnamProvince::CAN_THO->value,
            'hp' => VietnamProvince::HAI_PHONG->value,
            'haiphong' => VietnamProvince::HAI_PHONG->value,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function keywords(string $input, string $term): array
    {
        return array_values(array_unique(array_filter([
            trim($input),
            $term,
            str_replace(' ', '_', $term),
        ], fn (string $value): bool => $value !== '')));
    }

    private function normalizeText(string $value): string
    {
        $value = Str::ascii(Str::lower(trim($value)));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function compact(string $value): string
    {
        return str_replace(' ', '', $value);
    }
}

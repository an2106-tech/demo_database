<?php

use App\Enums\VietnamProvince;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize from Vietnamese labels (e.g. "Hà Nội") to enum values (e.g. "ha_noi")
        foreach (VietnamProvince::cases() as $province) {
            DB::table('branches')
                ->where('city', $province->label())
                ->update(['city' => $province->value]);
        }

        // Normalize common ASCII variants used in seed/demo data
        DB::table('branches')
            ->whereIn('city', ['Ho Chi Minh', 'Hochiminh', 'HCM', 'TP HCM', 'TP.HCM', 'TP. HCM', 'Sai Gon', 'Saigon'])
            ->update(['city' => VietnamProvince::HO_CHI_MINH->value]);

        DB::table('branches')
            ->whereIn('city', ['Ha Noi', 'Hanoi'])
            ->update(['city' => VietnamProvince::HA_NOI->value]);

        // Best-effort backfill province_code when missing.
        foreach (VietnamProvince::cases() as $province) {
            DB::table('branches')
                ->where('city', $province->value)
                ->where(function ($query) {
                    $query->whereNull('province_code')->orWhere('province_code', '');
                })
                ->update(['province_code' => $province->provinceCode()]);
        }
    }

    public function down(): void
    {
        // Revert enum values back to their Vietnamese labels.
        foreach (VietnamProvince::cases() as $province) {
            DB::table('branches')
                ->where('city', $province->value)
                ->update(['city' => $province->label()]);
        }
    }
};


<?php

namespace App\Services;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\RecruitmentJob;
use Illuminate\Validation\ValidationException;

class JobApplicationEligibilityService
{
    public function assertCanApply(RecruitmentJob $job): void
    {
        $status = $job->status instanceof StatusRecruitmentJobsEnum
            ? $job->status
            : StatusRecruitmentJobsEnum::tryFrom((string) $job->status);

        if ($status !== StatusRecruitmentJobsEnum::PUBLISHED) {
            throw ValidationException::withMessages([
                'application' => 'Tin tuyển dụng hiện không nhận thêm hồ sơ.',
            ]);
        }

        if ($job->deadline?->endOfDay()->isPast()) {
            throw ValidationException::withMessages([
                'application' => 'Tin tuyển dụng đã hết hạn nhận hồ sơ.',
            ]);
        }

        if ((int) $job->positions_count < 1) {
            throw ValidationException::withMessages([
                'application' => 'Vị trí này hiện không còn chỉ tiêu tuyển dụng.',
            ]);
        }
    }
}

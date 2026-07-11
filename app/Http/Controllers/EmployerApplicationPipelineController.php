<?php

namespace App\Http\Controllers;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\User;
use App\Services\ApplicationPipelineService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmployerApplicationPipelineController extends Controller
{
    public function advance(
        Request $request,
        Application $application,
        ApplicationPipelineService $pipelineService,
    ): RedirectResponse {
        /** @var User|null $user */
        $user = $request->user();

        abort_unless($this->canManageApplication($user, $application), 403);

        $nextStatus = collect($pipelineService->allowedTransitions($application->status))
            ->first(fn (StatusApplicationEnum $status): bool => $status !== StatusApplicationEnum::REJECTED);

        if (! $nextStatus) {
            return back()->with('error', 'Hồ sơ này chưa có bước kế tiếp phù hợp.');
        }

        try {
            $pipelineService->transition(
                $application,
                $nextStatus,
                $user,
                'HR chuyển nhanh từ Pipeline.',
            );
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', 'Đã chuyển hồ sơ sang: '.$this->statusLabel($nextStatus).'.');
    }

    private function canManageApplication(?User $user, Application $application): bool
    {
        if (! $user) {
            return false;
        }

        $application->loadMissing('job');

        if ($user->isSuperAdmin() || in_array($user->role, ['admin', 'director'], true)) {
            $branchId = $user->branchScopeId();

            return ! $branchId || (int) ($application->branch_id ?: $application->job?->branch_id) === (int) $branchId;
        }

        $branchId = $user->branchScopeId();

        if ($branchId) {
            return (int) ($application->branch_id ?: $application->job?->branch_id) === (int) $branchId;
        }

        return (int) $application->job?->created_by === (int) $user->id;
    }

    private function statusLabel(StatusApplicationEnum $status): string
    {
        return match ($status) {
            StatusApplicationEnum::CV_REVIEWING => 'Chờ sàng lọc CV',
            StatusApplicationEnum::SCREENING => 'Sơ tuyển',
            StatusApplicationEnum::INTERVIEW_SCHEDULED => 'Đã lên lịch phỏng vấn',
            StatusApplicationEnum::INTERVIEWING => 'Chờ đánh giá phỏng vấn',
            StatusApplicationEnum::OFFERED => 'Đề nghị tuyển dụng',
            StatusApplicationEnum::HIRED => 'Đã tuyển',
            StatusApplicationEnum::REJECTED => 'Từ chối',
        };
    }
}

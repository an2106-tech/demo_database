<?php

namespace App\Services;

use App\Models\Interview;
use App\Models\InterviewEvaluator;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class InterviewEvaluatorService
{
    /**
     * @param  array<int, int|string>  $memberIds
     */
    public function sync(Interview $interview, array $memberIds = []): void
    {
        $leadId = (int) $interview->interviewer_id;
        if ($leadId <= 0) {
            throw ValidationException::withMessages([
                'interviewer_id' => 'Vui lòng chọn người phụ trách phỏng vấn.',
            ]);
        }

        $ids = collect($memberIds)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0 && $id !== $leadId)
            ->prepend($leadId)
            ->unique()
            ->values();

        $this->assertEligibleUsers($interview, $ids);

        $existing = $interview->evaluators()->get()->keyBy('user_id');

        foreach ($ids as $userId) {
            $assignment = $existing->get($userId) ?? new InterviewEvaluator([
                'interview_id' => $interview->id,
                'user_id' => $userId,
                'assigned_at' => now(),
            ]);

            $assignment->fill([
                'role' => $userId === $leadId ? 'lead' : 'member',
                'is_required' => true,
                'waived_at' => null,
                'waived_by_user_id' => null,
                'waiver_reason' => null,
            ])->save();
        }

        $interview->evaluators()
            ->whereNotIn('user_id', $ids)
            ->whereNull('submitted_at')
            ->delete();
    }

    public function ensureLead(Interview $interview): InterviewEvaluator
    {
        $assignment = $interview->evaluators()->firstOrCreate(
            ['user_id' => (int) $interview->interviewer_id],
            [
                'role' => 'lead',
                'is_required' => true,
                'assigned_at' => $interview->created_at ?: now(),
            ],
        );

        if ($assignment->role !== 'lead' || ! $assignment->is_required || $assignment->waived_at) {
            $assignment->forceFill([
                'role' => 'lead',
                'is_required' => true,
                'waived_at' => null,
                'waived_by_user_id' => null,
                'waiver_reason' => null,
            ])->save();
        }

        return $assignment;
    }

    public function isAssigned(Interview $interview, ?User $user): bool
    {
        if (! $user?->id) {
            return false;
        }

        if ((int) $interview->interviewer_id === (int) $user->id) {
            return true;
        }

        return $interview->evaluators()
            ->where('user_id', $user->id)
            ->where('is_required', true)
            ->exists();
    }

    public function waivePendingEvaluator(Interview $interview, int $userId, User $actor, string $reason): InterviewEvaluator
    {
        $reason = trim($reason);
        $isLead = (int) $interview->interviewer_id === (int) $actor->id;
        $canOversee = $actor->isSuperAdmin() || $actor->role === 'admin';

        if (! $isLead && ! $canOversee) {
            throw ValidationException::withMessages([
                'waiver_reason' => 'Chỉ người phụ trách phỏng vấn mới có thể cập nhật yêu cầu gửi phiếu.',
            ]);
        }

        if ($interview->finalized_at || $interview->result !== 'pending') {
            throw ValidationException::withMessages([
                'waiver_reason' => 'Vòng phỏng vấn đã được chốt nên không thể thay đổi người đánh giá.',
            ]);
        }

        if ((int) $interview->interviewer_id === $userId) {
            throw ValidationException::withMessages([
                'waiver_reason' => 'Người phụ trách phải gửi phiếu và không thể bỏ yêu cầu này.',
            ]);
        }

        if ($reason === '') {
            throw ValidationException::withMessages([
                'waiver_reason' => 'Vui lòng nhập lý do không yêu cầu phiếu đánh giá.',
            ]);
        }

        if (mb_strlen($reason) > 500) {
            throw ValidationException::withMessages([
                'waiver_reason' => 'Lý do không được vượt quá 500 ký tự.',
            ]);
        }

        $assignment = $interview->evaluators()
            ->where('user_id', $userId)
            ->first();

        if (! $assignment || ! $assignment->is_required) {
            throw ValidationException::withMessages([
                'waiver_reason' => 'Thành viên này không còn trong danh sách cần gửi phiếu.',
            ]);
        }

        if ($assignment->submitted_at) {
            throw ValidationException::withMessages([
                'waiver_reason' => 'Thành viên đã gửi phiếu nên không thể bỏ yêu cầu.',
            ]);
        }

        $assignment->forceFill([
            'is_required' => false,
            'waived_at' => now(),
            'waived_by_user_id' => $actor->id,
            'waiver_reason' => $reason,
        ])->save();

        $application = $interview->application;
        if ($application) {
            $status = $application->status instanceof \BackedEnum
                ? (string) $application->status->value
                : (string) $application->status;
            $assignment->loadMissing('user:id,name');
            $application->statusHistories()->create([
                'from_status' => $status,
                'to_status' => $status,
                'changed_by_id' => $actor->id,
                'comment' => 'Hội đồng phỏng vấn: Không yêu cầu phiếu của '
                    .($assignment->user?->name ?: 'thành viên được phân công')
                    .'. Lý do: '.$reason,
            ]);
        }

        return $assignment->refresh();
    }

    public function markSubmitted(Interview $interview, User $user): void
    {
        $assignment = $interview->evaluators()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'role' => (int) $interview->interviewer_id === (int) $user->id ? 'lead' : 'member',
                'is_required' => true,
                'assigned_at' => now(),
            ],
        );

        $assignment->forceFill(['submitted_at' => now()])->save();
    }

    /** @return array{assigned: int, required: int, submitted: int, pending: int, waived: int, is_panel: bool, all_submitted: bool} */
    public function progress(Interview $interview): array
    {
        $this->ensureLead($interview);

        $assigned = $interview->evaluators()->count();
        $required = $interview->evaluators()->where('is_required', true)->count();
        $submitted = $interview->evaluators()
            ->where('is_required', true)
            ->whereNotNull('submitted_at')
            ->count();
        $waived = $interview->evaluators()
            ->where('is_required', false)
            ->whereNotNull('waived_at')
            ->count();

        return [
            'assigned' => $assigned,
            'required' => $required,
            'submitted' => $submitted,
            'pending' => max(0, $required - $submitted),
            'waived' => $waived,
            'is_panel' => $assigned > 1,
            'all_submitted' => $required > 0 && $required === $submitted,
        ];
    }

    /** @return Collection<int, InterviewEvaluator> */
    public function assignments(Interview $interview): Collection
    {
        $this->ensureLead($interview);

        return $interview->evaluators()
            ->with(['user:id,name,email,role', 'waivedBy:id,name'])
            ->orderByRaw("CASE WHEN role = 'lead' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get();
    }

    /** @param Collection<int, int> $ids */
    private function assertEligibleUsers(Interview $interview, Collection $ids): void
    {
        $branchId = $interview->application?->job?->branch_id ?: $interview->application?->branch_id;

        $eligibleCount = User::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->when($branchId, fn (Builder $query): Builder => $query->where('branch_id', $branchId))
            ->where(function (Builder $query): void {
                $query
                    ->whereIn('role', ['director', 'pm', 'hr'])
                    ->orWhereHas('roles', fn (Builder $roleQuery): Builder => $roleQuery->whereIn('name', ['director', 'pm', 'hr']));
            })
            ->count();

        if ($eligibleCount !== $ids->count()) {
            throw ValidationException::withMessages([
                'evaluator_ids' => 'Người phụ trách và người cùng đánh giá phải đang hoạt động, thuộc đúng chi nhánh tuyển dụng.',
            ]);
        }
    }
}

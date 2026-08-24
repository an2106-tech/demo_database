<?php

namespace App\Services;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\ApplicationPreScreening;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationPreScreeningService
{
    /** @var array<string, string> */
    private const OUTCOMES = [
        'passed' => 'Đạt sơ tuyển',
        'follow_up' => 'Hẹn liên hệ lại',
        'rejected' => 'Từ chối hồ sơ',
    ];

    public function contactMethodOptions(): array
    {
        return [
            'phone' => 'Điện thoại',
            'email' => 'Email',
            'zalo' => 'Zalo',
            'in_person' => 'Trực tiếp',
            'other' => 'Khác',
        ];
    }

    /** @return array<string, string> */
    public function outcomeOptions(): array
    {
        return self::OUTCOMES;
    }

    /** @return array<string, string> */
    public function rejectionReasonOptions(): array
    {
        return [
            'unreachable' => 'Không liên hệ được',
            'candidate_withdrew' => 'Ứng viên không còn quan tâm',
            'availability_mismatch' => 'Không phù hợp thời gian hoặc địa điểm',
            'expectation_mismatch' => 'Kỳ vọng chưa phù hợp',
            'not_qualified' => 'Thông tin chưa đáp ứng yêu cầu',
            'other' => 'Lý do khác',
        ];
    }

    public function outcomeLabel(?string $outcome): string
    {
        return self::OUTCOMES[$outcome] ?? 'Chưa xác định';
    }

    public function rejectionReasonLabel(?string $code): string
    {
        return $this->rejectionReasonOptions()[$code] ?? 'Lý do khác';
    }

    public function contactMethodLabel(?string $method, ?string $detail = null): string
    {
        if ($method === 'other' && filled($detail)) {
            return (string) $detail;
        }

        return $this->contactMethodOptions()[$method] ?? 'Chưa xác định';
    }

    public function latest(Application $application): ?ApplicationPreScreening
    {
        if ($application->relationLoaded('latestPreScreening')) {
            return $application->latestPreScreening;
        }

        return $application->preScreenings()->latest('id')->first();
    }

    public function hasPassed(Application $application): bool
    {
        return $this->latest($application)?->outcome === 'passed';
    }

    public function record(
        Application $application,
        User $actor,
        string $channel,
        CarbonInterface $contactedAt,
        string $outcome,
        ?CarbonInterface $followUpAt = null,
        ?string $note = null,
        ?string $rejectionReason = null,
        ?string $channelDetail = null,
        ?string $rejectionReasonCode = null,
    ): ApplicationPreScreening {
        return $application->preScreenings()->create([
            'handled_by_user_id' => $actor->id,
            'contact_channel' => $channel,
            'contact_channel_detail' => $channelDetail,
            'contacted_at' => $contactedAt,
            'outcome' => $outcome,
            'follow_up_at' => $followUpAt,
            'note' => $note,
            'rejection_reason_code' => $rejectionReasonCode,
            'rejection_reason' => $rejectionReason,
        ]);
    }

    /**
     * Validate and persist one pre-screening contact attempt. Every surface
     * (table and Kanban) goes through this method to keep the workflow aligned.
     *
     * @param array<string, mixed> $data
     */
    public function recordOutcome(Application $application, User $actor, array $data): ApplicationPreScreening
    {
        if ((string) $application->status->value !== StatusApplicationEnum::SCREENING->value) {
            throw ValidationException::withMessages([
                'outcome' => 'Hồ sơ không còn ở giai đoạn sơ tuyển.',
            ]);
        }

        if ($this->hasPassed($application)) {
            throw ValidationException::withMessages([
                'outcome' => 'Hồ sơ đã đạt sơ tuyển và đang chờ tạo lịch phỏng vấn.',
            ]);
        }

        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $channel = trim((string) ($data['contact_channel'] ?? ''));
        $channelDetail = trim((string) ($data['contact_channel_detail'] ?? ''));
        $outcome = trim((string) ($data['outcome'] ?? ''));
        $note = trim((string) ($data['note'] ?? ''));
        $reasonCode = trim((string) ($data['rejection_reason_code'] ?? ''));
        $reasonDetail = trim((string) ($data['rejection_reason'] ?? ''));

        $contactedAt = $this->parseDateTime($data['contacted_at'] ?? null, $timezone, 'contacted_at', 'Vui lòng chọn thời điểm liên hệ.');
        $followUpAt = filled($data['follow_up_at'] ?? null)
            ? $this->parseDateTime($data['follow_up_at'], $timezone, 'follow_up_at', 'Vui lòng chọn thời điểm hẹn liên hệ lại.')
            : null;

        if (! array_key_exists($channel, $this->contactMethodOptions())) {
            throw ValidationException::withMessages(['contact_channel' => 'Vui lòng chọn hình thức liên hệ.']);
        }

        if ($channel === 'other' && $channelDetail === '') {
            throw ValidationException::withMessages(['contact_channel_detail' => 'Vui lòng ghi rõ hình thức liên hệ.']);
        }

        if (! array_key_exists($outcome, self::OUTCOMES)) {
            throw ValidationException::withMessages(['outcome' => 'Vui lòng chọn kết quả sơ tuyển.']);
        }

        if ($contactedAt->gt(now($timezone))) {
            throw ValidationException::withMessages(['contacted_at' => 'Chọn thời điểm liên hệ là hiện tại hoặc trước đó.']);
        }

        if (in_array($outcome, ['passed', 'follow_up'], true) && $note === '') {
            throw ValidationException::withMessages(['note' => 'Vui lòng ghi ngắn gọn kết quả lần liên hệ này.']);
        }

        if ($outcome === 'follow_up' && (! $followUpAt || $followUpAt->lte($contactedAt) || $followUpAt->lte(now($timezone)))) {
            throw ValidationException::withMessages([
                'follow_up_at' => 'Hẹn liên hệ lại phải sau lần liên hệ này và ở thời điểm tương lai.',
            ]);
        }

        if ($outcome === 'rejected') {
            if (! array_key_exists($reasonCode, $this->rejectionReasonOptions())) {
                throw ValidationException::withMessages(['rejection_reason_code' => 'Vui lòng chọn lý do từ chối.']);
            }

            if ($reasonCode === 'other' && $reasonDetail === '') {
                throw ValidationException::withMessages(['rejection_reason' => 'Vui lòng mô tả lý do từ chối.']);
            }

            if ($reasonDetail === '') {
                $reasonDetail = $this->rejectionReasonLabel($reasonCode);
            }
        }

        return DB::transaction(function () use ($application, $actor, $channel, $channelDetail, $contactedAt, $outcome, $followUpAt, $note, $reasonCode, $reasonDetail): ApplicationPreScreening {
            $record = $this->record(
                $application,
                $actor,
                $channel,
                $contactedAt,
                $outcome,
                $outcome === 'follow_up' ? $followUpAt : null,
                $note !== '' ? $note : null,
                $reasonDetail !== '' ? $reasonDetail : null,
                $channel === 'other' ? $channelDetail : null,
                $outcome === 'rejected' ? $reasonCode : null,
            );

            if ($outcome === 'rejected') {
                $application->forceFill([
                    'rejected_stage' => 'pre_screening',
                    'rejected_reason' => $reasonDetail,
                ])->save();

                app(ApplicationPipelineService::class)->transition(
                    $application,
                    StatusApplicationEnum::REJECTED,
                    $actor,
                    'Sơ tuyển: Từ chối hồ sơ. Lý do: '.$reasonDetail,
                );
            } else {
                $comment = 'Sơ tuyển: '.$this->outcomeLabel($outcome).'. Ghi chú: '.$note;
                if ($followUpAt) {
                    $comment .= ' Hẹn lại: '.$followUpAt->format('H:i, d/m/Y').'.';
                }

                $application->recordStatusHistory(
                    StatusApplicationEnum::SCREENING->value,
                    StatusApplicationEnum::SCREENING->value,
                    $comment,
                );
            }

            return $record;
        });
    }

    public function remindDueFollowUps(): int
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $now = now($timezone);
        $reminded = 0;

        ApplicationPreScreening::query()
            ->where('outcome', 'follow_up')
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<=', $now)
            ->with(['application.job:id,title,branch_id'])
            ->orderBy('id')
            ->each(function (ApplicationPreScreening $preScreening) use ($now, &$reminded): void {
                $application = $preScreening->application;
                if (! $application || $application->status !== StatusApplicationEnum::SCREENING) {
                    return;
                }

                $latest = $this->latest($application);
                if (! $latest
                    || $latest->id !== $preScreening->id
                    || ($preScreening->follow_up_reminded_at && $preScreening->follow_up_reminded_at->gte($now->copy()->subDay()))) {
                    return;
                }

                $users = $this->followUpRecipients($application);
                if ($users->isEmpty()) {
                    return;
                }

                $isOverdue = $preScreening->follow_up_at->lt($now);
                $title = $isOverdue ? 'Quá hạn liên hệ sơ tuyển' : 'Đến hẹn liên hệ sơ tuyển';
                $message = $application->snapshotCandidateName().' - '.($application->job?->title ?: 'Vị trí tuyển dụng');

                $users->each(function (User $user) use ($application, $title, $message, $preScreening): void {
                    UserNotification::query()->create([
                        'user_id' => $user->id,
                        'type' => 'pre_screening_follow_up_due',
                        'data' => [
                            'title' => $title,
                            'message' => $message,
                            'application_id' => $application->id,
                            'follow_up_at' => $preScreening->follow_up_at?->toIso8601String(),
                            'url' => '/admin/applications/kanban',
                        ],
                    ]);
                });

                $preScreening->forceFill(['follow_up_reminded_at' => $now])->save();
                $reminded++;
            });

        return $reminded;
    }

    private function parseDateTime(mixed $value, string $timezone, string $field, string $message): CarbonInterface
    {
        try {
            if (! filled($value)) {
                throw new \InvalidArgumentException();
            }

            return Carbon::parse($value, $timezone);
        } catch (\Throwable) {
            throw ValidationException::withMessages([$field => $message]);
        }
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function followUpRecipients(Application $application): \Illuminate\Support\Collection
    {
        if ($application->assigned_hr_id) {
            return User::query()
                ->whereKey($application->assigned_hr_id)
                ->where('is_active', true)
                ->get();
        }

        $branchId = $application->branch_id ?: $application->job?->branch_id;

        return User::query()
            ->where('is_active', true)
            ->where('role', 'hr')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->get();
    }
}

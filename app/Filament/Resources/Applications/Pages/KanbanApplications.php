<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\Interview;
use App\Models\OfferLetterTemplate;
use App\Models\ScorecardTemplate;
use App\Models\User;
use App\Models\Workplace;
use App\Services\ApplicationAiAnalysisService;
use App\Services\InterviewCalendarService;
use App\Services\InterviewEvaluationService;
use App\Services\InterviewMeetingLinkValidator;
use App\Services\ApplicationKanbanTransitionService;
use App\Services\ApplicationPipelineService;
use App\Services\ApplicationWorkflowGuard;
use App\Services\ApplicationWorkflowSummaryService;
use App\Services\InterviewScheduleDeliveryService;
use App\Services\OfferWorkflowService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;

class KanbanApplications extends Page
{
    protected static string $resource = ApplicationResource::class;

    protected string $view = 'filament.resources.applications.pages.kanban-applications';

    protected ?string $heading = 'Kanban ứng tuyển';

    protected ?string $subheading = null;

    protected Width | string | null $maxContentWidth = Width::Full;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'queue')]
    public string $quickFilter = 'all';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $kanbanDropAction = null;

    public string $kanbanModalError = '';

    public int $kanbanModalErrorKey = 0;

    public string $kanbanRejectionReason = '';

    public string $kanbanScreeningDecision = '';

    public string $kanbanScreeningNote = '';

    public string $kanbanScreeningRejectedReason = '';

    public string $kanbanInterviewAvailabilityNotice = '';

    /**
     * @var array{title: string, empty: string, items: array<int, array{time: string, title: string, meta: string}>, suggestions: array<int, string>}
     */
    public array $kanbanInterviewSchedulePreview = [
        'title' => 'Lịch phỏng vấn gần nhất',
        'empty' => 'Chưa có lịch sắp tới.',
        'items' => [],
        'suggestions' => [],
    ];

    /**
     * @var array<string, mixed>
     */
    public array $kanbanInterviewForm = [
        'round_name' => '',
        'scheduled_at' => '',
        'duration_minutes' => '',
        'type' => '',
        'meeting_link' => '',
        'workplace_id' => '',
        'interviewer_id' => '',
        'notes' => '',
    ];

    /**
     * @var array{template_id: string, criteria: array<int, array{name: string, score: string|int|float|null, note: string|null}>, conclusion: string, notes: string, override_reason: string, rejected_reason: string, confirm_early_completion: bool}
     */
    public array $kanbanEvaluationForm = [
        'template_id' => '',
        'criteria' => [],
        'conclusion' => '',
        'notes' => '',
        'override_reason' => '',
        'rejected_reason' => '',
        'confirm_early_completion' => false,
    ];

    public ?string $kanbanEvaluationDraftSavedAt = null;

    public ?string $kanbanEvaluationDraftStatus = null;

    /** @var array<string, mixed> */
    public array $kanbanOfferForm = [
        'offer_letter_template_id' => '',
        'salary_offered' => '',
        'probation_months' => 2,
        'start_date' => '',
        'expires_at' => '',
        'content' => '',
    ];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('table')
                ->label('Dạng bảng')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(ApplicationResource::getUrl('index')),
            CreateAction::make(),
        ];
    }

    public function moveApplicationToStage(int $applicationId, string $targetStage): void
    {
        $transitionService = app(ApplicationKanbanTransitionService::class);
        $pipelineService = app(ApplicationPipelineService::class);

        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application) {
            $this->showKanbanDropAction([
                'type' => 'blocked',
                'title' => 'Không tìm thấy hồ sơ',
                'message' => 'Hồ sơ không tồn tại hoặc không thuộc phạm vi quản lý hiện tại.',
            ]);

            return;
        }

        $result = $transitionService->evaluateStageMove($application, $targetStage, Auth::user());

        if ($result['requires']) {
            $this->showKanbanDropAction($this->requiredActionPayload($application, $targetStage, $result));

            return;
        }

        if (! $result['allowed'] || ! $result['target_status']) {
            $this->showKanbanDropAction([
                'type' => 'blocked',
                'title' => 'Không thể chuyển giai đoạn',
                'message' => $result['message'],
            ]);

            return;
        }

        $pipelineService->transition(
            $application,
            $result['target_status'],
            Auth::user(),
            'Cập nhật từ Kanban ứng tuyển.',
        );

        Notification::make()
            ->success()
            ->title('Đã cập nhật giai đoạn')
            ->body($result['message'])
            ->send();
    }

    public function rejectApplicationFromKanban(): void
    {
        $applicationId = (int) data_get($this->kanbanDropAction, 'application_id');
        $reason = trim($this->kanbanRejectionReason);

        if ($applicationId <= 0) {
            $this->dismissKanbanDropAction();

            return;
        }

        if ($reason === '') {
            $this->setKanbanModalError('Vui lòng nhập lý do từ chối.');

            return;
        }

        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application) {
            $this->showKanbanDropAction([
                'type' => 'blocked',
                'title' => 'Không tìm thấy hồ sơ',
                'message' => 'Hồ sơ không tồn tại hoặc không thuộc phạm vi quản lý hiện tại.',
            ]);

            return;
        }

        $transitionService = app(ApplicationKanbanTransitionService::class);
        $result = $transitionService->evaluateStageMove($application, 'rejected', Auth::user());

        if ($result['requires'] !== 'rejection_reason') {
            $this->showKanbanDropAction([
                'type' => 'blocked',
                'title' => 'Không thể từ chối hồ sơ',
                'message' => $result['message'],
            ]);

            return;
        }

        $application->forceFill([
            'rejected_stage' => $this->rejectedStageFor($application),
            'rejected_reason' => $reason,
        ])->save();

        app(ApplicationPipelineService::class)->transition(
            $application,
            StatusApplicationEnum::REJECTED,
            Auth::user(),
            'Từ chối hồ sơ từ Kanban. Lý do: '.$reason,
        );

        $this->dismissKanbanDropAction();

        Notification::make()
            ->success()
            ->title('Đã từ chối hồ sơ')
            ->body('Hồ sơ đã được chuyển sang giai đoạn từ chối.')
            ->send();
    }

    public function openKanbanRejectionFromCard(int $applicationId): void
    {
        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application || ! app(ApplicationWorkflowGuard::class)->canRejectApplication(Auth::user(), $application)) {
            Notification::make()
                ->warning()
                ->title('Không thể từ chối hồ sơ')
                ->body('Hồ sơ không còn ở trạng thái có thể từ chối.')
                ->send();

            return;
        }

        $this->showKanbanDropAction([
            'type' => 'rejection',
            'title' => 'Từ chối hồ sơ',
            'message' => 'Nhập lý do để lưu lịch sử xử lý và phản hồi phù hợp cho ứng viên.',
            'application_id' => $application->id,
            'candidate' => $application->snapshotCandidateName(),
            'job' => $application->job?->title,
        ]);
    }

    public function screenApplicationFromKanban(): void
    {
        $applicationId = (int) data_get($this->kanbanDropAction, 'application_id');
        $decision = $this->kanbanScreeningDecision;

        if ($applicationId <= 0) {
            $this->dismissKanbanDropAction();

            return;
        }

        if (! in_array($decision, ['pass', 'reject'], true)) {
            $this->setKanbanModalError('Vui lòng chọn kết quả sàng lọc.');

            return;
        }

        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application) {
            $this->showKanbanDropAction([
                'type' => 'blocked',
                'title' => 'Không tìm thấy hồ sơ',
                'message' => 'Hồ sơ không tồn tại hoặc không thuộc phạm vi quản lý hiện tại.',
            ]);

            return;
        }

        $transitionService = app(ApplicationKanbanTransitionService::class);
        $result = $transitionService->evaluateStageMove($application, $decision === 'reject' ? 'rejected' : 'screening', Auth::user());

        if ($decision === 'reject') {
            $reason = trim($this->kanbanScreeningRejectedReason);

            if ($reason === '') {
                $this->setKanbanModalError('Vui lòng nhập lý do từ chối.');

                return;
            }

            if ($result['requires'] !== 'rejection_reason') {
                $this->showKanbanDropAction([
                    'type' => 'blocked',
                    'title' => 'Không thể từ chối hồ sơ',
                    'message' => $result['message'],
                ]);

                return;
            }

            $application->forceFill([
                'rejected_stage' => 'screening',
                'rejected_reason' => $reason,
            ])->save();

            app(ApplicationPipelineService::class)->transition(
                $application,
                StatusApplicationEnum::REJECTED,
                Auth::user(),
                'Sàng lọc CV: Không đạt. Lý do: '.$reason,
            );

            $this->dismissKanbanDropAction();

            Notification::make()
                ->success()
                ->title('Đã từ chối hồ sơ')
                ->body('Hồ sơ đã được chuyển sang giai đoạn từ chối.')
                ->send();

            return;
        }

        if ($result['requires'] !== 'cv_screening') {
            $this->showKanbanDropAction([
                'type' => 'blocked',
                'title' => 'Không thể sàng lọc hồ sơ',
                'message' => $result['message'],
            ]);

            return;
        }

        $note = trim($this->kanbanScreeningNote);

        if ($note === '') {
            $this->setKanbanModalError('Vui lòng nhập ghi chú sàng lọc.');

            return;
        }

        $application->forceFill([
            'rejected_stage' => null,
            'rejected_reason' => null,
        ])->save();

        app(ApplicationPipelineService::class)->transition(
            $application,
            StatusApplicationEnum::SCREENING,
            Auth::user(),
            'Sàng lọc CV: Đạt sơ tuyển. Ghi chú: '.$note,
        );

        $this->dismissKanbanDropAction();

        Notification::make()
            ->success()
            ->title('Đã ghi nhận sàng lọc')
            ->body('Hồ sơ đã được chuyển sang giai đoạn sơ tuyển.')
            ->send();
    }

    public function scheduleInterviewFromKanban(): void
    {
        $applicationId = (int) data_get($this->kanbanDropAction, 'application_id');

        if ($applicationId <= 0) {
            $this->dismissKanbanDropAction();

            return;
        }

        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application) {
            $this->showKanbanDropAction([
                'type' => 'blocked',
                'title' => 'Không tìm thấy hồ sơ',
                'message' => 'Hồ sơ không tồn tại hoặc không thuộc phạm vi quản lý hiện tại.',
            ]);

            return;
        }

        $errors = $this->validateInterviewForm($application);

        if ($errors !== []) {
            $field = (string) array_key_first($errors);
            $this->addError('kanbanInterviewForm.'.$field, (string) reset($errors));

            return;
        }

        $existingInterview = $application->interviews()->latest('id')->first();
        $currentStatus = $this->statusValue($application);

        if ($currentStatus === StatusApplicationEnum::SCREENING->value) {
            $result = app(ApplicationKanbanTransitionService::class)
                ->evaluateStageMove($application, 'interview', Auth::user());

            if ($result['requires'] !== 'interview_schedule') {
                $this->showKanbanDropAction([
                    'type' => 'blocked',
                    'title' => 'Không thể tạo lịch phỏng vấn',
                    'message' => $result['message'],
                ]);

                return;
            }
        } elseif (! app(ApplicationWorkflowGuard::class)->canManageInterview(Auth::user(), $application)) {
            $this->showKanbanDropAction([
                'type' => 'blocked',
                'title' => 'Không thể cập nhật lịch phỏng vấn',
                'message' => 'Lịch chỉ có thể cập nhật trước thời điểm phỏng vấn và trước khi đã có scorecard.',
            ]);

            return;
        }

        $roundNumber = (int) ($existingInterview?->round_number ?: 1);
        $scheduledAt = $this->resolveKanbanInterviewScheduledAt($this->kanbanInterviewForm['scheduled_at']);
        $type = (string) $this->kanbanInterviewForm['type'];

        $interview = $existingInterview ?? new Interview([
            'application_id' => $application->id,
            'round_number' => $roundNumber,
            'round_name' => 'Phỏng vấn vòng '.$roundNumber,
            'duration_minutes' => '',
            'result' => 'pending',
        ]);

        $interview->fill([
            'application_id' => $application->id,
            'interviewer_id' => (int) $this->kanbanInterviewForm['interviewer_id'],
            'round_name' => trim((string) $this->kanbanInterviewForm['round_name']) ?: 'Phỏng vấn vòng '.$roundNumber,
            'duration_minutes' => (int) $this->kanbanInterviewForm['duration_minutes'],
            'scheduled_at' => $scheduledAt,
            'type' => $type,
            'meeting_link' => $type === 'online' ? trim((string) $this->kanbanInterviewForm['meeting_link']) : null,
            'workplace_id' => $type === 'offline' ? (int) $this->kanbanInterviewForm['workplace_id'] : null,
            'notes' => filled($this->kanbanInterviewForm['notes']) ? trim((string) $this->kanbanInterviewForm['notes']) : null,
        ]);
        $interview->save();

        $interview->loadMissing(['application.job.branch', 'application.candidate', 'interviewer', 'workplace']);
        app(InterviewCalendarService::class)->store($interview);

        if ($currentStatus === StatusApplicationEnum::SCREENING->value) {
            app(ApplicationPipelineService::class)->transition(
                $application,
                StatusApplicationEnum::INTERVIEW_SCHEDULED,
                Auth::user(),
                $this->buildInterviewScheduleComment($interview, false),
            );
        } else {
            $application->recordStatusHistory(
                $currentStatus,
                $currentStatus ?? StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
                $this->buildInterviewScheduleComment($interview, true),
            );
        }

        $this->dismissKanbanDropAction();

        Notification::make()
            ->success()
            ->title($existingInterview ? 'Đã cập nhật lịch phỏng vấn' : 'Đã tạo lịch phỏng vấn')
            ->body('Lịch đã được lưu. Bước tiếp theo: gửi lịch phỏng vấn cho ứng viên và người liên quan.')
            ->send();
    }

    public function openInterviewScheduleFromKanban(int $applicationId): void
    {
        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application || ! app(ApplicationWorkflowGuard::class)->canManageInterview(Auth::user(), $application)) {
            Notification::make()
                ->warning()
                ->title('Không thể cập nhật lịch phỏng vấn')
                ->body('Lịch chỉ có thể cập nhật trước thời điểm phỏng vấn và trước khi đã có scorecard.')
                ->send();

            return;
        }

        $this->showKanbanDropAction($this->interviewSchedulePayload($application, true));
    }

    public function openInterviewEvaluationFromKanban(int $applicationId): void
    {
        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application || ! app(ApplicationWorkflowGuard::class)->canEvaluateInterview(Auth::user(), $application)) {
            Notification::make()
                ->warning()
                ->title('Chưa thể chấm phỏng vấn')
                ->body('Chỉ có thể chấm sau thời điểm phỏng vấn và với tài khoản được phân công.')
                ->send();

            return;
        }

        $this->showKanbanDropAction($this->interviewEvaluationPayload($application));
    }

    public function openOfferDraftFromKanban(int $applicationId): void
    {
        $application = ApplicationResource::getEloquentQuery()->whereKey($applicationId)->first();

        if (! $application || ! app(ApplicationWorkflowGuard::class)->canManageOffer(Auth::user(), $application)) {
            Notification::make()->warning()->title('Chưa thể xử lý đề nghị')->body('Hồ sơ này không còn ở giai đoạn cho phép tạo hoặc chỉnh sửa đề nghị.')->send();

            return;
        }

        $this->showKanbanDropAction($this->offerDraftPayload($application));
    }

    public function saveOfferDraftFromKanban(): void
    {
        $application = ApplicationResource::getEloquentQuery()
            ->whereKey((int) data_get($this->kanbanDropAction, 'application_id'))
            ->first();

        if (! $application) {
            $this->setKanbanModalError('Không tìm thấy hồ sơ cần tạo đề nghị.');

            return;
        }

        try {
            $offer = app(OfferWorkflowService::class)->saveDraft($application, $this->kanbanOfferForm, Auth::user());
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError('kanbanOfferForm.'.$field, (string) ($messages[0] ?? 'Dữ liệu đề nghị chưa hợp lệ.'));
            }

            return;
        }

        $this->dismissKanbanDropAction();

        Notification::make()
            ->success()
            ->title('Đã lưu bản nháp đề nghị')
            ->body(filled($offer->pdf_path)
                ? 'PDF đề nghị đã được cập nhật. Bạn có thể gửi giám đốc duyệt khi đã kiểm tra xong.'
                : 'Đề nghị đã được lưu. Bạn có thể gửi giám đốc duyệt khi đã kiểm tra xong.')
            ->send();
    }

    public function requestOfferApprovalFromKanban(int $applicationId): void
    {
        $application = ApplicationResource::getEloquentQuery()->whereKey($applicationId)->first();

        if (! $application) {
            return;
        }

        $offer = $application->offers()->latest('id')->first();
        if (! $offer || ! in_array($offer->status, ['draft', 'rejected'], true)) {
            Notification::make()->warning()->title('Chưa thể gửi duyệt')->body('Đề nghị cần ở trạng thái bản nháp hoặc cần điều chỉnh trước khi gửi duyệt.')->send();

            return;
        }

        $this->showKanbanDropAction([
            'type' => 'offer_approval',
            'title' => $offer->status === 'rejected' ? 'Gửi lại đề nghị tuyển dụng' : 'Gửi duyệt đề nghị tuyển dụng',
            'message' => 'Đề nghị sẽ được gửi đến giám đốc chi nhánh để xem xét trước khi gửi ứng viên.',
            'application_id' => $application->id,
            'candidate' => $application->snapshotCandidateName(),
            'job' => $application->job?->title,
        ]);
    }

    public function submitOfferForApprovalFromKanban(): void
    {
        $application = ApplicationResource::getEloquentQuery()
            ->whereKey((int) data_get($this->kanbanDropAction, 'application_id'))
            ->first();

        if (! $application) {
            $this->setKanbanModalError('Không tìm thấy hồ sơ cần gửi duyệt.');

            return;
        }

        try {
            $result = app(OfferWorkflowService::class)->submitForApproval($application, Auth::user());
        } catch (ValidationException $exception) {
            $this->setKanbanModalError((string) collect($exception->errors())->flatten()->first());

            return;
        }

        $this->dismissKanbanDropAction();

        Notification::make()
            ->success()
            ->title('Đã gửi đề nghị chờ duyệt')
            ->body($result['failed'] > 0
                ? 'Đề nghị đã chuyển sang chờ duyệt. Một số email thông báo chưa gửi được.'
                : 'Giám đốc chi nhánh đã nhận được đề nghị để xem xét.')
            ->send();
    }

    public function saveInterviewEvaluationDraftFromKanban(): void
    {
        $applicationId = (int) data_get($this->kanbanDropAction, 'application_id');
        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application) {
            $this->setKanbanModalError('Không tìm thấy hồ sơ cần đánh giá.');

            return;
        }

        try {
            $result = app(InterviewEvaluationService::class)->saveDraft(
                $application,
                $this->kanbanEvaluationForm,
                Auth::user(),
            );
        } catch (ValidationException $exception) {
            $errors = $exception->errors();
            $field = (string) array_key_first($errors);
            $message = (string) ($errors[$field][0] ?? 'Dữ liệu đánh giá chưa hợp lệ.');

            if (in_array($field, ['interview', 'criteria'], true)) {
                $this->setKanbanModalError($message);
            } else {
                $this->addError('kanbanEvaluationForm.'.$field, $message);
            }

            return;
        }

        $this->kanbanEvaluationDraftSavedAt = now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i');
        $this->kanbanEvaluationDraftStatus = $result['saved']
            ? ($result['is_complete']
                ? 'Đã lưu đủ điểm. Có thể hoàn tất đánh giá khi buổi phỏng vấn kết thúc.'
                : 'Đã lưu đánh giá tạm. Hồ sơ chưa chuyển giai đoạn.')
            : 'Không có thay đổi mới cần lưu.';
    }

    public function completeInterviewEvaluationFromKanban(): void
    {
        $applicationId = (int) data_get($this->kanbanDropAction, 'application_id');
        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application) {
            $this->setKanbanModalError('Không tìm thấy hồ sơ cần đánh giá.');

            return;
        }

        try {
            $result = app(InterviewEvaluationService::class)->complete(
                $application,
                $this->kanbanEvaluationForm,
                Auth::user(),
            );
        } catch (ValidationException $exception) {
            $errors = $exception->errors();
            $field = (string) array_key_first($errors);
            $message = (string) ($errors[$field][0] ?? 'Dữ liệu đánh giá chưa hợp lệ.');

            if (in_array($field, ['interview', 'criteria'], true)) {
                $this->setKanbanModalError($message);
            } else {
                $this->addError('kanbanEvaluationForm.'.$field, $message);
            }

            return;
        }

        $this->dismissKanbanDropAction();

        Notification::make()
            ->success()
            ->title('Đã hoàn tất đánh giá phỏng vấn')
            ->body(match ($result['conclusion']) {
                'pass' => 'Ứng viên đạt phỏng vấn - hồ sơ đã chuyển sang Đề nghị tuyển dụng.',
                'fail' => 'Ứng viên không đạt phỏng vấn - hồ sơ đã chuyển sang Từ chối.',
                default => 'Đánh giá đã hoàn tất. Hồ sơ được giữ ở giai đoạn Phỏng vấn để xem xét thêm.',
            })
            ->send();
    }

    public function updatedKanbanEvaluationForm(mixed $value, string $key): void
    {
        $this->resetValidation('kanbanEvaluationForm.'.$key);

        if ($key !== 'template_id' || blank($value)) {
            return;
        }

        $criteria = ScorecardTemplate::query()->find($value)?->criteria;
        if (is_array($criteria) && $criteria !== []) {
            $this->kanbanEvaluationForm['criteria'] = $criteria;
            $this->resetValidation('kanbanEvaluationForm.criteria');
        }
    }

    public function requestInterviewScheduleDeliveryFromKanban(int $applicationId): void
    {
        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application || ! app(ApplicationWorkflowGuard::class)->canSendInterviewSchedule(Auth::user(), $application)) {
            Notification::make()
                ->warning()
                ->title('Chưa thể gửi lịch phỏng vấn')
                ->body('Hãy lưu lịch mới hoặc cập nhật lịch trước khi gửi.')
                ->send();

            return;
        }

        $interview = $application->latestInterview ?? $application->interviews()->latest('id')->first();
        $isUpdate = filled($interview?->invite_sent_at);
        $recipientCount = count(app(InterviewScheduleDeliveryService::class)->recipients($application));

        $this->showKanbanDropAction([
            'type' => 'interview_delivery',
            'title' => $isUpdate ? 'Gửi cập nhật lịch phỏng vấn' : 'Gửi lịch phỏng vấn',
            'message' => $isUpdate
                ? 'Thông tin lịch mới và file lịch sẽ được gửi lại cho ứng viên cùng những người liên quan.'
                : 'Thông tin lịch và file lịch sẽ được gửi cho ứng viên cùng những người liên quan.',
            'application_id' => $application->id,
            'candidate' => $application->snapshotCandidateName(),
            'job' => $application->job?->title,
            'recipient_count' => $recipientCount,
            'is_update' => $isUpdate,
        ]);
    }

    public function sendInterviewScheduleFromKanban(): void
    {
        $applicationId = (int) data_get($this->kanbanDropAction, 'application_id');
        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application || ! app(ApplicationWorkflowGuard::class)->canSendInterviewSchedule(Auth::user(), $application)) {
            $this->dismissKanbanDropAction();

            Notification::make()
                ->warning()
                ->title('Chưa thể gửi lịch phỏng vấn')
                ->body('Lịch đã thay đổi hoặc không còn ở trạng thái có thể gửi. Vui lòng kiểm tra lại.')
                ->send();

            return;
        }

        $result = app(InterviewScheduleDeliveryService::class)->deliver($application);
        $this->dismissKanbanDropAction();

        $notification = Notification::make()->title($result['is_update'] ? 'Đã gửi cập nhật lịch' : 'Đã gửi lịch phỏng vấn');

        if (! $result['has_interview'] || $result['sent'] === 0) {
            $notification->warning()->body('Chưa gửi được email. Vui lòng kiểm tra thông tin người nhận và cấu hình mail.');
        } elseif ($result['failed'] > 0) {
            $notification->warning()->body('Lịch đã được gửi, nhưng một số người nhận chưa nhận được email.');
        } else {
            $notification->success()->body($result['is_update']
                ? 'Ứng viên và người liên quan đã nhận lịch cập nhật kèm file lịch.'
                : 'Ứng viên và người liên quan đã nhận lịch kèm file lịch.');
        }

        $notification->send();
    }

    public function checkInterviewAvailabilityFromKanban(): void
    {
        $applicationId = (int) data_get($this->kanbanDropAction, 'application_id');
        $this->kanbanInterviewAvailabilityNotice = '';

        if ($applicationId <= 0 || data_get($this->kanbanDropAction, 'type') !== 'interview_schedule') {
            $this->kanbanInterviewAvailabilityNotice = 'Không tìm thấy hồ sơ cần kiểm tra.';

            return;
        }

        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application) {
            $this->kanbanInterviewAvailabilityNotice = 'Không tìm thấy hồ sơ cần kiểm tra.';

            return;
        }

        $notice = $this->interviewAvailabilityNotice($application);

        if ($notice) {
            $this->kanbanInterviewAvailabilityNotice = $notice;
        }
    }

    public function updatedKanbanInterviewForm(mixed $value, string $key): void
    {
        $this->resetValidation('kanbanInterviewForm.'.$key);

        if (! in_array($key, ['scheduled_at', 'duration_minutes', 'type', 'workplace_id', 'interviewer_id'], true)) {
            return;
        }

        $this->refreshKanbanInterviewAvailabilityPreview();
    }

    public function updatedKanbanOfferForm(mixed $value, string $key): void
    {
        $this->resetValidation('kanbanOfferForm.'.$key);
    }

    public function analyzeScreeningAiFromKanban(): void
    {
        $applicationId = (int) data_get($this->kanbanDropAction, 'application_id');

        if ($applicationId <= 0 || data_get($this->kanbanDropAction, 'type') !== 'screening') {
            $this->setKanbanModalError('Không tìm thấy hồ sơ cần phân tích.');

            return;
        }

        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application) {
            $this->setKanbanModalError('Hồ sơ không tồn tại hoặc không thuộc phạm vi quản lý hiện tại.');

            return;
        }

        $currentAnalysis = $this->screeningAiAnalysisForDisplay($application);
        $force = $currentAnalysis?->status === 'completed';
        $analysis = app(ApplicationAiAnalysisService::class)
            ->analyzeScreening($application, Auth::user(), 'admin-kanban', $force);

        $application->refresh()->loadMissing(['latestScreeningAiAnalysis']);
        $this->kanbanDropAction['screening_context'] = $this->screeningContext($application);

        if ($analysis->status === 'completed') {
            return;
        }

        $this->setKanbanModalError('Chưa thể phân tích AI. Vui lòng kiểm tra CV, tin tuyển dụng hoặc cấu hình AI.');
    }

    public function dismissKanbanDropAction(): void
    {
        $this->kanbanDropAction = null;
        $this->kanbanModalError = '';
        $this->kanbanModalErrorKey = 0;
        $this->kanbanRejectionReason = '';
        $this->kanbanScreeningDecision = '';
        $this->kanbanScreeningNote = '';
        $this->kanbanScreeningRejectedReason = '';
        $this->kanbanInterviewAvailabilityNotice = '';
        $this->kanbanInterviewSchedulePreview = $this->emptyInterviewSchedulePreview();
        $this->resetKanbanInterviewForm();
        $this->resetKanbanEvaluationForm();
        $this->resetKanbanOfferForm();
    }

    /**
     * @param  array{allowed: bool, target_status: ?string, requires: ?string, message: string}  $result
     * @return array<string, mixed>
     */
    private function requiredActionPayload(Application $application, string $targetStage, array $result): array
    {
        if ($result['requires'] === 'rejection_reason') {
            return [
                'type' => 'rejection',
                'title' => 'Từ chối hồ sơ',
                'message' => $result['message'],
                'application_id' => $application->id,
                'candidate' => $application->snapshotCandidateName(),
                'job' => $application->job?->title,
            ];
        }

        if ($result['requires'] === 'cv_screening') {
            return [
                'type' => 'screening',
                'title' => 'Sàng lọc CV',
                'message' => $result['message'],
                'application_id' => $application->id,
                'candidate' => $application->snapshotCandidateName(),
                'job' => $application->job?->title,
                'target_stage' => $targetStage,
                'screening_context' => $this->screeningContext($application),
            ];
        }

        if ($result['requires'] === 'interview_schedule') {
            return $this->interviewSchedulePayload($application, false, $result['message'], $targetStage);
        }

        if ($result['requires'] === 'interview_evaluation') {
            if (app(ApplicationWorkflowGuard::class)->canEvaluateInterview(Auth::user(), $application)) {
                return $this->interviewEvaluationPayload($application);
            }

            return [
                'type' => 'requirement',
                'title' => 'Chưa đến thời điểm đánh giá',
                'message' => 'Có thể chấm scorecard khi buổi phỏng vấn đã đến thời điểm đánh giá và tài khoản được phân công.',
                'application_id' => $application->id,
                'candidate' => $application->snapshotCandidateName(),
                'job' => $application->job?->title,
            ];
        }

        if ($result['requires'] === 'offer_draft') {
            return $this->offerDraftPayload($application);
        }

        return [
            'type' => 'requirement',
            'title' => $this->requirementTitle((string) $result['requires']),
            'message' => $result['message'],
            'application_id' => $application->id,
            'candidate' => $application->snapshotCandidateName(),
            'job' => $application->job?->title,
            'target_stage' => $targetStage,
            'action_label' => $this->requirementActionLabel((string) $result['requires']),
            'action_url' => $this->tableSearchUrl($application),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function interviewSchedulePayload(
        Application $application,
        bool $isUpdate,
        ?string $message = null,
        ?string $targetStage = null,
    ): array {
        return [
            'type' => 'interview_schedule',
            'title' => $isUpdate ? 'Cập nhật lịch phỏng vấn' : 'Tạo lịch phỏng vấn',
            'message' => $message ?? ($isUpdate
                ? 'Điều chỉnh lịch trước khi buổi phỏng vấn diễn ra. Sau khi lưu, hãy gửi cập nhật lịch cho ứng viên.'
                : 'Tạo lịch trước khi chuyển hồ sơ sang giai đoạn phỏng vấn.'),
            'application_id' => $application->id,
            'candidate' => $application->snapshotCandidateName(),
            'job' => $application->job?->title,
            'target_stage' => $targetStage,
            'interview_context' => $this->interviewScheduleContext($application),
            'form' => $this->interviewFormDefaults($application),
            'interviewer_options' => $this->interviewerOptions($application),
            'workplace_options' => $this->workplaceOptions($application),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function interviewEvaluationPayload(Application $application): array
    {
        $interview = $application->interviews()->latest('id')->first();
        $scorecard = $interview
            ? $application->scorecards()
                ->where('interview_id', $interview->id)
                ->where('evaluator_id', Auth::id())
                ->latest('id')
                ->first()
            : null;
        $criteria = $scorecard?->criteria;
        $canFinalize = app(ApplicationWorkflowGuard::class)->canFinalizeInterviewEvaluation(Auth::user(), $application);

        return [
            'type' => 'interview_evaluation',
            'title' => $canFinalize ? 'Hoàn tất đánh giá phỏng vấn' : 'Ghi nhận đánh giá phỏng vấn',
            'message' => $canFinalize
                ? 'Kiểm tra lại scorecard và hoàn tất đánh giá để xử lý bước tiếp theo.'
                : 'Ghi nhận điểm và nhận xét trong buổi phỏng vấn. Hồ sơ chỉ chuyển bước khi hoàn tất đánh giá.',
            'application_id' => $application->id,
            'candidate' => $application->snapshotCandidateName(),
            'job' => $application->job?->title,
            'interview' => [
                'round_name' => $interview?->round_name ?: 'Vòng phỏng vấn',
                'scheduled_at' => $interview?->scheduled_at?->format('H:i, d/m/Y') ?: '-',
                'interviewer' => $interview?->interviewer?->name ?: '-',
                'type' => $interview?->type === 'online' ? 'Trực tuyến' : 'Trực tiếp',
            ],
            'template_options' => ScorecardTemplate::query()
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            'form' => [
                'template_id' => (string) ($scorecard?->template_id ?? ''),
                'criteria' => is_array($criteria) ? $criteria : [],
                'conclusion' => (string) ($scorecard?->conclusion ?? ''),
                'notes' => (string) ($scorecard?->notes ?? ''),
                'override_reason' => (string) ($scorecard?->override_reason ?? ''),
                'rejected_reason' => (string) ($application->rejected_reason ?? ''),
                'confirm_early_completion' => false,
            ],
            'can_finalize' => $canFinalize,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    /** @return array<string, mixed> */
    private function offerDraftPayload(Application $application): array
    {
        $offer = $application->offers()->latest('id')->first();
        $isReplacement = in_array($offer?->status, ['declined', 'expired'], true);

        return [
            'type' => 'offer_draft',
            'title' => $isReplacement ? 'Tạo đề nghị tuyển dụng mới' : ($offer ? 'Chỉnh sửa đề nghị tuyển dụng' : 'Tạo đề nghị tuyển dụng'),
            'message' => $isReplacement
                ? 'Đề nghị trước đã kết thúc. Thông tin mới sẽ được lưu thành một đề nghị riêng.'
                : 'Lưu bản nháp trước, sau đó chủ động gửi giám đốc chi nhánh duyệt.',
            'application_id' => $application->id,
            'candidate' => $application->snapshotCandidateName(),
            'job' => $application->job?->title,
            'approval_note' => $offer?->status === 'rejected' ? $offer->approval_notes : null,
            'offer_context' => $this->offerDraftContext($application),
            'template_options' => OfferLetterTemplate::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            'form' => [
                'offer_letter_template_id' => (string) ($offer?->offer_letter_template_id ?? ''),
                'salary_offered' => (string) ($offer?->salary_offered ?? ''),
                'probation_months' => (int) ($offer?->probation_months ?? 2),
                'start_date' => $offer?->start_date?->format('Y-m-d') ?? '',
                'expires_at' => $offer?->expires_at?->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('Y-m-d\\TH:i')
                    ?? now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->addDays(3)->format('Y-m-d\\TH:i'),
                'content' => (string) ($offer?->content ?? ''),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function offerDraftContext(Application $application): array
    {
        $scorecard = $application->scorecards()
            ->whereNotNull('conclusion')
            ->latest('id')
            ->first();
        $analysis = $this->screeningAiContext($this->screeningAiAnalysisForDisplay($application));

        return [
            'candidate_name' => $application->snapshotCandidateName() ?: 'Ứng viên',
            'job_title' => $application->job?->title ?: '-',
            'branch' => $application->job?->branch?->name ?? $application->branch?->name ?? '-',
            'interview_result' => app(InterviewEvaluationService::class)->conclusionLabel($scorecard?->conclusion),
            'average_score' => $scorecard?->average_score !== null
                ? number_format((float) $scorecard->average_score, 2, ',', '.').'/10'
                : '-',
            'recommendation' => app(InterviewEvaluationService::class)->conclusionLabel($scorecard?->recommended_conclusion),
            'interview_note' => $this->compactAiText($scorecard?->notes ?: 'Chưa có nhận xét tổng quan từ buổi phỏng vấn.', 180),
            'ai_summary' => $analysis['available']
                ? $this->compactAiText((string) ($analysis['summary'] ?? ''), 180)
                : null,
            'cv_name' => $application->submittedCvName() ?: 'CV ứng tuyển',
            'cv_url' => $application->submittedCvUrl(),
        ];
    }

    private function showKanbanDropAction(array $payload): void
    {
        $this->kanbanDropAction = $payload;
        $this->kanbanModalError = '';
        $this->kanbanModalErrorKey = 0;
        $this->kanbanRejectionReason = '';
        $this->kanbanScreeningDecision = '';
        $this->kanbanScreeningNote = '';
        $this->kanbanScreeningRejectedReason = '';
        $this->kanbanInterviewAvailabilityNotice = '';
        $this->kanbanInterviewSchedulePreview = $this->emptyInterviewSchedulePreview();
        $this->resetKanbanInterviewForm();
        $this->resetKanbanEvaluationForm();
        $this->resetKanbanOfferForm();

        if (($payload['type'] ?? null) === 'interview_schedule' && is_array($payload['form'] ?? null)) {
            $this->kanbanInterviewForm = array_merge($this->kanbanInterviewForm, $payload['form']);

            if (filled($this->kanbanInterviewForm['interviewer_id']) || filled($this->kanbanInterviewForm['workplace_id'])) {
                $this->refreshKanbanInterviewAvailabilityPreview();
            } else {
                $this->kanbanInterviewSchedulePreview = [
                    'title' => 'Lịch phỏng vấn tham khảo',
                    'empty' => 'Chọn người phỏng vấn hoặc địa điểm để xem lịch liên quan.',
                    'items' => [],
                    'suggestions' => [],
                ];
            }
        }

        if (($payload['type'] ?? null) === 'interview_evaluation' && is_array($payload['form'] ?? null)) {
            $this->kanbanEvaluationForm = array_merge($this->kanbanEvaluationForm, $payload['form']);
        }

        if (($payload['type'] ?? null) === 'offer_draft' && is_array($payload['form'] ?? null)) {
            $this->kanbanOfferForm = array_merge($this->kanbanOfferForm, $payload['form']);
        }
    }

    private function resetKanbanInterviewForm(): void
    {
        $this->kanbanInterviewForm = [
            'round_name' => '',
            'scheduled_at' => '',
            'duration_minutes' => '',
            'type' => '',
            'meeting_link' => '',
            'workplace_id' => '',
            'interviewer_id' => '',
            'notes' => '',
        ];
    }

    private function resetKanbanEvaluationForm(): void
    {
        $this->kanbanEvaluationDraftSavedAt = null;
        $this->kanbanEvaluationDraftStatus = null;
        $this->kanbanEvaluationForm = [
            'template_id' => '',
            'criteria' => [],
            'conclusion' => '',
            'notes' => '',
            'override_reason' => '',
            'rejected_reason' => '',
            'confirm_early_completion' => false,
        ];
    }

    private function resetKanbanOfferForm(): void
    {
        $this->kanbanOfferForm = [
            'offer_letter_template_id' => '',
            'salary_offered' => '',
            'probation_months' => 2,
            'start_date' => '',
            'expires_at' => '',
            'content' => '',
        ];
    }

    /**
     * @return array<int, array{name: string, score: null, note: null}>
     */
    private function defaultInterviewCriteria(): array
    {
        return [
            ['name' => 'Kinh nghiệm phù hợp vị trí', 'score' => null, 'note' => null],
            ['name' => 'Kỹ năng chuyên môn', 'score' => null, 'note' => null],
            ['name' => 'Tư duy giải quyết vấn đề', 'score' => null, 'note' => null],
            ['name' => 'Kỹ năng giao tiếp', 'score' => null, 'note' => null],
            ['name' => 'Thái độ và mức độ phù hợp văn hóa', 'score' => null, 'note' => null],
        ];
    }

    private function setKanbanModalError(string $message): void
    {
        $this->kanbanModalError = $message;
        $this->kanbanModalErrorKey++;
    }

    /**
     * @return array<string, mixed>
     */
    private function screeningContext(Application $application): array
    {
        $analysis = $this->screeningAiAnalysisForDisplay($application);

        return [
            'candidate_email' => $application->snapshotCandidateEmail() ?: '-',
            'candidate_phone' => $application->snapshotCandidatePhone() ?: '-',
            'experience' => is_numeric($application->snapshotCandidateExperienceYears())
                ? $application->snapshotCandidateExperienceYears().' năm'
                : '-',
            'profile_title' => $application->snapshotProfileTitle() ?: '-',
            'cv_name' => $application->submittedCvName() ?: 'CV ứng tuyển',
            'cv_url' => $application->submittedCvUrl(),
            'ai' => $this->screeningAiContext($analysis),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function screeningAiContext(mixed $analysis): array
    {
        if (! $analysis) {
            return [
                'available' => false,
                'status' => 'missing',
                'label' => 'Chưa có khuyến nghị',
                'summary' => 'Có thể phân tích CV bằng AI ở thao tác sàng lọc để có thêm căn cứ tham khảo.',
            ];
        }

        if ($analysis->status !== 'completed') {
            return [
                'available' => false,
                'status' => $analysis->status,
                'label' => $analysis->status === 'failed' ? 'Cần kiểm tra' : 'Đang xử lý',
                'summary' => $analysis->error_message ?: 'Kết quả phân tích AI chưa hoàn tất.',
            ];
        }

        return [
            'available' => true,
            'status' => 'completed',
            'label' => $this->aiRecommendationLabel($analysis->recommendation),
            'score' => is_numeric($analysis->score) ? (int) $analysis->score : null,
            'score_tone' => $this->aiScoreTone(is_numeric($analysis->score) ? (int) $analysis->score : null),
            'summary' => $this->compactAiText($analysis->summary ?: 'AI chưa trả về tóm tắt.', 220),
            'strengths' => $this->compactAiItems((array) $analysis->strengths),
            'gaps' => $this->compactAiItems((array) $analysis->gaps),
            'suggested_note' => $analysis->suggested_note ? $this->compactAiText($analysis->suggested_note, 260) : null,
        ];
    }

    private function screeningAiAnalysisForDisplay(Application $application): mixed
    {
        $latest = $application->latestScreeningAiAnalysis;

        if (! $latest || $latest->status === 'completed') {
            return $latest;
        }

        return $application->aiAnalyses()
            ->where('analysis_type', 'screening')
            ->where('status', 'completed')
            ->latest('id')
            ->first() ?: $latest;
    }

    /**
     * @return array<string, mixed>
     */
    private function interviewScheduleContext(Application $application): array
    {
        $summary = app(ApplicationWorkflowSummaryService::class)->summarize($application);
        $analysis = $this->screeningAiContext($this->screeningAiAnalysisForDisplay($application));
        $screeningHistory = $application->statusHistories()
            ->where('to_status', StatusApplicationEnum::SCREENING->value)
            ->latest('id')
            ->first();

        return [
            'candidate_name' => $application->snapshotCandidateName(),
            'candidate_email' => $application->snapshotCandidateEmail() ?: '-',
            'candidate_phone' => $application->snapshotCandidatePhone() ?: '-',
            'job_title' => $application->job?->title ?: '-',
            'branch' => $application->job?->branch?->name ?? $application->branch?->name ?? '-',
            'department' => $application->job?->department?->name ?: '-',
            'current_stage' => $summary['stage_label'] ?? 'Sơ tuyển',
            'current_status' => $summary['status_label'] ?? 'Cần tạo lịch phỏng vấn',
            'screening_note' => $this->compactAiText($screeningHistory?->comment ?: 'Chưa có ghi chú sàng lọc.', 180),
            'ai' => $analysis,
            'cv_name' => $application->submittedCvName() ?: 'CV ứng tuyển',
            'cv_url' => $application->submittedCvUrl(),
        ];
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, string>
     */
    private function compactAiItems(array $items): array
    {
        return collect($items)
            ->filter(fn ($value): bool => filled($value))
            ->take(2)
            ->map(fn ($value): string => $this->compactAiText((string) $value, 110))
            ->values()
            ->all();
    }

    private function compactAiText(?string $text, int $limit): string
    {
        $text = trim((string) preg_replace('/\s+/', ' ', (string) $text));

        if ($text === '' || Str::length($text) <= $limit) {
            return $text;
        }

        $sentences = preg_split('/(?<=[.!?])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $compact = '';

        foreach ($sentences as $sentence) {
            $candidate = trim($compact.' '.$sentence);

            if ($candidate !== '' && Str::length($candidate) <= $limit) {
                $compact = $candidate;

                continue;
            }

            break;
        }

        if ($compact !== '') {
            return $compact;
        }

        $truncated = Str::substr($text, 0, $limit);
        $softCut = preg_replace('/\s+\S*$/u', '', $truncated);

        if (is_string($softCut) && Str::length(trim($softCut)) >= (int) floor($limit * 0.65)) {
            $truncated = trim($softCut);
        }

        return rtrim($truncated, " \t\n\r\0\x0B.,;:").'.';
    }

    private function aiRecommendationLabel(?string $recommendation): string
    {
        return match ($recommendation) {
            'pass' => 'Ưu tiên sơ tuyển',
            'consider' => 'Cần đối chiếu thêm',
            'reject' => 'Chưa nên chuyển bước',
            default => 'Chưa có khuyến nghị',
        };
    }

    private function aiScoreTone(?int $score): string
    {
        return match (true) {
            $score === null => 'neutral',
            $score < 50 => 'low',
            $score < 75 => 'medium',
            default => 'high',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function interviewFormDefaults(Application $application): array
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $interview = $application->interviews()->latest('id')->first();
        $scheduledAt = $interview?->scheduled_at
            ? $interview->scheduled_at->copy()->setTimezone($timezone)
            : now($timezone)->addDay()->setMinute(0)->setSecond(0);

        return [
            'round_name' => $interview?->round_name ?: '',
            'scheduled_at' => $interview ? $scheduledAt->format('Y-m-d\TH:i') : '',
            'duration_minutes' => $interview?->duration_minutes ? (string) $interview->duration_minutes : '',
            'type' => $interview?->type ?: '',
            'meeting_link' => (string) ($interview?->meeting_link ?: ''),
            'workplace_id' => $interview?->workplace_id ? (string) $interview->workplace_id : '',
            'interviewer_id' => $interview?->interviewer_id ? (string) $interview->interviewer_id : '',
            'notes' => (string) ($interview?->notes ?: ''),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function interviewerOptions(Application $application): array
    {
        $branchId = $application->job?->branch_id;

        if (! $branchId) {
            return [];
        }

        return User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', ['director', 'pm', 'hr']))
            ->with(['branch', 'roles'])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $user): array => [$user->id => $this->formatInterviewerLabel($user)])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function workplaceOptions(Application $application): array
    {
        $branchId = $application->job?->branch_id;

        if (! $branchId) {
            return [];
        }

        return Workplace::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Workplace $workplace): array => [$workplace->id => $this->formatWorkplaceLabel($workplace)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function validateInterviewForm(Application $application): array
    {
        $duration = (int) ($this->kanbanInterviewForm['duration_minutes'] ?? 0);
        $type = (string) ($this->kanbanInterviewForm['type'] ?? '');
        $interviewerOptions = $this->interviewerOptions($application);
        $workplaceOptions = $this->workplaceOptions($application);

        if (blank($this->kanbanInterviewForm['round_name'] ?? null)) {
            return ['round_name' => 'Vui lòng nhập tên vòng phỏng vấn.'];
        }

        if (blank($this->kanbanInterviewForm['scheduled_at'] ?? null)) {
            return ['scheduled_at' => 'Vui lòng chọn thời gian phỏng vấn.'];
        }

        try {
            $scheduledAt = $this->resolveKanbanInterviewScheduledAt($this->kanbanInterviewForm['scheduled_at']);
        } catch (\Throwable) {
            return ['scheduled_at' => 'Thời gian phỏng vấn không hợp lệ.'];
        }

        if ($scheduledAt->lt(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')))) {
            return ['scheduled_at' => 'Thời gian phỏng vấn không được ở quá khứ.'];
        }

        if (! in_array($duration, [30, 45, 60, 90], true)) {
            return ['duration_minutes' => 'Vui lòng chọn thời lượng phỏng vấn hợp lệ.'];
        }

        $interviewerId = (int) ($this->kanbanInterviewForm['interviewer_id'] ?? 0);

        if ($interviewerId <= 0 || ! array_key_exists($interviewerId, $interviewerOptions)) {
            return ['interviewer_id' => 'Vui lòng chọn người phỏng vấn thuộc chi nhánh.'];
        }

        if (! in_array($type, ['online', 'offline'], true)) {
            return ['type' => 'Vui lòng chọn hình thức phỏng vấn.'];
        }

        if ($type === 'online') {
            $meetingLink = trim((string) ($this->kanbanInterviewForm['meeting_link'] ?? ''));

            if (! app(InterviewMeetingLinkValidator::class)->isValid($meetingLink)) {
                return ['meeting_link' => 'Dùng link họp https hợp lệ, ví dụ Google Meet/Zoom/Teams.'];
            }
        }

        $workplaceId = (int) ($this->kanbanInterviewForm['workplace_id'] ?? 0);

        if ($type === 'offline' && ($workplaceId <= 0 || ! array_key_exists($workplaceId, $workplaceOptions))) {
            return ['workplace_id' => 'Vui lòng chọn địa điểm phỏng vấn thuộc chi nhánh.'];
        }

        $availabilityNotice = $this->interviewAvailabilityNotice($application);

        if ($availabilityNotice === 'Người phỏng vấn đang bận khung giờ này.') {
            return ['interviewer_id' => $availabilityNotice];
        }

        if ($availabilityNotice === 'Phòng đã có lịch trong khung giờ này.') {
            return ['workplace_id' => $availabilityNotice];
        }

        return [];
    }

    private function refreshKanbanInterviewAvailabilityPreview(): void
    {
        $applicationId = (int) data_get($this->kanbanDropAction, 'application_id');

        if ($applicationId <= 0 || data_get($this->kanbanDropAction, 'type') !== 'interview_schedule') {
            return;
        }

        $application = ApplicationResource::getEloquentQuery()
            ->whereKey($applicationId)
            ->first();

        if (! $application) {
            return;
        }

        if (! $this->shouldLoadInterviewSchedulePreview()) {
            $this->kanbanInterviewAvailabilityNotice = '';
            $this->kanbanInterviewSchedulePreview = [
                'title' => 'Lịch phỏng vấn tham khảo',
                'empty' => 'Chọn người phỏng vấn hoặc địa điểm để xem lịch liên quan.',
                'items' => [],
                'suggestions' => [],
            ];

            return;
        }

        $this->kanbanInterviewAvailabilityNotice = $this->interviewAvailabilityNotice($application, false) ?: '';
        $this->kanbanInterviewSchedulePreview = $this->interviewSchedulePreview($application);
    }

    private function shouldLoadInterviewSchedulePreview(): bool
    {
        return (int) ($this->kanbanInterviewForm['interviewer_id'] ?? 0) > 0
            || (
                ($this->kanbanInterviewForm['type'] ?? '') === 'offline'
                && (int) ($this->kanbanInterviewForm['workplace_id'] ?? 0) > 0
            );
    }

    /**
     * @return array{title: string, empty: string, items: array<int, array{time: string, title: string, meta: string}>, suggestions: array<int, string>}
     */
    private function emptyInterviewSchedulePreview(): array
    {
        return [
            'title' => 'Lịch phỏng vấn gần nhất',
            'empty' => 'Chưa có lịch sắp tới.',
            'items' => [],
            'suggestions' => [],
        ];
    }

    /**
     * @return array{title: string, empty: string, items: array<int, array{time: string, title: string, meta: string}>, suggestions: array<int, string>}
     */
    private function interviewSchedulePreview(Application $application): array
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $interviewerId = (int) ($this->kanbanInterviewForm['interviewer_id'] ?? 0);
        $workplaceId = (int) ($this->kanbanInterviewForm['workplace_id'] ?? 0);
        $type = (string) ($this->kanbanInterviewForm['type'] ?? '');
        $branchId = $application->job?->branch_id;
        $existingInterview = $application->interviews()->latest('id')->first();

        $title = 'Lịch phỏng vấn gần nhất';

        $query = Interview::query()
            ->with(['application.job', 'interviewer', 'workplace'])
            ->where('result', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now($timezone)->startOfMinute())
            ->when($existingInterview?->id, fn (Builder $query): Builder => $query->whereKeyNot($existingInterview->id))
            ->when($branchId, function (Builder $query) use ($branchId): Builder {
                return $query->whereHas('application.job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId));
            });

        if ($interviewerId > 0) {
            $query->where('interviewer_id', $interviewerId);
            $title = 'Lịch gần nhất của người phỏng vấn';
        } elseif ($type === 'offline' && $workplaceId > 0) {
            $query->where('workplace_id', $workplaceId);
            $title = 'Lịch gần nhất của phòng';
        }

        $items = $query
            ->orderBy('scheduled_at')
            ->limit(3)
            ->get()
            ->map(function (Interview $interview) use ($timezone): array {
                $start = $interview->scheduled_at?->copy()->setTimezone($timezone);
                $duration = max(15, (int) ($interview->duration_minutes ?: 60));
                $end = $start?->copy()->addMinutes($duration);
                $time = $start && $end
                    ? $start->format('H:i').' - '.$end->format('H:i, d/m')
                    : '-';
                $application = $interview->application;
                $title = trim(implode(' - ', array_filter([
                    $application?->snapshotCandidateName(),
                    $application?->job?->title,
                ])));
                $meta = trim(implode(' · ', array_filter([
                    $interview->interviewer?->name,
                    $interview->type === 'offline'
                        ? ($interview->workplace ? $this->formatWorkplaceLabel($interview->workplace) : 'Offline')
                        : 'Online',
                ])));

                return [
                    'time' => $time,
                    'title' => $title ?: 'Lịch phỏng vấn',
                    'meta' => $meta ?: 'Chưa có thông tin bổ sung',
                ];
            })
            ->all();

        return [
            'title' => $title,
            'empty' => 'Chưa có lịch sắp tới.',
            'items' => $items,
            'suggestions' => $this->kanbanInterviewAvailabilityNotice !== ''
                ? $this->suggestInterviewTimes($application)
                : [],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function suggestInterviewTimes(Application $application): array
    {
        $duration = (int) ($this->kanbanInterviewForm['duration_minutes'] ?? 0);
        $type = (string) ($this->kanbanInterviewForm['type'] ?? '');
        $interviewerId = (int) ($this->kanbanInterviewForm['interviewer_id'] ?? 0);

        if (
            blank($this->kanbanInterviewForm['scheduled_at'] ?? null)
            || $duration <= 0
            || $interviewerId <= 0
            || ! in_array($type, ['online', 'offline'], true)
        ) {
            return [];
        }

        try {
            $startAt = $this->resolveKanbanInterviewScheduledAt($this->kanbanInterviewForm['scheduled_at']);
        } catch (\Throwable) {
            return [];
        }

        $workplaceId = (int) ($this->kanbanInterviewForm['workplace_id'] ?? 0);

        if ($type === 'offline' && $workplaceId <= 0) {
            return [];
        }

        $existingInterview = $application->interviews()->latest('id')->first();
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $cursor = $startAt->copy()->addMinutes(30)->second(0);
        $remainder = $cursor->minute % 30;

        if ($remainder > 0) {
            $cursor->addMinutes(30 - $remainder);
        }

        $suggestions = [];

        while (count($suggestions) < 3 && $cursor->lte($startAt->copy()->addDays(2))) {
            if ($cursor->hour < 8) {
                $cursor->setTime(8, 0);
            }

            if ($cursor->hour >= 21) {
                $cursor->addDay()->setTime(8, 0);
            }

            $endAt = $cursor->copy()->addMinutes(max(15, $duration));
            $busy = $this->hasInterviewOverlap(
                $cursor,
                $endAt,
                $existingInterview?->id,
                fn (Builder $query): Builder => $query->where('interviewer_id', $interviewerId),
            );

            if (! $busy && $type === 'offline') {
                $busy = $this->hasInterviewOverlap(
                    $cursor,
                    $endAt,
                    $existingInterview?->id,
                    fn (Builder $query): Builder => $query->where('workplace_id', $workplaceId),
                );
            }

            if (! $busy && $cursor->gt(now($timezone))) {
                $suggestions[] = $cursor->format('H:i, d/m');
            }

            $cursor->addMinutes(30);
        }

        return $suggestions;
    }

    private function interviewAvailabilityNotice(Application $application, bool $reportIncomplete = true): ?string
    {
        $duration = (int) ($this->kanbanInterviewForm['duration_minutes'] ?? 0);
        $type = (string) ($this->kanbanInterviewForm['type'] ?? '');
        $interviewerId = (int) ($this->kanbanInterviewForm['interviewer_id'] ?? 0);

        if (
            blank($this->kanbanInterviewForm['scheduled_at'] ?? null)
            || $duration <= 0
            || $interviewerId <= 0
            || ! in_array($type, ['online', 'offline'], true)
        ) {
            return $reportIncomplete ? 'Chọn đủ thông tin để kiểm tra.' : null;
        }

        try {
            $scheduledAt = $this->resolveKanbanInterviewScheduledAt($this->kanbanInterviewForm['scheduled_at']);
        } catch (\Throwable) {
            return 'Chọn thời gian sắp tới.';
        }

        if ($scheduledAt->lt(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')))) {
            return 'Chọn thời gian sắp tới.';
        }

        $interviewerOptions = $this->interviewerOptions($application);

        if (! array_key_exists($interviewerId, $interviewerOptions)) {
            return 'Chưa có người phỏng vấn để chọn.';
        }

        $existingInterview = $application->interviews()->latest('id')->first();
        $endAt = $scheduledAt->copy()->addMinutes(max(15, $duration));

        if ($this->hasInterviewOverlap(
            $scheduledAt,
            $endAt,
            $existingInterview?->id,
            fn (Builder $query): Builder => $query->where('interviewer_id', $interviewerId),
        )) {
            return 'Người phỏng vấn đang bận khung giờ này.';
        }

        if ($type === 'offline') {
            $workplaceId = (int) ($this->kanbanInterviewForm['workplace_id'] ?? 0);
            $workplaceOptions = $this->workplaceOptions($application);

            if ($workplaceId <= 0 || ! array_key_exists($workplaceId, $workplaceOptions)) {
                return $reportIncomplete ? 'Chưa có phòng phỏng vấn để chọn.' : null;
            }

            if ($this->hasInterviewOverlap(
                $scheduledAt,
                $endAt,
                $existingInterview?->id,
                fn (Builder $query): Builder => $query->where('workplace_id', $workplaceId),
            )) {
                return 'Phòng đã có lịch trong khung giờ này.';
            }
        }

        return null;
    }

    private function resolveKanbanInterviewScheduledAt(mixed $value): CarbonInterface
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');

        if ($value instanceof CarbonInterface) {
            return $value->copy()->setTimezone($timezone);
        }

        return Carbon::parse((string) $value, $timezone);
    }

    private function hasInterviewOverlap(
        CarbonInterface $startAt,
        CarbonInterface $endAt,
        ?int $ignoreInterviewId,
        \Closure $scope,
    ): bool {
        $query = Interview::query()
            ->where('result', 'pending')
            ->where('scheduled_at', '>=', $startAt->copy()->subDay())
            ->where('scheduled_at', '<', $endAt);

        if ($ignoreInterviewId) {
            $query->whereKeyNot($ignoreInterviewId);
        }

        $scope($query);

        return $query
            ->get(['id', 'scheduled_at', 'duration_minutes'])
            ->contains(function (Interview $interview) use ($startAt): bool {
                $interviewEndAt = $interview->scheduled_at
                    ? $interview->scheduled_at->copy()->addMinutes(max(15, (int) ($interview->duration_minutes ?: 60)))
                    : null;

                return $interviewEndAt?->gt($startAt) ?? false;
            });
    }

    private function buildInterviewScheduleComment(Interview $interview, bool $isUpdate): string
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $scheduledAt = $interview->scheduled_at
            ? $interview->scheduled_at->copy()->setTimezone($timezone)->format('H:i, d/m/Y')
            : '-';
        $type = $interview->type === 'offline' ? 'Offline' : 'Online';
        $duration = (int) ($interview->duration_minutes ?: 60);
        $location = app(InterviewCalendarService::class)->resolveLocation($interview);
        $prefix = $isUpdate ? 'Đã cập nhật lịch phỏng vấn' : 'Đã tạo lịch phỏng vấn';

        return sprintf(
            '%s: %s, %s, %s, %d phút, %s.',
            $prefix,
            $interview->round_name ?: 'Phỏng vấn',
            $scheduledAt,
            $type,
            $duration,
            $location ?: 'Chưa có địa điểm/link'
        );
    }

    private function formatInterviewerLabel(User $user): string
    {
        $roleKey = $user->role;

        if (! filled($roleKey)) {
            $allowed = ['director', 'pm', 'hr'];
            $roleKey = $user->roles->first(fn ($role) => in_array($role->name, $allowed, true))?->name;
        }

        $nameWithRole = $user->name;

        if (filled($roleKey)) {
            $nameWithRole .= ' ('.$this->formatUserRole($roleKey).')';
        }

        return trim(implode(' - ', array_filter([$nameWithRole, $user->branch?->name])));
    }

    private function formatWorkplaceLabel(Workplace $workplace): string
    {
        return implode(' - ', array_filter([
            $workplace->name,
            $workplace->room ? 'Phòng '.$workplace->room : null,
            $workplace->floor ? 'Tầng '.$workplace->floor : null,
        ]));
    }

    private function formatUserRole(?string $role): string
    {
        return match ($role) {
            'director' => 'Giám đốc',
            'pm' => 'PM',
            'hr' => 'HR',
            default => $role ? strtoupper($role) : '',
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $summaryService = app(ApplicationWorkflowSummaryService::class);
        $applications = ApplicationResource::getEloquentQuery()
            ->latest('updated_at')
            ->get();

        $searchFilteredApplications = $this->filterBySearch($applications, $summaryService);
        $filteredApplications = $this->filterByWorkQueue($searchFilteredApplications);
        $workQueues = $this->workQueues($searchFilteredApplications);

        return [
            'columns' => $this->buildColumns($filteredApplications, $summaryService),
            'workQueues' => $workQueues,
            'activeQueue' => $workQueues[$this->quickFilter] ?? $workQueues['all'],
            'search' => $this->search,
            'quickFilter' => $this->quickFilter,
            'totalApplications' => $filteredApplications->count(),
            'unfilteredApplications' => $applications->count(),
        ];
    }

    /**
     * @param  Collection<int, Application>  $applications
     * @return array<int, array<string, mixed>>
     */
    private function buildColumns(Collection $applications, ApplicationWorkflowSummaryService $summaryService): array
    {
        return collect(StatusApplicationEnum::pipelineStages())
            ->map(function (array $stage, string $stageKey) use ($applications, $summaryService): array {
                $statusValues = StatusApplicationEnum::statusValuesForPipelineStage($stageKey);
                $stageApplications = $applications
                    ->filter(fn (Application $application): bool => in_array($this->statusValue($application), $statusValues, true))
                    ->values();

                return [
                    'key' => $stageKey,
                    'label' => $stage['label'],
                    'count' => $stageApplications->count(),
                    'cards' => $stageApplications
                        ->map(fn (Application $application): array => $this->buildCard($application, $summaryService))
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Application>  $applications
     * @return Collection<int, Application>
     */
    private function filterBySearch(Collection $applications, ApplicationWorkflowSummaryService $summaryService): Collection
    {
        $search = $this->normalizeSearchText($this->search);

        return $applications
            ->filter(function (Application $application) use ($search, $summaryService): bool {
                $summary = $summaryService->summarize($application);

                return $this->matchesSearch($application, $summary, $search);
            })
            ->values();
    }

    /**
     * @param  Collection<int, Application>  $applications
     * @return Collection<int, Application>
     */
    private function filterByWorkQueue(Collection $applications): Collection
    {
        return $applications
            ->filter(fn (Application $application): bool => $this->matchesWorkQueue($application, $this->quickFilter))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    private function matchesSearch(Application $application, array $summary, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        $haystack = $this->normalizeSearchText(implode(' ', array_filter([
            (string) $application->id,
            'HS'.$application->id,
            'hoso '.$application->id,
            'ho so '.$application->id,
            $application->snapshotCandidateName(),
            $application->snapshotCandidateEmail(),
            $application->snapshotCandidatePhone(),
            $application->job?->title,
            $application->job?->branch?->name ?? $application->branch?->name,
            $application->job?->department?->name,
            $summary['stage_label'] ?? null,
            $summary['status_label'] ?? null,
            $summary['description'] ?? null,
        ])));

        return collect(explode(' ', $search))
            ->filter()
            ->every(fn (string $token): bool => str_contains($haystack, $token));
    }

    private function normalizeSearchText(?string $value): string
    {
        $value = Str::ascii((string) $value);
        $value = Str::lower($value);
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function matchesWorkQueue(Application $application, string $queue): bool
    {
        $status = $this->statusValue($application);
        $interview = $application->latestInterview;
        $offer = $application->latestOffer;

        return match ($queue) {
            'cv_reviewing' => $status === StatusApplicationEnum::CV_REVIEWING->value,
            'interview_schedule_needed' => $status === StatusApplicationEnum::SCREENING->value && ! $interview,
            'interview_invite_unsent' => in_array($status, [
                StatusApplicationEnum::SCREENING->value,
                StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
                StatusApplicationEnum::INTERVIEWING->value,
            ], true) && $interview && blank($interview->invite_sent_at),
            'interview_overdue' => in_array($status, [
                StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
                StatusApplicationEnum::INTERVIEWING->value,
            ], true)
                && $interview
                && ($interview->result ?? 'pending') === 'pending'
                && $interview->scheduled_at?->isPast(),
            'offer_needed' => $status === StatusApplicationEnum::OFFERED->value && ! $offer,
            'offer_awaiting_approval' => $status === StatusApplicationEnum::OFFERED->value
                && $offer?->status === 'awaiting_approval',
            'offer_expiring' => $status === StatusApplicationEnum::OFFERED->value
                && $offer?->status === 'pending'
                && $offer->expires_at
                && $offer->expires_at->isFuture()
                && $offer->expires_at->lte(now()->addDays(2)),
            default => true,
        };
    }

    /**
     * @param  Collection<int, Application>  $applications
     * @return array<string, array{label: string, description: string, count: int}>
     */
    private function workQueues(Collection $applications): array
    {
        $queues = [
            'all' => [
                'label' => 'Tất cả',
                'description' => 'Toàn bộ hồ sơ đang theo dõi.',
            ],
            'cv_reviewing' => [
                'label' => 'Chờ sàng lọc',
                'description' => 'Hồ sơ mới cần xem CV và quyết định bước tiếp theo.',
            ],
            'interview_schedule_needed' => [
                'label' => 'Cần lên lịch',
                'description' => 'Ứng viên đã qua sơ tuyển nhưng chưa có lịch phỏng vấn.',
            ],
            'interview_invite_unsent' => [
                'label' => 'Chưa gửi thư mời',
                'description' => 'Lịch đã tạo nhưng chưa gửi email cho ứng viên.',
            ],
            'interview_overdue' => [
                'label' => 'Cần chấm phỏng vấn',
                'description' => 'Buổi phỏng vấn đã đến hạn và cần ghi nhận scorecard.',
            ],
            'offer_needed' => [
                'label' => 'Cần tạo đề nghị',
                'description' => 'Ứng viên đã qua đánh giá, cần tạo đề nghị tuyển dụng.',
            ],
            'offer_awaiting_approval' => [
                'label' => 'Chờ duyệt đề nghị',
                'description' => 'Đề nghị đã gửi giám đốc chi nhánh duyệt.',
            ],
            'offer_expiring' => [
                'label' => 'Sắp hết hạn phản hồi',
                'description' => 'Đề nghị đã gửi ứng viên và sắp hết hạn phản hồi.',
            ],
        ];

        return collect($queues)
            ->map(function (array $queue, string $key) use ($applications): array {
                $queue['count'] = $key === 'all'
                    ? $applications->count()
                    : $applications->filter(fn (Application $application): bool => $this->matchesWorkQueue($application, $key))->count();

                return $queue;
            })
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(
        Application $application,
        ApplicationWorkflowSummaryService $summaryService,
    ): array
    {
        $summary = $summaryService->summarize($application);
        $analysis = $this->screeningAiAnalysisForDisplay($application);

        return [
            'id' => $application->id,
            'candidate' => $application->snapshotCandidateName() ?: 'Hồ sơ #'.$application->id,
            'job' => $application->job?->title ?? 'Chưa có vị trí',
            'branch' => $application->job?->branch?->name ?? $application->branch?->name,
            'department' => $application->job?->department?->name,
            'status' => $summary['status_label'] ?? $this->statusLabel($application),
            'description' => $summary['description'] ?? null,
            'color' => $summary['color'] ?? 'gray',
            'applied_at' => $application->applied_at?->format('d/m/Y H:i'),
            'ai_score' => $analysis?->status === 'completed' ? $analysis->score : null,
            'has_ai' => $analysis?->status === 'completed',
            'url' => ApplicationResource::getUrl('view', ['record' => $application]),
            'stage_actions' => $this->stageActions($application),
            'can_reject' => app(ApplicationWorkflowGuard::class)->canRejectApplication(Auth::user(), $application)
                && ! in_array($this->statusValue($application), [
                    StatusApplicationEnum::HIRED->value,
                    StatusApplicationEnum::REJECTED->value,
                ], true),
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, hint: string, primary: bool}>
     */
    private function stageActions(Application $application): array
    {
        $status = $this->statusValue($application);
        $interview = $application->latestInterview;
        $workflowGuard = app(ApplicationWorkflowGuard::class);

        if ($status === StatusApplicationEnum::OFFERED->value) {
            if (! $workflowGuard->canManageOffer(Auth::user(), $application)) {
                return [];
            }

            $offer = $application->latestOffer ?? $application->offers()->latest('id')->first();
            $canCreateReplacement = $workflowGuard->shouldCreateReplacementOffer($offer);

            if (! $offer || $canCreateReplacement) {
                return [[
                    'key' => 'edit_offer_draft',
                    'label' => $canCreateReplacement ? 'Tạo đề nghị mới' : 'Tạo đề nghị',
                    'hint' => $canCreateReplacement
                        ? 'Tạo đề nghị mới dựa trên phản hồi hoặc hạn của đề nghị trước.'
                        : 'Lập nội dung, điều kiện và hạn phản hồi trước khi gửi duyệt.',
                    'primary' => true,
                ]];
            }

            if (in_array($offer->status, ['draft', 'rejected'], true)) {
                return [
                    [
                        'key' => 'submit_offer_approval',
                        'label' => $offer->status === 'rejected' ? 'Gửi duyệt lại' : 'Gửi duyệt',
                        'hint' => $offer->status === 'rejected'
                            ? 'Kiểm tra nội dung đã điều chỉnh trước khi gửi lại giám đốc duyệt.'
                            : 'Gửi đề nghị cho giám đốc chi nhánh xem xét.',
                        'primary' => true,
                    ],
                    [
                        'key' => 'edit_offer_draft',
                        'label' => $offer->status === 'rejected' ? 'Điều chỉnh đề nghị' : 'Chỉnh sửa',
                        'hint' => 'Cập nhật nội dung bản nháp trước khi gửi duyệt.',
                        'primary' => false,
                    ],
                ];
            }

            return [];
        }

        if (! in_array($status, [StatusApplicationEnum::INTERVIEW_SCHEDULED->value, StatusApplicationEnum::INTERVIEWING->value], true)) {
            return [];
        }

        $actions = [];

        if ($workflowGuard->canSendInterviewSchedule(Auth::user(), $application)) {
            $actions[] = [
                'key' => 'send_interview_schedule',
                'label' => filled($interview?->invite_sent_at) ? 'Gửi cập nhật lịch' : 'Gửi lịch phỏng vấn',
                'hint' => filled($interview?->invite_sent_at)
                    ? 'Gửi lại lịch mới cho ứng viên và người liên quan.'
                    : 'Gửi email và file lịch cho ứng viên, người liên quan.',
                'primary' => true,
            ];
        }

        if ($workflowGuard->canManageInterview(Auth::user(), $application)) {
            $actions[] = [
                'key' => 'update_interview_schedule',
                'label' => 'Cập nhật lịch',
                'hint' => 'Điều chỉnh lịch trước thời điểm phỏng vấn.',
                'primary' => false,
            ];
        }

        if ($workflowGuard->canEvaluateInterview(Auth::user(), $application)) {
            $canFinalize = $workflowGuard->canFinalizeInterviewEvaluation(Auth::user(), $application);
            $actions[] = [
                'key' => 'evaluate_interview',
                'label' => $canFinalize ? 'Hoàn tất đánh giá' : 'Ghi nhận đánh giá',
                'hint' => $canFinalize
                    ? 'Kiểm tra scorecard và chốt kết quả phỏng vấn.'
                    : 'Lưu điểm và nhận xét tạm trong buổi phỏng vấn.',
                'primary' => true,
            ];
        }

        return $actions;
    }

    private function requirementTitle(string $requirement): string
    {
        return match ($requirement) {
            'cv_screening' => 'Cần sàng lọc CV',
            'interview_schedule' => 'Cần tạo lịch phỏng vấn',
            'interview_evaluation' => 'Cần đánh giá phỏng vấn',
            'offer_draft' => 'Cần tạo đề nghị tuyển dụng',
            default => 'Cần xử lý hồ sơ',
        };
    }

    private function requirementActionLabel(string $requirement): string
    {
        return match ($requirement) {
            'cv_screening' => 'Mở sàng lọc CV',
            'interview_schedule' => 'Mở tạo lịch',
            'interview_evaluation' => 'Mở đánh giá',
            'offer_draft' => 'Mở đề nghị',
            default => 'Mở hồ sơ xử lý',
        };
    }

    private function rejectedStageFor(Application $application): string
    {
        return match ($this->statusValue($application)) {
            StatusApplicationEnum::CV_REVIEWING->value,
            StatusApplicationEnum::SCREENING->value => 'screening',
            StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
            StatusApplicationEnum::INTERVIEWING->value => 'interview',
            StatusApplicationEnum::OFFERED->value => 'offer',
            default => 'screening',
        };
    }

    private function tableSearchUrl(Application $application): string
    {
        $url = ApplicationResource::getUrl('index');
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'search='.rawurlencode((string) $application->id);
    }

    private function statusValue(Application $application): ?string
    {
        return $application->status instanceof StatusApplicationEnum
            ? $application->status->value
            : $application->status;
    }

    private function statusLabel(Application $application): string
    {
        if ($application->status instanceof StatusApplicationEnum) {
            return (string) $application->status->getLabel();
        }

        return StatusApplicationEnum::tryFrom((string) $application->status)?->getLabel()
            ?? 'Chưa xác định';
    }
}

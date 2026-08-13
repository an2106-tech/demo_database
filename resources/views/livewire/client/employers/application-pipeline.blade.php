<div>
    <style>
        .pipeline-container {
            display: flex;
            overflow-x: auto;
            gap: 1rem;
            padding: 1rem 0.25rem 2rem;
            min-height: calc(100vh - 300px);
            scrollbar-width: thin;
            scrollbar-color: var(--fpt-orange) #f1f5f9;
        }

        .pipeline-column {
            flex: 0 0 380px;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            max-height: 100%;
            border: 1px solid #e2e8f0;
            box-shadow: 0 18px 36px -28px rgba(15, 23, 42, 0.45);
        }

        .column-header {
            padding: 1.25rem;
            background: #fff;
            border-radius: 16px 16px 0 0;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .column-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .column-count {
            background: #f1f5f9;
            color: #64748b;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .column-content {
            padding: 0.9rem;
            flex-grow: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        .candidate-card {
            background: #fff;
            border-radius: 18px;
            padding: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 20px 38px -30px rgba(15, 23, 42, 0.5);
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .candidate-card:hover {
            box-shadow: 0 24px 44px -28px rgba(15, 23, 42, 0.6);
            transform: translateY(-2px);
            border-color: rgba(243, 112, 33, 0.35);
        }

        .candidate-card__top {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) auto;
            gap: 0.75rem;
            align-items: start;
        }

        .candidate-card__main {
            min-width: 0;
        }

        .candidate-card__date {
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .candidate-avatar {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            object-fit: cover;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: var(--fpt-orange);
            font-weight: 800;
        }

        .candidate-avatar svg {
            width: 21px;
            height: 21px;
            stroke: currentColor;
        }

        .candidate-name {
            font-weight: 800;
            font-size: 0.92rem;
            color: #1e293b;
            line-height: 1.25;
            margin: 0 0 0.35rem;
        }

        .job-tag {
            font-size: 0.72rem;
            color: #64748b;
            margin-top: 0.22rem;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .job-tag--strong {
            color: #334155;
            font-weight: 800;
        }

        .candidate-card__meta {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.75rem;
            align-items: center;
            margin-top: 0.85rem;
            padding: 0.75rem;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #eef2f7;
        }

        .candidate-card__cv {
            color: #475569;
            font-size: 0.72rem;
            font-weight: 700;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ai-match-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 9px;
            border-radius: 999px;
            display: inline-block;
            white-space: nowrap;
        }

        .card-actions {
            margin-top: 0.85rem;
            padding-top: 0.85rem;
            border-top: 1px solid #f1f5f9;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
        }

        .card-actions form {
            margin: 0;
            width: 100%;
        }

        .pipeline-alerts {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-top: 0.75rem;
        }

        .pipeline-alert {
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 3px 8px;
        }

        .pipeline-alert--warning {
            background: #fffbeb;
            color: #92400e;
        }

        .pipeline-alert--info {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .pipeline-alert--danger {
            background: #fef2f2;
            color: #b91c1c;
        }

        .pipeline-action {
            align-items: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            color: #475569;
            cursor: pointer;
            display: flex;
            font-size: 0.72rem;
            font-weight: 700;
            gap: 0.38rem;
            justify-content: center;
            line-height: 1.1;
            min-height: 34px;
            padding: 8px 10px;
            text-decoration: none;
            transition: all 0.18s ease;
            width: 100%;
        }

        .pipeline-action:hover {
            border-color: var(--fpt-orange);
            color: var(--fpt-orange);
            text-decoration: none;
            transform: translateY(-1px);
        }

        .pipeline-action:active {
            transform: translateY(0);
        }

        .pipeline-action--primary {
            background: #fff7ed;
            border-color: var(--fpt-orange);
            color: var(--fpt-orange);
        }

        .pipeline-action--primary:hover {
            background: var(--fpt-orange);
            color: #fff;
        }

        .pipeline-action--danger {
            border-color: #fecaca;
            color: #b91c1c;
        }

        .pipeline-action--danger:hover {
            background: #fef2f2;
            border-color: #ef4444;
            color: #991b1b;
        }

        .pipeline-action--schedule {
            background: #f0fdf4;
            border-color: #86efac;
            color: #166534;
        }

        .pipeline-action--schedule:hover {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
        }

        .pipeline-action--wide {
            grid-column: 1 / -1;
        }

        .interview-modal-backdrop {
            align-items: center;
            background: rgba(15, 23, 42, 0.48);
            display: flex;
            inset: 0;
            justify-content: center;
            padding: 1.5rem;
            position: fixed;
            z-index: 1050;
        }

        .interview-modal {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            box-shadow: 0 35px 80px -35px rgba(15, 23, 42, 0.65);
            max-height: 92vh;
            max-width: 760px;
            overflow-y: auto;
            padding: 1.5rem;
            width: min(760px, 100%);
        }

        .interview-modal__head {
            align-items: flex-start;
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }

        .interview-modal__head h3 {
            color: #0f172a;
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0;
        }

        .interview-modal__head p {
            color: #64748b;
            font-size: 0.86rem;
            margin: 0.25rem 0 0;
        }

        .interview-form-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .interview-field {
            display: grid;
            gap: 0.35rem;
        }

        .interview-field--full {
            grid-column: 1 / -1;
        }

        .interview-field label {
            color: #334155;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .interview-field input,
        .interview-field select,
        .interview-field textarea {
            border: 1px solid #dbe3ef;
            border-radius: 12px;
            color: #0f172a;
            font-size: 0.9rem;
            padding: 0.72rem 0.85rem;
            width: 100%;
        }

        .interview-field textarea {
            min-height: 92px;
            resize: vertical;
        }

        .interview-error {
            color: #b91c1c;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .interview-modal__actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 1.35rem;
        }

        .interview-modal__button {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 800;
            padding: 0.78rem 1rem;
        }

        .interview-modal__button--primary {
            background: var(--fpt-orange);
            border-color: var(--fpt-orange);
            color: #fff;
        }

        .filter-section {
            background: #fff;
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .premium-select {
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
            color: #475569;
            background-color: #f8fafc;
            min-width: 250px;
        }

        .premium-select:focus {
            border-color: var(--fpt-orange);
            outline: none;
            box-shadow: 0 0 0 3px rgba(243, 112, 33, 0.1);
        }

        @media (max-width: 767.98px) {
            .filter-section {
                align-items: stretch;
                flex-direction: column;
            }

            .premium-select {
                min-width: 100%;
                width: 100%;
            }

            .pipeline-column {
                flex-basis: 86vw;
            }
        }
    </style>

    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li class="active">Pipeline ứng viên</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right">
                        @if (session()->has('message'))
                            <div class="alert alert-success">{{ session('message') }}</div>
                        @endif

                        @if (session()->has('warning'))
                            <div class="alert alert-warning">{{ session('warning') }}</div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="filter-section">
                            <div>
                                <h3 style="margin: 0; font-weight: 700;">Pipeline tuyển dụng</h3>
                                <p style="margin: 5px 0 0; color: #64748b; font-size: 0.9rem;">
                                    Theo dõi tiến độ ứng tuyển theo chi nhánh và vị trí.
                                </p>
                            </div>
                            <div>
                                <select wire:model.live="selectedJobId" class="premium-select">
                                    <option value="">Tất cả vị trí</option>
                                    @foreach($jobs as $job)
                                        <option value="{{ $job->id }}">{{ $job->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="pipeline-container">
                            @foreach($stages as $stageKey => $stage)
                                @php
                                    $stageIcons = [
                                        'applied' => 'fa-inbox',
                                        'screening' => 'fa-search',
                                        'interview' => 'fa-users',
                                        'offer' => 'fa-envelope-open',
                                        'hired' => 'fa-check-circle',
                                        'rejected' => 'fa-times-circle',
                                    ];
                                    $stageLabels = [
                                        'applied' => 'Ứng tuyển',
                                        'screening' => 'Sơ tuyển',
                                        'interview' => 'Phỏng vấn',
                                        'offer' => 'Đề nghị tuyển dụng',
                                        'hired' => 'Đã tuyển',
                                        'rejected' => 'Từ chối',
                                    ];
                                @endphp
                                <div class="pipeline-column" wire:key="pipeline-column-{{ $stageKey }}">
                                    <div class="column-header">
                                        <div class="column-title">
                                            <i class="fa {{ $stageIcons[$stageKey] ?? 'fa-circle' }}"></i>
                                            {{ $stageLabels[$stageKey] ?? $stage['label'] }}
                                        </div>
                                        <span class="column-count">{{ count($applicationsByStage[$stageKey]) }}</span>
                                    </div>
                                    <div class="column-content">
                                        @foreach($applicationsByStage[$stageKey] as $app)
                                            @php
                                                $snapshot = is_array($app->profile_snapshot) ? $app->profile_snapshot : [];
                                                $statusLabels = [
                                                    \App\Enums\StatusApplicationEnum::CV_REVIEWING->value => 'Chờ sàng lọc CV',
                                                    \App\Enums\StatusApplicationEnum::SCREENING->value => 'Sơ tuyển',
                                                    \App\Enums\StatusApplicationEnum::INTERVIEW_SCHEDULED->value => 'Đã lên lịch phỏng vấn',
                                                    \App\Enums\StatusApplicationEnum::INTERVIEWING->value => 'Chờ đánh giá phỏng vấn',
                                                    \App\Enums\StatusApplicationEnum::OFFERED->value => 'Đề nghị tuyển dụng',
                                                    \App\Enums\StatusApplicationEnum::HIRED->value => 'Đã tuyển',
                                                    \App\Enums\StatusApplicationEnum::REJECTED->value => 'Từ chối',
                                                ];
                                                $candidateName = data_get($snapshot, 'name') ?: data_get($snapshot, 'candidate.name') ?: $app->candidate?->name ?: 'Ứng viên';
                                                $candidateEmail = data_get($snapshot, 'email') ?: data_get($snapshot, 'candidate.email') ?: $app->candidate?->email;
                                                $profileTitle = data_get($snapshot, 'profile_title') ?: data_get($snapshot, 'resume.profile_title');
                                                $submittedCvName = data_get($snapshot, 'cv.original_filename') ?: ($app->cv_path ? basename($app->cv_path) : null);
                                                $latestSubmission = $latestSubmissionsByApplicationKey[$app->candidate_id . ':' . $app->job_id] ?? null;
                                                $status = $app->status instanceof \App\Enums\StatusApplicationEnum
                                                    ? $app->status
                                                    : \App\Enums\StatusApplicationEnum::tryFrom((string) $app->status);
                                                $nextAction = $nextActionStatusesByApplicationId[$app->id] ?? null;
                                                $actionPermissions = $pipelineActionPermissions[$app->id] ?? [
                                                    'manage' => false,
                                                    'schedule' => false,
                                                    'evaluate' => false,
                                                    'reject' => false,
                                                ];
                                                $cvUrl = $app->submittedCvUrl();
                                                $advancedUrl = \Illuminate\Support\Facades\Route::has('filament.admin.resources.applications.edit')
                                                    ? route('filament.admin.resources.applications.edit', ['record' => $app->id])
                                                    : null;
                                                $canScheduleInterview = $actionPermissions['schedule'];
                                                $canEvaluateInterview = $actionPermissions['evaluate'];
                                                $warnings = [];
                                                if (! $app->is_viewed) {
                                                    $warnings[] = ['label' => 'Chưa xem', 'class' => 'pipeline-alert--warning'];
                                                }
                                                if (! $latestSubmission?->ai_matching_score) {
                                                    $warnings[] = ['label' => 'Chưa có AI', 'class' => 'pipeline-alert--info'];
                                                }
                                                if (in_array($status, [\App\Enums\StatusApplicationEnum::INTERVIEW_SCHEDULED, \App\Enums\StatusApplicationEnum::INTERVIEWING], true) && ! $app->latestScorecard) {
                                                    $warnings[] = ['label' => 'Thiếu scorecard', 'class' => 'pipeline-alert--warning'];
                                                }
                                                if ($app->latestOffer?->status === 'awaiting_approval') {
                                                    $warnings[] = ['label' => 'Offer chờ duyệt', 'class' => 'pipeline-alert--danger'];
                                                }
                                            @endphp

                                            <div class="candidate-card" wire:key="pipeline-application-{{ $app->id }}-{{ $status?->value ?? 'none' }}">
                                                <div class="candidate-card__top">
                                                    @if($app->candidate?->user?->avatar && file_exists(public_path('storage/' . $app->candidate->user->avatar)))
                                                        <img src="{{ asset('storage/' . $app->candidate->user->avatar) }}" class="candidate-avatar" alt="">
                                                    @else
                                                        <div class="candidate-avatar d-flex align-items-center justify-content-center">
                                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                <path d="M12 12.25a4.25 4.25 0 1 0 0-8.5 4.25 4.25 0 0 0 0 8.5Z" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M4.75 20.25c1.35-3.15 3.7-4.75 7.25-4.75s5.9 1.6 7.25 4.75" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </div>
                                                    @endif

                                                    <div class="candidate-card__main">
                                                        <h5 class="candidate-name text-truncate">
                                                            <a href="{{ route('employers.candidate_detail', ['candidate' => $app->candidate_id]) }}" class="text-decoration-none text-dark">
                                                                {{ $candidateName }}
                                                            </a>
                                                        </h5>
                                                        @if($candidateEmail)
                                                            <span class="job-tag" title="{{ $candidateEmail }}">{{ $candidateEmail }}</span>
                                                        @endif
                                                        <span class="job-tag job-tag--strong" title="{{ $app->job?->title }}">{{ $app->job?->title ?? 'Vị trí không còn khả dụng' }}</span>
                                                        @if($profileTitle)
                                                            <span class="job-tag" title="{{ $profileTitle }}">{{ $profileTitle }}</span>
                                                        @endif
                                                        @if($status)
                                                            <span class="job-tag" title="{{ $statusLabels[$status->value] ?? $status->getLabel() }}">{{ $statusLabels[$status->value] ?? $status->getLabel() }}</span>
                                                        @endif
                                                    </div>

                                                    <span class="candidate-card__date">{{ optional($app->applied_at ?? $app->created_at)->format('d/m') }}</span>
                                                </div>

                                                <div class="candidate-card__meta">
                                                    <div class="candidate-card__cv" title="{{ $submittedCvName ?: 'Chưa có CV' }}">
                                                        CV: {{ $submittedCvName ?: 'Chưa có CV' }}
                                                    </div>

                                                    @if($latestSubmission && $latestSubmission->ai_matching_score)
                                                        <span class="ai-match-badge {{ $latestSubmission->ai_matching_score >= 80 ? 'bg-success text-white' : ($latestSubmission->ai_matching_score >= 50 ? 'bg-warning text-dark' : 'bg-danger text-white') }}">
                                                            AI: {{ $latestSubmission->ai_matching_score }}%
                                                        </span>
                                                    @else
                                                        <span class="ai-match-badge bg-light text-muted">Chưa có AI</span>
                                                    @endif
                                                </div>

                                                @if($warnings !== [])
                                                    <div class="pipeline-alerts">
                                                        @foreach($warnings as $warning)
                                                            <span class="pipeline-alert {{ $warning['class'] }}">{{ $warning['label'] }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <div class="card-actions">
                                                    <button
                                                        type="button"
                                                        wire:click="openMessageModal({{ $app->id }})"
                                                        class="pipeline-action pipeline-action--primary"
                                                    >
                                                        <i class="fa fa-commenting-o"></i> Nhắn tin
                                                    </button>

                                                    @if($cvUrl)
                                                        <a href="{{ $cvUrl }}" target="_blank" rel="noopener" class="pipeline-action">
                                                            <i class="fa fa-file-text-o"></i> CV chi tiết
                                                        </a>
                                                    @endif

                                                    @if($advancedUrl)
                                                        <a href="{{ $advancedUrl }}" target="_blank" rel="noopener" class="pipeline-action pipeline-action--wide">
                                                            <i class="fa fa-cog"></i> ATS xử lý nâng cao
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($showRejectModal)
        <div class="interview-modal-backdrop" wire:keydown.escape.window="closeRejectionModal">
            <div class="interview-modal">
                <div class="interview-modal__head">
                    <div>
                        <h3>Từ chối hồ sơ ứng viên</h3>
                        <p>Lý do sẽ được lưu vào lịch sử tuyển dụng và dùng khi cần đối soát.</p>
                    </div>
                    <button type="button" wire:click="closeRejectionModal" class="pipeline-action" style="width: auto;">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <form wire:submit="rejectApplication">
                    <div class="interview-form-grid">
                        <div class="interview-field interview-field--full">
                            <label for="rejection-reason">Lý do từ chối</label>
                            <textarea id="rejection-reason" wire:model="rejectionReason" rows="5" placeholder="Nêu rõ lý do hồ sơ chưa phù hợp ở vòng hiện tại..."></textarea>
                            @error('rejectionReason') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="interview-modal__actions">
                        <button type="button" wire:click="closeRejectionModal" class="interview-modal__button">Hủy</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="rejectApplication" class="interview-modal__button interview-modal__button--primary">
                            Xác nhận từ chối
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showInterviewModal)
        <div class="interview-modal-backdrop" wire:keydown.escape.window="closeInterviewScheduler">
            <div class="interview-modal">
                <div class="interview-modal__head">
                    <div>
                        <h3>{{ $selectedInterviewApplication?->latestInterview ? 'Cập nhật lịch phỏng vấn' : 'Lên lịch phỏng vấn' }}</h3>
                        <p>
                            {{ $selectedInterviewApplication?->snapshotCandidateName() ?? 'Ứng viên' }}
                            @if($selectedInterviewApplication?->job)
                                · {{ $selectedInterviewApplication->job->title }}
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('employers.application_pipeline') }}" class="pipeline-action" style="width: auto;">
                        <i class="fa fa-times"></i>
                    </a>
                </div>

                @php
                    $interviewFormValues = [
                        'round_name' => old('round_name', $interviewForm['round_name'] ?? ''),
                        'scheduled_at' => old('scheduled_at', $interviewForm['scheduled_at'] ?? ''),
                        'duration_minutes' => (string) old('duration_minutes', $interviewForm['duration_minutes'] ?? 60),
                        'interviewer_id' => (string) old('interviewer_id', $interviewForm['interviewer_id'] ?? ''),
                        'type' => old('type', $interviewForm['type'] ?? 'online'),
                        'meeting_link' => old('meeting_link', $interviewForm['meeting_link'] ?? ''),
                        'workplace_id' => (string) old('workplace_id', $interviewForm['workplace_id'] ?? ''),
                        'notes' => old('notes', $interviewForm['notes'] ?? ''),
                    ];
                @endphp

                <form method="POST" action="{{ route('employers.application_pipeline.schedule_interview', ['application' => $selectedInterviewApplication->id ?? 0]) }}">
                    @csrf
                    <div class="interview-form-grid">
                        <div class="interview-field">
                            <label for="interview-round-name">Tên vòng phỏng vấn</label>
                            <input id="interview-round-name" name="round_name" type="text" wire:model="interviewForm.round_name" value="{{ $interviewFormValues['round_name'] }}" placeholder="Phỏng vấn vòng 1">
                            @error('round_name') <span class="interview-error">{{ $message }}</span> @enderror
                            @error('interviewForm.round_name') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field">
                            <label for="interview-scheduled-at">Thời gian</label>
                            <input id="interview-scheduled-at" name="scheduled_at" type="datetime-local" wire:model="interviewForm.scheduled_at" value="{{ $interviewFormValues['scheduled_at'] }}">
                            @error('scheduled_at') <span class="interview-error">{{ $message }}</span> @enderror
                            @error('interviewForm.scheduled_at') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field">
                            <label for="interview-duration">Thời lượng</label>
                            <select id="interview-duration" name="duration_minutes" wire:model="interviewForm.duration_minutes">
                                <option value="30" @selected($interviewFormValues['duration_minutes'] === '30')>30 phút</option>
                                <option value="45" @selected($interviewFormValues['duration_minutes'] === '45')>45 phút</option>
                                <option value="60" @selected($interviewFormValues['duration_minutes'] === '60')>60 phút</option>
                                <option value="90" @selected($interviewFormValues['duration_minutes'] === '90')>90 phút</option>
                            </select>
                            @error('duration_minutes') <span class="interview-error">{{ $message }}</span> @enderror
                            @error('interviewForm.duration_minutes') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field">
                            <label for="interview-interviewer">Người phỏng vấn</label>
                            <select id="interview-interviewer" name="interviewer_id" wire:model="interviewForm.interviewer_id">
                                <option value="">Chọn người phỏng vấn</option>
                                @foreach($interviewerOptions as $id => $label)
                                    <option value="{{ $id }}" @selected($interviewFormValues['interviewer_id'] === (string) $id)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('interviewer_id') <span class="interview-error">{{ $message }}</span> @enderror
                            @error('interviewForm.interviewer_id') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field">
                            <label for="interview-type">Hình thức</label>
                            <select id="interview-type" name="type" wire:model.live="interviewForm.type">
                                <option value="online" @selected($interviewFormValues['type'] === 'online')>Online</option>
                                <option value="offline" @selected($interviewFormValues['type'] === 'offline')>Offline</option>
                            </select>
                            @error('type') <span class="interview-error">{{ $message }}</span> @enderror
                            @error('interviewForm.type') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field">
                            <label for="interview-meeting-link">Link phỏng vấn</label>
                            <input id="interview-meeting-link" name="meeting_link" type="url" wire:model="interviewForm.meeting_link" value="{{ $interviewFormValues['meeting_link'] }}" placeholder="https://meet.google.com/...">
                            @error('meeting_link') <span class="interview-error">{{ $message }}</span> @enderror
                            @error('interviewForm.meeting_link') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field">
                            <label for="interview-workplace">Phòng phỏng vấn</label>
                            <select id="interview-workplace" name="workplace_id" wire:model="interviewForm.workplace_id">
                                <option value="">Chọn phòng phỏng vấn nếu offline</option>
                                @foreach($workplaceOptions as $id => $label)
                                    <option value="{{ $id }}" @selected($interviewFormValues['workplace_id'] === (string) $id)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('workplace_id') <span class="interview-error">{{ $message }}</span> @enderror
                            @error('interviewForm.workplace_id') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field interview-field--full">
                            <label for="interview-notes">Ghi chú gửi kèm</label>
                            <textarea id="interview-notes" name="notes" wire:model="interviewForm.notes" placeholder="Ví dụ: chuẩn bị portfolio, vào meeting trước 5 phút...">{{ $interviewFormValues['notes'] }}</textarea>
                            @error('notes') <span class="interview-error">{{ $message }}</span> @enderror
                            @error('interviewForm.notes') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="interview-modal__actions">
                        <a href="{{ route('employers.application_pipeline') }}" class="interview-modal__button">Hủy</a>
                        <button type="submit" class="interview-modal__button interview-modal__button--primary">
                            Lưu lịch phỏng vấn
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showEvaluationModal && $selectedEvaluationApplication)
        @php
            $evaluationScorecard = $selectedEvaluationApplication->latestScorecard;
            $evaluationCriteria = collect($evaluationScorecard?->criteria ?? [])->keyBy('name');
            $evaluationValues = [
                'technical_score' => old('technical_score', data_get($evaluationCriteria->get('Kinh nghiem va chuyen mon'), 'score', '')),
                'problem_solving_score' => old('problem_solving_score', data_get($evaluationCriteria->get('Tu duy giai quyet van de'), 'score', '')),
                'communication_score' => old('communication_score', data_get($evaluationCriteria->get('Giao tiep va phoi hop'), 'score', '')),
                'culture_score' => old('culture_score', data_get($evaluationCriteria->get('Phu hop van hoa FPT Education'), 'score', '')),
                'conclusion' => old('conclusion', $evaluationScorecard?->conclusion ?: 'hold'),
                'notes' => old('notes', $evaluationScorecard?->notes ?: ''),
                'rejected_reason' => old('rejected_reason', $selectedEvaluationApplication->rejected_reason ?: ''),
            ];
        @endphp

        <div class="interview-modal-backdrop">
            <div class="interview-modal">
                <div class="interview-modal__head">
                    <div>
                        <h3>Đánh giá kết quả phỏng vấn</h3>
                        <p>
                            {{ $selectedEvaluationApplication->snapshotCandidateName() }}
                            @if($selectedEvaluationApplication->job)
                                · {{ $selectedEvaluationApplication->job->title }}
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('employers.application_pipeline') }}" class="pipeline-action" style="width: auto;">
                        <i class="fa fa-times"></i>
                    </a>
                </div>

                <form method="POST" action="{{ route('employers.application_pipeline.evaluate_interview', ['application' => $selectedEvaluationApplication->id]) }}">
                    @csrf
                    <div class="interview-form-grid">
                        <div class="interview-field">
                            <label for="technical-score">Chuyên môn / kinh nghiệm</label>
                            <input id="technical-score" name="technical_score" type="number" min="0" max="10" step="0.5" value="{{ $evaluationValues['technical_score'] }}" placeholder="0 - 10">
                            @error('technical_score') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field">
                            <label for="problem-solving-score">Tư duy giải quyết vấn đề</label>
                            <input id="problem-solving-score" name="problem_solving_score" type="number" min="0" max="10" step="0.5" value="{{ $evaluationValues['problem_solving_score'] }}" placeholder="0 - 10">
                            @error('problem_solving_score') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field">
                            <label for="communication-score">Giao tiếp / phối hợp</label>
                            <input id="communication-score" name="communication_score" type="number" min="0" max="10" step="0.5" value="{{ $evaluationValues['communication_score'] }}" placeholder="0 - 10">
                            @error('communication_score') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field">
                            <label for="culture-score">Phù hợp văn hóa FPT Edu</label>
                            <input id="culture-score" name="culture_score" type="number" min="0" max="10" step="0.5" value="{{ $evaluationValues['culture_score'] }}" placeholder="0 - 10">
                            @error('culture_score') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field interview-field--full">
                            <label for="interview-conclusion">Kết luận</label>
                            <select id="interview-conclusion" name="conclusion">
                                <option value="pass" @selected($evaluationValues['conclusion'] === 'pass')>Đạt - chuyển sang đề nghị tuyển dụng</option>
                                <option value="hold" @selected($evaluationValues['conclusion'] === 'hold')>Cần theo dõi / phỏng vấn thêm</option>
                                <option value="fail" @selected($evaluationValues['conclusion'] === 'fail')>Không đạt - từ chối</option>
                            </select>
                            @error('conclusion') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field interview-field--full">
                            <label for="evaluation-notes">Nhận xét phỏng vấn</label>
                            <textarea id="evaluation-notes" name="notes" placeholder="Ví dụ: chuyên môn tốt, cần đào tạo thêm về quy trình nội bộ...">{{ $evaluationValues['notes'] }}</textarea>
                            @error('notes') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="interview-field interview-field--full">
                            <label for="evaluation-rejected-reason">Lý do từ chối nếu kết luận không đạt</label>
                            <textarea id="evaluation-rejected-reason" name="rejected_reason" placeholder="Bắt buộc khi chọn Không đạt">{{ $evaluationValues['rejected_reason'] }}</textarea>
                            @error('rejected_reason') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="interview-modal__actions">
                        <a href="{{ route('employers.application_pipeline') }}" class="interview-modal__button">Hủy</a>
                        <button type="submit" class="interview-modal__button interview-modal__button--primary">
                            Lưu đánh giá PV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Message Modal -->
    @if($showMessageModal)
        <div class="interview-modal-backdrop" wire:click.self="closeMessageModal">
            <div class="interview-modal">
                <div class="interview-modal__header">
                    <h3 class="interview-modal__title">Nhắn tin cho ứng viên</h3>
                    <button type="button" class="interview-modal__close" wire:click="closeMessageModal">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="sendMessage" class="interview-modal__body">
                    <div class="interview-form-grid" style="grid-template-columns: 1fr;">
                        <div class="interview-field interview-field--full">
                            <label>Nội dung tin nhắn</label>
                            <textarea wire:model="messageContent" rows="5" placeholder="Nhập tin nhắn để thông báo cho ứng viên..."></textarea>
                            @error('messageContent') <span class="interview-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="interview-modal__actions">
                        <button type="button" wire:click="closeMessageModal" class="interview-modal__button">Hủy</button>
                        <button type="submit" class="interview-modal__button interview-modal__button--primary">
                            Gửi tin nhắn
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

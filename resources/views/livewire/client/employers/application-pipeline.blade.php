<div>
    <style>
        .pipeline-container {
            display: flex;
            overflow-x: auto;
            gap: 1.5rem;
            padding: 1rem 0 2rem;
            min-height: calc(100vh - 300px);
            scrollbar-width: thin;
            scrollbar-color: var(--fpt-orange) #f1f5f9;
        }

        .pipeline-column {
            flex: 0 0 320px;
            background: #f8fafc;
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            max-height: 100%;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
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
            padding: 1rem;
            flex-grow: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .candidate-card {
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
            cursor: move;
        }

        .candidate-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
            border-color: var(--fpt-orange-light);
        }

        .candidate-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .candidate-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1e293b;
            margin: 0;
        }

        .job-tag {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ai-match-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 0.5rem;
        }

        .card-actions {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-status-change {
            font-size: 0.75rem;
            color: #64748b;
            background: none;
            border: none;
            padding: 4px;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn-status-change:hover {
            background: #f1f5f9;
            color: var(--fpt-orange);
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
    </style>

    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li class="active">Pipeline Ứng viên</li>
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
                        <div class="filter-section">
                            <div>
                                <h3 style="margin: 0; font-weight: 700;">Pipeline Tuyển Dụng</h3>
                                <p style="margin: 5px 0 0; color: #64748b; font-size: 0.9rem;">Quản lý quy trình ứng tuyển của các ứng viên.</p>
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

                        @if (session()->has('message'))
                            <div class="alert alert-success border-0 rounded-4 mb-4" style="background: #ecfdf5; color: #059669;">
                                <i class="fa fa-check-circle me-1"></i> {{ session('message') }}
                            </div>
                        @endif

                        <div class="pipeline-container">
                            @foreach($statuses as $status)
                                <div class="pipeline-column">
                                    <div class="column-header">
                                        <div class="column-title">
                                            <i class="fa {{ $status->getIcon() }}" style="color: {{ $status->getColor() }};"></i>
                                            {{ $status->getLabel() }}
                                        </div>
                                        <span class="column-count">{{ count($applicationsByStatus[$status->value]) }}</span>
                                    </div>
                                    <div class="column-content">
                                        @foreach($applicationsByStatus[$status->value] as $app)
                                            @php
                                                $snapshot = is_array($app->profile_snapshot) ? $app->profile_snapshot : [];
                                                $candidateName = data_get($snapshot, 'name') ?: data_get($snapshot, 'candidate.name') ?: $app->candidate?->name ?: 'Ứng viên';
                                                $candidateEmail = data_get($snapshot, 'email') ?: data_get($snapshot, 'candidate.email') ?: $app->candidate?->email;
                                                $profileTitle = data_get($snapshot, 'profile_title') ?: data_get($snapshot, 'resume.profile_title');
                                                $submittedCvName = data_get($snapshot, 'cv.original_filename') ?: ($app->cv_path ? basename($app->cv_path) : null);
                                            @endphp
                                            <div class="candidate-card">
                                                <div class="d-flex align-items-center gap-3">
                                                    @if($app->candidate?->user?->avatar && file_exists(public_path('storage/' . $app->candidate->user->avatar)))
                                                        <img src="{{ asset('storage/' . $app->candidate->user->avatar) }}" class="candidate-avatar" alt="">
                                                    @else
                                                        <div class="candidate-avatar d-flex align-items-center justify-content-center bg-light text-muted font-weight-bold">
                                                            {{ mb_substr($candidateName, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div style="min-width: 0;">
                                                        <h5 class="candidate-name text-truncate">
                                                            <a href="{{ route('candidates.candidate_detail', ['id' => $app->candidate_id]) }}" class="text-decoration-none text-dark">
                                                                {{ $candidateName }}
                                                            </a>
                                                        </h5>
                                                        @if($candidateEmail)
                                                            <span class="job-tag" title="{{ $candidateEmail }}">{{ $candidateEmail }}</span>
                                                        @endif
                                                        @if($profileTitle)
                                                            <span class="job-tag" title="{{ $profileTitle }}">{{ $profileTitle }}</span>
                                                        @endif
                                                        <span class="job-tag" title="{{ $app->job?->title }}">{{ $app->job?->title ?? 'Vị trí không còn khả dụng' }}</span>
                                                    </div>
                                                </div>

                                                @php
                                                    $latestSubmission = $latestSubmissionsByApplicationKey[$app->candidate_id . ':' . $app->job_id] ?? null;
                                                @endphp

                                                <div class="d-flex justify-content-between align-items-center">
                                                    @if($latestSubmission && $latestSubmission->ai_matching_score)
                                                        <span class="ai-match-badge {{ $latestSubmission->ai_matching_score >= 80 ? 'bg-success text-white' : ($latestSubmission->ai_matching_score >= 50 ? 'bg-warning text-dark' : 'bg-danger text-white') }}">
                                                            AI: {{ $latestSubmission->ai_matching_score }}%
                                                        </span>
                                                    @else
                                                        <span class="text-muted" style="font-size: 0.7rem;">Chưa có điểm AI</span>
                                                    @endif
                                                    
                                                    <span class="text-muted" style="font-size: 0.7rem;">{{ optional($app->applied_at ?? $app->created_at)->format('d/m') }}</span>
                                                </div>

                                                @if($submittedCvName)
                                                    <div class="job-tag" title="{{ $submittedCvName }}">
                                                        CV: {{ $submittedCvName }}
                                                    </div>
                                                @endif

                                                <div class="card-actions">
                                                    <div class="dropdown">
                                                        <button class="btn-status-change" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fa fa-exchange"></i> Chuyển...
                                                        </button>
                                                        <ul class="dropdown-menu shadow border-0 rounded-3">
                                                            @foreach(\App\Enums\StatusApplicationEnum::cases() as $targetStatus)
                                                                @if($targetStatus->value !== $status->value)
                                                                    <li>
                                                                        <a class="dropdown-item py-2" href="#" wire:click.prevent="updateStatus({{ $app->id }}, '{{ $targetStatus->value }}')">
                                                                            <i class="fa {{ $targetStatus->getIcon() }} me-2" style="color: {{ $targetStatus->getColor() }};"></i>
                                                                            {{ $targetStatus->getLabel() }}
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <a href="{{ route('candidates.candidate_detail', ['id' => $app->candidate_id]) }}" class="text-decoration-none" style="font-size: 0.75rem; color: var(--fpt-orange);">
                                                        Hồ sơ <i class="fa fa-arrow-right"></i>
                                                    </a>
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
</div>

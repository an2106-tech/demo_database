<div>
    @php
        $application = $selectedApplication;
        $status = $application?->status instanceof \App\Enums\StatusApplicationEnum
            ? $application->status
            : \App\Enums\StatusApplicationEnum::tryFrom((string) $application?->status);
        $snapshot = is_array($application?->profile_snapshot) ? $application->profile_snapshot : [];
        $resumeSnapshot = data_get($snapshot, 'resume', []);
        $candidateName = $application?->snapshotCandidateName() ?: $candidate->name;
        $candidateEmail = $application?->snapshotCandidateEmail() ?: $candidate->email;
        $candidatePhone = $application?->snapshotCandidatePhone() ?: $candidate->phone;
        $profileTitle = $application?->snapshotProfileTitle()
            ?: $candidate->resume?->profile_title
            ?: 'Ứng viên';
        $cvUrl = $application?->submittedCvUrl();
        $cvName = $application?->submittedCvName();
        $aiScore = $latestSubmission?->ai_matching_score;
        $aiTone = match (true) {
            $aiScore !== null && $aiScore >= 80 => 'success',
            $aiScore !== null && $aiScore >= 50 => 'warning',
            $aiScore !== null => 'danger',
            default => 'muted',
        };
        $latestInterview = $interviews->first();
        $latestScorecard = $scorecards->first();
    @endphp

    <style>
        .ats-detail-shell { display: grid; gap: 24px; }
        .ats-hero { background: linear-gradient(135deg, #fff 0%, #fff7ed 100%); border: 1px solid rgba(243,112,33,.18); border-radius: 24px; box-shadow: 0 18px 50px rgba(15,23,42,.08); padding: 28px; }
        .ats-hero__grid { align-items: center; display: grid; gap: 24px; grid-template-columns: 1fr auto; }
        .ats-eyebrow { color: #f37021; font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        .ats-title { color: #0f172a; font-size: clamp(26px, 3vw, 40px); font-weight: 900; margin: 8px 0; }
        .ats-subtitle { color: #64748b; font-size: 15px; line-height: 1.7; margin: 0; max-width: 760px; }
        .ats-avatar { align-items: center; background: #fff; border: 1px solid #fed7aa; border-radius: 24px; color: #f37021; display: flex; font-size: 42px; font-weight: 900; height: 104px; justify-content: center; overflow: hidden; width: 104px; }
        .ats-avatar img { height: 100%; object-fit: cover; width: 100%; }
        .ats-kpis { display: grid; gap: 14px; grid-template-columns: repeat(4, minmax(0, 1fr)); margin-top: 24px; }
        .ats-kpi { background: rgba(255,255,255,.78); border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 16px; }
        .ats-kpi span { color: #64748b; display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; }
        .ats-kpi strong { color: #0f172a; display: block; font-size: 20px; font-weight: 900; }
        .ats-layout { display: grid; gap: 24px; grid-template-columns: minmax(0, 1.45fr) minmax(320px, .75fr); }
        .ats-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 22px; box-shadow: 0 12px 36px rgba(15,23,42,.06); padding: 24px; }
        .ats-card + .ats-card { margin-top: 20px; }
        .ats-card__head { align-items: center; display: flex; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
        .ats-card h3 { color: #0f172a; font-size: 18px; font-weight: 900; margin: 0; }
        .ats-muted { color: #64748b; }
        .ats-chip { align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 999px; color: #475569; display: inline-flex; font-size: 12px; font-weight: 800; gap: 6px; padding: 7px 11px; }
        .ats-chip--success { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
        .ats-chip--warning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .ats-chip--danger { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .ats-chip--accent { background: #fff7ed; border-color: #fed7aa; color: #c2410c; }
        .ats-info-grid { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ats-info { background: #f8fafc; border-radius: 16px; padding: 14px; }
        .ats-info span { color: #64748b; display: block; font-size: 12px; font-weight: 700; margin-bottom: 5px; }
        .ats-info strong { color: #0f172a; font-size: 14px; font-weight: 800; word-break: break-word; }
        .ats-timeline { border-left: 2px solid #fed7aa; display: grid; gap: 18px; margin-left: 9px; padding-left: 22px; }
        .ats-timeline__item { position: relative; }
        .ats-timeline__item::before { background: #f37021; border: 3px solid #fff7ed; border-radius: 999px; content: ""; height: 16px; left: -31px; position: absolute; top: 3px; width: 16px; }
        .ats-timeline__item h4 { color: #0f172a; font-size: 14px; font-weight: 900; margin: 0 0 4px; }
        .ats-timeline__item p { color: #64748b; font-size: 13px; line-height: 1.6; margin: 0; }
        .ats-list { display: grid; gap: 12px; }
        .ats-list__item { border: 1px solid #e2e8f0; border-radius: 16px; padding: 14px; }
        .ats-list__item h4 { color: #0f172a; font-size: 14px; font-weight: 900; margin: 0 0 6px; }
        .ats-list__item p { color: #64748b; font-size: 13px; margin: 0; }
        .ats-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .ats-btn { background: #f37021; border: none; border-radius: 12px; color: #fff !important; display: inline-flex; font-size: 13px; font-weight: 900; gap: 8px; padding: 10px 14px; text-decoration: none; }
        .ats-btn--secondary { background: #fff; border: 1px solid #fed7aa; color: #c2410c !important; }
        @media (max-width: 1199px) { .ats-layout { grid-template-columns: 1fr; } }
        @media (max-width: 767px) { .ats-hero__grid, .ats-kpis, .ats-info-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li><a href="{{ route('employers.manage_candidates') }}">Ứng viên</a></li>
            <li class="active">Chi tiết ATS</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                @if (request()->routeIs('employers.*'))
                    <div class="col-lg-3 col-xl-3 dashboard-left-border">
                        @include('livewire.client.partials.employer-sidebar')
                    </div>
                @endif

                <div class="{{ request()->routeIs('employers.*') ? 'col-lg-9 col-xl-9' : 'col-12' }}">
                    <div class="ats-detail-shell">
                        <section class="ats-hero">
                            <div class="ats-hero__grid">
                                <div>
                                    <span class="ats-eyebrow">Candidate ATS Detail</span>
                                    <h1 class="ats-title">{{ $candidateName }}</h1>
                                    <p class="ats-subtitle">
                                        {{ $profileTitle }} · Hồ sơ được xem theo snapshot lúc ứng tuyển để HR đánh giá đúng dữ liệu ứng viên đã nộp.
                                    </p>
                                    <div class="ats-actions mt-4">
                                        @if ($cvUrl)
                                            <a href="{{ $cvUrl }}" target="_blank" rel="noopener" class="ats-btn">
                                                <i class="fa fa-file-pdf-o"></i> Mở CV đã nộp
                                            </a>
                                        @endif
                                        @if ($candidateEmail)
                                            <a href="mailto:{{ $candidateEmail }}" class="ats-btn ats-btn--secondary">
                                                <i class="fa fa-envelope"></i> Gửi email
                                            </a>
                                        @endif
                                        <a href="{{ route('employers.application_pipeline') }}" class="ats-btn ats-btn--secondary">
                                            <i class="fa fa-columns"></i> Về pipeline
                                        </a>
                                    </div>
                                </div>
                                <div class="ats-avatar">
                                    @if ($candidate->user?->avatar && file_exists(public_path('storage/' . $candidate->user->avatar)))
                                        <img src="{{ asset('storage/' . $candidate->user->avatar) }}" alt="{{ $candidateName }}">
                                    @else
                                        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="24" cy="24" r="24" fill="#FFF7ED"/>
                                            <circle cx="24" cy="18" r="8" fill="#F37021"/>
                                            <path d="M10 40C12.25 31.75 17.25 28 24 28C30.75 28 35.75 31.75 38 40" fill="#FDBA74"/>
                                            <path d="M10 40C12.25 31.75 17.25 28 24 28C30.75 28 35.75 31.75 38 40" stroke="#F37021" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <div class="ats-kpis">
                                <div class="ats-kpi">
                                    <span>Giai đoạn</span>
                                    <strong>{{ $status?->getPipelineStageLabel() ?? 'Chưa có' }}</strong>
                                </div>
                                <div class="ats-kpi">
                                    <span>AI Match</span>
                                    <strong>{{ $aiScore !== null ? $aiScore.'%' : 'Chưa chấm' }}</strong>
                                </div>
                                <div class="ats-kpi">
                                    <span>Phỏng vấn</span>
                                    <strong>{{ $interviews->count() }}</strong>
                                </div>
                                <div class="ats-kpi">
                                    <span>Scorecard</span>
                                    <strong>{{ $scorecards->count() }}</strong>
                                </div>
                            </div>
                        </section>

                        @if (! $application)
                            <div class="ats-card text-center">
                                <h3>Chưa có hồ sơ ứng tuyển trong phạm vi bạn quản lý</h3>
                                <p class="ats-muted mb-0 mt-2">Ứng viên có thể chỉ mới tồn tại trong hệ thống nhưng chưa ứng tuyển vào vị trí thuộc chi nhánh của bạn.</p>
                            </div>
                        @else
                            <div class="ats-layout">
                                <main>
                                    <section class="ats-card">
                                        <div class="ats-card__head">
                                            <h3>Snapshot ứng tuyển</h3>
                                            <span class="ats-chip ats-chip--accent">{{ $status?->getLabel() ?? 'Chưa có trạng thái' }}</span>
                                        </div>
                                        <div class="ats-info-grid">
                                            <div class="ats-info"><span>Vị trí</span><strong>{{ $application->job?->title ?? '-' }}</strong></div>
                                            <div class="ats-info"><span>Chi nhánh</span><strong>{{ $application->job?->branch?->name ?? '-' }}</strong></div>
                                            <div class="ats-info"><span>Phòng ban</span><strong>{{ $application->job?->department?->name ?? '-' }}</strong></div>
                                            <div class="ats-info"><span>Nơi làm việc</span><strong>{{ $application->job?->workplace?->name ?? '-' }}</strong></div>
                                            <div class="ats-info"><span>Email snapshot</span><strong>{{ $candidateEmail ?? '-' }}</strong></div>
                                            <div class="ats-info"><span>Số điện thoại</span><strong>{{ $candidatePhone ?? '-' }}</strong></div>
                                            <div class="ats-info"><span>Kinh nghiệm</span><strong>{{ $application->snapshotCandidateExperienceYears() ?? $candidate->experience_years ?? '-' }} năm</strong></div>
                                            <div class="ats-info"><span>CV</span><strong>{{ $cvName ?? '-' }}</strong></div>
                                        </div>
                                    </section>

                                    <section class="ats-card">
                                        <div class="ats-card__head">
                                            <h3>Timeline xử lý hồ sơ</h3>
                                        </div>
                                        <div class="ats-timeline">
                                            @forelse ($timeline as $event)
                                                <div class="ats-timeline__item">
                                                    <h4>{{ $event['title'] }}</h4>
                                                    <p>{{ $event['description'] ?: '-' }}</p>
                                                    <p><i class="fa fa-clock-o"></i> {{ optional($event['time'])->format('d/m/Y H:i') }}</p>
                                                </div>
                                            @empty
                                                <p class="ats-muted mb-0">Chưa có lịch sử xử lý.</p>
                                            @endforelse
                                        </div>
                                    </section>

                                    <section class="ats-card">
                                        <div class="ats-card__head">
                                            <h3>Phỏng vấn</h3>
                                            @if ($latestInterview)
                                                <span class="ats-chip">Gần nhất: {{ optional($latestInterview->scheduled_at)->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                        <div class="ats-list">
                                            @forelse ($interviews as $interview)
                                                <div class="ats-list__item">
                                                    <h4>{{ $interview->round_name ?: 'Vòng '.$interview->round_number }} · {{ ucfirst((string) $interview->type) }}</h4>
                                                    <p>Thời gian: {{ optional($interview->scheduled_at)->format('d/m/Y H:i') }} · {{ $interview->duration_minutes ?? 60 }} phút</p>
                                                    <p>Người phỏng vấn: {{ $interview->interviewer?->name ?? 'Chưa gán' }}</p>
                                                    <p>Địa điểm/link: {{ $interview->meeting_link ?: $interview->workplace?->name ?: '-' }}</p>
                                                    <p>Kết quả: {{ $interview->result ?: 'Chưa cập nhật' }}</p>
                                                </div>
                                            @empty
                                                <p class="ats-muted mb-0">Chưa có lịch phỏng vấn.</p>
                                            @endforelse
                                        </div>
                                    </section>
                                </main>

                                <aside>
                                    <section class="ats-card">
                                        <div class="ats-card__head">
                                            <h3>AI & Submission</h3>
                                            <span class="ats-chip ats-chip--{{ $aiTone }}">{{ $aiScore !== null ? $aiScore.'%' : 'N/A' }}</span>
                                        </div>
                                        @if ($latestSubmission?->ai_analysis)
                                            <div class="ats-list">
                                                <div class="ats-list__item">
                                                    <h4>Điểm phù hợp</h4>
                                                    <p>{{ $latestSubmission->job?->title ?? '-' }}</p>
                                                </div>
                                                <div class="ats-list__item">
                                                    <h4>Lý do phù hợp</h4>
                                                    @forelse (($latestSubmission->ai_analysis['match_reasons'] ?? []) as $reason)
                                                        <p>• {{ $reason }}</p>
                                                    @empty
                                                        <p>Chưa có phân tích.</p>
                                                    @endforelse
                                                </div>
                                                <div class="ats-list__item">
                                                    <h4>Kỹ năng còn thiếu</h4>
                                                    @forelse (($latestSubmission->ai_analysis['missing_skills'] ?? []) as $missing)
                                                        <p>• {{ $missing }}</p>
                                                    @empty
                                                        <p>Chưa có ghi nhận.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @else
                                            <p class="ats-muted mb-0">Chưa có kết quả phân tích AI cho submission gần nhất.</p>
                                        @endif
                                    </section>

                                    <section class="ats-card">
                                        <div class="ats-card__head">
                                            <h3>Scorecard</h3>
                                            @if ($latestScorecard)
                                                <span class="ats-chip ats-chip--accent">{{ $latestScorecard->average_score ?? '-' }}/10</span>
                                            @endif
                                        </div>
                                        <div class="ats-list">
                                            @forelse ($scorecards as $scorecard)
                                                <div class="ats-list__item">
                                                    <h4>{{ $scorecard->evaluator?->name ?? 'Người đánh giá' }}</h4>
                                                    <p>Điểm TB: {{ $scorecard->average_score ?? '-' }}</p>
                                                    <p>Kết luận: {{ $scorecard->recommended_conclusion ?: $scorecard->conclusion ?: '-' }}</p>
                                                    @if ($scorecard->notes)
                                                        <p>Ghi chú: {{ $scorecard->notes }}</p>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="ats-muted mb-0">Chưa có scorecard.</p>
                                            @endforelse
                                        </div>
                                    </section>

                                    <section class="ats-card">
                                        <div class="ats-card__head">
                                            <h3>Offer gần nhất</h3>
                                            @if ($latestOffer)
                                                <span class="ats-chip ats-chip--accent">{{ $latestOffer->status }}</span>
                                            @endif
                                        </div>
                                        @if ($latestOffer)
                                            <div class="ats-info-grid" style="grid-template-columns: 1fr;">
                                                <div class="ats-info"><span>Lương đề nghị</span><strong>{{ $latestOffer->salary_offered ? number_format((float) $latestOffer->salary_offered, 0, ',', '.').' VND' : '-' }}</strong></div>
                                                <div class="ats-info"><span>Ngày bắt đầu</span><strong>{{ optional($latestOffer->start_date)->format('d/m/Y') ?? '-' }}</strong></div>
                                                <div class="ats-info"><span>Hạn phản hồi</span><strong>{{ optional($latestOffer->expires_at)->format('d/m/Y H:i') ?? '-' }}</strong></div>
                                                <div class="ats-info"><span>Người duyệt</span><strong>{{ $latestOffer->approvedByUser?->name ?? '-' }}</strong></div>
                                            </div>
                                        @else
                                            <p class="ats-muted mb-0">Chưa có offer.</p>
                                        @endif
                                    </section>

                                    <section class="ats-card">
                                        <div class="ats-card__head">
                                            <h3>Các lần ứng tuyển</h3>
                                        </div>
                                        <div class="ats-list">
                                            @foreach ($applications as $app)
                                                @php
                                                    $appStatus = $app->status instanceof \App\Enums\StatusApplicationEnum
                                                        ? $app->status
                                                        : \App\Enums\StatusApplicationEnum::tryFrom((string) $app->status);
                                                @endphp
                                                <div class="ats-list__item">
                                                    <h4>{{ $app->job?->title ?? 'Vị trí không còn khả dụng' }}</h4>
                                                    <p>{{ $app->job?->branch?->name ?? '-' }} · {{ optional($app->applied_at ?? $app->created_at)->format('d/m/Y') }}</p>
                                                    <span class="ats-chip">{{ $appStatus?->getLabel() ?? '-' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </section>
                                </aside>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

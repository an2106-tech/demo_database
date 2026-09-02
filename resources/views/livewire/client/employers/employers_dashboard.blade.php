<div class="premium-dashboard-container">
    <section class="candidate-dashboard-area section_70" style="padding: 28px 0 60px 0; background: #f8fafc; min-height: 85vh;">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right d-flex flex-column gap-4">
                        <!-- Welcome Section (Clean White Bento Hero) -->
                        <div class="p-4 p-lg-5 rounded-4 shadow-sm bg-white position-relative overflow-hidden" style="border: 1px solid #e2e8f0;">
                            <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(243, 112, 33, 0.08) 0%, rgba(255,255,255,0) 70%); border-radius: 50%; pointer-events: none;"></div>
                            
                            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4 position-relative">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative">
                                        <img src="{{ $user->avatar_url ?? asset('assets/img/candidate-default.png') }}" 
                                             alt="{{ $user->name }}" 
                                             class="rounded-circle object-fit-cover shadow-sm border border-2 border-white" 
                                             style="width: 64px; height: 64px;">
                                        <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle"></span>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                            <span class="badge rounded-pill px-2.5 py-1" style="background: rgba(243, 112, 33, 0.1); color: #f37021; font-size: 11.5px; font-weight: 700;">
                                                <i class="fa fa-building-o me-1"></i> FPT Education Portal
                                            </span>
                                            @if($user->branch)
                                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                                    <i class="fa fa-map-marker me-1 text-danger"></i> {{ $user->branch->name }}
                                                </span>
                                            @endif
                                            <span class="badge bg-secondary-subtle text-dark border rounded-pill px-2 py-1 text-uppercase" style="font-size: 10px; font-weight: 800; letter-spacing: 0.5px;">
                                                {{ $user->role }}
                                            </span>
                                        </div>
                                        <h2 class="fw-bold mb-1" style="color: #0f172a; font-size: 24px; letter-spacing: -0.02em;">
                                            {{ $greeting }}, {{ $user->name }}!
                                        </h2>
                                        <p class="mb-0 text-muted" style="font-size: 13.5px;">
                                            Hệ thống quản trị tuyển dụng và theo dõi hồ sơ ứng viên thông minh.
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2.5 flex-wrap">
                                    <a href="{{ route('employers.application_pipeline') }}" class="btn px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 rounded-3" style="background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; font-size: 13px; transition: all 0.2s ease;">
                                        <i class="fa fa-columns text-primary"></i> Pipeline tuyển dụng
                                    </a>
                                    <a href="{{ route('employers.post_job') }}" class="btn px-4 py-2 text-white fw-bold d-inline-flex align-items-center gap-2 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #f37021 0%, #ea580c 100%); border: none; font-size: 13px; transition: all 0.2s ease;">
                                        <i class="fa fa-plus-circle"></i> Đăng tin mới
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- 4 Primary Stat Cards -->
                        <div class="row g-3">
                            <div class="col-6 col-lg-3">
                                <div class="p-3.5 p-xl-4 rounded-4 bg-white shadow-sm h-100 d-flex align-items-center justify-content-between" style="border: 1px solid #e2e8f0; transition: transform 0.2s ease;">
                                    <div>
                                        <div class="text-muted fw-semibold mb-1" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.3px;">Việc làm đã đăng</div>
                                        <div class="fw-bold" style="font-size: 26px; color: #0f172a; line-height: 1.2;">{{ number_format($totalJobs) }}</div>
                                        <span class="badge rounded-pill mt-2" style="background: #e0f2fe; color: #0284c7; font-size: 11px;">
                                            <i class="fa fa-check me-1"></i> Đang hiển thị
                                        </span>
                                    </div>
                                    <div style="width: 46px; height: 46px; border-radius: 13px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                        <i class="fa fa-briefcase"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="p-3.5 p-xl-4 rounded-4 bg-white shadow-sm h-100 d-flex align-items-center justify-content-between" style="border: 1px solid #e2e8f0; transition: transform 0.2s ease;">
                                    <div>
                                        <div class="text-muted fw-semibold mb-1" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.3px;">Hồ sơ đã nhận</div>
                                        <div class="fw-bold" style="font-size: 26px; color: #0f172a; line-height: 1.2;">{{ number_format($totalApplications) }}</div>
                                        <span class="badge rounded-pill mt-2" style="background: #ede9fe; color: #7c3aed; font-size: 11px;">
                                            <i class="fa fa-file-text-o me-1"></i> {{ number_format($totalCandidates) }} ứng viên
                                        </span>
                                    </div>
                                    <div style="width: 46px; height: 46px; border-radius: 13px; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                        <i class="fa fa-id-card-o"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="p-3.5 p-xl-4 rounded-4 bg-white shadow-sm h-100 d-flex align-items-center justify-content-between" style="border: 1px solid #e2e8f0; transition: transform 0.2s ease;">
                                    <div>
                                        <div class="text-muted fw-semibold mb-1" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.3px;">Phỏng vấn sắp tới</div>
                                        <div class="fw-bold" style="font-size: 26px; color: #ea580c; line-height: 1.2;">{{ count($upcomingInterviews) }}</div>
                                        <span class="badge rounded-pill mt-2" style="background: #fff7ed; color: #ea580c; font-size: 11px;">
                                            <i class="fa fa-calendar-check-o me-1"></i> Cần đánh giá
                                        </span>
                                    </div>
                                    <div style="width: 46px; height: 46px; border-radius: 13px; background: #fff7ed; color: #ea580c; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="p-3.5 p-xl-4 rounded-4 bg-white shadow-sm h-100 d-flex align-items-center justify-content-between" style="border: 1px solid #e2e8f0; transition: transform 0.2s ease;">
                                    <div>
                                        <div class="text-muted fw-semibold mb-1" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.3px;">Đã tuyển thành công</div>
                                        <div class="fw-bold" style="font-size: 26px; color: #059669; line-height: 1.2;">{{ number_format($pipelineMetrics['hired'] ?? 0) }}</div>
                                        <span class="badge rounded-pill mt-2" style="background: #ecfdf5; color: #059669; font-size: 11px;">
                                            <i class="fa fa-trophy me-1"></i> Nhân sự mới
                                        </span>
                                    </div>
                                    <div style="width: 46px; height: 46px; border-radius: 13px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                        <i class="fa fa-user-plus"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Director Pending Jobs Notice if any -->
                        @if($isDirector && $pendingJobs > 0)
                            <div class="p-3.5 rounded-4 shadow-sm d-flex align-items-center justify-content-between gap-3" style="background: #fff7ed; border: 1px solid #fed7aa;">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #ea580c; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                        <i class="fa fa-bell"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 14px; color: #9a3412;">Có {{ $pendingJobs }} tin tuyển dụng đang chờ Giám đốc phê duyệt!</div>
                                        <div style="font-size: 12.5px; color: #c2410c;">Vui lòng kiểm tra và duyệt tin để các vị trí được hiển thị công khai cho ứng viên.</div>
                                    </div>
                                </div>
                                <a href="{{ route('director.approve_jobs') }}" class="btn btn-sm text-white fw-bold px-3 py-2 rounded-3 text-nowrap" style="background: #ea580c; border: none; font-size: 12.5px;">
                                    Duyệt tin ngay <i class="fa fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        @endif

                        <!-- Recruitment Funnel Overview -->
                        <div class="p-4 rounded-4 bg-white shadow-sm" style="border: 1px solid #e2e8f0;">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(243, 112, 33, 0.1); color: #f37021; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                                        <i class="fa fa-filter"></i>
                                    </div>
                                    <h4 class="fw-bold mb-0" style="font-size: 15px; color: #0f172a;">
                                        Phễu tuyển dụng hiện tại (Recruitment Funnel)
                                    </h4>
                                </div>
                                <a href="{{ route('employers.application_pipeline') }}" class="text-decoration-none fw-bold" style="font-size: 12.5px; color: #f37021;">
                                    Chi tiết Pipeline <i class="fa fa-angle-right ms-1"></i>
                                </a>
                            </div>

                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #f1f5f9;">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-bold" style="font-size: 12px; color: #64748b;">1. Sàng lọc & Sơ tuyển</span>
                                            <span class="badge bg-secondary-subtle text-dark">{{ $pipelineMetrics['screening'] ?? 0 }}</span>
                                        </div>
                                        <div class="fw-bold" style="font-size: 20px; color: #1e293b;">{{ $pipelineMetrics['screening'] ?? 0 }} <span style="font-size: 12px; font-weight: 500; color: #94a3b8;">hồ sơ</span></div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar" style="width: {{ $totalApplications > 0 ? (($pipelineMetrics['screening'] ?? 0) / $totalApplications * 100) : 0 }}%; background: #64748b;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-3" style="background: #f0fdf4; border: 1px solid #dcfce7;">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-bold" style="font-size: 12px; color: #15803d;">2. Phỏng vấn</span>
                                            <span class="badge bg-success-subtle text-success">{{ $pipelineMetrics['interviewing'] ?? 0 }}</span>
                                        </div>
                                        <div class="fw-bold" style="font-size: 20px; color: #15803d;">{{ $pipelineMetrics['interviewing'] ?? 0 }} <span style="font-size: 12px; font-weight: 500; color: #86efac;">ứng viên</span></div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $totalApplications > 0 ? (($pipelineMetrics['interviewing'] ?? 0) / $totalApplications * 100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-3" style="background: #fff7ed; border: 1px solid #ffedd5;">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-bold" style="font-size: 12px; color: #c2410c;">3. Đề nghị nhận việc</span>
                                            <span class="badge bg-warning-subtle text-warning">{{ $pipelineMetrics['offered'] ?? 0 }}</span>
                                        </div>
                                        <div class="fw-bold" style="font-size: 20px; color: #c2410c;">{{ $pipelineMetrics['offered'] ?? 0 }} <span style="font-size: 12px; font-weight: 500; color: #fdba74;">offer</span></div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar bg-warning" style="width: {{ $totalApplications > 0 ? (($pipelineMetrics['offered'] ?? 0) / $totalApplications * 100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-3" style="background: #eff6ff; border: 1px solid #dbeafe;">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-bold" style="font-size: 12px; color: #1d4ed8;">4. Đã tuyển dụng</span>
                                            <span class="badge bg-primary-subtle text-primary">{{ $pipelineMetrics['hired'] ?? 0 }}</span>
                                        </div>
                                        <div class="fw-bold" style="font-size: 20px; color: #1d4ed8;">{{ $pipelineMetrics['hired'] ?? 0 }} <span style="font-size: 12px; font-weight: 500; color: #93c5fd;">nhân sự</span></div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $totalApplications > 0 ? (($pipelineMetrics['hired'] ?? 0) / $totalApplications * 100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2 Columns Bento Layout -->
                        <div class="row g-4">
                            <!-- Recent Applications Column (8/12) -->
                            <div class="col-lg-8">
                                <div class="p-4 rounded-4 bg-white shadow-sm h-100" style="border: 1px solid #e2e8f0;">
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 28px; height: 28px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                                                <i class="fa fa-users"></i>
                                            </div>
                                            <h4 class="fw-bold mb-0" style="font-size: 15px; color: #0f172a;">
                                                Hồ sơ ứng tuyển mới nhất
                                            </h4>
                                        </div>
                                        <a href="{{ route('employers.manage_candidates') }}" class="fw-bold text-decoration-none" style="font-size: 12.5px; color: #f37021;">
                                            Xem tất cả <i class="fa fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>

                                    <div class="d-flex flex-column gap-2.5">
                                        @forelse($recentApplications as $app)
                                            <div class="p-3 rounded-3 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3" style="background: #f8fafc; border: 1px solid #f1f5f9; transition: all 0.2s ease;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="position-relative flex-shrink-0">
                                                        <img src="{{ $app->candidate?->user?->avatar_url ?? asset('assets/img/candidate-default.png') }}" 
                                                             alt="{{ $app->snapshotCandidateName() }}" 
                                                             class="rounded-circle object-fit-cover border" 
                                                             style="width: 44px; height: 44px; background: #fff;">
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <a href="{{ route('employers.candidate_detail', $app->candidate_id ?? 0) }}" class="fw-bold text-decoration-none text-dark" style="font-size: 14px;">
                                                                {{ $app->snapshotCandidateName() }}
                                                            </a>
                                                            <span class="badge rounded-pill px-2.5 py-0.5" style="background: {{ $app->status->getColor() }}15; color: {{ $app->status->getColor() }}; font-size: 10.5px; font-weight: 700;">
                                                                {{ $app->status->getLabel() }}
                                                            </span>
                                                        </div>
                                                        <div class="text-muted" style="font-size: 12px; margin-top: 2px;">
                                                            <i class="fa fa-briefcase me-1 text-primary"></i> {{ $app->job?->title ?? 'Vị trí đã đóng' }}
                                                            <span class="mx-1">•</span>
                                                            <i class="fa fa-clock-o me-1"></i> {{ optional($app->applied_at ?? $app->created_at)->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center gap-2 align-self-end align-self-sm-center">
                                                    <a href="{{ route('employers.application_pipeline', ['selectedJobId' => $app->job_id]) }}" class="btn btn-sm btn-light border px-2.5 py-1 rounded-2 text-secondary" title="Xem trên Pipeline" style="font-size: 11.5px;">
                                                        <i class="fa fa-columns"></i>
                                                    </a>
                                                    <a href="{{ route('employers.candidate_detail', $app->candidate_id ?? 0) }}" class="btn btn-sm fw-bold px-3 py-1 rounded-2 text-white" style="background: #f37021; border: none; font-size: 11.5px;">
                                                        Chi tiết
                                                    </a>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 text-muted">
                                                <i class="fa fa-inbox" style="font-size: 36px; color: #cbd5e1; margin-bottom: 8px;"></i>
                                                <p class="m-0" style="font-size: 13px;">Chưa có hồ sơ ứng tuyển mới nào trong chi nhánh.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column (4/12): Upcoming Interviews & Quick Actions -->
                            <div class="col-lg-4 d-flex flex-column gap-4">
                                <!-- Upcoming Interviews Widget -->
                                <div class="p-4 rounded-4 bg-white shadow-sm" style="border: 1px solid #e2e8f0;">
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 28px; height: 28px; border-radius: 8px; background: #fff7ed; color: #ea580c; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                                                <i class="fa fa-calendar-check-o"></i>
                                            </div>
                                            <h4 class="fw-bold mb-0" style="font-size: 15px; color: #0f172a;">
                                                Lịch phỏng vấn sắp tới
                                            </h4>
                                        </div>
                                        <span class="badge rounded-pill bg-light text-secondary border">{{ count($upcomingInterviews) }}</span>
                                    </div>

                                    <div class="d-flex flex-column gap-2.5">
                                        @forelse($upcomingInterviews as $interview)
                                            <div class="p-2.5 rounded-3" style="background: #f8fafc; border: 1px solid #f1f5f9;">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-bold text-truncate" style="font-size: 13px; color: #0f172a; max-width: 160px;">
                                                        {{ $interview->application?->snapshotCandidateName() ?? 'Ứng viên' }}
                                                    </span>
                                                    <span class="badge rounded-pill" style="background: {{ $interview->type === 'online' ? '#dbeafe' : '#fef3c7' }}; color: {{ $interview->type === 'online' ? '#1e40af' : '#92400e' }}; font-size: 10px;">
                                                        {{ $interview->type === 'online' ? 'Online' : 'Offline' }}
                                                    </span>
                                                </div>
                                                <div class="text-muted" style="font-size: 11.5px;">
                                                    <i class="fa fa-clock-o text-warning me-1"></i>
                                                    {{ $interview->scheduled_at ? $interview->scheduled_at->format('H:i - d/m/Y') : 'Chưa định giờ' }}
                                                </div>
                                                <div class="text-muted text-truncate" style="font-size: 11px; margin-top: 2px;">
                                                    <i class="fa fa-briefcase me-1"></i> {{ $interview->application?->job?->title ?? 'Vị trí phỏng vấn' }}
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-3 text-muted">
                                                <i class="fa fa-calendar-o" style="font-size: 24px; color: #cbd5e1; margin-bottom: 4px;"></i>
                                                <p class="m-0" style="font-size: 12px;">Không có lịch phỏng vấn nào sắp tới.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Quick Actions Navigation -->
                                <div class="p-4 rounded-4 bg-white shadow-sm" style="border: 1px solid #e2e8f0;">
                                    <div class="pb-2 mb-3 border-bottom">
                                        <h4 class="fw-bold mb-0" style="font-size: 15px; color: #0f172a;">
                                            <i class="fa fa-compass text-primary me-2"></i> Phím tắt quản trị
                                        </h4>
                                    </div>

                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('employers.manage_jobs') }}" class="p-2.5 rounded-3 d-flex align-items-center justify-content-between text-decoration-none" style="background: #f8fafc; border: 1px solid #f1f5f9; color: #0f172a;">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div style="width: 32px; height: 32px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                                                    <i class="fa fa-briefcase"></i>
                                                </div>
                                                <div>
                                                    <div style="font-size: 12.5px; font-weight: 700;">Quản lý tin đăng</div>
                                                    <div style="font-size: 11px; color: #64748b;">Xem danh sách & trạng thái tin</div>
                                                </div>
                                            </div>
                                            <i class="fa fa-angle-right text-muted"></i>
                                        </a>

                                        <a href="{{ route('employers.company_profile') }}" class="p-2.5 rounded-3 d-flex align-items-center justify-content-between text-decoration-none" style="background: #f8fafc; border: 1px solid #f1f5f9; color: #0f172a;">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div style="width: 32px; height: 32px; border-radius: 8px; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                                                    <i class="fa fa-building"></i>
                                                </div>
                                                <div>
                                                    <div style="font-size: 12.5px; font-weight: 700;">Hồ sơ đơn vị / Cơ sở</div>
                                                    <div style="font-size: 11px; color: #64748b;">Thông tin thương hiệu FPT</div>
                                                </div>
                                            </div>
                                            <i class="fa fa-angle-right text-muted"></i>
                                        </a>

                                        <a href="{{ route('employers.browse') }}" class="p-2.5 rounded-3 d-flex align-items-center justify-content-between text-decoration-none" style="background: #f8fafc; border: 1px solid #f1f5f9; color: #0f172a;">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div style="width: 32px; height: 32px; border-radius: 8px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                                                    <i class="fa fa-search"></i>
                                                </div>
                                                <div>
                                                    <div style="font-size: 12.5px; font-weight: 700;">Tìm kiếm ứng viên</div>
                                                    <div style="font-size: 11px; color: #64748b;">Khám phá kho hồ sơ tài năng</div>
                                                </div>
                                            </div>
                                            <i class="fa fa-angle-right text-muted"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

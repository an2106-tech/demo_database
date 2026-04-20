<div class="premium-dashboard-container">
    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right">
                        <!-- Welcome Section -->
                        <div class="welcome-section">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3>{{ $greeting }}, {{ $user->name }}!</h3>
                                    <p>Chúc bạn một ngày làm việc hiệu quả và tuyển dụng thành công.</p>
                                </div>
                                <div class="col-md-4 text-md-end d-none d-md-block">
                                    <a href="{{ route('employers.post_job') }}" class="quick-action-btn">
                                        <i class="fa fa-plus-circle"></i> Đăng tin mới
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="dashboard-stats-grid">
                            <div class="premium-stat-card">
                                <div class="stat-icon" style="background: linear-gradient(135deg, var(--fpt-orange) 0%, #ff4b1f 100%);">
                                    <i class="fa fa-briefcase"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value">{{ number_format($totalJobs) }}</div>
                                    <div class="stat-label">Việc làm đã đăng</div>
                                </div>
                            </div>

                            <div class="premium-stat-card">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
                                    <i class="fa fa-file-text-o"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value">{{ number_format($totalApplications) }}</div>
                                    <div class="stat-label">Hồ sơ đã nhận</div>
                                </div>
                            </div>

                            <div class="premium-stat-card">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="fa fa-users"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value">{{ number_format($totalCandidates) }}</div>
                                    <div class="stat-label">Ứng viên ứng tuyển</div>
                                </div>
                            </div>

                            @if($isDirector && $pendingJobs > 0)
                            <a href="{{ route('director.approve_jobs') }}" class="premium-stat-card" style="border: 2px dashed var(--fpt-orange);">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                                    <i class="fa fa-check-circle"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value">{{ $pendingJobs }}</div>
                                    <div class="stat-label">Tin chờ duyệt</div>
                                </div>
                            </a>
                            @endif
                        </div>

                        <div class="dashboard-sections-grid">
                            <!-- Recent Applications -->
                            <div class="dashboard-panel">
                                <div class="panel-header">
                                    <h4>Ứng viên mới nhất</h4>
                                    <a href="{{ route('employers.manage_candidates') }}" class="text-orange" style="font-size: 0.85rem; font-weight: 600;">Xem tất cả</a>
                                </div>
                                <div class="recent-activity-list">
                                    @forelse($recentApplications as $app)
                                        <div class="activity-item">
                                            <div class="activity-badge">
                                                <i class="fa fa-user"></i>
                                            </div>
                                            <div class="activity-content">
                                                <div class="activity-text"><strong>{{ $app->candidate->name }}</strong> vừa ứng tuyển vào <strong>{{ $app->job->title }}</strong></div>
                                                <div class="activity-time">{{ $app->created_at->diffForHumans() }} • Nguồn: {{ ucfirst($app->source) }}</div>
                                            </div>
                                            <div class="activity-status">
                                                <span class="badge rounded-pill" style="background: {{ $app->status->getColor() }}20; color: {{ $app->status->getColor() }};">
                                                    {{ $app->status->getLabel() }}
                                                </span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4">
                                            <p class="text-muted">Chưa có ứng viên mới nào.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Dashboard Quick Tips / Info -->
                            <div class="dashboard-panel">
                                <div class="panel-header">
                                    <h4>Thông tin nhanh</h4>
                                </div>
                                <div class="quick-actions-list d-flex flex-column gap-3">
                                    <div class="activity-item">
                                        <div class="activity-badge text-primary"><i class="fa fa-info-circle"></i></div>
                                        <div>
                                            <div class="activity-text"><strong>Mẹo tuyển dụng</strong></div>
                                            <div class="activity-time">Đăng tin kèm mức lương giúp tăng 40% lượng hồ sơ.</div>
                                        </div>
                                    </div>

                                    @if($isDirector)
                                    <div class="alert alert-info py-2 px-3 rounded-4 border-0 mb-0" style="background: #eff6ff; color: #1e40af;">
                                        <small><i class="fa fa-shield"></i> Bạn đang xem với vai trò <strong>Director</strong> của chi nhánh.</small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

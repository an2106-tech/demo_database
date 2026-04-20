<div class="premium-dashboard-container">
    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-lg-3 col-md-4 dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8">
                    <div class="dashboard-right">
                        <!-- Welcome Section -->
                        <div class="welcome-section">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3 style="color: #0f172a; margin-bottom: 8px;">{{ $greeting }}, {{ $userName }}!</h3>
                                    <p style="color: #64748b; margin: 0;">Khám phá những cơ hội nghề nghiệp mới nhất dành riêng cho bạn hôm nay.</p>
                                </div>
                                <div class="col-md-4 text-md-end d-none d-md-block">
                                    <a href="{{ route('candidates.browse_job') }}" class="btn" style="background: var(--fpt-orange); color: white; border-radius: 12px; padding: 12px 24px; font-weight: 700; box-shadow: var(--fpt-orange-glow) 0 4px 12px;">
                                        <i class="fa fa-search"></i> Tìm việc ngay
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="dashboard-stats-grid">
                            <a href="{{ route('candidates.browse_job') }}" class="premium-stat-card">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                    <i class="fa fa-briefcase"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value">{{ number_format($publishedJobsCount) }}</div>
                                    <div class="stat-label">Việc đang mở</div>
                                </div>
                            </a>

                            <a href="{{ route('candidates.manage_jobs') }}" class="premium-stat-card">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="fa fa-paper-plane"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value">{{ number_format($appliedCount) }}</div>
                                    <div class="stat-label">Đã ứng tuyển</div>
                                </div>
                            </a>

                            <div class="premium-stat-card">
                                <div class="stat-icon" style="background: linear-gradient(135deg, var(--fpt-orange) 0%, #f97316 100%);">
                                    <i class="fa fa-tachometer"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value">{{ $profileCompletion }}%</div>
                                    <div class="stat-label">Hoàn thiện hồ sơ</div>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-sections-grid mt-4">
                            <!-- Recent Applications -->
                            <div class="premium-panel">
                                <div class="panel-header">
                                    <h4>Ứng tuyển gần đây</h4>
                                    <a href="{{ route('candidates.manage_jobs') }}" style="color: var(--fpt-orange); font-size: 14px; font-weight: 600;">Xem tất cả</a>
                                </div>
                                
                                <div class="recent-activity-list">
                                    @forelse($recentApplications as $application)
                                    <div class="activity-item">
                                        <div class="activity-badge">
                                            <i class="fa fa-file-text-o"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-text">
                                                Bạn đã ứng tuyển vào vị trí <strong>{{ $application->job->title }}</strong>
                                            </div>
                                            <div class="activity-time">
                                                {{ $application->created_at->diffForHumans() }} 
                                                <span class="mx-2">•</span> 
                                                <span class="badge rounded-pill published" style="font-size: 10px; padding: 2px 8px !important;">
                                                    {{ $application->status->getLabel() }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-4">
                                        <p class="text-muted">Bạn chưa ứng tuyển vị trí nào.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Profile Completion Card -->
                            <div class="premium-panel">
                                <div class="panel-header">
                                    <h4>Thao tác nhanh</h4>
                                </div>
                                <div class="quick-actions-list" style="display: flex; flex-direction: column; gap: 12px;">
                                    <a href="{{ route('candidates.candidate_profile') }}" class="d-flex align-items-center gap-3 p-3" style="background: #f8fafc; border-radius: 12px; text-decoration: none; border: 1px solid #f1f5f9; transition: all 0.3s;">
                                        <div style="width: 40px; height: 40px; background: rgba(243, 112, 33, 0.1); color: var(--fpt-orange); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-user"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: #1e293b; font-size: 14px;">Cập nhật hồ sơ</div>
                                            <div style="font-size: 12px; color: #64748b;">Tăng 20% tỉ lệ matching</div>
                                        </div>
                                    </a>

                                    <div class="p-3 mt-2" style="background: rgba(16, 185, 129, 0.05); border: 1px dashed #10b981; border-radius: 12px; text-align: center;">
                                        <div style="color: #10b981; font-weight: 700; margin-bottom: 8px;">
                                            <i class="fa fa-check-circle"></i> CV đã sẵn sàng!
                                        </div>
                                        <p style="font-size: 12px; color: #34d399; margin: 0;">Hồ sơ của bạn đã được tối ưu hóa cho các nhà tuyển dụng.</p>
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

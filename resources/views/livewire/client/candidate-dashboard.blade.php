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
                                                Bạn đã ứng tuyển vào vị trí <strong>{{ $application->job?->title ?? 'Vị trí không còn khả dụng' }}</strong>
                                            </div>
                                            <div class="activity-time">
                                                {{ optional($application->applied_at ?? $application->created_at)->diffForHumans() }} 
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

                                    @if ($hasCv)
                                    <a href="{{ route('candidates.candidate_profile') }}" class="d-flex align-items-center justify-content-between gap-3 p-3 mt-2" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; text-decoration: none;">
                                        <div>
                                            <div style="color: #047857; font-weight: 700; font-size: 14px;">CV đã sẵn sàng</div>
                                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Xem hồ sơ và đánh giá CV tại đây</div>
                                        </div>
                                        <i class="fa fa-arrow-right" style="color: #047857;" aria-hidden="true"></i>
                                    </a>
                                    @else
                                    <a href="{{ route('candidates.candidate_profile') }}" class="p-3 mt-2 d-block" style="background: rgba(249, 115, 22, 0.06); border: 1px dashed var(--fpt-orange); border-radius: 12px; text-align: center; text-decoration: none;">
                                        <div style="color: var(--fpt-orange); font-weight: 700; margin-bottom: 8px;">
                                            <i class="fa fa-upload"></i> Cần bổ sung CV
                                        </div>
                                        <p style="font-size: 12px; color: #c2410c; margin: 0;">Tải CV lên để hồ sơ đủ điều kiện ứng tuyển.</p>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="premium-panel mt-4 ai-job-matching-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <div style="max-width: 620px;">
                                <h4 style="margin: 0; font-size: 17px; font-weight: 800; color: #0f172a;">Việc làm phù hợp với hồ sơ</h4>
                                <p style="margin: 5px 0 0; font-size: 13px; color: #64748b; line-height: 1.5;">Đối chiếu kỹ năng, kinh nghiệm và mong muốn nghề nghiệp để tìm các vị trí phù hợp nhất.</p>
                            </div>

                            <button wire:click="findMatchingJobsWithAi" class="btn" style="background: #f37021; color: white; font-weight: 700; border-radius: 10px; padding: 9px 16px; border: none; box-shadow: 0 4px 12px rgba(243, 112, 33, 0.2); white-space: nowrap;" wire:loading.attr="disabled" wire:target="findMatchingJobsWithAi">
                                <span wire:loading.remove wire:target="findMatchingJobsWithAi">Tìm việc phù hợp</span>
                                <span wire:loading wire:target="findMatchingJobsWithAi"><i class="fa fa-circle-o-notch fa-spin"></i> Đang tìm...</span>
                            </button>
                        </div>

                        @if(!empty($aiRecommendedJobs))
                            <div class="recommended-jobs-list mt-4">
                                @foreach($aiRecommendedJobs as $recJob)
                                    <div class="job-card mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; position: relative; overflow: hidden;">
                                        <div style="position: absolute; top: 16px; right: 16px; background: {{ $recJob['match_percentage'] >= 80 ? '#d1fae5' : '#fef3c7' }}; color: {{ $recJob['match_percentage'] >= 80 ? '#059669' : '#d97706' }}; padding: 4px 10px; border-radius: 20px; font-weight: 800; font-size: 13px;">
                                            {{ $recJob['match_percentage'] }}% phù hợp
                                        </div>

                                        <h5 style="margin: 0 0 8px; font-size: 16px; font-weight: 700; color: #1e293b; padding-right: 90px;">
                                            <a href="{{ $recJob['public_url'] ?? '#' }}" target="_blank" style="color: inherit; text-decoration: none;">
                                                {{ $recJob['title'] }}
                                            </a>
                                        </h5>
                                        
                                        <div style="background: #fff; border-left: 3px solid #f37021; padding: 10px 14px; margin-top: 12px; border-radius: 4px; font-size: 13.5px; color: #475569; font-style: italic;">
                                            "{{ $recJob['reason'] }}"
                                        </div>

                                        @if(!empty($recJob['matched_requirements']) || !empty($recJob['missing_requirements']))
                                            <div class="row g-2 mt-2">
                                                @if(!empty($recJob['matched_requirements']))
                                                    <div class="col-md-6">
                                                        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 10px 12px; height: 100%;">
                                                            <div style="font-size: 12px; font-weight: 800; color: #047857; margin-bottom: 6px;">
                                                                <i class="fa fa-check-circle me-1"></i> Điểm phù hợp
                                                            </div>
                                                            @foreach($recJob['matched_requirements'] as $requirement)
                                                                <div style="font-size: 12.5px; color: #065f46; margin-top: 3px;">• {{ $requirement }}</div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(!empty($recJob['missing_requirements']))
                                                    <div class="col-md-6">
                                                        <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 10px 12px; height: 100%;">
                                                            <div style="font-size: 12px; font-weight: 800; color: #c2410c; margin-bottom: 6px;">
                                                                <i class="fa fa-exclamation-circle me-1"></i> Cần bổ sung/xác minh
                                                            </div>
                                                            @foreach($recJob['missing_requirements'] as $requirement)
                                                                <div style="font-size: 12.5px; color: #9a3412; margin-top: 3px;">• {{ $requirement }}</div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="mt-3">
                                            <a href="{{ $recJob['public_url'] ?? '#' }}" target="_blank" class="btn btn-sm" style="background: #e2e8f0; color: #475569; font-weight: 600; border-radius: 6px;">
                                                Xem chi tiết <i class="fa fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4" style="background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
                                <div style="font-size: 40px; color: #94a3b8; margin-bottom: 12px;"><i class="fa fa-briefcase"></i></div>
                                <h6 style="color: #64748b; font-weight: 600;">Chưa có gợi ý nào</h6>
                                <p style="font-size: 13px; color: #94a3b8; margin: 0;">Chọn “Tìm việc phù hợp” để nhận đề xuất dựa trên hồ sơ của bạn.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

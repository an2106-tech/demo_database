<div class="premium-dashboard-container">
    <section class="candidate-dashboard-area section_70" style="padding: 24px 0 60px 0; background: #f8fafc;">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right d-flex flex-column gap-4">
                        <!-- Welcome Section (Clean White Bento Hero) -->
                        <div class="p-4 p-lg-5 rounded-4 shadow-sm bg-white" style="border: 1px solid #e2e8f0; position: relative; overflow: hidden;">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                <div>
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(243, 112, 33, 0.1); color: #f37021; font-size: 12px; font-weight: 700;">
                                        <i class="fa fa-briefcase"></i> Cổng thông tin Tuyển dụng FPT
                                    </div>
                                    <h2 class="fw-bold mb-2" style="color: #0f172a; font-size: 26px; letter-spacing: -0.02em;">
                                        {{ $greeting }}, {{ $user->name }}!
                                    </h2>
                                    <p class="mb-0 text-muted" style="font-size: 14px; line-height: 1.6;">
                                        Chúc bạn một ngày làm việc hiệu quả và tuyển dụng thành công các nhân tài xuất sắc.
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('employers.post_job') }}" class="btn px-4 py-2 text-white fw-bold d-inline-flex align-items-center gap-2 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #f37021 0%, #ea580c 100%); border: none; font-size: 13.5px; transition: all 0.2s ease;">
                                        <i class="fa fa-plus-circle"></i> Đăng tin mới
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid (Bento Cards matching Candidate Dashboard) -->
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-xl-{{ ($isDirector && $pendingJobs > 0) ? '3' : '4' }}">
                                <div class="p-3 p-lg-4 rounded-4 bg-white shadow-sm h-100 d-flex align-items-center justify-content-between" style="border: 1px solid #e2e8f0; transition: transform 0.2s ease;">
                                    <div>
                                        <div class="text-muted fw-semibold mb-1" style="font-size: 12.5px;">Việc làm đã đăng</div>
                                        <div class="fw-bold" style="font-size: 28px; color: #0f172a; line-height: 1.2;">{{ number_format($totalJobs) }}</div>
                                        <span class="badge rounded-pill mt-2" style="background: #e0f2fe; color: #0284c7; font-size: 11px;">Đang tuyển dụng</span>
                                    </div>
                                    <div style="width: 48px; height: 48px; border-radius: 14px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                        <i class="fa fa-briefcase"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl-{{ ($isDirector && $pendingJobs > 0) ? '3' : '4' }}">
                                <div class="p-3 p-lg-4 rounded-4 bg-white shadow-sm h-100 d-flex align-items-center justify-content-between" style="border: 1px solid #e2e8f0; transition: transform 0.2s ease;">
                                    <div>
                                        <div class="text-muted fw-semibold mb-1" style="font-size: 12.5px;">Hồ sơ đã nhận</div>
                                        <div class="fw-bold" style="font-size: 28px; color: #0f172a; line-height: 1.2;">{{ number_format($totalApplications) }}</div>
                                        <span class="badge rounded-pill mt-2" style="background: #ede9fe; color: #7c3aed; font-size: 11px;">Tổng lượt nộp</span>
                                    </div>
                                    <div style="width: 48px; height: 48px; border-radius: 14px; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                        <i class="fa fa-file-text-o"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl-{{ ($isDirector && $pendingJobs > 0) ? '3' : '4' }}">
                                <div class="p-3 p-lg-4 rounded-4 bg-white shadow-sm h-100 d-flex align-items-center justify-content-between" style="border: 1px solid #e2e8f0; transition: transform 0.2s ease;">
                                    <div>
                                        <div class="text-muted fw-semibold mb-1" style="font-size: 12.5px;">Ứng viên ứng tuyển</div>
                                        <div class="fw-bold" style="font-size: 28px; color: #0f172a; line-height: 1.2;">{{ number_format($totalCandidates) }}</div>
                                        <span class="badge rounded-pill mt-2" style="background: #ecfdf5; color: #059669; font-size: 11px;">Ứng viên active</span>
                                    </div>
                                    <div style="width: 48px; height: 48px; border-radius: 14px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                        <i class="fa fa-users"></i>
                                    </div>
                                </div>
                            </div>

                            @if($isDirector && $pendingJobs > 0)
                            <div class="col-12 col-sm-6 col-xl-3">
                                <a href="{{ route('director.approve_jobs') }}" class="p-3 p-lg-4 rounded-4 bg-white shadow-sm h-100 d-flex align-items-center justify-content-between text-decoration-none" style="border: 1px solid #fed7aa; transition: transform 0.2s ease;">
                                    <div>
                                        <div class="text-muted fw-semibold mb-1" style="font-size: 12.5px;">Tin chờ duyệt</div>
                                        <div class="fw-bold" style="font-size: 28px; color: #ea580c; line-height: 1.2;">{{ $pendingJobs }}</div>
                                        <span class="badge rounded-pill mt-2" style="background: #fff7ed; color: #ea580c; font-size: 11px;">Cần phê duyệt</span>
                                    </div>
                                    <div style="width: 48px; height: 48px; border-radius: 14px; background: #fff7ed; color: #ea580c; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                </a>
                            </div>
                            @endif
                        </div>

                        <!-- 2 Columns Bento Layout -->
                        <div class="row g-4">
                            <!-- Recent Applications Column -->
                            <div class="col-lg-8">
                                <div class="p-4 rounded-4 bg-white shadow-sm h-100" style="border: 1px solid #e2e8f0;">
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                        <h4 class="fw-bold mb-0" style="font-size: 16px; color: #0f172a;">
                                            <i class="fa fa-clock-o text-primary me-2"></i> Ứng tuyển gần đây
                                        </h4>
                                        <a href="{{ route('employers.manage_candidates') }}" class="fw-bold" style="font-size: 13px; color: #f37021; text-decoration: none;">
                                            Xem tất cả <i class="fa fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>

                                    <div class="d-flex flex-column gap-3">
                                        @forelse($recentApplications as $app)
                                            <div class="p-3 rounded-3 d-flex align-items-center justify-content-between" style="background: #f8fafc; border: 1px solid #f1f5f9;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                                                        <i class="fa fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <div style="font-size: 13.5px; color: #0f172a;">
                                                            <strong>{{ $app->snapshotCandidateName() }}</strong> vừa ứng tuyển vào <strong>{{ $app->job?->title ?? 'Vị trí đã bị xoá' }}</strong>
                                                        </div>
                                                        <div class="text-muted" style="font-size: 12px; margin-top: 2px;">
                                                            {{ optional($app->applied_at ?? $app->created_at)->diffForHumans() }} • Nguồn: {{ ucfirst((string) $app->source) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <span class="badge rounded-pill px-3 py-2" style="background: {{ $app->status->getColor() }}15; color: {{ $app->status->getColor() }}; font-size: 11px; font-weight: 700;">
                                                        {{ $app->status->getLabel() }}
                                                    </span>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted">
                                                <i class="fa fa-inbox" style="font-size: 32px; color: #cbd5e1; margin-bottom: 8px;"></i>
                                                <p class="m-0" style="font-size: 13px;">Chưa có hồ sơ ứng tuyển mới nào.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions / Tips Column -->
                            <div class="col-lg-4">
                                <div class="p-4 rounded-4 bg-white shadow-sm h-100 d-flex flex-column gap-3" style="border: 1px solid #e2e8f0;">
                                    <div class="pb-2 border-bottom">
                                        <h4 class="fw-bold mb-0" style="font-size: 16px; color: #0f172a;">
                                            <i class="fa fa-compass text-primary me-2"></i> Thao tác nhanh
                                        </h4>
                                    </div>

                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('employers.manage_candidates') }}" class="p-3 rounded-3 d-flex align-items-center justify-content-between text-decoration-none" style="background: #f8fafc; border: 1px solid #f1f5f9; color: #0f172a; transition: all 0.2s ease;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width: 34px; height: 34px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                                    <i class="fa fa-users"></i>
                                                </div>
                                                <div>
                                                    <div style="font-size: 13px; font-weight: 700;">Quản lý ứng viên</div>
                                                    <div style="font-size: 11.5px; color: #64748b;">Xem hồ sơ & lọc theo trạng thái</div>
                                                </div>
                                            </div>
                                            <i class="fa fa-angle-right text-muted"></i>
                                        </a>

                                        <a href="{{ route('employers.application_pipeline') }}" class="p-3 rounded-3 d-flex align-items-center justify-content-between text-decoration-none" style="background: #f8fafc; border: 1px solid #f1f5f9; color: #0f172a; transition: all 0.2s ease;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width: 34px; height: 34px; border-radius: 8px; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                                    <i class="fa fa-sitemap"></i>
                                                </div>
                                                <div>
                                                    <div style="font-size: 13px; font-weight: 700;">Pipeline tuyển dụng</div>
                                                    <div style="font-size: 11.5px; color: #64748b;">Kanban quy trình tuyển dụng</div>
                                                </div>
                                            </div>
                                            <i class="fa fa-angle-right text-muted"></i>
                                        </a>

                                        <div class="p-3 rounded-3" style="background: #fffbeb; border: 1px solid #fef3c7;">
                                            <div class="d-flex align-items-center gap-2 mb-1" style="font-size: 12.5px; font-weight: 700; color: #b45309;">
                                                <i class="fa fa-lightbulb-o"></i> Mẹo tuyển dụng
                                            </div>
                                            <p class="m-0" style="font-size: 11.5px; color: #92400e; line-height: 1.5;">
                                                Đăng tin kèm dải lương cụ thể giúp tăng đến 40% lượng ứng viên nộp hồ sơ chất lượng.
                                            </p>
                                        </div>

                                        @if($isDirector)
                                            <div class="p-3 rounded-3" style="background: #eff6ff; border: 1px solid #dbeafe;">
                                                <div style="font-size: 11.5px; color: #1e40af;">
                                                    <i class="fa fa-shield me-1"></i> Bạn đang điều hành với vai trò <strong>Director</strong> của chi nhánh.
                                                </div>
                                            </div>
                                        @endif
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

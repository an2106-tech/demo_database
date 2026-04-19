<div>
    <div class="employer-page-head">
        <h1>Bảng Điều Khiển</h1>
        <p>Tổng quan nhanh hiệu suất tuyển dụng và các thống kê quan trọng.</p>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right">
                        <div class="welcome-dashboard">
                            <h3>Chào Mừng Trở Lại, <span>{{ $user->name }}</span>!</h3>
                        </div>

                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex widget_bg_blue">
                                    <div class="widget-icon">
                                        <i class="fa fa-briefcase"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>{{ $totalJobs }}</h4>
                                        <h2>Việc Làm Đã Đăng</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex widget_bg_purple">
                                    <div class="widget-icon">
                                        <i class="fa fa-file-text-o"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>{{ $totalApplications }}</h4>
                                        <h2>Hồ Sơ Đã Nhận</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex widget_bg_dark_red">
                                    <div class="widget-icon">
                                        <i class="fa fa-users"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>{{ $totalCandidates }}</h4>
                                        <h2>Ứng Viên Ứng Tuyển</h2>
                                    </div>
                                </div>
                            </div>
                            @if($isDirector && $pendingJobs > 0)
                            <div class="col-lg-4 col-md-6">
                                <a href="{{ route('director.approve_jobs') }}" class="widget_card_page grid_flex widget_bg_orange" style="text-decoration:none;">
                                    <div class="widget-icon">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>{{ $pendingJobs }}</h4>
                                        <h2>Tin Chờ Duyệt</h2>
                                    </div>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

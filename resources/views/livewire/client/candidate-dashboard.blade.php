<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Bảng điều khiển</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="candidate-dashboard-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4 dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8">
                    <div class="dashboard-right">
                        <div class="welcome-dashboard">
                            <h3>Chào mừng trở lại, {{ $userName }}!</h3>
                        </div>

                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex widget_bg_blue">
                                    <div class="widget-icon">
                                        <i class="fa fa-briefcase"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>{{ number_format($publishedJobsCount) }}</h4>
                                        <h2>Việc đang mở</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex widget_bg_purple">
                                    <div class="widget-icon">
                                        <i class="fa fa-file-text-o"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>{{ $hasCv ? 'Đã có' : 'Chưa có' }}</h4>
                                        <h2>CV</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex widget_bg_dark_red">
                                    <div class="widget-icon">
                                        <i class="fa fa-check-square-o"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>{{ number_format($appliedCount) }}</h4>
                                        <h2>Đã ứng tuyển</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (! $hasCv)
                            <div class="alert alert-warning mt-3">
                                Bạn chưa tải CV. Vui lòng cập nhật tại
                                <a href="{{ route('candidates.candidate_profile') }}">Hồ sơ của tôi</a>.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


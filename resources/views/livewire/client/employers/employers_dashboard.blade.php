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
        <div class="breadcromb-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box-pagin">
                            <ul>
                                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                                <li><a href="{{ route('employers.browse') }}">Nhà tuyển dụng</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.dashboard') }}">Bảng điều khiển</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="candidate-dashboard-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>
                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right">
                        <div class="welcome-dashboard">
                            <h3>Chào mừng trở lại, <span>Arino inc !</span></h3>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex widget_bg_blue">
                                    <div class="widget-icon">
                                        <i class="fa fa-gavel"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>1426</h4>
                                        <h2>Lượt đấu thầu</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex  widget_bg_purple">
                                    <div class="widget-icon">
                                        <i class="fa fa-usd"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>$12,000</h4>
                                        <h2> Thanh toán</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex widget_bg_dark_red">
                                    <div class="widget-icon">
                                        <i class="fa fa-users"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>127</h4>
                                        <h2>Ứng viên</h2>
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

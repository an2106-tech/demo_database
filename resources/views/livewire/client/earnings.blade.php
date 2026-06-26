<div>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('candidates.candidate_dashboard') }}">Ứng viên</a></li>
            <li class="active">Thu nhập</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right">
                        <div class="earnings-page-box manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>Thu nhập ước tính</h3>
                            </div>

                            <div class="earnings-flex">
                                <div class="single-earnings">
                                    <div class="earnings-icon">
                                        <i class="fa fa-line-chart"></i>
                                    </div>
                                    <h4>Hôm nay</h4>
                                    <h2>$14.00</h2>
                                </div>
                                <div class="single-earnings">
                                    <div class="earnings-icon">
                                        <i class="fa fa-line-chart"></i>
                                    </div>
                                    <h4>7 ngày qua</h4>
                                    <h2>$210.30</h2>
                                    <p>+$0.00</p>
                                    <p>so với cùng kỳ tuần trước</p>
                                </div>
                                <div class="single-earnings">
                                    <div class="earnings-icon">
                                        <i class="fa fa-line-chart"></i>
                                    </div>
                                    <h4>28 ngày qua</h4>
                                    <h2>$2293.80</h2>
                                    <p>+$0.00</p>
                                    <p>so với 28 ngày trước đó</p>
                                </div>
                            </div>

                            <div class="balance-box-flex">
                                <div class="my-balance single-balance-box">
                                    <div class="widget_chart_analytics_right">
                                        <p>Tháng 1 2018</p>
                                        <p>+ 42.6%</p>
                                    </div>
                                    <h3>Số dư</h3>
                                    <h2>$1856.00</h2>
                                    <p>lần thanh toán cuối</p>
                                    <p>$122.55</p>
                                </div>
                                <div class="transfer-balance single-balance-box">
                                    <h3>Tài khoản thanh toán <span><a href="#">thiết lập mới</a></span></h3>
                                    <img src="{{ asset('assets/img/payoneer.jpg') }}" alt="payoneer" />
                                </div>
                            </div>

                            <div class="balance-transfer-btn">
                                <a href="#" class="jobguru-btn-2">Chuyển tiền nhanh</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

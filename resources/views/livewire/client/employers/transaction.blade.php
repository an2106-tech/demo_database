<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Giao dịch</h3>
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
                                <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.transaction') }}">Giao dịch</a></li>
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
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>
                
                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right">
                        <div class="manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>Lịch sử giao dịch</h3>
                            </div>
                            <div class="single-manage-jobs table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Gói dịch vụ</th>
                                            <th>Ngày thanh toán</th>
                                            <th>Phương thức thanh toán</th>
                                            <th>Số tiền</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Frontend React Developer</a></td>
                                            <td class="table-date">28 Tháng 6, 2018</td>
                                            <td>Chuyển khoản ngân hàng</td>
                                            <td>$197.00</td>
                                            <td><span class="pending">Đã duyệt</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Node.js Developer</a></td>
                                            <td class="table-date">12 Tháng 6, 2018</td>
                                            <td>Paypal</td>
                                            <td>$210.50</td>
                                            <td><span class="pending">Đã duyệt</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Full Stack PHP Developer </a></td>
                                            <td class="table-date">29 Tháng 5, 2018</td>
                                            <td>Chuyển khoản ngân hàng</td>
                                            <td>$122.00</td>
                                            <td><span class="expired">Chờ phê duyệt</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Frontend React Developer</a></td>
                                            <td class="table-date">14 Tháng 5, 2018</td>
                                            <td>Chuyển khoản ngân hàng</td>
                                            <td>$197.00</td>
                                            <td><span class="pending">Đã duyệt</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Full Stack PHP Developer </a></td>
                                            <td class="table-date">29 Tháng 5, 2018</td>
                                            <td>Payoneer</td>
                                            <td>$122.00</td>
                                            <td><span class="expired">Chờ phê duyệt</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Frontend React Developer</a></td>
                                            <td class="table-date">28 Tháng 6, 2018</td>
                                            <td>Chuyển khoản ngân hàng</td>
                                            <td>$197.00</td>
                                            <td><span class="pending">Đã duyệt</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Full Stack PHP Developer </a></td>
                                            <td class="table-date">29 Tháng 5, 2018</td>
                                            <td>Swift</td>
                                            <td>$122.00</td>
                                            <td><span class="expired">Chờ phê duyệt</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <div class="pagination-box-row">
                                    <p>Trang 1 trên 6</p>
                                    <ul class="pagination">
                                        <li class="active"><a href="#">1</a></li>
                                        <li><a href="#">2</a></li>
                                        <li><a href="#">3</a></li>
                                        <li>...</li>
                                        <li><a href="#">6</a></li>
                                        <li><a href="#"><i class="fa fa-angle-double-right"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

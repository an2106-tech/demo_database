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
                                <li><a href="{{ route('employers.dashboard') }}">Trang chủ</a></li>
                                <li><a href="#">Ứng viên</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.manage_jobs') }}">Quản lý công việc</a></li>
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
                    <div class="dashboard-left">
                        <ul class="dashboard-menu">
                            <li>
                                <a href="{{ route('employers.dashboard') }}">
                                    <i class="fa fa-tachometer"></i>
                                    Bảng điều khiển
                                </a>
                            </li>
                            <li><a href="{{ route('employers.company_profile') }}"><i class="fa fa-users"></i>Hồ sơ của tôi</a></li>
                            <li><a href="{{ route('employers.message') }}"><i class="fa fa-envelope-open"></i>Tin nhắn</a></li>
                            <li class="active">
                                <a href="{{ route('employers.manage_jobs') }}">
                                    <i class="fa fa-briefcase"></i>
                                    Quản lý công việc
                                </a>
                            </li>
                            <li><a href="{{ route('employers.candidate_earnings') }}"><i class="fa fa-rocket"></i>Thu nhập</a></li>
                            <li><a href="{{ route('employers.change_password') }}"><i class="fa fa-lock"></i>Đổi mật khẩu</a></li>
                            <li><a href="#"><i class="fa fa-power-off"></i>Đăng xuất</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right ">
                        <div class="manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>Danh sách công việc của tôi</h3>
                            </div>
                            <div class="single-manage-jobs table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Tiêu đề</th>
                                            <th>Ngày đăng</th>
                                            <th>Ngày hết hạn</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Frontend React Developer</a></td>
                                            <td class="table-date">28 Tháng 6, 2018</td>
                                            <td class="table-date">10 Tháng 7, 2018</td>
                                            <td><span class="pending">Đang chờ duyệt</span></td>
                                            <td class="action">
                                                <a href="#" class="action-edit" title="Sửa"><i
                                                        class="fa fa-pencil-square-o"></i></a>
                                                <a href="#" class="action-delete" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Full Stack PHP Developer </a></td>
                                            <td class="table-date">28 Tháng 6, 2018</td>
                                            <td class="table-date">10 Tháng 7, 2018</td>
                                            <td><span class="expired">Hết hạn</span></td>
                                            <td class="action">
                                                <a href="#" class="action-edit" title="Sửa"><i
                                                        class="fa fa-pencil-square-o"></i></a>
                                                <a href="#" class="action-delete" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Node.js Developer</a></td>
                                            <td class="table-date">28 Tháng 6, 2018</td>
                                            <td class="table-date">10 Tháng 7, 2018</td>
                                            <td><span class="pending">Đang chờ duyệt</span></td>
                                            <td class="action">
                                                <a href="#" class="action-edit" title="Sửa"><i
                                                        class="fa fa-pencil-square-o"></i></a>
                                                <a href="#" class="action-delete" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Frontend React Developer</a></td>
                                            <td class="table-date">28 Tháng 6, 2018</td>
                                            <td class="table-date">10 Tháng 7, 2018</td>
                                            <td><span class="pending">Đang chờ duyệt</span></td>
                                            <td class="action">
                                                <a href="#" class="action-edit" title="Sửa"><i
                                                        class="fa fa-pencil-square-o"></i></a>
                                                <a href="#" class="action-delete" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Full Stack PHP Developer </a></td>
                                            <td class="table-date">28 Tháng 6, 2018</td>
                                            <td class="table-date">10 Tháng 7, 2018</td>
                                            <td><span class="expired">Hết hạn</span></td>
                                            <td class="action">
                                                <a href="#" class="action-edit" title="Sửa"><i
                                                        class="fa fa-pencil-square-o"></i></a>
                                                <a href="#" class="action-delete" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Node.js Developer</a></td>
                                            <td class="table-date">28 Tháng 6, 2018</td>
                                            <td class="table-date">10 Tháng 7, 2018</td>
                                            <td><span class="pending">Đang chờ duyệt</span></td>
                                            <td class="action">
                                                <a href="#" class="action-edit" title="Sửa"><i
                                                        class="fa fa-pencil-square-o"></i></a>
                                                <a href="#" class="action-delete" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Frontend React Developer</a></td>
                                            <td class="table-date">28 Tháng 6, 2018</td>
                                            <td class="table-date">10 Tháng 7, 2018</td>
                                            <td><span class="pending">Đang chờ duyệt</span></td>
                                            <td class="action">
                                                <a href="#" class="action-edit" title="Sửa"><i
                                                        class="fa fa-pencil-square-o"></i></a>
                                                <a href="#" class="action-delete" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Full Stack PHP Developer </a></td>
                                            <td class="table-date">28 Tháng 6, 2018</td>
                                            <td class="table-date">10 Tháng 7, 2018</td>
                                            <td><span class="expired">Hết hạn</span></td>
                                            <td class="action">
                                                <a href="#" class="action-edit" title="Sửa"><i
                                                        class="fa fa-pencil-square-o"></i></a>
                                                <a href="#" class="action-delete" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Node.js Developer</a></td>
                                            <td class="table-date">28 Tháng 6, 2018</td>
                                            <td class="table-date">10 Tháng 7, 2018</td>
                                            <td><span class="pending">Đang chờ duyệt</span></td>
                                            <td class="action">
                                                <a href="#" class="action-edit" title="Sửa"><i
                                                        class="fa fa-pencil-square-o"></i></a>
                                                <a href="#" class="action-delete" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                            </td>
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
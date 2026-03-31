<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Đăng tin tuyển dụng</h3>
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
                                <li><a href="{{ route('home') }}">home</a></li>
                                <li><a href="{{ route('employers.dashboard') }}">Employeer</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.post_job') }}">Post A job</a></li>
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
                <div class="col-md-4 col-lg-3  mx-auto dashboard-left-border">
                    <div class="dashboard-left">
                        <ul class="dashboard-menu">
                            <li>
                                <a href="{{ route('employers.dashboard') }}">
                                    <i class="fa fa-tachometer"></i>
                                    Bảng điều khiển
                                </a>
                            </li>
                            <li><a href="{{ route('employers.candidate_profile') }}"><i class="fa fa-users"></i>My Profile</a></li>
                            <li><a href="{{ route('employers.message') }}"><i class="fa fa-envelope-open"></i>messages</a></li>
                            <li><a href="{{ route('employers.manage_jobs') }}"><i class="fa fa-briefcase"></i>manage jobs</a></li>
                            <li class="active"><a href="{{ route('employers.candidate_earnings') }}"><i class="fa fa-rocket"></i>earnings</a></li>
                            <li><a href="{{ route('employers.change_password') }}"><i class="fa fa-lock"></i>change password</a></li>
                            <li><a href="#"><i class="fa fa-power-off"></i>LogOut</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-8 col-lg-9 mx-auto">
                    <div class="dashboard-right">
                        <div class="earnings-page-box manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>Đăng một công việc mới</h3>
                            </div>
                            <div class="new-job-submission">
                                <form>
                                    <div class="resume-box">
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="j_title">Tiêu đề công việc:</label>
                                                <input type="text" id="j_title">
                                            </div>
                                            <div class="single-input">
                                                <label for="Location">Địa điểm:</label>
                                                <input type="text" placeholder="Ví dụ: Hà Nội" id="Location">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="j_reg">Khu vực:</label>
                                                <select id="j_reg">
                                                    <option value=''>Chọn khu vực</option>
                                                    <option value="1">Los Angeles</option>
                                                    <option value="2">Miami</option>
                                                    <option value="3">New York</option>
                                                    <option value="4">San Francisco</option>
                                                </select>
                                            </div>
                                            <div class="single-input">
                                                <label for="j_type">Loại hình công việc:</label>
                                                <select id="j_type">
                                                    <option value=''>Chọn loại hình</option>
                                                    <option value="1">Toàn thời gian</option>
                                                    <option value="2">Tự do (Freelance)</option>
                                                    <option value="3">Bán thời gian</option>
                                                    <option value="4">Thực tập</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="j_category">Danh mục công việc:</label>
                                                <select id="j_category">
                                                    <option value="122">Kế toán / Tài chính</option>
                                                    <option value="124">Ô tô</option>
                                                    <option value="132">Xây dựng / Cơ sở hạ tầng</option>
                                                    <option value="137">Thiết kế, Nghệ thuật & Đa phương tiện</option>
                                                    <option value="172">Adobe Photoshop</option>
                                                    <option value="173">Hoạt ảnh (Animation)</option>
                                                    <option value="145">Thiết kế đồ họa</option>
                                                    <option value="147">Minh họa (Illustration)</option>
                                                    <option value="150">Thiết kế Logo</option>
                                                    <option value="168">Video</option>
                                                    <option value="140">Giáo dục / Đào tạo</option>
                                                    <option value="146">Y tế</option>
                                                    <option value="157">Nhà hàng / Dịch vụ ăn uống</option>
                                                    <option value="159">Bán hàng / Tiếp thị</option>
                                                    <option value="175">Quảng cáo hiển thị</option>
                                                    <option value="176">Email Marketing</option>
                                                    <option value="177">Tìm kiếm khách hàng tiềm năng</option>
                                                    <option value="179">Chiến lược Marketing</option>
                                                    <option value="180">Quan hệ công chúng (PR)</option>
                                                    <option value="165">Viễn thông</option>
                                                    <option value="167">Vận tải / Logistics</option>
                                                </select>
                                            </div>
                                            <div class="single-input">
                                                <label for="External">Liên kết ứng tuyển bên ngoài :
                                                    <span>(tùy chọn)</span></label>
                                                <input type="text" placeholder="http://" id="External">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="mn_salary">Mức lương tối thiểu ($):</label>
                                                <input type="text" placeholder="Ví dụ: 20000" id="mn_salary">
                                            </div>
                                            <div class="single-input">
                                                <label for="mx_salary">Mức lương tối đa ($):</label>
                                                <input type="text" placeholder="Ví dụ: 50000" id="mx_salary">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild">
                                            <div class="single-input">
                                                <label for="j_desc">Mô tả công việc:</label>
                                                <textarea id="j_desc"></textarea>
                                            </div>
                                        </div>
                                        <div class="single-resume-feild upload_file">
                                            <div class="product-upload">
                                                <p>
                                                    <i class="fa fa-upload"></i>
                                                    Tải tệp lên
                                                </p>
                                                <input type="file" id="w_screen">
                                            </div>
                                            <p>Hình ảnh hoặc tài liệu có thể hữu ích trong việc mô tả công việc của bạn</p>
                                        </div>
                                    </div>
                                    <div class="single-input submit-resume">
                                        <button type="submit">Đăng tin</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

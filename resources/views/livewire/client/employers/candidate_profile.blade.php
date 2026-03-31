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
                                <li><a href="{{ route('home') }}">home</a></li>
                                <li><a href="{{ route('candidates.browse_job') }}">candidates</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.candidate_profile') }}">Dashboard</a></li>
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
                <div class="col-lg-3 col-md-4 dashboard-left-border">
                    <div class="dashboard-left">
                        <ul class="dashboard-menu">
                            <li>
                                <a href="candidate-dashboard.html">
                                    <i class="fa fa-tachometer"></i>
                                    Bảng điều khiển
                                </a>
                            </li>
                            <li class="active">
                                <a href="candidate-profile.html">
                                    <i class="fa fa-users"></i>
                                    Hồ sơ của tôi
                                </a>
                            </li>
                            <li><a href="message.html"><i class="fa fa-envelope-open"></i>tin nhắn</a></li>
                            <li><a href="manage-jobs.html"><i class="fa fa-briefcase"></i>quản lý công việc</a></li>
                            <li><a href="candidate-earnings.html"><i class="fa fa-rocket"></i>thu nhập</a></li>
                            <li><a href="change-password.html"><i class="fa fa-lock"></i>đổi mật khẩu</a></li>
                            <li><a href="#"><i class="fa fa-power-off"></i>Đăng xuất</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="dashboard-right">
                        <div class="candidate-profile">
                            <div class="candidate-single-profile-info">
                                <div class="single-resume-feild resume-avatar">
                                    <div class="resume-image">
                                        <img src="{{ asset('assets/img/author.jpg') }}" alt="resume avatar">
                                        <div class="resume-avatar-hover">
                                            <div class="resume-avatar-upload">
                                                <p>
                                                    <i class="fa fa-pencil"></i>
                                                    Sửa
                                                </p>
                                                <input type="file">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="candidate-single-profile-info">
                                <form>
                                    <div class="resume-box">
                                        <h3>Hồ sơ của tôi</h3>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="name">Họ và Tên:</label>
                                                <input type="text" value="Jennie Wilson" id="name">
                                            </div>
                                            <div class="single-input">
                                                <label for="p_title">Tiêu đề nghề nghiệp:</label>
                                                <input type="text" value="Web Developer" id="p_title">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Region">Ngôn ngữ:</label>
                                                <select id="Region">
                                                    <option selected>Tiếng Anh</option>
                                                    <option>Tiếng Pháp</option>
                                                    <option>Tiếng Bangla</option>
                                                </select>
                                            </div>
                                            <div class="single-input">
                                                <label for="Age">Tuổi:</label>
                                                <input type="text" value="25" id="Age">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Salary">Mức lương hiện tại($) :</label>
                                                <input type="text" value="$1200" id="Salary">
                                            </div>
                                            <div class="single-input">
                                                <label for="Expected"> Mức lương mong muốn:</label>
                                                <input type="text" value="$2000" id="Expected">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild ">
                                            <div class="single-input">
                                                <label for="Bio">Giới thiệu bản thân:</label>
                                                <textarea id="Bio">Maecenas chỉ đơn giản là văn bản giả của ngành in ấn và sắp chữ. Lorem Ipsum đã là văn bản giả tiêu chuẩn của ngành kể từ những năm 1500, khi một người thợ in không xác định lấy một bộ chữ và xáo trộn nó để tạo thành một cuốn sách mẫu chữ.</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="resume-box">
                                        <h3>Thông tin liên hệ</h3>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Phone">Điện thoại:</label>
                                                <input type="text" value="+88-123-4467-9" id="Phone">
                                            </div>
                                            <div class="single-input">
                                                <label for="Email">Email:</label>
                                                <input type="text" value="demo@mail.com" id="Email">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="contry">Quốc gia:</label>
                                                <select id="contry">
                                                    <option>Các Tiểu vương quốc Ả Rập Thống nhất</option>
                                                    <option>Mỹ</option>
                                                    <option>Hà Lan</option>
                                                    <option>Nga</option>
                                                    <option selected>Bangladesh</option>
                                                    <option>Ấn Độ</option>
                                                    <option>Pakistan</option>
                                                    <option>Brazil</option>
                                                    <option>Châu Phi</option>
                                                </select>
                                            </div>
                                            <div class="single-input">
                                                <label for="City">Thành phố:</label>
                                                <input type="text" value="Dhaka" id="City">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Zip">Mã bưu điện (Zip):</label>
                                                <input type="text" value="6100" id="Zip">
                                            </div>
                                            <div class="single-input">
                                                <label for="Address22">Địa chỉ:</label>
                                                <input type="text" value="New york city,22/A Street,01" id="Address22">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="resume-box">
                                        <h3>Liên kết mạng xã hội</h3>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="twitter">
                                                    <i class="fa fa-twitter twitter"></i>
                                                    twitter
                                                </label>
                                                <input type="text" value="https://www.twitter.com/" id="twitter"
                                                    name="twitter">
                                            </div>
                                            <div class="single-input">
                                                <label for="facebook">
                                                    <i class="fa fa-facebook facebook"></i>
                                                    facebook
                                                </label>
                                                <input type="text" value="https://www.facebook.com/" id="facebook"
                                                    name="facebook">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="google">
                                                    <i class="fa fa-google-plus google"></i>
                                                    Google
                                                </label>
                                                <input type="text" value="https://www.google.com/" id="google"
                                                    name="google">
                                            </div>
                                            <div class="single-input">
                                                <label for="linkedin">
                                                    <i class="fa fa-linkedin linkedin"></i>
                                                    linkedin
                                                </label>
                                                <input type="text" value="https://www.linkedin.com/" id="linkedin"
                                                    name="linkedin">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="submit-resume">
                                        <button type="submit">Cập nhật</button>
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
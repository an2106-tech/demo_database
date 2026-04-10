<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Hồ sơ công ty</h3>
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
                                <li class="active-breadcromb"><a href="{{ route('employers.company_profile') }}">Hồ sơ công ty</a></li>
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
                        <div class="candidate-profile">
                            <div class="candidate-single-profile-info">
                                <div class="single-resume-feild resume-avatar">
                                    <div class="resume-image company-resume-image">
                                        <img src="{{ asset('assets/img/company_page_logo.jpg') }}" alt="resume avatar">
                                        <div class="resume-avatar-hover">
                                            <div class="resume-avatar-upload">
                                                <p>
                                                    <i class="fa fa-pencil"></i>
                                                    Chỉnh sửa
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
                                        <h3>Thông tin công ty</h3>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="name">Tên công ty:</label>
                                                <input type="text" value="Jennie Wilson" id="name">
                                            </div>
                                            <div class="single-input">
                                                <label for="c_cat">Lĩnh vực hoạt động:</label>
                                                <select id="c_cat">
                                                    <option selected>Chọn danh mục</option>
                                                    <option>Dịch vụ CNTT</option>
                                                    <option>Phi lợi nhuận</option>
                                                    <option>Khởi nghiệp (StartUP)</option>
                                                    <option>Tập đoàn</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Start">Năm thành lập:</label>
                                                <input type="text" value="1990" id="Start">
                                            </div>
                                            <div class="single-input">
                                                <label for="member">Số lượng nhân sự:</label>
                                                <input type="text" value="132" id="member">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Location">Quốc gia:</label>
                                                <input type="text" value="London" id="Location">
                                            </div>
                                            <div class="single-input">
                                                <label for="City">Thành phố:</label>
                                                <input type="text" value="Westminster" id="City">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild ">
                                            <div class="single-input">
                                                <label for="Bio">Mô tả chi tiết:</label>
                                                <textarea
                                                    id="Bio">Maecenas chỉ đơn giản là một đoạn văn bản giả được sử dụng trong việc sắp xếp và trình bày các bản in. Lorem Ipsum đã là văn bản giả tiêu chuẩn của ngành kể từ những năm 1500, khi một thợ in không xác định lấy một bộ các ký tự và sắp xếp chúng để tạo ra một cuốn sách mẫu văn bản.</textarea>
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
                                                    <option>Các tiểu vương quốc Ả Rập Thống nhất</option>
                                                    <option>Mỹ</option>
                                                    <option>Hà Lan</option>
                                                    <option>Nga</option>
                                                    <option selected>Bangladesh</option>
                                                    <option>Ấn Độ</option>
                                                    <option>Pakistan</option>
                                                    <option>Brazil</option>
                                                    <option>Châu Phi</option>
                                                    <option>Việt Nam</option>
                                                </select>
                                            </div>
                                            <div class="single-input">
                                                <label for="City2">Thành phố:</label>
                                                <input type="text" value="Dhaka" id="City2">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Zip">Mã bưu điện (Zip):</label>
                                                <input type="text" value="6100" id="Zip">
                                            </div>
                                            <div class="single-input">
                                                <label for="Address22">Địa chỉ:</label>
                                                <input type="text" value="Thành phố New York, Phố 22/A, 01" id="Address22">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="resume-box">
                                        <h3>Liên kết mạng xã hội</h3>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="twitter">
                                                    <i class="fa fa-twitter twitter"></i>
                                                    Twitter
                                                </label>
                                                <input type="text" value="https://www.twitter.com/" id="twitter"
                                                    name="twitter">
                                            </div>
                                            <div class="single-input">
                                                <label for="twitter">
                                                    <i class="fa fa-facebook facebook"></i>
                                                    Facebook
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
                                                    name="twitter">
                                            </div>
                                            <div class="single-input">
                                                <label for="linkedin">
                                                    <i class="fa fa-linkedin linkedin"></i>
                                                    Linkedin
                                                </label>
                                                <input type="text" value="https://www.linkedin.com/" id="linkedin"
                                                    name="twitter">
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
    <!-- Candidate Dashboard Area End -->
</div>

<div>
    <footer class="jobguru-footer-area client-app-footer {{ ($isEmployerFooter ?? false) ? 'footer--employer' : 'footer--candidate' }}">
        <div class="footer-top section_50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="single-footer-widget">
                            <div class="footer-logo">
                                <a href="{{ route('home') }}">
                                    <img src="{{ asset('assets/img/fe-logo.png') }}" alt="jobguru logo" />
                                </a>
                            </div>
                            <p style="margin-top: 12px;">
                                {{ ($isEmployerFooter ?? false)
                                    ? 'Nền tảng tuyển dụng giúp bạn đăng tin nhanh, lọc ứng viên chuẩn và quản lý quy trình tuyển dụng hiệu quả.'
                                    : 'Nền tảng kết nối việc làm uy tín, giúp ứng viên tìm thấy cơ hội mơ ước và hỗ trợ doanh nghiệp xây dựng đội ngũ nhân sự.' }}
                            </p>
                            <ul class="footer-social" style="margin-top: 16px;">
                                <li><a href="#" aria-label="Facebook"><i class="fa fa-facebook"></i></a></li>
                                <li><a href="#" aria-label="Twitter"><i class="fa fa-twitter"></i></a></li>
                                <li><a href="#" aria-label="LinkedIn"><i class="fa fa-linkedin"></i></a></li>
                                <li><a href="#" aria-label="Google Plus"><i class="fa fa-google-plus"></i></a></li>
                                <li><a href="#" aria-label="Skype"><i class="fa fa-skype"></i></a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="single-footer-widget">
                            <h3>{{ ($isEmployerFooter ?? false) ? 'Nhà tuyển dụng' : 'Ứng viên' }}</h3>
                            <ul>
                                @if ($isEmployerFooter ?? false)
                                    <li><a href="{{ route('employers.post_job') }}"><i class="fa fa-angle-double-right"></i> Đăng tin tuyển dụng</a></li>
                                    <li><a href="{{ route('employers.browse') }}"><i class="fa fa-angle-double-right"></i> Tìm ứng viên</a></li>
                                    <li><a href="{{ route('employers.manage_jobs') }}"><i class="fa fa-angle-double-right"></i> Quản lý tin tuyển dụng</a></li>
                                    <li><a href="{{ route('employers.transaction') }}"><i class="fa fa-angle-double-right"></i> Giao dịch</a></li>
                                    <li><a href="{{ route('employers.change_password') }}"><i class="fa fa-angle-double-right"></i> Bảo mật tài khoản</a></li>
                                @else
                                    <li><a href="{{ route('candidates.browse_job') }}"><i class="fa fa-angle-double-right"></i> Tìm việc làm</a></li>
                                    <li><a href="{{ route('candidates.browse_categories') }}"><i class="fa fa-angle-double-right"></i> Danh mục ngành nghề</a></li>
                                    <li><a href="{{ route('candidates.browse_companies') }}"><i class="fa fa-angle-double-right"></i> Danh sách chi nhánh</a></li>
                                    <li><a href="{{ route('candidates.submit_resume') }}"><i class="fa fa-angle-double-right"></i> Nộp hồ sơ (CV)</a></li>
                                    <li><a href="{{ route('candidates.change_password') }}"><i class="fa fa-angle-double-right"></i> Bảo mật tài khoản</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <div class="single-footer-widget">
                            <h3>Liên kết</h3>
                            <ul>
                                <li><a href="{{ route('pages.about') }}"><i class="fa fa-angle-double-right"></i> Về chúng tôi</a></li>
                                <li><a href="{{ route('pages.blog') }}"><i class="fa fa-angle-double-right"></i> Tin tức</a></li>
                                <li><a href="{{ route('pages.contact') }}"><i class="fa fa-angle-double-right"></i> Liên hệ</a></li>
                                @guest
                                    <li><a href="{{ route('auth.login') }}"><i class="fa fa-angle-double-right"></i> Đăng nhập</a></li>
                                    <li><a href="{{ route('auth.sign_up') }}"><i class="fa fa-angle-double-right"></i> Đăng ký</a></li>
                                @endguest
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="single-footer-widget footer-contact">
                            <h3>Liên hệ</h3>
                            <p><i class="fa fa-map-marker"></i> Số 4257, Đường SunnyVale, Hoa Kỳ</p>
                            <p><i class="fa fa-phone"></i> 012-3456-789</p>
                            <p><i class="fa fa-envelope-o"></i> info@jobguru.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       
    </footer>
</div>

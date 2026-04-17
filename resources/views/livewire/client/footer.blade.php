<div>
    <footer class="jobguru-footer-area client-app-footer {{ ($isEmployerFooter ?? false) ? 'footer--employer' : 'footer--candidate' }}">
        <div class="footer-top section_50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="single-footer-widget">
                            <div class="footer-logo">
                                <a href="{{ ($isEmployerFooter ?? false) ? route('employers.portal') : route('home') }}">
                                    <img src="{{ asset('assets/img/fe-logo.png') }}" alt="jobguru logo" />
                                </a>
                            </div>
                            <p style="margin-top: 12px;">
                                {{ ($isEmployerFooter ?? false)
                                    ? 'Nền tảng tuyển dụng dành riêng cho HR để đăng tin, lọc ứng viên và theo dõi quy trình.'
                                    : 'Nền tảng dành riêng cho ứng viên để tìm việc, tạo CV và theo dõi trạng thái ứng tuyển.' }}
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="single-footer-widget">
                            <h3>{{ ($isEmployerFooter ?? false) ? 'Nhà tuyển dụng' : 'Ứng viên' }}</h3>
                            <ul>
                                @if ($isEmployerFooter ?? false)
                                    @if(!($canEmployerAccess ?? false))
                                        <li><a href="{{ route('employers.portal') }}"><i class="fa fa-angle-double-right"></i> Trang nhà tuyển dụng</a></li>
                                        <li><a href="{{ route('employers.register') }}"><i class="fa fa-angle-double-right"></i> Tạo tài khoản HR</a></li>
                                        <li><a href="{{ route('employers.login') }}"><i class="fa fa-angle-double-right"></i> Đăng nhập HR</a></li>
                                    @else
                                        <li><a href="{{ route('employers.post_job') }}"><i class="fa fa-angle-double-right"></i> Đăng tin tuyển dụng</a></li>
                                        <li><a href="{{ route('employers.browse') }}"><i class="fa fa-angle-double-right"></i> Tìm ứng viên</a></li>
                                        <li><a href="{{ route('employers.manage_jobs') }}"><i class="fa fa-angle-double-right"></i> Quản lý tin tuyển dụng</a></li>
                                        <li><a href="{{ route('employers.transaction') }}"><i class="fa fa-angle-double-right"></i> Giao dịch</a></li>
                                    @endif
                                @else
                                    <li><a href="{{ route('candidates.browse_job') }}"><i class="fa fa-angle-double-right"></i> Tìm việc làm</a></li>
                                    <li><a href="{{ route('candidates.browse_categories') }}"><i class="fa fa-angle-double-right"></i> Danh mục ngành nghề</a></li>
                                    <li><a href="{{ route('candidates.browse_companies') }}"><i class="fa fa-angle-double-right"></i> Danh sách công ty</a></li>
                                    <li><a href="{{ route('candidates.submit_resume') }}"><i class="fa fa-angle-double-right"></i> Cập nhật CV</a></li>
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
                                @if(($isEmployerFooter ?? false) ? !($canEmployerAccess ?? false) : !($canCandidateAccess ?? false))
                                    <li>
                                        <a href="{{ ($isEmployerFooter ?? false) ? route('employers.login') : route('candidates.login') }}">
                                            <i class="fa fa-angle-double-right"></i> Đăng nhập
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ ($isEmployerFooter ?? false) ? route('employers.register') : route('candidates.register') }}">
                                            <i class="fa fa-angle-double-right"></i> Đăng ký
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="single-footer-widget footer-contact">
                            <h3>Liên hệ</h3>
                            <p><i class="fa fa-map-marker"></i> Số 4257, đường SunnyVale, Hoa Kỳ</p>
                            <p><i class="fa fa-phone"></i> 012-3456-789</p>
                            <p><i class="fa fa-envelope-o"></i> info@jobguru.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>

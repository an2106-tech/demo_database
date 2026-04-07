<div>
    <style>
        .jobguru-footer-area.footer--employer {
            --footer-accent: #22c55e;
            --footer-accent-2: #0ea5e9;
            --footer-bg: #0b1220;
            --footer-bg-2: #0f1b33;
        }

        .jobguru-footer-area.footer--candidate {
            --footer-accent: #2f7ff7;
            --footer-accent-2: #16a34a;
            --footer-bg: #0b1830;
            --footer-bg-2: #10264a;
        }

        .jobguru-footer-area.footer--employer .footer-top,
        .jobguru-footer-area.footer--candidate .footer-top {
            background: radial-gradient(1200px 600px at 10% 0%, rgba(34, 197, 94, .14), transparent 55%),
                radial-gradient(1000px 520px at 90% 10%, rgba(14, 165, 233, .14), transparent 60%),
                linear-gradient(180deg, var(--footer-bg-2), var(--footer-bg));
            color: rgba(255, 255, 255, .84);
        }

        .jobguru-footer-area.footer--employer .single-footer-widget h3,
        .jobguru-footer-area.footer--candidate .single-footer-widget h3 {
            color: #fff;
            font-weight: 900;
            letter-spacing: .2px;
        }

        .jobguru-footer-area.footer--employer a,
        .jobguru-footer-area.footer--candidate a {
            color: rgba(255, 255, 255, .86);
        }

        .jobguru-footer-area.footer--employer a:hover,
        .jobguru-footer-area.footer--candidate a:hover {
            color: #fff;
        }

        .jobguru-footer-area .footer-cta {
            border-radius: 18px;
            padding: 16px 16px;
            border: 1px solid rgba(255, 255, 255, .10);
            background: linear-gradient(135deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, .04));
            box-shadow: 0 20px 60px rgba(0, 0, 0, .22);
        }

        .jobguru-footer-area .footer-cta__title {
            font-weight: 900;
            color: #fff;
            margin: 0 0 6px;
        }

        .jobguru-footer-area .footer-cta__desc {
            margin: 0 0 12px;
            color: rgba(255, 255, 255, .78);
            line-height: 1.65;
        }

        .jobguru-footer-area .footer-cta__btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            font-weight: 900;
            border: 1px solid rgba(255, 255, 255, .12);
            background: linear-gradient(135deg, var(--footer-accent), var(--footer-accent-2));
            color: #04110a;
        }

        .jobguru-footer-area .footer-cta__btn:hover {
            filter: brightness(1.03);
        }

        .jobguru-footer-area.footer--candidate .footer-cta__btn {
            color: #071526;
        }

        .jobguru-footer-area .footer-logo img {
            height: 34px;
            width: auto;
        }

        .jobguru-footer-area .footer-social li a {
            background: rgba(255, 255, 255, .10);
        }

        .jobguru-footer-area .footer-social li a:hover {
            background: rgba(255, 255, 255, .16);
        }

        .jobguru-footer-area.footer--employer .footer-copyright,
        .jobguru-footer-area.footer--candidate .footer-copyright {
            background: rgba(0, 0, 0, .18);
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        .jobguru-footer-area.footer--employer .footer-copyright p,
        .jobguru-footer-area.footer--candidate .footer-copyright p {
            color: rgba(255, 255, 255, .76);
            margin: 0;
        }
    </style>

    <footer class="jobguru-footer-area {{ ($isEmployerFooter ?? false) ? 'footer--employer' : 'footer--candidate' }}">
        <div class="footer-top section_50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="single-footer-widget">
                            <div class="footer-logo">
                                <a href="{{ route('home') }}">
                                    <img src="{{ asset('assets/img/logo.png') }}" alt="jobguru logo" />
                                </a>
                            </div>
                            <p style="margin-top: 12px;">
                                {{ ($isEmployerFooter ?? false)
                                    ? 'Nền tảng tuyển dụng giúp bạn đăng tin nhanh, lọc ứng viên chuẩn và quản lý quy trình tuyển dụng hiệu quả.'
                                    : 'Nền tảng kết nối việc làm uy tín, giúp ứng viên tìm thấy cơ hội mơ ước và hỗ trợ doanh nghiệp xây dựng đội ngũ nhân sự.' }}
                            </p>
                            <ul class="footer-social" style="margin-top: 16px;">
                                <li><a href="#" class="fb" aria-label="Facebook"><i class="fa fa-facebook"></i></a></li>
                                <li><a href="#" class="twitter" aria-label="Twitter"><i class="fa fa-twitter"></i></a></li>
                                <li><a href="#" class="linkedin" aria-label="LinkedIn"><i class="fa fa-linkedin"></i></a></li>
                                <li><a href="#" class="gp" aria-label="Google Plus"><i class="fa fa-google-plus"></i></a></li>
                                <li><a href="#" class="skype" aria-label="Skype"><i class="fa fa-skype"></i></a></li>
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

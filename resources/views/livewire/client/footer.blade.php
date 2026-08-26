@php
    $isEmployer = $isEmployerFooter ?? false;
    $homeUrl = $isEmployer ? route('employers.portal') : route('home');
    $brandTitle = 'Tổ chức Giáo dục FPT';

    $primaryHeading = $isEmployer ? 'Nhà tuyển dụng' : 'Dành cho Ứng viên';

    $primaryLinks = $isEmployer
        ? [
            ['label' => 'Cổng thông tin tuyển dụng', 'url' => route('employers.portal')],
            ['label' => 'Đăng tin tuyển dụng mới', 'url' => route('employers.post_job')],
            ['label' => 'Quản lý tin & Ứng viên', 'url' => route('employers.manage_jobs')],
            ['label' => 'Tìm kiếm ứng viên tiềm năng', 'url' => route('employers.browse')],
        ]
        : [
            ['label' => 'Khám phá việc làm', 'url' => route('candidates.browse_job')],
            ['label' => 'Danh mục ngành nghề', 'url' => route('candidates.browse_categories')],
            ['label' => 'Cơ sở & Đơn vị thành viên', 'url' => route('candidates.browse_companies')],
            ['label' => 'Thiết kế CV Online', 'url' => route('candidates.cv_builder')],
            ['label' => 'Quản lý hồ sơ đã nộp', 'url' => route('candidates.manage_jobs')],
        ];

    $systemLinks = [
        ['label' => 'Về FPT Education', 'url' => route('pages.about')],
        ['label' => 'Tin tức & Sự kiện', 'url' => route('pages.blog')],
        ['label' => 'Văn hóa & Đãi ngộ', 'url' => route('pages.about')],
        ['label' => 'Liên hệ hợp tác', 'url' => route('pages.contact')],
    ];

    $accountLinks = [];
    if (($isEmployer && !($canEmployerAccess ?? false)) || (!$isEmployer && !($canCandidateAccess ?? false))) {
        $accountLinks[] = [
            'label' => 'Đăng nhập tài khoản',
            'url' => $isEmployer ? route('employers.login') : route('candidates.login'),
        ];
        $accountLinks[] = [
            'label' => 'Đăng ký tài khoản',
            'url' => $isEmployer ? route('employers.register') : route('candidates.register'),
        ];
    }

    $branches = [
        ['city' => 'Hà Nội', 'address' => 'Tòa nhà FPT, 10 Phạm Văn Bạch, Cầu Giấy'],
        ['city' => 'TP. Hồ Chí Minh', 'address' => 'Lô E2a-7, Đường D1, Khu CNC, TP. Thủ Đức'],
        ['city' => 'Đà Nẵng', 'address' => 'Khu đô thị FPT City, Ngũ Hành Sơn'],
        ['city' => 'Cần Thơ', 'address' => 'Số 600 đường Nguyễn Văn Cừ, Ninh Kiều'],
        ['city' => 'Quy Nhơn', 'address' => 'Khu đô thị An Phú Thịnh, TP. Quy Nhơn'],
    ];
@endphp

<footer class="fpt-elite-footer {{ $isEmployer ? 'is-employer' : 'is-candidate' }}">
    <style>
        .fpt-elite-footer {
            --fpt-foot-bg: #ffffff;
            --fpt-foot-border: #e2e8f0;
            --fpt-foot-ink: #0f172a;
            --fpt-foot-muted: #64748b;
            --fpt-foot-soft: #f8fafc;
            --fpt-foot-primary: #f37021;
            --fpt-foot-primary-soft: rgba(243, 112, 33, 0.08);
            --fpt-foot-ease: cubic-bezier(0.16, 1, 0.3, 1);

            background: var(--fpt-foot-bg) !important;
            border-top: 1px solid var(--fpt-foot-border) !important;
            color: var(--fpt-foot-ink) !important;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
            position: relative;
            margin-top: 60px;
            box-shadow: 0 -4px 20px rgba(15, 23, 42, 0.02);
            width: 100%;
        }

        .fpt-foot-container {
            width: 100%;
            max-width: 100%;
            padding: 48px clamp(24px, 4vw, 72px) 28px;
            margin: 0;
            box-sizing: border-box;
        }

        /* Top Grid */
        .fpt-foot-grid {
            display: grid;
            grid-template-columns: 1.6fr 1.05fr 1.05fr 1.3fr 1.5fr;
            gap: clamp(20px, 3vw, 52px);
            margin-bottom: 44px;
        }

        @media (max-width: 1280px) {
            .fpt-foot-grid {
                grid-template-columns: 1.5fr 1fr 1fr 1.4fr;
                gap: 28px;
            }
            .fpt-foot-col--branches {
                grid-column: 1 / -1;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 16px;
                padding-top: 20px;
                border-top: 1px dashed var(--fpt-foot-border);
            }
        }

        @media (max-width: 767.98px) {
            .fpt-foot-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }
            .fpt-foot-col--branches {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
        }

        /* Brand Column */
        .fpt-foot-brand__logo {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid var(--fpt-foot-border);
            border-radius: 14px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            transition: transform 0.2s ease;
        }

        .fpt-foot-brand__logo:hover {
            transform: translateY(-2px);
        }

        .fpt-foot-brand__logo img {
            height: 40px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .fpt-foot-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 700;
            color: #047857;
            background: #ecfdf5;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #a7f3d0;
            margin-bottom: 14px;
        }

        .fpt-foot-desc {
            font-size: 13.5px;
            line-height: 1.65;
            color: var(--fpt-foot-muted) !important;
            margin-bottom: 20px;
            max-width: 380px;
        }

        /* Social Icons */
        .fpt-foot-socials {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fpt-foot-social-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--fpt-foot-soft);
            border: 1px solid var(--fpt-foot-border);
            color: #475569 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            text-decoration: none !important;
            transition: all 0.2s var(--fpt-foot-ease);
        }

        .fpt-foot-social-btn:hover {
            background: var(--fpt-foot-primary) !important;
            color: #ffffff !important;
            border-color: var(--fpt-foot-primary) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.3);
        }

        /* Column Headers */
        .fpt-foot-title {
            font-size: 15px !important;
            font-weight: 800 !important;
            color: var(--fpt-foot-ink) !important;
            margin: 0 0 18px !important;
            letter-spacing: -0.01em;
            position: relative;
            padding-bottom: 8px;
        }

        .fpt-foot-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 24px;
            height: 2px;
            background: var(--fpt-foot-primary);
            border-radius: 2px;
        }

        /* Nav Links */
        .fpt-foot-nav {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
            display: flex;
            flex-direction: column;
            gap: 11px;
        }

        .fpt-foot-nav li {
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .fpt-foot-nav a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13.5px;
            color: var(--fpt-foot-muted) !important;
            text-decoration: none !important;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .fpt-foot-nav a i {
            font-size: 10px;
            color: #cbd5e1;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .fpt-foot-nav a:hover {
            color: var(--fpt-foot-primary) !important;
            transform: translateX(3px);
        }

        .fpt-foot-nav a:hover i {
            color: var(--fpt-foot-primary);
            transform: translateX(2px);
        }

        /* Contact Items Card */
        .fpt-foot-contact-box {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .fpt-foot-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: var(--fpt-foot-soft);
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid var(--fpt-foot-border);
            transition: all 0.2s ease;
        }

        .fpt-foot-contact-item:hover {
            border-color: rgba(243, 112, 33, 0.3);
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        }

        .fpt-foot-contact-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--fpt-foot-primary-soft);
            color: var(--fpt-foot-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .fpt-foot-contact-info small {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .fpt-foot-contact-info a,
        .fpt-foot-contact-info span {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--fpt-foot-ink) !important;
            text-decoration: none !important;
            line-height: 1.4;
            transition: color 0.2s ease;
        }

        .fpt-foot-contact-info a:hover {
            color: var(--fpt-foot-primary) !important;
        }

        /* Branches List */
        .fpt-foot-branch-item {
            margin-bottom: 12px;
            font-size: 13px;
            line-height: 1.45;
        }

        .fpt-foot-branch-city {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 800;
            color: var(--fpt-foot-ink);
            margin-bottom: 2px;
        }

        .fpt-foot-branch-city i {
            color: var(--fpt-foot-primary);
            font-size: 11px;
        }

        .fpt-foot-branch-addr {
            display: block;
            color: var(--fpt-foot-muted);
            font-size: 12.5px;
            padding-left: 17px;
        }

        /* Bottom Copyright Bar */
        .fpt-foot-bottom {
            padding-top: 24px;
            border-top: 1px solid var(--fpt-foot-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 13px;
            color: var(--fpt-foot-muted);
        }

        .fpt-foot-bottom strong {
            color: var(--fpt-foot-ink);
        }

        .fpt-foot-bottom-links {
            display: flex;
            align-items: center;
            gap: 16px;
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .fpt-foot-bottom-links li {
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .fpt-foot-bottom-links a {
            color: var(--fpt-foot-muted) !important;
            text-decoration: none !important;
            font-size: 12.5px;
            transition: color 0.2s ease;
        }

        .fpt-foot-bottom-links a:hover {
            color: var(--fpt-foot-primary) !important;
        }

        @media (max-width: 767.98px) {
            .fpt-foot-bottom {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }
            .fpt-foot-bottom-links {
                justify-content: center;
                flex-wrap: wrap;
            }
        }
    </style>

    <div class="fpt-foot-container">
        <div class="fpt-foot-grid">
            {{-- Col 1: Brand Info --}}
            <div class="fpt-foot-col fpt-foot-brand">
                <a href="{{ $homeUrl }}" class="fpt-foot-brand__logo" aria-label="FPT Careers">
                    <img src="{{ asset('assets/img/fe-logo.png') }}" alt="FPT Education" />
                </a>

                <div>
                    <span class="fpt-foot-badge">
                        <i class="fa fa-check-circle"></i> Cổng thông tin chính thức
                    </span>
                </div>

                <p class="fpt-foot-desc">
                    <strong>Tổ chức Giáo dục FPT</strong> — Môi trường học tập và làm việc chuẩn quốc tế, kiến tạo cơ hội phát triển sự nghiệp bền vững và năng lực cạnh tranh toàn cầu.
                </p>

                <div class="fpt-foot-socials">
                    <a href="{{ route('pages.contact') }}" class="fpt-foot-social-btn" title="Liên hệ hỗ trợ">
                        <i class="fa fa-envelope-o"></i>
                    </a>
                    <a href="{{ route('pages.blog') }}" class="fpt-foot-social-btn" title="Tin tức tuyển dụng">
                        <i class="fa fa-newspaper-o"></i>
                    </a>
                    <a href="{{ route('pages.about') }}" class="fpt-foot-social-btn" title="Về FPT Education">
                        <i class="fa fa-building-o"></i>
                    </a>
                    <a href="tel:02473001866" class="fpt-foot-social-btn" title="Hotline hỗ trợ">
                        <i class="fa fa-phone"></i>
                    </a>
                </div>
            </div>

            {{-- Col 2: Primary Nav Links --}}
            <div class="fpt-foot-col">
                <h4 class="fpt-foot-title">{{ $primaryHeading }}</h4>
                <ul class="fpt-foot-nav">
                    @foreach ($primaryLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}">
                                <i class="fa fa-angle-right"></i>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Col 3: Ecosystem & System Links --}}
            <div class="fpt-foot-col">
                <h4 class="fpt-foot-title">Hệ sinh thái FPT</h4>
                <ul class="fpt-foot-nav">
                    @foreach ($systemLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}">
                                <i class="fa fa-angle-right"></i>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        </li>
                    @endforeach

                    @foreach ($accountLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}">
                                <i class="fa fa-angle-right"></i>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Col 4: Contact Center --}}
            <div class="fpt-foot-col">
                <h4 class="fpt-foot-title">Trung tâm Hỗ trợ</h4>
                <div class="fpt-foot-contact-box">
                    <div class="fpt-foot-contact-item">
                        <div class="fpt-foot-contact-icon">
                            <i class="fa fa-phone"></i>
                        </div>
                        <div class="fpt-foot-contact-info">
                            <small>Tổng đài tuyển dụng</small>
                            <a href="tel:02473001866">024 7300 1866</a>
                        </div>
                    </div>

                    <div class="fpt-foot-contact-item">
                        <div class="fpt-foot-contact-icon">
                            <i class="fa fa-envelope-o"></i>
                        </div>
                        <div class="fpt-foot-contact-info">
                            <small>Hộp thư ứng tuyển</small>
                            <a href="mailto:tuyendung.fe@fpt.edu.vn">tuyendung.fe@fpt.edu.vn</a>
                        </div>
                    </div>

                    <div class="fpt-foot-contact-item">
                        <div class="fpt-foot-contact-icon">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <div class="fpt-foot-contact-info">
                            <small>Giờ làm việc</small>
                            <span>Thứ 2 - Thứ 6 (08:00 - 17:30)</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Col 5: Main Campuses & Branches --}}
            <div class="fpt-foot-col fpt-foot-col--branches">
                <h4 class="fpt-foot-title">Cơ sở & Chi nhánh</h4>
                <div>
                    @foreach ($branches as $branch)
                        <div class="fpt-foot-branch-item">
                            <span class="fpt-foot-branch-city">
                                <i class="fa fa-map-marker"></i> {{ $branch['city'] }}
                            </span>
                            <span class="fpt-foot-branch-addr">{{ $branch['address'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="fpt-foot-bottom">
            <div>
                &copy; {{ date('Y') }} <strong>{{ $brandTitle }}</strong> (FPT Education). Đã đăng ký bản quyền.
            </div>

            <ul class="fpt-foot-bottom-links">
                <li><a href="{{ route('pages.about') }}">Giới thiệu</a></li>
                <li><a href="{{ route('pages.contact') }}">Chính sách bảo mật</a></li>
                <li><a href="{{ route('pages.contact') }}">Điều khoản dịch vụ</a></li>
                <li><a href="{{ route('pages.contact') }}">Quy chế hoạt động</a></li>
            </ul>
        </div>
    </div>
</footer>

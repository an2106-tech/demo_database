<header class="jobguru-header-area stick-top forsticky page-header client-app-header app-header-candidate" role="banner" x-data="{ openUserMenu: false }">
    <style>
        .candidate-actions ul {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: nowrap !important;
            white-space: nowrap;
        }

        .candidate-actions ul > li {
            flex: 0 0 auto;
            width: auto;
        }

        .candidate-actions ul > li > a:not(.switch-role-btn) {
            display: inline-flex !important;
            align-items: center;
            white-space: nowrap;
        }

        .candidate-user-menu {
            position: relative;
        }

        .candidate-user-trigger {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: 1px solid rgba(14, 116, 144, .2);
            background: #fff;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .1);
        }

        .candidate-user-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 220px;
            padding: 10px;
            border-radius: 14px;
            border: 1px solid rgba(148, 163, 184, .25);
            background: #fff;
            box-shadow: 0 16px 30px rgba(15, 23, 42, .15);
            z-index: 1000;
        }

        .candidate-user-dropdown a,
        .candidate-user-dropdown a:visited,
        .candidate-user-dropdown a:focus {
            display: block;
            padding: 10px 12px;
            border-radius: 10px;
            color: #1e293b !important;
            font-weight: 600;
        }

        .candidate-user-dropdown a:hover {
            background: #f8fafc;
            color: #1e293b !important;
        }

        .candidate-user-dropdown .client-logout-btn {
            width: 100% !important;
            margin-top: 8px;
            justify-content: center;
        }

        .app-header-candidate .header-right-menu ul li a.switch-role-btn,
        .app-header-candidate .header-right-menu ul li a.switch-role-btn:hover,
        .app-header-candidate .header-right-menu ul li a.switch-role-btn:focus,
        .app-header-candidate .header-right-menu ul li a.switch-role-btn:visited,
        .app-header-candidate .header-right-menu ul li a.switch-role-btn:active {
            all: unset !important;
            box-sizing: border-box !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 44px !important;
            padding: 12px 24px !important;
            border-radius: 999px !important;
            background: #ff8a1d !important;
            border: none !important;
            color: #fff !important;
            -webkit-text-fill-color: #fff !important;
            font-family: 'Inter', sans-serif !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            line-height: 1 !important;
            letter-spacing: 0 !important;
            white-space: nowrap !important;
            text-decoration: none !important;
            cursor: pointer !important;
            opacity: 1 !important;
            filter: none !important;
            box-shadow: none !important;
            transform: none !important;
            transition: none !important;
        }
    </style>
    <div class="menu-animation">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-2">
                    <div class="site-logo">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('assets/img/fe-logo.png') }}" alt="FPT Polytechnic" />
                        </a>
                    </div>
                    <div class="jobguru-responsive-menu"></div>
                </div>

                <div class="col-lg-6">
                    <div class="header-menu">
                        <nav id="navigation" aria-label="Candidate navigation">
                            <ul id="jobguru_navigation">
                                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                                <li><a href="{{ route('candidates.browse_job') }}">Việc làm</a></li>
                                <li><a href="{{ route('candidates.browse_categories') }}">Ngành nghề</a></li>
                                <li><a href="{{ route('candidates.browse_companies') }}">Công ty</a></li>
                                <li><a href="{{ route('pages.about') }}">Về chúng tôi</a></li>
                                <li><a href="{{ route('pages.contact') }}">Liên hệ</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="header-right-menu candidate-actions">
                        <ul>
                            <li>
                                <a
                                    href="{{ request()->routeIs('employers.portal') ? route('candidates.browse_job') : route('employers.portal') }}"
                                    class="switch-role-btn"
                                >
                                    {{ request()->routeIs('employers.portal') ? 'Chuyển Sang Ứng Viên' : 'Khu Nhà Tuyển Dụng' }}
                                </a>
                            </li>
                            @if($canCandidateAccess ?? false)
                                <li class="candidate-user-menu" @click.outside="openUserMenu = false">
                                    <button
                                        type="button"
                                        class="candidate-user-trigger"
                                        aria-label="Mở menu tài khoản"
                                        :aria-expanded="openUserMenu.toString()"
                                        @click="openUserMenu = !openUserMenu"
                                    >
                                        <i class="fa fa-user"></i>
                                    </button>

                                    <div class="candidate-user-dropdown" x-show="openUserMenu" x-transition.opacity.duration.150ms>
                                        <a href="{{ route('candidates.candidate_dashboard') }}">Hồ sơ thông tin</a>
                                        <livewire:client.logout-button />
                                    </div>
                                </li>
                            @else
                                <li><a href="{{ route('candidates.register') }}"><i class="fa fa-user"></i> Đăng ký</a></li>
                                <li><a href="{{ route('candidates.login') }}"><i class="fa fa-lock"></i> Đăng nhập</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

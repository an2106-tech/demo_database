<header class="jobguru-header-area stick-top forsticky page-header client-app-header app-header-employer" role="banner" x-data="{ openEmployerUserMenu: false }">
    <style>
        .app-header-employer .menu-animation {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }

        .app-header-employer #jobguru_navigation {
            display: flex;
            align-items: center;
            gap: 2px;
            flex-wrap: nowrap;
        }

        .app-header-employer #jobguru_navigation > li > a {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .employer-actions ul {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: nowrap !important;
            white-space: nowrap;
        }

        .employer-actions ul > li {
            flex: 0 0 auto;
            width: auto;
        }

        .employer-actions ul > li > a:not(.switch-role-btn) {
            display: inline-flex !important;
            align-items: center;
            white-space: nowrap;
        }

        .app-header-employer .header-right-menu ul li a.switch-role-btn,
        .app-header-employer .header-right-menu ul li a.switch-role-btn:hover,
        .app-header-employer .header-right-menu ul li a.switch-role-btn:focus,
        .app-header-employer .header-right-menu ul li a.switch-role-btn:visited,
        .app-header-employer .header-right-menu ul li a.switch-role-btn:active {
            all: unset !important;
            box-sizing: border-box !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 44px !important;
            padding: 11px 18px !important;
            border-radius: 999px !important;
            background: #ff8a1d !important;
            border: none !important;
            color: #fff !important;
            -webkit-text-fill-color: #fff !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 14px !important;
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

        .employer-user-menu {
            position: relative;
        }

        .employer-user-trigger {
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

        .employer-user-dropdown {
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

        .employer-user-dropdown a,
        .employer-user-dropdown a:visited,
        .employer-user-dropdown a:focus {
            display: block;
            padding: 10px 12px;
            border-radius: 10px;
            color: #1e293b !important;
            font-weight: 600;
        }

        .employer-user-dropdown a:hover {
            background: #f8fafc;
            color: #1e293b !important;
        }

        .employer-user-dropdown .client-logout-btn {
            width: 100% !important;
            margin-top: 8px;
            justify-content: center;
        }

        @media (max-width: 991.98px) {
            .app-header-employer .header-menu {
                display: none !important;
            }

            .app-header-employer .row {
                gap: 12px;
            }

            .employer-actions ul {
                justify-content: flex-start;
                flex-wrap: wrap !important;
                white-space: normal;
            }
        }

        @media (max-width: 575.98px) {
            .app-header-employer .menu-animation {
                padding-left: 10px;
                padding-right: 10px;
            }

            .app-header-employer .header-right-menu ul li a.switch-role-btn,
            .app-header-employer .header-right-menu ul li a.switch-role-btn:hover,
            .app-header-employer .header-right-menu ul li a.switch-role-btn:focus,
            .app-header-employer .header-right-menu ul li a.switch-role-btn:visited,
            .app-header-employer .header-right-menu ul li a.switch-role-btn:active {
                min-height: 40px !important;
                padding: 10px 14px !important;
                font-size: 13px !important;
            }
        }
    </style>
    <div class="menu-animation">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-2">
                    <div class="site-logo">
                        <a href="{{ route('employers.dashboard') }}">
                            <img src="{{ asset('assets/img/fe-logo.png') }}" alt="FPT Polytechnic" />
                        </a>
                    </div>
                    <div class="jobguru-responsive-menu"></div>
                </div>

                <div class="col-lg-6">
                    <div class="header-menu">
                        <nav id="navigation" aria-label="Employer navigation">
                            <ul id="jobguru_navigation">
                                <li><a href="{{ route('employers.portal') }}">Trang chủ</a></li>
                                @auth
                                    <li><a href="{{ route('employers.dashboard') }}">Tổng quan</a></li>
                                    <li><a href="{{ route('employers.post_job') }}">Đăng tin</a></li>
                                    <li><a href="{{ route('employers.manage_jobs') }}">Tin tuyển</a></li>
                                    <li><a href="{{ route('employers.browse') }}">Ứng viên</a></li>
                                    <li><a href="{{ route('employers.transaction') }}">Thanh toán</a></li>
                                @else
                                <li><a href="{{ route('employers.dashboard') }}">Tổng quan</a></li>
                                <li><a href="{{ route('employers.post_job') }}">Đăng tin</a></li>
                                <li><a href="{{ route('employers.manage_jobs') }}">Tin tuyển</a></li>
                                <li><a href="{{ route('employers.browse') }}">Ứng viên</a></li>
                                <li><a href="{{ route('employers.transaction') }}">Thanh toán</a></li>
                                @endauth
                            </ul>
                        </nav>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="header-right-menu employer-actions">
                        <ul>
                            <li>
                                <a
                                    href="{{ route('candidates.browse_job') }}"
                                    class="switch-role-btn"
                                    style="background:#ff8a1d !important;color:#fff !important;-webkit-text-fill-color:#fff !important;"
                                >
                                    Dành cho ứng viên
                                </a>
                            </li>
                            @auth
                                @if(in_array(Auth::user()->role, ['hr', 'director', 'admin']) || (isset(Auth::user()->metadata['account_types']) && in_array('employer', Auth::user()->metadata['account_types'])))
                                <li class="employer-user-menu" @click.outside="openEmployerUserMenu = false">
                                    <button
                                        type="button"
                                        class="employer-user-trigger"
                                        aria-label="Mở menu tài khoản"
                                        :aria-expanded="openEmployerUserMenu.toString()"
                                        @click="openEmployerUserMenu = !openEmployerUserMenu"
                                    >
                                        <i class="fa fa-user"></i>
                                    </button>

                                    <div class="employer-user-dropdown" x-show="openEmployerUserMenu" x-transition.opacity.duration.150ms>
                                        <a href="{{ route('employers.company_profile') }}">Hồ sơ công ty</a>
                                        @if(!($canCandidateAccess ?? false))
                                            <a href="{{ route('candidates.register') }}">Kích hoạt hồ sơ ứng viên</a>
                                        @else
                                            <a href="{{ route('candidates.candidate_dashboard') }}">Khu ứng viên</a>
                                        @endif
                                        <a href="{{ route('director.approve_jobs') }}">Duyệt tin</a>
                                        <livewire:client.logout-button />
                                    </div>
                                </li>
                                @else
                                <li><a href="{{ route('employers.register') }}"><i class="fa fa-user"></i> Đăng ký</a></li>
                                <li><a href="{{ route('employers.login') }}"><i class="fa fa-lock"></i> Đăng nhập</a></li>
                                @endif
                            @else
                                <li><a href="{{ route('employers.register') }}"><i class="fa fa-user"></i> Đăng ký</a></li>
                                <li><a href="{{ route('employers.login') }}"><i class="fa fa-lock"></i> Đăng nhập</a></li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

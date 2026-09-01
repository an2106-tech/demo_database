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
            display: inline-flex !important;
            align-items: center;
            white-space: nowrap !important;
            line-height: 1.2 !important;
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

        /* High-End Segmented Role Switcher */
        .fpt-role-segmented-switcher {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            gap: 3px;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .fpt-role-seg-btn {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 14px !important;
            border-radius: 999px;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
            font-size: 12.5px !important;
            font-weight: 750 !important;
            line-height: 1 !important;
            color: #64748b !important;
            -webkit-text-fill-color: #64748b !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
            cursor: pointer;
        }

        .fpt-role-seg-btn i {
            font-size: 12.5px;
            transition: transform 0.2s ease;
        }

        .fpt-role-seg-btn:hover {
            color: #0f172a !important;
            -webkit-text-fill-color: #0f172a !important;
            background: rgba(255, 255, 255, 0.6);
        }

        .fpt-role-seg-btn.is-active {
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%) !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(243, 112, 33, 0.28) !important;
        }

        .fpt-role-seg-btn.is-active i {
            color: #ffffff !important;
        }

        .employer-user-menu {
            position: relative;
        }

        .employer-user-trigger {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1.5px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            padding: 0;
        }

        .employer-user-trigger:hover {
            transform: translateY(-2px);
            border-color: #f37021;
            box-shadow: 0 8px 20px rgba(243, 112, 33, 0.18);
        }

        .employer-user-trigger img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .employer-user-dropdown {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            width: 280px;
            padding: 8px;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.16), 0 4px 16px -2px rgba(15, 23, 42, 0.06);
            z-index: 10000;
            text-align: left;
        }

        .employer-user-dropdown-header {
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .employer-user-dropdown-header img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            flex-shrink: 0;
        }

        .employer-user-dropdown-info {
            min-width: 0;
            flex: 1;
        }

        .employer-user-dropdown-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
        }

        .employer-user-dropdown-role {
            font-size: 11px;
            color: #f37021;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .employer-user-dropdown-menu-list {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .employer-user-dropdown-item {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 10px;
            color: #334155 !important;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
        }

        .employer-user-dropdown-item > .fa-angle-right {
            flex: 0 0 auto;
            margin: 0 0 0 10px !important;
        }

        .employer-user-dropdown-item:hover {
            background: #f8fafc;
            color: #f37021 !important;
            transform: translateX(2px);
        }

        .employer-user-dropdown-item-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .employer-user-dropdown-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13.5px;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .employer-user-dropdown-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 6px 0;
        }

        @media (max-width: 991.98px) {
            .app-header-employer .row {
                justify-content: space-between;
            }

            .app-header-employer .col-lg-2 {
                width: 100% !important;
                max-width: none;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .app-header-employer .header-menu {
                display: none !important;
            }

            .app-header-employer .col-lg-6,
            .app-header-employer .col-lg-4 {
                display: none !important;
            }

            .app-header-employer .site-logo {
                margin: 0 !important;
                padding: 0 !important;
            }

            .app-header-employer .site-logo img {
                max-width: 136px;
                height: auto;
            }

            .app-header-employer .jobguru-responsive-menu {
                display: flex;
                justify-content: flex-end;
                margin-left: auto;
            }
        }

        @media (min-width: 992px) and (max-width: 1320px) {
            .app-header-employer #jobguru_navigation > li:nth-child(6) {
                display: none;
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
                                <div class="fpt-role-segmented-switcher">
                                    <a
                                        href="{{ route('candidates.browse_job') }}"
                                        class="fpt-role-seg-btn"
                                        title="Chuyển sang Khu vực Ứng viên"
                                    >
                                        <i class="fa fa-user-circle-o"></i>
                                        <span>Ứng viên</span>
                                    </a>
                                    <a
                                        href="{{ route('employers.dashboard') }}"
                                        class="fpt-role-seg-btn is-active"
                                        title="Khu vực Nhà tuyển dụng"
                                    >
                                        <i class="fa fa-building-o"></i>
                                        <span>Tuyển dụng</span>
                                    </a>
                                </div>
                            </li>
                            @auth
                                @if(in_array(Auth::user()->role, ['hr', 'director', 'admin', 'pm']) || (isset(Auth::user()->metadata['account_types']) && in_array('employer', Auth::user()->metadata['account_types'])))
                                @php
                                    $user = Auth::user();
                                    $avatarUrl = $user?->avatar_url ?? asset('assets/img/avatar_detail.jpg');
                                    $roleLabel = match($user->role) {
                                        'director' => 'Director',
                                        'admin' => 'Admin',
                                        'pm' => 'Project Manager',
                                        default => 'Tuyển dụng / HR',
                                    };
                                @endphp
                                <li class="employer-user-menu" @click.outside="openEmployerUserMenu = false">
                                    <button
                                        type="button"
                                        class="employer-user-trigger"
                                        aria-label="Mở menu tài khoản"
                                        :aria-expanded="openEmployerUserMenu.toString()"
                                        @click="openEmployerUserMenu = !openEmployerUserMenu"
                                    >
                                        @if($user && $user->avatar)
                                            <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" onerror="this.onerror=null; this.src='{{ asset('assets/img/avatar_detail.jpg') }}';">
                                        @else
                                            <i class="fa fa-user"></i>
                                        @endif
                                    </button>

                                    <div class="employer-user-dropdown" x-show="openEmployerUserMenu" x-transition.opacity.duration.150ms>
                                        <!-- Profile Header Card -->
                                        <div class="employer-user-dropdown-header">
                                            <img src="{{ $avatarUrl }}" alt="{{ $user?->name }}" onerror="this.onerror=null; this.src='{{ asset('assets/img/avatar_detail.jpg') }}';">
                                            <div class="employer-user-dropdown-info">
                                                <div class="employer-user-dropdown-name">{{ $user?->name ?? 'Nhà tuyển dụng' }}</div>
                                                <div class="employer-user-dropdown-role">
                                                    <i class="fa fa-shield text-primary"></i> {{ $roleLabel }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Menu Items -->
                                        <div class="employer-user-dropdown-menu-list">
                                            <a href="{{ route('employers.dashboard') }}" class="employer-user-dropdown-item">
                                                <div class="employer-user-dropdown-item-left">
                                                    <div class="employer-user-dropdown-icon" style="background: #e0f2fe; color: #0284c7;">
                                                        <i class="fa fa-tachometer"></i>
                                                    </div>
                                                    <span>Bảng điều khiển</span>
                                                </div>
                                                <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                            </a>

                                            <a href="{{ route('employers.company_profile') }}" class="employer-user-dropdown-item">
                                                <div class="employer-user-dropdown-item-left">
                                                    <div class="employer-user-dropdown-icon" style="background: #ede9fe; color: #7c3aed;">
                                                        <i class="fa fa-building-o"></i>
                                                    </div>
                                                    <span>Hồ sơ chi nhánh</span>
                                                </div>
                                                <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                            </a>

                                            <a href="{{ route('employers.manage_candidates') }}" class="employer-user-dropdown-item">
                                                <div class="employer-user-dropdown-item-left">
                                                    <div class="employer-user-dropdown-icon" style="background: #ecfdf5; color: #059669;">
                                                        <i class="fa fa-users"></i>
                                                    </div>
                                                    <span>Quản lý ứng viên</span>
                                                </div>
                                                <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                            </a>

                                            <a href="{{ route('employers.post_job') }}" class="employer-user-dropdown-item">
                                                <div class="employer-user-dropdown-item-left">
                                                    <div class="employer-user-dropdown-icon" style="background: #fff7ed; color: #ea580c;">
                                                        <i class="fa fa-plus-circle"></i>
                                                    </div>
                                                    <span>Đăng tin tuyển dụng</span>
                                                </div>
                                                <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                            </a>

                                            <a href="{{ route('employers.application_pipeline') }}" class="employer-user-dropdown-item">
                                                <div class="employer-user-dropdown-item-left">
                                                    <div class="employer-user-dropdown-icon" style="background: #fdf2f8; color: #db2777;">
                                                        <i class="fa fa-sitemap"></i>
                                                    </div>
                                                    <span>Pipeline tuyển dụng</span>
                                                </div>
                                                <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                            </a>

                                            @if(in_array($user->role, ['director', 'admin']))
                                                <a href="{{ route('director.approve_jobs') }}" class="employer-user-dropdown-item">
                                                    <div class="employer-user-dropdown-item-left">
                                                        <div class="employer-user-dropdown-icon" style="background: #fef3c7; color: #d97706;">
                                                            <i class="fa fa-check-square-o"></i>
                                                        </div>
                                                        <span>Duyệt tin tuyển dụng</span>
                                                    </div>
                                                    <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                                </a>
                                            @endif

                                            <a href="{{ route('employers.notifications') }}" class="employer-user-dropdown-item">
                                                <div class="employer-user-dropdown-item-left">
                                                    <div class="employer-user-dropdown-icon" style="background: #fef2f2; color: #ef4444;">
                                                        <i class="fa fa-bell-o"></i>
                                                    </div>
                                                    <span>Thông báo hệ thống</span>
                                                </div>
                                                @if($unreadNotificationCount > 0)
                                                    <span class="badge rounded-pill bg-danger" style="font-size: 10.5px; padding: 2px 6px; font-weight: 800;">{{ $unreadNotificationCount }}</span>
                                                @else
                                                    <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                                @endif
                                            </a>

                                            <div class="employer-user-dropdown-divider"></div>

                                            @if(!($canCandidateAccess ?? false))
                                                <a href="{{ route('candidates.register') }}" class="employer-user-dropdown-item">
                                                    <div class="employer-user-dropdown-item-left">
                                                        <div class="employer-user-dropdown-icon" style="background: #f1f5f9; color: #475569;">
                                                            <i class="fa fa-user-plus"></i>
                                                        </div>
                                                        <span>Kích hoạt hồ sơ ứng viên</span>
                                                    </div>
                                                    <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('candidates.candidate_dashboard') }}" class="employer-user-dropdown-item">
                                                    <div class="employer-user-dropdown-item-left">
                                                        <div class="employer-user-dropdown-icon" style="background: #f1f5f9; color: #475569;">
                                                            <i class="fa fa-user-o"></i>
                                                        </div>
                                                        <span>Chuyển sang Khu ứng viên</span>
                                                    </div>
                                                    <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                                </a>
                                            @endif

                                            <div class="employer-user-dropdown-divider"></div>

                                            <livewire:client.logout-button />
                                        </div>
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

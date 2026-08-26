<header class="jobguru-header-area stick-top forsticky page-header client-app-header app-header-candidate" role="banner" x-data="{ openUserMenu: false }">
    <style>
        @media (min-width: 992px) {
            .app-header-candidate .row {
                flex-wrap: nowrap;
            }

            .app-header-candidate .col-lg-2 {
                flex: 0 0 150px;
                max-width: 150px;
            }

            .app-header-candidate .col-lg-6 {
                flex: 1 1 auto;
                max-width: none;
                width: auto;
                min-width: 0;
            }

            .app-header-candidate .col-lg-4 {
                flex: 0 0 auto;
                max-width: none;
                width: auto;
            }

            .app-header-candidate .header-menu {
                overflow: visible;
            }

            .app-header-candidate #jobguru_navigation {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 2px;
                flex-wrap: nowrap;
            }

            .app-header-candidate #jobguru_navigation > li {
                flex: 0 0 auto;
            }

            .app-header-candidate #jobguru_navigation > li > a {
                display: inline-flex !important;
                align-items: center;
                white-space: nowrap !important;
                line-height: 1.2 !important;
                padding-left: 7px !important;
                padding-right: 7px !important;
            }
        }

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

        .candidate-user-trigger:hover {
            transform: translateY(-2px);
            border-color: #f37021;
            box-shadow: 0 8px 20px rgba(243, 112, 33, 0.18);
        }

        .candidate-user-trigger img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .candidate-user-dropdown {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            width: 270px;
            padding: 8px;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.16), 0 4px 16px -2px rgba(15, 23, 42, 0.06);
            z-index: 10000;
            text-align: left;
        }

        .candidate-user-dropdown-header {
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .candidate-user-dropdown-header img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            flex-shrink: 0;
        }

        .candidate-user-dropdown-info {
            min-width: 0;
            flex: 1;
        }

        .candidate-user-dropdown-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
        }

        .candidate-user-dropdown-role {
            font-size: 11px;
            color: #f37021;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .candidate-user-dropdown-menu-list {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .candidate-user-dropdown-item {
            display: flex;
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

        .candidate-user-dropdown-item:hover {
            background: #f8fafc;
            color: #f37021 !important;
            transform: translateX(2px);
        }

        .candidate-user-dropdown-item-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .candidate-user-dropdown-icon {
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

        .candidate-user-dropdown-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 6px 0;
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

        @media (max-width: 991.98px) {
            .app-header-candidate .row {
                justify-content: space-between;
            }

            .app-header-candidate .col-lg-2 {
                width: 100% !important;
                max-width: none;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .app-header-candidate .col-lg-6,
            .app-header-candidate .col-lg-4 {
                display: none !important;
            }

            .app-header-candidate .site-logo {
                margin: 0 !important;
                padding: 0 !important;
            }

            .app-header-candidate .site-logo img {
                max-width: 136px;
                height: auto;
            }

            .app-header-candidate .jobguru-responsive-menu {
                display: flex;
                justify-content: flex-end;
                margin-left: auto;
            }
        }

        @media (min-width: 992px) and (max-width: 1320px) {
            .app-header-candidate #jobguru_navigation > li:nth-child(6) {
                display: none;
            }
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
                                <li><a href="{{ route('candidates.browse_companies') }}">Chi nhánh</a></li>
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
                                <div class="fpt-role-segmented-switcher">
                                    <a
                                        href="{{ route('candidates.browse_job') }}"
                                        class="fpt-role-seg-btn is-active"
                                        title="Khu vực Ứng viên"
                                    >
                                        <i class="fa fa-user-circle-o"></i>
                                        <span>Ứng viên</span>
                                    </a>
                                    <a
                                        href="{{ ($canEmployerAccess ?? false) ? route('employers.dashboard') : route('employers.portal') }}"
                                        class="fpt-role-seg-btn"
                                        title="Chuyển sang Cổng Tuyển dụng"
                                    >
                                        <i class="fa fa-building-o"></i>
                                        <span>Tuyển dụng</span>
                                    </a>
                                </div>
                            </li>
                            @if($canCandidateAccess ?? false)
                                @php
                                    $user = Auth::user();
                                    $avatarUrl = $user?->avatar_url ?? asset('assets/img/avatar_detail.jpg');
                                @endphp
                                <li class="candidate-user-menu" @click.outside="openUserMenu = false">
                                    <button
                                        type="button"
                                        class="candidate-user-trigger"
                                        aria-label="Mở menu tài khoản"
                                        :aria-expanded="openUserMenu.toString()"
                                        @click="openUserMenu = !openUserMenu"
                                    >
                                        @if($user && $user->avatar)
                                            <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" onerror="this.onerror=null; this.src='{{ asset('assets/img/avatar_detail.jpg') }}';">
                                        @else
                                            <i class="fa fa-user"></i>
                                        @endif
                                    </button>

                                    <div class="candidate-user-dropdown" x-show="openUserMenu" x-transition.opacity.duration.150ms>
                                        <!-- Profile Header Card -->
                                        <div class="candidate-user-dropdown-header">
                                            <img src="{{ $avatarUrl }}" alt="{{ $user?->name }}" onerror="this.onerror=null; this.src='{{ asset('assets/img/avatar_detail.jpg') }}';">
                                            <div class="candidate-user-dropdown-info">
                                                <div class="candidate-user-dropdown-name">{{ $user?->name ?? 'Ứng viên' }}</div>
                                                <div class="candidate-user-dropdown-role">
                                                    <i class="fa fa-check-circle text-success"></i> Ứng viên FPT
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Menu Items -->
                                        <div class="candidate-user-dropdown-menu-list">
                                            <a href="{{ route('candidates.candidate_dashboard') }}" class="candidate-user-dropdown-item">
                                                <div class="candidate-user-dropdown-item-left">
                                                    <div class="candidate-user-dropdown-icon" style="background: #e0f2fe; color: #0284c7;">
                                                        <i class="fa fa-tachometer"></i>
                                                    </div>
                                                    <span>Tổng quan hồ sơ</span>
                                                </div>
                                                <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                            </a>

                                            <a href="{{ route('candidates.candidate_profile') }}" class="candidate-user-dropdown-item">
                                                <div class="candidate-user-dropdown-item-left">
                                                    <div class="candidate-user-dropdown-icon" style="background: #ede9fe; color: #7c3aed;">
                                                        <i class="fa fa-user-o"></i>
                                                    </div>
                                                    <span>Cập nhật hồ sơ</span>
                                                </div>
                                                <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                            </a>

                                            <a href="{{ route('candidates.manage_jobs') }}" class="candidate-user-dropdown-item">
                                                <div class="candidate-user-dropdown-item-left">
                                                    <div class="candidate-user-dropdown-icon" style="background: #ecfdf5; color: #059669;">
                                                        <i class="fa fa-paper-plane-o"></i>
                                                    </div>
                                                    <span>Đơn đã ứng tuyển</span>
                                                </div>
                                                <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                            </a>

                                            <a href="{{ route('candidates.cv_builder') }}" class="candidate-user-dropdown-item">
                                                <div class="candidate-user-dropdown-item-left">
                                                    <div class="candidate-user-dropdown-icon" style="background: #fff7ed; color: #ea580c;">
                                                        <i class="fa fa-magic"></i>
                                                    </div>
                                                    <span>Tạo CV Online AI</span>
                                                </div>
                                                <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                            </a>

                                            @php
                                                $headerUnreadCount = Auth::check() ? \App\Models\UserNotification::where('user_id', Auth::id())->whereNull('read_at')->count() : 0;
                                            @endphp
                                            <a href="{{ route('candidates.notifications') }}" class="candidate-user-dropdown-item">
                                                <div class="candidate-user-dropdown-item-left">
                                                    <div class="candidate-user-dropdown-icon" style="background: #fef2f2; color: #ef4444;">
                                                        <i class="fa fa-bell-o"></i>
                                                    </div>
                                                    <span>Thông báo hệ thống</span>
                                                </div>
                                                @if($headerUnreadCount > 0)
                                                    <span class="badge rounded-pill bg-danger" style="font-size: 10.5px; padding: 2px 6px; font-weight: 800;">{{ $headerUnreadCount }}</span>
                                                @else
                                                    <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                                @endif
                                            </a>

                                            <div class="candidate-user-dropdown-divider"></div>

                                            @if(!($canEmployerAccess ?? false))
                                                <a href="{{ route('employers.register') }}" class="candidate-user-dropdown-item">
                                                    <div class="candidate-user-dropdown-item-left">
                                                        <div class="candidate-user-dropdown-icon" style="background: #f1f5f9; color: #475569;">
                                                            <i class="fa fa-briefcase"></i>
                                                        </div>
                                                        <span>Đăng ký nhà tuyển dụng</span>
                                                    </div>
                                                    <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('employers.dashboard') }}" class="candidate-user-dropdown-item">
                                                    <div class="candidate-user-dropdown-item-left">
                                                        <div class="candidate-user-dropdown-icon" style="background: #fef3c7; color: #d97706;">
                                                            <i class="fa fa-building-o"></i>
                                                        </div>
                                                        <span>Khu nhà tuyển dụng</span>
                                                    </div>
                                                    <i class="fa fa-angle-right text-muted" style="font-size: 12px;"></i>
                                                </a>
                                            @endif

                                            <div class="candidate-user-dropdown-divider"></div>

                                            <livewire:client.logout-button />
                                        </div>
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

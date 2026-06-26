<div class="sidebar-shell">
    <div class="sidebar-shell__brand">
        <div class="sidebar-shell__mark">
            <i class="fa fa-suitcase"></i>
        </div>
        <div>
            <h3>Nhà tuyển dụng</h3>
            <p>Điều hướng hồ sơ, tin đăng và pipeline</p>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('employers.dashboard') ? 'is-active' : '' }}" href="{{ route('employers.dashboard') }}">
                <i class="fa fa-tachometer"></i>
                <span>Bảng điều khiển</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('employers.company_profile') ? 'is-active' : '' }}" href="{{ route('employers.company_profile') }}">
                <i class="fa fa-building"></i>
                <span>Hồ sơ công ty</span>
            </a>
        </li>

        @auth
            @if (in_array(Auth::user()->role, ['director', 'admin']))
                <li>
                    <a class="sidebar-nav__link {{ request()->routeIs('director.approve_jobs') ? 'is-active' : '' }}" href="{{ route('director.approve_jobs') }}">
                        <i class="fa fa-check-circle"></i>
                        <span>Duyệt tin</span>
                    </a>
                </li>
            @endif
        @endauth

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('employers.post_job') ? 'is-active' : '' }}" href="{{ route('employers.post_job') }}">
                <i class="fa fa-bullhorn"></i>
                <span>Đăng tin tuyển dụng</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('employers.manage_jobs') ? 'is-active' : '' }}" href="{{ route('employers.manage_jobs') }}">
                <i class="fa fa-briefcase"></i>
                <span>Quản lý tin đăng</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('employers.manage_candidates') ? 'is-active' : '' }}" href="{{ route('employers.manage_candidates') }}">
                <i class="fa fa-user-circle"></i>
                <span>Quản lý ứng viên</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('employers.application_pipeline') ? 'is-active' : '' }}" href="{{ route('employers.application_pipeline') }}">
                <i class="fa fa-columns"></i>
                <span>Pipeline ứng viên</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('employers.change_password') ? 'is-active' : '' }}" href="{{ route('employers.change_password') }}">
                <i class="fa fa-lock"></i>
                <span>Đổi mật khẩu</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-shell__footer">
        <livewire:client.logout-button class="sidebar-logout" />
    </div>
</div>

<div class="sidebar-shell">
    <div class="sidebar-shell__brand">
        <div class="sidebar-shell__mark">
            <i class="fa fa-user"></i>
        </div>
        <div>
            <h3>Hồ sơ ứng viên</h3>
            <p>Quản lý hồ sơ, ứng tuyển và bảo mật</p>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('candidates.candidate_dashboard') ? 'is-active' : '' }}" href="{{ route('candidates.candidate_dashboard') }}">
                <i class="fa fa-tachometer"></i>
                <span>Bảng điều khiển</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('candidates.manage_cv') ? 'is-active' : '' }}" href="{{ route('candidates.manage_cv') }}">
                <i class="fa fa-file-text-o" style="color: #38bdf8;"></i>
                <span>Quản lý CV</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('candidates.candidate_profile') ? 'is-active' : '' }}" href="{{ route('candidates.candidate_profile') }}">
                <i class="fa fa-id-card"></i>
                <span>Hồ sơ của tôi</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('candidates.cv_builder') ? 'is-active' : '' }}" href="{{ route('candidates.cv_builder') }}">
                <i class="fa fa-magic" style="color: #f37021;"></i>
                <span>Tạo CV Online (AI)</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('candidates.messages') ? 'is-active' : '' }}" href="{{ route('candidates.messages') }}">
                <i class="fa fa-comments"></i>
                <span>Tin nhắn</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('candidates.manage_jobs') ? 'is-active' : '' }}" href="{{ route('candidates.manage_jobs') }}">
                <i class="fa fa-briefcase"></i>
                <span>Việc đã ứng tuyển</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('candidates.earnings') ? 'is-active' : '' }}" href="{{ route('candidates.earnings') }}">
                <i class="fa fa-line-chart"></i>
                <span>Thu nhập & Thưởng</span>
            </a>
        </li>

        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('candidates.change_password') ? 'is-active' : '' }}" href="{{ route('candidates.change_password') }}">
                <i class="fa fa-shield"></i>
                <span>Bảo mật & Mật khẩu</span>
            </a>
        </li>
        <li>
            <a class="sidebar-nav__link {{ request()->routeIs('candidates.notifications') ? 'is-active' : '' }}" href="{{ route('candidates.notifications') }}">
                <i class="fa fa-bell"></i>
                <span>Thông báo</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-shell__footer">
        <livewire:client.logout-button class="sidebar-logout" />
    </div>
</div>

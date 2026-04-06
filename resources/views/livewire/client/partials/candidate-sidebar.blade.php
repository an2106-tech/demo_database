<div class="dashboard-left">
    <ul class="dashboard-menu">
        <li class="{{ request()->routeIs('candidates.candidate_dashboard') ? 'active' : '' }}">
            <a href="{{ route('candidates.candidate_dashboard') }}">
                <i class="fa fa-tachometer"></i>
                Bảng điều khiển
            </a>
        </li>

        <li class="{{ request()->routeIs('candidates.candidate_profile') ? 'active' : '' }}">
            <a href="{{ route('candidates.candidate_profile') }}">
                <i class="fa fa-users"></i>
                Hồ sơ của tôi
            </a>
        </li>

        <li class="{{ request()->routeIs('candidates.messages') ? 'active' : '' }}">
            <a href="{{ route('candidates.messages') }}">
                <i class="fa fa-envelope-open"></i>
                Tin nhắn
            </a>
        </li>

        <li class="{{ request()->routeIs('candidates.manage_jobs') ? 'active' : '' }}">
            <a href="{{ route('candidates.manage_jobs') }}">
                <i class="fa fa-briefcase"></i>
                Quản lý công việc
            </a>
        </li>

        <li class="{{ request()->routeIs('candidates.earnings') ? 'active' : '' }}">
            <a href="{{ route('candidates.earnings') }}">
                <i class="fa fa-rocket"></i>
                Thu nhập
            </a>
        </li>

        <li class="{{ request()->routeIs('candidates.change_password') ? 'active' : '' }}">
            <a href="{{ route('candidates.change_password') }}">
                <i class="fa fa-lock"></i>
                Đổi mật khẩu
            </a>
        </li>

        <li>
            <div style="padding: 12px 15px;">
                <livewire:client.logout-button />
            </div>
        </li>
    </ul>
</div>


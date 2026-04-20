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
                <i class="fa fa-user-circle"></i>
                Hồ sơ cá nhân
            </a>
        </li>

        <li class="{{ request()->routeIs('candidates.messages') ? 'active' : '' }}">
            <a href="{{ route('candidates.messages') }}">
                <i class="fa fa-comments"></i>
                Tin nhắn
            </a>
        </li>

        <li class="{{ request()->routeIs('candidates.manage_jobs') ? 'active' : '' }}">
            <a href="{{ route('candidates.manage_jobs') }}">
                <i class="fa fa-briefcase"></i>
                Việc làm đã ứng tuyển
            </a>
        </li>

        <li class="{{ request()->routeIs('candidates.earnings') ? 'active' : '' }}">
            <a href="{{ route('candidates.earnings') }}">
                <i class="fa fa-money"></i>
                Thu nhập & Thưởng
            </a>
        </li>

        <li class="{{ request()->routeIs('candidates.change_password') ? 'active' : '' }}">
            <a href="{{ route('candidates.change_password') }}">
                <i class="fa fa-shield"></i>
                Bảo mật & Mật khẩu
            </a>
        </li>

        <li style="margin-top: 20px; padding: 0 15px;">
            <div style="border-top: 1px solid #f1f5f9; padding-top: 20px;">
                <livewire:client.logout-button />
            </div>
        </li>
    </ul>
</div>

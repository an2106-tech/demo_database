<div class="dashboard-left">
    <ul class="dashboard-menu">
        <li class="{{ request()->routeIs('employers.dashboard') ? 'active' : '' }}">
            <a href="{{ route('employers.dashboard') }}">
                <i class="fa fa-tachometer"></i>
                Bảng Điều Khiển
            </a>
        </li>

        <li class="{{ request()->routeIs('employers.company_profile') ? 'active' : '' }}">
            <a href="{{ route('employers.company_profile') }}">
                <i class="fa fa-users"></i>
                Hồ Sơ Công Ty
            </a>
        </li>

        @auth
            @if(in_array(Auth::user()->role, ['director', 'admin']))
            <li class="{{ request()->routeIs('director.approve_jobs') ? 'active' : '' }}">
                <a href="{{ route('director.approve_jobs') }}">
                    <i class="fa fa-check-circle"></i>
                    Duyệt Tin
                </a>
            </li>
            @endif
        @endauth

        <li class="{{ request()->routeIs('employers.post_job') ? 'active' : '' }}">
            <a href="{{ route('employers.post_job') }}">
                <i class="fa fa-bullhorn"></i>
                Đăng Tin Tuyển Dụng
            </a>
        </li>

        <li class="{{ request()->routeIs('employers.manage_jobs') ? 'active' : '' }}">
            <a href="{{ route('employers.manage_jobs') }}">
                <i class="fa fa-briefcase"></i>
                Quản Lý Tin Đăng
            </a>
        </li>

        <li class="{{ request()->routeIs('employers.manage_candidates') ? 'active' : '' }}">
            <a href="{{ route('employers.manage_candidates') }}">
                <i class="fa fa-user-circle"></i>
                Quản Lý Ứng Viên
            </a>
        </li>

        <li class="{{ request()->routeIs('employers.application_pipeline') ? 'active' : '' }}">
            <a href="{{ route('employers.application_pipeline') }}">
                <i class="fa fa-columns"></i>
                Pipeline Ứng Viên
            </a>
        </li>


        <li class="{{ request()->routeIs('employers.change_password') ? 'active' : '' }}">
            <a href="{{ route('employers.change_password') }}">
                <i class="fa fa-lock"></i>
                Đổi Mật Khẩu
            </a>
        </li>

        <li>
            <div style="padding: 12px 15px;">
                <livewire:client.logout-button />
            </div>
        </li>
    </ul>
</div>


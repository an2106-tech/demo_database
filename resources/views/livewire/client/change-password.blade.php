<div>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('candidates.candidate_dashboard') }}">Ứng viên</a></li>
            <li class="active">Bảo mật & Mật khẩu</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right">
                        <div class="premium-panel" style="padding: 28px;">
                            <div class="manage-jobs-heading" style="margin-bottom: 22px;">
                                <span style="display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:999px;background:rgba(243,112,33,.08);color:#9a3412;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Bảo mật</span>
                                <h3 style="margin: 10px 0 0; color: #0f172a;">Thiết lập mật khẩu</h3>
                                <p style="margin: 8px 0 0; color: #64748b; max-width: 760px;">
                                    Đảm bảo mật khẩu của bạn an toàn bằng cách sử dụng tối thiểu 8 ký tự và không trùng với mật khẩu hiện tại.
                                </p>
                            </div>

                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form style="margin-top: 1.5rem; max-width: 760px;" wire:submit.prevent="updatePassword" novalidate>
                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label for="old_pass">Mật khẩu hiện tại</label>
                                        <input type="password" placeholder="••••••••" id="old_pass" autocomplete="current-password" wire:model.defer="current_password">
                                        @error('current_password') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label for="new_pass">Mật khẩu mới</label>
                                        <input type="password" placeholder="••••••••" id="new_pass" autocomplete="new-password" wire:model.defer="password">
                                        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label for="confirm_pass">Xác nhận mật khẩu mới</label>
                                        <input type="password" placeholder="••••••••" id="confirm_pass" autocomplete="new-password" wire:model.defer="password_confirmation">
                                    </div>
                                </div>
                                <div class="submit-resume" style="margin-top: 1.5rem;">
                                    <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword">Cập nhật mật khẩu</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

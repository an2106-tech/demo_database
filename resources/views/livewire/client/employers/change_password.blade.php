<div>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li class="active">Đổi mật khẩu</li>
        </ul>
    </div>
    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-lg-3 col-md-4 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="dashboard-right">
                        <div class="premium-panel">
                            <div class="manage-jobs-heading">
                                <h3>Thiết lập mật khẩu</h3>
                                <p style="margin: 10px 0 0; color: #64748b;">
                                    Đảm bảo mật khẩu của bạn có độ dài tối thiểu 8 ký tự và không trùng với mật khẩu hiện tại.
                                </p>
                            </div>

                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form style="margin-top: 2rem;" wire:submit.prevent="updatePassword" novalidate>
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

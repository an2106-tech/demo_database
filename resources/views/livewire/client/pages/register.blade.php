<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Đăng ký</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="jobguru-login-area section_70">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 auth-card">
                    <div class="login-box" style="max-width:100%;">
                        @if(auth()->check() && auth()->user()->role === 'hr' && $role === 'candidate')
                            <div class="login-title text-center auth-header">
                                <h3>Đăng ký ứng viên</h3>
                                <p>Các mục có dấu <span class="text-danger">*</span> là bắt buộc.</p>
                            </div>

                            <div class="auth-note">
                                Tài khoản HR của bạn có thể kích hoạt thêm chế độ ứng viên bằng cùng email. Sau khi kích hoạt, bạn có thể dùng nút chuyển chế độ trên header để qua menu ứng viên.
                            </div>

                            <form wire:submit.prevent="register" class="auth-form">
                                <div class="auth-actions single-login-field">
                                    <button type="submit" class="auth-submit-btn">Kích hoạt tài khoản ứng viên</button>
                                </div>
                            </form>

                            <div class="auth-links">
                                <a href="{{ route('home') }}">Quay lại trang chủ</a>
                            </div>
                        @else
                            @if($role === 'employer')
                                <div class="login-title text-center auth-header">
                                    <h3>Đăng ký nhà tuyển dụng</h3>
                                    <p>Các mục có dấu <span class="text-danger">*</span> là bắt buộc.</p>
                                </div>

                                <form wire:submit.prevent="register" class="auth-form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="hr-name">Họ và tên <span class="text-danger">*</span></label>
                                                <input id="hr-name" class="form-control" type="text" placeholder="Nguyễn Văn A" wire:model="name" autocomplete="name">
                                                @error('name') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="hr-email">Email <span class="text-danger">*</span></label>
                                                <input id="hr-email" class="form-control" type="email" placeholder="you@example.com" wire:model="email" autocomplete="email" inputmode="email">
                                                @error('email') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="hr-phone">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                                <input id="hr-phone" class="form-control" type="tel" placeholder="0xxxxxxxxx" wire:model="phone" autocomplete="tel" inputmode="tel">
                                                @error('phone') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="hr-province">Tỉnh/Thành <span class="text-danger">*</span></label>
                                                <select id="hr-province" class="form-select" wire:model.live="province">
                                                    <option value="">-- Chọn tỉnh/thành --</option>
                                                    @foreach($provinceOptions as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error('province') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="hr-branch">Chi nhánh <span class="text-danger">*</span></label>
                                                <select
                                                    id="hr-branch"
                                                    class="form-select"
                                                    wire:model.live="branch_id"
                                                    wire:key="hr-branch-{{ $province !== '' ? $province : 'none' }}"
                                                    @disabled(empty($province))
                                                >
                                                    <option value="">{{ empty($province) ? '-- Chọn tỉnh/thành trước --' : '-- Chọn chi nhánh --' }}</option>
                                                    @foreach($branches as $b)
                                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('branch_id') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="hr-address">Địa chỉ <span class="text-danger">*</span></label>
                                                <input id="hr-address" class="form-control" type="text" placeholder="Số nhà, đường, phường/xã..." wire:model="address" autocomplete="street-address">
                                                @error('address') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="hr-password">Mật khẩu <span class="text-danger">*</span></label>
                                                <input id="hr-password" class="form-control" type="password" placeholder="Tối thiểu 8 ký tự" wire:model="password" autocomplete="new-password">
                                                @error('password') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="hr-password-confirm">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                                <input id="hr-password-confirm" class="form-control" type="password" placeholder="Nhập lại mật khẩu" wire:model="password_confirmation" autocomplete="new-password">
                                                @error('password_confirmation') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="terms-field">
                                        <input type="checkbox" id="terms-hr" wire:model="terms_accepted">
                                        <div class="terms-copy">
                                            <label for="terms-hr">Chấp nhận các điều khoản và điều kiện <span class="text-danger">*</span></label>
                                            @error('terms_accepted') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div class="auth-actions single-login-field">
                                        <button type="submit" class="auth-submit-btn">Tạo tài khoản nhà tuyển dụng</button>
                                    </div>

                                    <div class="auth-note">
                                        Tài khoản HR dùng để đăng tin và quản lý ứng viên. Sau khi tạo tài khoản, bạn có thể cập nhật thêm thông tin trong hồ sơ.
                                    </div>
                                </form>

                                <div class="auth-links">
                                    <a href="{{ route('auth.login', ['role' => 'employer']) }}">Bạn đã có tài khoản? Đăng nhập</a>
                                    <a href="{{ route('auth.sign_up', ['role' => 'candidate']) }}">Bạn là ứng viên? Đăng ký ứng viên</a>
                                </div>
                            @else
                                <div class="auth-shell auth-shell--candidate">
                                    <div class="auth-shell__aside">
                                        <div class="auth-shell__badge">Ứng viên</div>
                                        <h3 class="auth-shell__title">Tạo tài khoản để ứng tuyển nhanh</h3>
                                        <p class="auth-shell__subtitle">Lưu việc yêu thích, nhận gợi ý phù hợp và theo dõi trạng thái hồ sơ.</p>
                                        <ul class="auth-shell__list">
                                            <li><i class="fa fa-check-circle"></i> Ứng tuyển 1 chạm với hồ sơ đã lưu</li>
                                            <li><i class="fa fa-check-circle"></i> Nhận thông báo việc làm theo kỹ năng</li>
                                            <li><i class="fa fa-check-circle"></i> Theo dõi lịch phỏng vấn &amp; phản hồi</li>
                                        </ul>
                                    </div>
                                    <div class="auth-shell__main">
                                        <div class="auth-shell__header">
                                            <h3>Đăng ký ứng viên</h3>
                                            <p>Các mục có dấu <span class="text-danger">*</span> là bắt buộc.</p>
                                        </div>

                                        <form wire:submit.prevent="register" class="auth-form">
                                    <div class="row candidate-grid">
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="cand-name">Họ và tên <span class="text-danger">*</span></label>
                                                <input id="cand-name" class="form-control" type="text" placeholder="Nguyễn Văn A" wire:model="name" autocomplete="name">
                                                @error('name') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="cand-email">Email <span class="text-danger">*</span></label>
                                                <input id="cand-email" class="form-control" type="email" placeholder="you@example.com" wire:model="email" autocomplete="email" inputmode="email">
                                                @error('email') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row candidate-grid">
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="cand-phone">Số điện thoại <span class="text-danger">*</span></label>
                                                <input id="cand-phone" class="form-control" type="tel" placeholder="0xxxxxxxxx" wire:model="phone" autocomplete="tel" inputmode="tel">
                                                @error('phone') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="cand-password">Mật khẩu <span class="text-danger">*</span></label>
                                                <input id="cand-password" class="form-control" type="password" placeholder="Tối thiểu 8 ký tự" wire:model="password" autocomplete="new-password">
                                                @error('password') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row candidate-grid">
                                        <div class="col-md-6">
                                            <div class="single-login-field">
                                                <label for="cand-password-confirm">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                                <input id="cand-password-confirm" class="form-control" type="password" placeholder="Nhập lại mật khẩu" wire:model="password_confirmation" autocomplete="new-password">
                                                @error('password_confirmation') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6 d-flex align-items-end">
                                            <div class="auth-note" style="margin-top:0; width:100%;">
                                                Sau khi tạo tài khoản, bạn có thể cập nhật hồ sơ và nộp hồ sơ ứng tuyển.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="terms-field">
                                        <input type="checkbox" id="terms-candidate" wire:model="terms_accepted">
                                        <div class="terms-copy">
                                            <label for="terms-candidate">Chấp nhận các điều khoản và điều kiện <span class="text-danger">*</span></label>
                                            @error('terms_accepted') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                            <div class="auth-actions single-login-field">
                                                <button type="submit" class="auth-submit-btn">Tạo tài khoản ứng viên</button>
                                            </div>
                                        </form>

                                        <div class="auth-links">
                                            <a href="{{ route('auth.login', ['role' => 'candidate']) }}">Bạn đã có tài khoản? Đăng nhập</a>
                                            <a href="{{ route('auth.sign_up', ['role' => 'employer']) }}">Bạn là nhà tuyển dụng? Đăng ký nhà tuyển dụng</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

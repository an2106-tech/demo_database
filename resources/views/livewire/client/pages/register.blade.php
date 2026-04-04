<div>
    <style>
        .auth-card {
            max-width: 920px;
            margin: 0 auto;
        }

        .auth-header h3 {
            margin: 0;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .auth-header p {
            margin: .5rem 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        .auth-form .single-login-field {
            margin-bottom: 14px;
        }

        .auth-form .single-login-field label {
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
            color: #0f172a;
        }

        .auth-form input.form-control,
        .auth-form select.form-select {
            width: 100%;
            height: 48px;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .auth-form input.form-control:focus,
        .auth-form select.form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 .2rem rgba(59, 130, 246, .15);
            outline: none;
        }

        .auth-form .invalid-text {
            margin: 6px 0 0;
            font-size: 13px;
        }

        .auth-actions {
            margin-top: 10px;
        }

        .auth-actions button {
            width: 100%;
            border-radius: 12px;
            font-weight: 800;
        }

        .auth-links {
            margin-top: 14px;
            display: grid;
            gap: 8px;
            text-align: center;
        }

        .auth-links a {
            color: #0ea5e9;
            font-weight: 700;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .auth-note {
            margin-top: 10px;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            color: #475569;
            font-size: 13px;
        }
    </style>

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
        <div class="breadcromb-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box-pagin">
                            <ul>
                                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                                <li><a href="#">Tài khoản</a></li>
                                <li class="active-breadcromb"><a href="#">Đăng ký</a></li>
                            </ul>
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
                        <div class="login-title text-center auth-header">
                            <h3>{{ $role === 'employer' ? 'Đăng ký nhà tuyển dụng' : 'Đăng ký ứng viên' }}</h3>
                            <p>Các mục có dấu <span class="text-danger">*</span> là bắt buộc.</p>
                        </div>

                        @if(auth()->check() && auth()->user()->role === 'hr' && $role === 'candidate')
                            <div class="auth-note">
                                Tài khoản HR của bạn có thể kích hoạt thêm chế độ Ứng viên (dùng chung email, không tạo tài khoản mới).
                            </div>

                            <form wire:submit.prevent="register" class="auth-form">
                                <div class="auth-actions single-login-field">
                                    <button type="submit">Kích hoạt tài khoản ứng viên</button>
                                </div>
                            </form>

                            <div class="auth-links">
                                <a href="{{ route('home') }}">Quay lại trang chủ</a>
                            </div>
                        @else
                        @if($role === 'employer')
                            <form wire:submit.prevent="register" class="auth-form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="single-login-field">
                                            <label for="hr-name">Họ và tên <span class="text-danger">*</span></label>
                                            <input id="hr-name" class="form-control" type="text" placeholder="Nguyễn Văn A"
                                                wire:model="name" autocomplete="name">
                                            @error('name') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-login-field">
                                            <label for="hr-email">Email <span class="text-danger">*</span></label>
                                            <input id="hr-email" class="form-control" type="email"
                                                placeholder="you@example.com" wire:model="email" autocomplete="email"
                                                inputmode="email">
                                            @error('email') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="single-login-field">
                                            <label for="hr-phone">Số điện thoại liên hệ <span
                                                    class="text-danger">*</span></label>
                                            <input id="hr-phone" class="form-control" type="tel" placeholder="0xxxxxxxxx"
                                                wire:model="phone" autocomplete="tel" inputmode="tel">
                                            @error('phone') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-login-field">
                                            <label for="hr-province">Tỉnh/Thành <span class="text-danger">*</span></label>
                                            <select id="hr-province" class="form-select" wire:model="province">
                                                <option value="">— Chọn tỉnh/thành —</option>
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
                                            <select id="hr-branch" class="form-select" wire:model="branch_id" @disabled(empty($province))>
                                                <option value="">{{ empty($province) ? '— Chọn tỉnh/thành trước —' : '— Chọn chi nhánh —' }}</option>
                                                @foreach($branches as $b)
                                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id') <p class="text-danger invalid-text">{{ $message }}</p>
                                            @enderror
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
                                            <input id="hr-password" class="form-control" type="password"
                                                placeholder="Tối thiểu 8 ký tự" wire:model="password"
                                                autocomplete="new-password">
                                            @error('password') <p class="text-danger invalid-text">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-login-field">
                                            <label for="hr-password-confirm">Xác nhận mật khẩu <span
                                                    class="text-danger">*</span></label>
                                            <input id="hr-password-confirm" class="form-control" type="password"
                                                placeholder="Nhập lại mật khẩu" wire:model="password_confirmation"
                                                autocomplete="new-password">
                                            @error('password_confirmation') <p class="text-danger invalid-text">
                                            {{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="remember-row single-login-field clearfix">
                                    <p class="checkbox remember">
                                        <input class="checkbox-spin" type="checkbox" id="terms-hr"
                                            wire:model="terms_accepted">
                                        <label for="terms-hr"><span></span>Chấp nhận các điều khoản & điều kiện <span
                                                class="text-danger">*</span></label>
                                    </p>
                                    @error('terms_accepted') <p class="text-danger invalid-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="auth-actions single-login-field">
                                    <button type="submit">Tạo tài khoản nhà tuyển dụng</button>
                                </div>

                                <div class="auth-note">
                                    Tài khoản HR dùng để đăng tin và quản lý ứng viên. Sau khi tạo tài khoản, bạn có thể cập
                                    nhật thêm thông tin trong hồ sơ.
                                </div>
                            </form>

                            <div class="auth-links">
                                <a href="{{ route('auth.login', ['role' => 'employer']) }}">Bạn đã có tài khoản? Đăng
                                    nhập</a>
                                <a href="{{ route('auth.sign_up', ['role' => 'candidate']) }}">Bạn là ứng viên? Đăng ký ứng
                                    viên</a>
                            </div>
                        @else
                            <form wire:submit.prevent="register" class="auth-form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="single-login-field">
                                            <label for="cand-name">Họ và tên <span class="text-danger">*</span></label>
                                            <input id="cand-name" class="form-control" type="text"
                                                placeholder="Nguyễn Văn A" wire:model="name" autocomplete="name">
                                            @error('name') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-login-field">
                                            <label for="cand-email">Email <span class="text-danger">*</span></label>
                                            <input id="cand-email" class="form-control" type="email"
                                                placeholder="you@example.com" wire:model="email" autocomplete="email"
                                                inputmode="email">
                                            @error('email') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="single-login-field">
                                            <label for="cand-phone">Số điện thoại <span class="text-danger">*</span></label>
                                            <input id="cand-phone" class="form-control" type="tel" placeholder="0xxxxxxxxx"
                                                wire:model="phone" autocomplete="tel" inputmode="tel">
                                            @error('phone') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="single-login-field">
                                            <label for="cand-password">Mật khẩu <span class="text-danger">*</span></label>
                                            <input id="cand-password" class="form-control" type="password"
                                                placeholder="Tối thiểu 8 ký tự" wire:model="password"
                                                autocomplete="new-password">
                                            @error('password') <p class="text-danger invalid-text">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-login-field">
                                            <label for="cand-password-confirm">Xác nhận mật khẩu <span
                                                    class="text-danger">*</span></label>
                                            <input id="cand-password-confirm" class="form-control" type="password"
                                                placeholder="Nhập lại mật khẩu" wire:model="password_confirmation"
                                                autocomplete="new-password">
                                            @error('password_confirmation') <p class="text-danger invalid-text">
                                            {{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="remember-row single-login-field clearfix">
                                    <p class="checkbox remember">
                                        <input class="checkbox-spin" type="checkbox" id="terms-candidate"
                                            wire:model="terms_accepted">
                                        <label for="terms-candidate"><span></span>Chấp nhận các điều khoản & điều kiện <span
                                                class="text-danger">*</span></label>
                                    </p>
                                    @error('terms_accepted') <p class="text-danger invalid-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="auth-actions single-login-field">
                                    <button type="submit">Tạo tài khoản ứng viên</button>
                                </div>

                                <div class="auth-note">
                                    Sau khi tạo tài khoản, bạn có thể cập nhật hồ sơ và nộp hồ sơ ứng tuyển.
                                </div>
                            </form>

                            <div class="auth-links">
                                <a href="{{ route('auth.login', ['role' => 'candidate']) }}">Bạn đã có tài khoản? Đăng
                                    nhập</a>
                                <a href="{{ route('auth.sign_up', ['role' => 'employer']) }}">Bạn là nhà tuyển dụng? Đăng ký
                                    nhà tuyển dụng</a>
                            </div>
                        @endif
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

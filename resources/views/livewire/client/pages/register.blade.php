<div>
    <section class="auth-redesign-page">
        <div class="container auth-redesign-container">
            <div class="auth-redesign-frame auth-redesign-frame--{{ $role }}">
                <div class="auth-redesign-main">
                    @if(auth()->check() && auth()->user()->role === 'hr' && $role === 'candidate')
                        <div class="auth-redesign-card">
                            <div class="auth-redesign-card__header auth-redesign-card__header--compact">
                                <a href="{{ route('home') }}" class="auth-redesign-brand" aria-label="Trang chủ">
                                    <img src="{{ asset('assets/img/fe-logo.png') }}" alt="FPT Careers">
                                </a>
                                <h2>Kích hoạt tài khoản ứng viên</h2>
                            </div>

                            <form wire:submit.prevent="register" class="auth-redesign-form">
                                <button type="submit" class="auth-redesign-submit">Kích hoạt tài khoản ứng viên</button>
                            </form>

                            <div class="auth-redesign-footer">
                                <a href="{{ route('home') }}">Quay lại</a>
                            </div>
                        </div>
                    @elseif(auth()->check() && $role === 'employer')
                        <div class="auth-redesign-card">
                            <div class="auth-redesign-card__header auth-redesign-card__header--compact">
                                <a href="{{ route('home') }}" class="auth-redesign-brand" aria-label="Trang chủ">
                                    <img src="{{ asset('assets/img/fe-logo.png') }}" alt="FPT Careers">
                                </a>
                                <h2>Kích hoạt tài khoản nhà tuyển dụng</h2>
                            </div>

                            <form wire:submit.prevent="register" class="auth-redesign-form">
                                <div class="auth-redesign-grid auth-redesign-grid--two">
                                    <div class="auth-redesign-field">
                                        <label for="hr-name">Họ và tên phụ trách <span class="text-danger">*</span></label>
                                        <input id="hr-name" class="form-control" type="text" placeholder="Nguyễn Văn A" wire:model="name" autocomplete="name">
                                        @error('name') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="auth-redesign-field">
                                        <label for="hr-phone">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                        <input id="hr-phone" class="form-control" type="tel" placeholder="0xxxxxxxxx" wire:model="phone" autocomplete="tel" inputmode="tel">
                                        @error('phone') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="auth-redesign-field">
                                        <label for="hr-province">Tỉnh/Thành <span class="text-danger">*</span></label>
                                        <select id="hr-province" class="form-select" wire:model.live="province">
                                            <option value="">-- Chọn tỉnh/thành --</option>
                                            @foreach($provinceOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('province') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="auth-redesign-field">
                                        <label for="hr-branch">Chi nhánh <span class="text-danger">*</span></label>
                                        <select
                                            id="hr-branch"
                                            class="form-select"
                                            wire:model.live="branch_id"
                                            wire:key="hr-branch-auth-{{ $province !== '' ? $province : 'none' }}"
                                            @disabled(empty($province))
                                        >
                                            <option value="">{{ empty($province) ? '-- Chọn tỉnh/thành trước --' : '-- Chọn chi nhánh --' }}</option>
                                            @foreach($branches as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('branch_id') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="auth-redesign-field auth-redesign-field--full">
                                        <label for="hr-address">Địa chỉ <span class="text-danger">*</span></label>
                                        <input id="hr-address" class="form-control" type="text" placeholder="Số nhà, đường, phường/xã..." wire:model="address" autocomplete="street-address">
                                        @error('address') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <label class="auth-redesign-check auth-redesign-check--box">
                                    <input type="checkbox" id="terms-hr-auth" wire:model="terms_accepted">
                                    <span>Chấp nhận các điều khoản và điều kiện <span class="text-danger">*</span></span>
                                </label>
                                @error('terms_accepted') <p class="text-danger invalid-text">{{ $message }}</p> @enderror

                                <button type="submit" class="auth-redesign-submit">Kích hoạt khu nhà tuyển dụng</button>
                            </form>

                            <div class="auth-redesign-footer">
                                <a href="{{ route('candidates.candidate_dashboard') }}">Quay lại</a>
                            </div>
                        </div>
                    @else
                        @if($showRoleTabs ?? false)
                            <div class="auth-redesign-tabs" role="tablist" aria-label="Đăng ký theo vai trò">
                                <a href="{{ route('candidates.register') }}" wire:navigate class="auth-redesign-tab {{ $role === 'candidate' ? 'is-active' : '' }}" wire:click.prevent="setRole('candidate')">Ứng viên</a>
                                <a href="{{ route('employers.register') }}" wire:navigate class="auth-redesign-tab {{ $role === 'employer' ? 'is-active' : '' }}" wire:click.prevent="setRole('employer')">Nhà tuyển dụng</a>
                            </div>
                        @endif

                        <div class="auth-redesign-card">
                            <div class="auth-redesign-card__header auth-redesign-card__header--compact">
                                <a href="{{ route('home') }}" class="auth-redesign-brand" aria-label="Trang chủ">
                                    <img src="{{ asset('assets/img/fe-logo.png') }}" alt="FPT Careers">
                                </a>
                                <h2>{{ $role === 'employer' ? 'Đăng ký nhà tuyển dụng' : 'Đăng ký ứng viên' }}</h2>
                            </div>

                            <form wire:submit.prevent="register" class="auth-redesign-form">
                                @if($role === 'employer')
                                    <div class="auth-redesign-grid auth-redesign-grid--two">
                                        <div class="auth-redesign-field">
                                            <label for="hr-name">Họ và tên <span class="text-danger">*</span></label>
                                            <input id="hr-name" class="form-control" type="text" placeholder="Nguyễn Văn A" wire:model="name" autocomplete="name">
                                            @error('name') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="auth-redesign-field">
                                            <label for="hr-email">Email <span class="text-danger">*</span></label>
                                            <input id="hr-email" class="form-control" type="email" placeholder="you@example.com" wire:model="email" autocomplete="email" inputmode="email">
                                            @error('email') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="auth-redesign-field">
                                            <label for="hr-phone">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                            <input id="hr-phone" class="form-control" type="tel" placeholder="0xxxxxxxxx" wire:model="phone" autocomplete="tel" inputmode="tel">
                                            @error('phone') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="auth-redesign-field">
                                            <label for="hr-province">Tỉnh/Thành <span class="text-danger">*</span></label>
                                            <select id="hr-province" class="form-select" wire:model.live="province">
                                                <option value="">-- Chọn tỉnh/thành --</option>
                                                @foreach($provinceOptions as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('province') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="auth-redesign-field">
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

                                        <div class="auth-redesign-field">
                                            <label for="hr-address">Địa chỉ <span class="text-danger">*</span></label>
                                            <input id="hr-address" class="form-control" type="text" placeholder="Số nhà, đường, phường/xã..." wire:model="address" autocomplete="street-address">
                                            @error('address') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="auth-redesign-field">
                                            <label for="hr-password">Mật khẩu <span class="text-danger">*</span></label>
                                            <input id="hr-password" class="form-control" type="password" placeholder="Tối thiểu 8 ký tự" wire:model="password" autocomplete="new-password">
                                            @error('password') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="auth-redesign-field">
                                            <label for="hr-password-confirm">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                            <input id="hr-password-confirm" class="form-control" type="password" placeholder="Nhập lại mật khẩu" wire:model="password_confirmation" autocomplete="new-password">
                                            @error('password_confirmation') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                @else
                                    <div class="auth-redesign-grid auth-redesign-grid--two">
                                        <div class="auth-redesign-field">
                                            <label for="cand-name">Họ và tên <span class="text-danger">*</span></label>
                                            <input id="cand-name" class="form-control" type="text" placeholder="Nguyễn Văn A" wire:model="name" autocomplete="name">
                                            @error('name') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="auth-redesign-field">
                                            <label for="cand-email">Email <span class="text-danger">*</span></label>
                                            <input id="cand-email" class="form-control" type="email" placeholder="you@example.com" wire:model="email" autocomplete="email" inputmode="email">
                                            @error('email') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="auth-redesign-field">
                                            <label for="cand-phone">Số điện thoại <span class="text-danger">*</span></label>
                                            <input id="cand-phone" class="form-control" type="tel" placeholder="0xxxxxxxxx" wire:model="phone" autocomplete="tel" inputmode="tel">
                                            @error('phone') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="auth-redesign-field">
                                            <label for="cand-password">Mật khẩu <span class="text-danger">*</span></label>
                                            <input id="cand-password" class="form-control" type="password" placeholder="Tối thiểu 8 ký tự" wire:model="password" autocomplete="new-password">
                                            @error('password') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="auth-redesign-field">
                                            <label for="cand-password-confirm">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                            <input id="cand-password-confirm" class="form-control" type="password" placeholder="Nhập lại mật khẩu" wire:model="password_confirmation" autocomplete="new-password">
                                            @error('password_confirmation') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                @endif

                                <label class="auth-redesign-check auth-redesign-check--box">
                                    <input type="checkbox" id="terms-auth" wire:model="terms_accepted">
                                    <span>Chấp nhận các điều khoản và điều kiện <span class="text-danger">*</span></span>
                                </label>
                                @error('terms_accepted') <p class="text-danger invalid-text">{{ $message }}</p> @enderror

                                <button type="submit" class="auth-redesign-submit">
                                    {{ $role === 'employer' ? 'Tạo tài khoản nhà tuyển dụng' : 'Tạo tài khoản ứng viên' }}
                                </button>
                            </form>

                            <div class="auth-redesign-footer">
                                <a href="{{ $role === 'employer' ? route('employers.login') : route('candidates.login') }}">Bạn đã có tài khoản? Đăng nhập ngay</a>
                            </div>
                        </div>
                    @endif
                </div>

                <aside class="auth-redesign-visual auth-redesign-visual--{{ $role }}" aria-hidden="true">
                    <div class="auth-redesign-visual__media">
                        <img
                            src="{{ asset($role === 'employer' ? 'assets/img/auth-employer-register-side.png' : 'assets/img/auth-candidate-side-v2.png') }}"
                            alt=""
                        >
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>

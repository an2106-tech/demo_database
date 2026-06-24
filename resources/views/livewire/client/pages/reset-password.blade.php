<div>
    <section class="auth-redesign-page">
        <div class="container auth-redesign-container">
            <div class="auth-redesign-frame auth-redesign-frame--{{ $contextRole }}">
                <div class="auth-redesign-main">
                    <div class="auth-redesign-card">
                        <div class="auth-redesign-card__header auth-redesign-card__header--compact">
                            <a href="{{ route('home') }}" class="auth-redesign-brand" aria-label="Trang chủ">
                                <img src="{{ asset('assets/img/fe-logo.png') }}" alt="FPT Careers">
                            </a>
                            <h2>Đặt lại mật khẩu</h2>
                        </div>

                        <form wire:submit.prevent="resetPassword" class="auth-redesign-form" novalidate>
                            @if ($errors->has('email'))
                                <div class="auth-redesign-alert auth-redesign-alert--error">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif

                            <div class="auth-redesign-field">
                                <label for="reset-email">Email</label>
                                <input id="reset-email" type="email" class="form-control" placeholder="you@example.com" wire:model="email" autocomplete="email" inputmode="email">
                            </div>

                            <div class="auth-redesign-field">
                                <label for="reset-password">Mật khẩu mới</label>
                                <input id="reset-password" type="password" class="form-control" placeholder="Tối thiểu 8 ký tự" wire:model="password" autocomplete="new-password">
                                @error('password') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                            </div>

                            <div class="auth-redesign-field">
                                <label for="reset-password-confirm">Xác nhận mật khẩu mới</label>
                                <input id="reset-password-confirm" type="password" class="form-control" placeholder="Nhập lại mật khẩu mới" wire:model="password_confirmation" autocomplete="new-password">
                                @error('password_confirmation') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" class="auth-redesign-submit">Cập nhật mật khẩu</button>
                        </form>

                        <div class="auth-redesign-footer">
                            <a href="{{ $contextRole === 'employer' ? route('employers.login') : route('candidates.login') }}">
                                Quay lại đăng nhập
                            </a>
                        </div>
                    </div>
                </div>

                <aside class="auth-redesign-visual auth-redesign-visual--{{ $contextRole }}">
                    <div class="auth-redesign-visual__media">
                        <img
                            src="{{ asset($contextRole === 'employer' ? 'assets/img/auth-employer-login-side.png' : 'assets/img/auth-candidate-side-v2.png') }}"
                            alt=""
                        >
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>

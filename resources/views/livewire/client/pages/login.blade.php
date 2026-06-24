<div>
    <section class="auth-redesign-page">
        <div class="container auth-redesign-container">
            <div class="auth-redesign-frame auth-redesign-frame--{{ $contextRole }}">
                <div class="auth-redesign-main">
                    <div class="auth-redesign-tabs" role="tablist" aria-label="Đăng nhập theo vai trò">
                        <a href="{{ route('candidates.login') }}" class="auth-redesign-tab {{ $contextRole === 'candidate' ? 'is-active' : '' }}">Ứng viên</a>
                        <a href="{{ route('employers.login') }}" class="auth-redesign-tab {{ $contextRole === 'employer' ? 'is-active' : '' }}">Nhà tuyển dụng</a>
                    </div>

                    <div class="auth-redesign-card">
                        <div class="auth-redesign-card__header">
                            <a href="{{ route('home') }}" class="auth-redesign-brand" aria-label="Trang chủ">
                                <img src="{{ asset('assets/img/fe-logo.png') }}" alt="FPT Careers">
                            </a>
                            <h2>Đăng nhập</h2>
                        </div>

                        <form wire:submit.prevent="login" class="auth-redesign-form" novalidate>
                            @if (session('status'))
                                <div class="auth-redesign-alert auth-redesign-alert--success">
                                    {{ session('status') }}
                                </div>
                            @endif

                            @if ($errors->has('email'))
                                <div class="auth-redesign-alert auth-redesign-alert--error">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif

                            <div class="auth-redesign-field">
                                <label for="login-email">Email</label>
                                <input id="login-email" type="email" class="form-control" placeholder="you@example.com" wire:model="email" autocomplete="email" inputmode="email">
                            </div>

                            <div class="auth-redesign-field">
                                <label for="login-password">Mật khẩu</label>
                                <input id="login-password" type="password" class="form-control" placeholder="Nhập mật khẩu" wire:model="password" autocomplete="current-password">
                                @error('password') <p class="text-danger invalid-text">{{ $message }}</p> @enderror
                            </div>

                            <div class="auth-redesign-inline">
                                <label class="auth-redesign-check">
                                    <input type="checkbox" wire:model="remember">
                                    <span>Ghi nhớ đăng nhập</span>
                                </label>
                                <a href="{{ route('password.request', ['role' => $contextRole]) }}" class="auth-redesign-link">Quên mật khẩu?</a>
                            </div>

                            <button type="submit" class="auth-redesign-submit">Đăng nhập</button>
                        </form>

                        <div class="auth-redesign-footer">
                            <a href="{{ $contextRole === 'employer' ? route('employers.register') : route('candidates.register') }}">
                                Chưa có tài khoản? Đăng ký ngay
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

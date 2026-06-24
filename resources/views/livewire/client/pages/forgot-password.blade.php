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
                            <h2>Quên mật khẩu</h2>
                        </div>

                        <form wire:submit.prevent="sendResetLink" class="auth-redesign-form" novalidate>
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
                                <label for="forgot-email">Email</label>
                                <input id="forgot-email" type="email" class="form-control" placeholder="you@example.com" wire:model="email" autocomplete="email" inputmode="email">
                            </div>

                            <button type="submit" class="auth-redesign-submit">Gửi liên kết đặt lại mật khẩu</button>
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

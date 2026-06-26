@component('emails.layouts.base', [
    'title' => 'Xác thực email ứng tuyển',
    'eyebrow' => 'Xác thực hồ sơ',
    'preview' => 'Xác thực email để bảo vệ hồ sơ ứng tuyển của bạn.',
])
    <p>Thân gửi <strong>{{ $candidate->name }}</strong>,</p>
    <p>Vui lòng xác thực email đã dùng để ứng tuyển vị trí <strong>{{ $application->job?->title ?? 'đang tuyển' }}</strong>. Việc này giúp hệ thống liên kết hồ sơ chính xác khi bạn đăng ký tài khoản sau này.</p>

    <div class="info-card">
        <div class="info-item">Mã hồ sơ: <span class="info-value">#{{ $application->id }}</span></div>
        <div class="info-item">Email ứng tuyển: <span class="info-value">{{ $candidate->email }}</span></div>
    </div>

    <div class="mail-actions">
        <a href="{{ $verificationUrl }}" class="mail-button mail-button--orange">
            <span>Xác thực email</span><i>→</i>
        </a>
    </div>

    <p>Liên kết này có hiệu lực trong 7 ngày. Nếu bạn không thực hiện ứng tuyển, vui lòng bỏ qua email này.</p>
@endcomponent

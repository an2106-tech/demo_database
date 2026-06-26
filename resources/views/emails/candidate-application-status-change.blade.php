@component('emails.layouts.base', [
    'title' => $subjectLine,
    'eyebrow' => 'Cập nhật hồ sơ',
    'preview' => 'Trạng thái hồ sơ ứng tuyển của bạn đã thay đổi.',
])
    <p>Thân gửi <strong>{{ $application->snapshotCandidateName() }}</strong>,</p>
    <p>Hồ sơ của bạn cho vị trí <strong>{{ $job->title }}</strong> vừa được cập nhật trạng thái.</p>

    <div class="info-card">
        <div class="info-item">Mã hồ sơ: <span class="info-value">#{{ $application->id }}</span></div>
        <div class="info-item">Trạng thái cũ: <span class="info-value">{{ $oldStatus->getLabel() }}</span></div>
        <div class="info-item">Trạng thái mới: <span class="info-value">{{ $newStatus->getLabel() }}</span></div>
        <div class="info-item">Cập nhật lúc: <span class="info-value">{{ $application->updated_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</span></div>
    </div>

    <p>Vui lòng đăng nhập vào hệ thống để xem chi tiết và các bước tiếp theo.</p>

    @if (Route::has('candidates.login'))
        <div class="mail-actions">
            <a href="{{ route('candidates.login') }}" class="mail-button mail-button--orange">
                <span>Đăng nhập</span><i>→</i>
            </a>
        </div>
    @endif
@endcomponent

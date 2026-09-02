@component('emails.layouts.base', [
    'title' => $subjectLine,
    'eyebrow' => 'Cập nhật hồ sơ',
    'preview' => 'Trạng thái hồ sơ ứng tuyển của bạn đã thay đổi.',
])
    <p>Thân gửi <strong>{{ $application->snapshotCandidateName() }}</strong>,</p>
    <p>Hồ sơ của bạn cho vị trí <strong>{{ $job->title }}</strong> vừa được cập nhật trạng thái.</p>

    <div class="info-card">
        <div class="info-item">
            <span>Mã hồ sơ</span>
            <span class="info-value">#{{ $application->id }}</span>
        </div>
        <div class="info-item">
            <span>Trạng thái trước</span>
            <span class="info-value">{{ $oldStatus->getLabel() }}</span>
        </div>
        <div class="info-item">
            <span>Trạng thái hiện tại</span>
            <span class="info-value">{{ $newStatus->getLabel() }}</span>
        </div>
        <div class="info-item">
            <span>Thời gian cập nhật</span>
            <span class="info-value">{{ $application->updated_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <p>Vui lòng đăng nhập vào tài khoản để xem chi tiết thông tin hồ sơ và các bước tiếp theo.</p>

    @if (Route::has('candidates.login'))
        <div class="mail-actions">
            <a href="{{ route('candidates.login') }}" class="mail-button">
                <span>Đăng nhập hệ thống</span>
            </a>
        </div>
    @endif
@endcomponent

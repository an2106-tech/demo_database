@component('emails.layouts.base', [
    'title' => 'Yêu cầu duyệt đề nghị tuyển dụng',
    'eyebrow' => 'Duyệt đề nghị',
    'preview' => 'Có đề nghị tuyển dụng mới cần được xem xét.',
])
    <p>Chào <strong>{{ $recipientName }}</strong>,</p>
    <p>HR đã tạo một đề nghị tuyển dụng mới và cần bạn xem chi tiết trước khi duyệt.</p>

    <div class="info-card">
        <span class="info-item"><strong>Ứng viên:</strong> <span class="info-value">{{ $candidateName }}</span></span>
        <span class="info-item"><strong>Vị trí:</strong> <span class="info-value">{{ $jobTitle }}</span></span>
        <span class="info-item"><strong>Mức lương:</strong> <span class="info-value">{{ $salaryOffered }}</span></span>
    </div>

    <div class="mail-actions">
        <a href="{{ $approvalUrl }}" class="mail-button mail-button--orange">
            <span>Xem chi tiết</span><i>→</i>
        </a>
    </div>
@endcomponent

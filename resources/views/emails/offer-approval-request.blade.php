@component('emails.layouts.base', [
    'title' => 'Yêu cầu duyệt đề nghị tuyển dụng',
    'eyebrow' => 'Phê duyệt đề nghị',
    'preview' => 'Có đề nghị tuyển dụng mới cần được xem xét.',
])
    <p>Chào <strong>{{ $recipientName }}</strong>,</p>
    <p>Bộ phận tuyển dụng vừa tạo một đề nghị tuyển dụng mới (Offer) và gửi yêu cầu phê duyệt đến bạn.</p>

    <div class="info-card">
        <div class="info-item">
            <span>Ứng viên</span>
            <span class="info-value">{{ $candidateName }}</span>
        </div>
        <div class="info-item">
            <span>Vị trí</span>
            <span class="info-value">{{ $jobTitle }}</span>
        </div>
        <div class="info-item">
            <span>Mức lương đề xuất</span>
            <span class="info-value">{{ $salaryOffered }}</span>
        </div>
        <div class="info-item">
            <span>Ngày bắt đầu dự kiến</span>
            <span class="info-value">{{ $startDate }}</span>
        </div>
        <div class="info-item">
            <span>Thời gian thử việc</span>
            <span class="info-value">{{ $probationMonths }} tháng</span>
        </div>
        <div class="info-item">
            <span>Hạn phản hồi</span>
            <span class="info-value">{{ $responseDeadline }}</span>
        </div>
    </div>

    <div class="mail-actions">
        <a href="{{ $approvalUrl }}" class="mail-button">
            <span>Xem chi tiết đề nghị</span>
        </a>
    </div>
@endcomponent

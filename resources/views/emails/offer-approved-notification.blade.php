@component('emails.layouts.base', [
    'title' => 'Thư mời nhận việc đã được gửi',
    'eyebrow' => 'Đang chờ ứng viên phản hồi',
    'preview' => 'Thư mời nhận việc đã được gửi đến ứng viên và đang chờ phản hồi.',
])
    <p>Chào <strong>{{ $recipientName }}</strong>,</p>
    <p>Thư mời nhận việc cho ứng viên <strong>{{ $candidateName }}</strong> ở vị trí <strong>{{ $jobTitle }}</strong> đã được gửi thành công.</p>
    <p>Ứng viên hiện đang trong giai đoạn xem xét và phản hồi đề nghị. Bạn có thể theo dõi trạng thái hồ sơ trên hệ thống.</p>

    <div class="info-card">
        <div class="info-item">
            <span>Ứng viên</span>
            <span class="info-value">{{ $candidateName }}</span>
        </div>
        <div class="info-item">
            <span>Email</span>
            <span class="info-value">{{ $candidateEmail }}</span>
        </div>
        <div class="info-item">
            <span>Vị trí</span>
            <span class="info-value">{{ $jobTitle }}</span>
        </div>
        @if($showSalary)
            <div class="info-item">
                <span>Mức lương</span>
                <span class="info-value">{{ $salaryOffered }}</span>
            </div>
        @endif
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
            <span class="info-value">{{ $responseDeadline }} @if($responseWindow !== 'Chưa xác định')({{ $responseWindow }})@endif</span>
        </div>
    </div>
@endcomponent

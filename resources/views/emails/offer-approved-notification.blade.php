@component('emails.layouts.base', [
    'title' => 'Thư mời nhận việc đã được gửi',
    'eyebrow' => 'Đang chờ ứng viên phản hồi',
    'preview' => 'Thư mời nhận việc đã được gửi đến ứng viên và đang chờ phản hồi.',
])
    <p>Chào <strong>{{ $recipientName }}</strong>,</p>
    <p>Thư mời nhận việc cho ứng viên <strong>{{ $candidateName }}</strong> ở vị trí <strong>{{ $jobTitle }}</strong> đã được gửi thành công.</p>
    <p>Ứng viên hiện đang trong giai đoạn phản hồi đề nghị. Vui lòng theo dõi trạng thái hồ sơ để kịp thời hỗ trợ các bước tiếp theo.</p>

    <div class="info-card">
        <span class="info-item"><strong>Ứng viên:</strong> <span class="info-value">{{ $candidateName }}</span></span>
        <span class="info-item"><strong>Email:</strong> <span class="info-value">{{ $candidateEmail }}</span></span>
        <span class="info-item"><strong>Vị trí:</strong> <span class="info-value">{{ $jobTitle }}</span></span>
        @if($showSalary)
            <span class="info-item"><strong>Mức lương:</strong> <span class="info-value">{{ $salaryOffered }}</span></span>
        @endif
        <span class="info-item"><strong>Ngày bắt đầu:</strong> <span class="info-value">{{ $startDate }}</span></span>
        <span class="info-item"><strong>Thời gian thử việc:</strong> <span class="info-value">{{ $probationMonths }} tháng</span></span>
    </div>

    <div class="mail-panel">
        <p><strong>Trạng thái hiện tại:</strong></p>
        <ul>
            <li>Thư mời nhận việc đã được gửi đến ứng viên.</li>
            <li>Hạn phản hồi: {{ $responseDeadline }} @if($responseWindow !== 'Chưa xác định')({{ $responseWindow }})@endif.</li>
            <li>Hồ sơ đang chờ ứng viên xác nhận đồng ý hoặc từ chối đề nghị.</li>
        </ul>
    </div>
@endcomponent

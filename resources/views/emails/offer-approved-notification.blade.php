@component('emails.layouts.base', [
    'title' => 'Đề nghị tuyển dụng đã được duyệt',
    'eyebrow' => 'Đã duyệt đề nghị',
    'preview' => 'Đề nghị tuyển dụng đã được duyệt và sẽ được gửi tới ứng viên.',
])
    <p>Chào <strong>{{ $recipientName }}</strong>,</p>
    <p>Đề nghị tuyển dụng cho ứng viên dưới đây đã được duyệt và sẽ được gửi cho ứng viên.</p>

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

    @if(!$showSalary)
        <div class="mail-panel">
            <p><strong>Lưu ý:</strong> Thông tin mức lương được bảo mật và chỉ hiển thị cho HR và giám đốc chi nhánh.</p>
        </div>
    @endif

    <div class="mail-panel">
        <p><strong>Hành động đã thực hiện:</strong></p>
        <ul>
            <li>Thư mời nhận việc PDF đã được gửi tới ứng viên.</li>
            <li>Ứng viên có {{ $offer->expires_at?->diffInDays(now()) ?? '3' }} ngày để phản hồi.</li>
            <li>HR và giám đốc chi nhánh đã được thông báo.</li>
        </ul>
    </div>
@endcomponent

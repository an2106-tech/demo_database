@component('emails.layouts.base', [
    'title' => 'Yêu cầu phê duyệt tin tuyển dụng',
    'eyebrow' => 'Duyệt tin',
    'preview' => 'Có tin tuyển dụng mới đang chờ phê duyệt.',
])
    <p>Xin chào <strong>{{ $directorName }}</strong>,</p>
    <p>Bạn nhận được yêu cầu phê duyệt một tin tuyển dụng mới vừa được đăng bởi <strong>{{ $hrName }}</strong>.</p>

    <div class="job-card">
        <h2 style="margin:0 0 12px;color:#111827;font-size:18px;">{{ $jobTitle }}</h2>
        <span class="job-detail"><strong>Vị trí:</strong> {{ $jobTitle }}</span>
        <span class="job-detail"><strong>Chi nhánh:</strong> {{ $branchName }}</span>
        <span class="job-detail"><strong>Phòng ban:</strong> {{ $departmentName }}</span>
        <span class="job-detail"><strong>Người đăng:</strong> {{ $hrName }}</span>
    </div>

    <p>Vui lòng kiểm tra nội dung trước khi tin được hiển thị công khai trên hệ thống.</p>

    <div class="mail-actions">
        <a href="{{ $filamentUrl }}" class="mail-button mail-button--orange">
            <span>Xem và phê duyệt</span><i>→</i>
        </a>
    </div>
@endcomponent

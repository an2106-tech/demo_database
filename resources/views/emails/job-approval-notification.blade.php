@component('emails.layouts.base', [
    'title' => 'Yêu cầu phê duyệt tin tuyển dụng',
    'eyebrow' => 'Phê duyệt',
    'preview' => 'Có tin tuyển dụng mới đang chờ phê duyệt.',
])
    <p>Xin chào <strong>{{ $directorName }}</strong>,</p>
    <p>Bộ phận nhân sự (<strong>{{ $hrName }}</strong>) vừa tạo một tin tuyển dụng mới và gửi yêu cầu phê duyệt trước khi công khai lên hệ thống.</p>

    <div class="job-card">
        <div class="job-detail">
            <span>Vị trí tuyển dụng</span>
            <span class="info-value">{{ $jobTitle }}</span>
        </div>
        <div class="job-detail">
            <span>Chi nhánh</span>
            <span class="info-value">{{ $branchName }}</span>
        </div>
        <div class="job-detail">
            <span>Phòng ban</span>
            <span class="info-value">{{ $departmentName }}</span>
        </div>
        <div class="job-detail">
            <span>Người tạo tin</span>
            <span class="info-value">{{ $hrName }}</span>
        </div>
    </div>

    <div class="mail-actions">
        <a href="{{ $filamentUrl }}" class="mail-button">
            <span>Xem và phê duyệt</span>
        </a>
    </div>
@endcomponent

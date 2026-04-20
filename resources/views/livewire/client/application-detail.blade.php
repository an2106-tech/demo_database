@php
    $job = $application->job;
    $status = $application->status;
    $statusValue = $status instanceof \App\Enums\StatusApplicationEnum ? $status->value : (string) $status;
    $statusLabel = $status instanceof \App\Enums\StatusApplicationEnum ? $status->getLabel() : ucfirst((string) $status);
    $statusClass = match ($statusValue) {
        'cv_reviewing' => 'pending',
        'screening' => 'pending',
        'interview_scheduled' => 'active',
        'interviewing' => 'active',
        'offered' => 'active',
        'hired' => 'approved',
        'rejected' => 'expired',
        default => 'pending',
    };
@endphp

<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Chi tiết ứng tuyển</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="candidate-dashboard-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right">
                        <div class="manage-jobs">
                            <div class="manage-jobs-heading d-flex justify-content-between align-items-center">
                                <h3>{{ $job?->title ?? 'Đơn ứng tuyển' }}</h3>
                                <a href="{{ route('candidates.manage_jobs') }}" class="jobguru-btn-2">Quay lại</a>
                            </div>

                            <div class="single-resume-feild">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Mã hồ sơ:</strong> #{{ $application->id }}</p>
                                        <p><strong>Trạng thái:</strong> <span class="{{ $statusClass }}">{{ $statusLabel }}</span></p>
                                        <p><strong>Ngày ứng tuyển:</strong> {{ optional($application->applied_at ?? $application->created_at)->format('d/m/Y H:i') }}</p>
                                        <p><strong>CV đã nộp:</strong> {{ $application->cv_path ? basename($application->cv_path) : 'Chưa có' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Chi nhánh:</strong> {{ $job?->branch?->name ?? '-' }}</p>
                                        <p><strong>Phòng ban:</strong> {{ $job?->department?->name ?? '-' }}</p>
                                        <p><strong>Nơi làm việc:</strong> {{ $job?->workplace?->name ?? '-' }}</p>
                                        <p><strong>Hạn nộp:</strong> {{ optional($job?->deadline)->format('d/m/Y') ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="single-resume-feild">
                                <h4>Mô tả vị trí</h4>
                                <p style="white-space: pre-line;">{{ $job?->description ?: 'Chưa có mô tả chi tiết.' }}</p>
                            </div>

                            @if (! empty($application->profile_snapshot))
                                <div class="single-resume-feild">
                                    <h4>Thông tin hồ sơ tại thời điểm ứng tuyển</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Họ tên:</strong> {{ data_get($application->profile_snapshot, 'name', '-') }}</p>
                                            <p><strong>Email:</strong> {{ data_get($application->profile_snapshot, 'email', '-') }}</p>
                                            <p><strong>Số điện thoại:</strong> {{ data_get($application->profile_snapshot, 'phone', '-') }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Kinh nghiệm:</strong> {{ data_get($application->profile_snapshot, 'experience_years', '-') }}</p>
                                            <p><strong>Tiêu đề hồ sơ:</strong> {{ data_get($application->profile_snapshot, 'profile_title', '-') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($statusValue === 'rejected' && filled($application->rejected_reason))
                                <div class="alert alert-danger mt-3 mb-0">
                                    <strong>Lý do từ chối:</strong> {{ $application->rejected_reason }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

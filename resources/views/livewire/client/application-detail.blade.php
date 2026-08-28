@php
    $job = $application->job;
    $status = $application->status;
    $statusValue = $status instanceof \App\Enums\StatusApplicationEnum ? $status->value : (string) $status;
    $statusLabel = $status instanceof \App\Enums\StatusApplicationEnum ? $status->getLabel() : ucfirst((string) $status);
    $statusChipClass = match ($statusValue) {
        'new', 'cv_reviewing', 'screening' => 'chip chip--warning',
        'interview_scheduled', 'interview', 'offer' => 'chip chip--accent',
        'hired' => 'chip chip--success',
        'rejected' => 'chip chip--danger',
        'withdrawn' => 'chip',
        default => 'chip',
    };
    $snapshot = $application->profile_snapshot ?? [];
    $snapshotName = data_get($snapshot, 'name') ?: data_get($snapshot, 'candidate.name', '-');
    $snapshotEmail = data_get($snapshot, 'email') ?: data_get($snapshot, 'candidate.email', '-');
    $snapshotPhone = data_get($snapshot, 'phone') ?: data_get($snapshot, 'candidate.phone', '-');
    $snapshotExperience = data_get($snapshot, 'experience_years') ?: data_get($snapshot, 'candidate.experience_years', '-');
    $snapshotTitle = data_get($snapshot, 'profile_title') ?: data_get($snapshot, 'resume.profile_title', '-');
    $submittedCvName = data_get($snapshot, 'cv.original_filename') ?: ($application->cv_path ? basename($application->cv_path) : 'Chưa có');
    $jobStatusLabel = is_object($job?->status) && method_exists($job->status, 'getLabel')
        ? $job->status->getLabel()
        : ($job?->status ?: '-');
@endphp

<div>
    <div class="fpt-breadcrumb-bar">
        <div class="container">
            <div class="fpt-breadcrumb-inner">
                <ul class="fpt-breadcrumb-trail">
                    <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('candidates.manage_jobs') }}">Việc làm đã ứng tuyển</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li class="current">Chi tiết hồ sơ</li>
                </ul>

                <a href="{{ route('candidates.manage_jobs') }}" class="fpt-back-btn">
                    <i class="fa fa-arrow-left"></i> Danh sách việc làm đã nộp
                </a>
            </div>
        </div>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-4 dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8">
                    <div class="application-shell">
                        <div class="portal-shell portal-shell--subtle p-4 p-lg-5">
                            <div class="application-hero">
                                <div>
                                    <span class="portal-eyebrow">Chi tiết hồ sơ đã ứng tuyển</span>
                                    <h1 class="application-hero__title">{{ $job?->title ?? 'Đơn ứng tuyển' }}</h1>
                                    <p class="portal-subtitle">
                                        Toàn bộ thông tin được chốt theo thời điểm nộp để đảm bảo nhà tuyển dụng đọc đúng snapshot, không phụ thuộc hồ sơ hiện tại.
                                    </p>
                                </div>

                                <div class="application-actions">
                                    @if (! $application->trashed() && ! in_array($statusValue, ['rejected', 'hired', 'withdrawn'], true))
                                        <button
                                            type="button"
                                            wire:click="withdraw"
                                            wire:confirm="Bạn có chắc muốn rút hồ sơ? Hồ sơ sẽ dừng tham gia quy trình tuyển dụng."
                                            wire:loading.attr="disabled"
                                            wire:target="withdraw"
                                            class="jobguru-btn-2"
                                            style="background: rgba(239, 68, 68, 0.08); color: #b91c1c; border-color: rgba(239, 68, 68, 0.16);"
                                        >
                                            Rút hồ sơ
                                        </button>
                                    @endif

                                    <a href="{{ route('candidates.manage_jobs') }}" class="jobguru-btn-2">Quay lại</a>
                                </div>
                            </div>
                        </div>

                        <div class="application-grid">
                            <div class="application-card">
                                <h4>Thông tin ứng tuyển</h4>
                                <div class="application-list">
                                    <div class="application-list__item">
                                        <span>Mã hồ sơ</span>
                                        <strong>#{{ $application->id }}</strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>Trạng thái</span>
                                        <strong><span class="{{ $statusChipClass }}">{{ $statusLabel }}</span></strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>Ngày ứng tuyển</span>
                                        <strong>{{ optional($application->applied_at ?? $application->created_at)->format('d/m/Y H:i') }}</strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>CV đã nộp</span>
                                        <strong>{{ $submittedCvName }}</strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>Chi nhánh</span>
                                        <strong>{{ $job?->branch?->name ?? '-' }}</strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>Hạn nộp</span>
                                        <strong>{{ optional($job?->deadline)->format('d/m/Y') ?? '-' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="application-card">
                                <h4>Vị trí & bộ phận</h4>
                                <div class="application-list">
                                    <div class="application-list__item">
                                        <span>Phòng ban</span>
                                        <strong>{{ $job?->department?->name ?? '-' }}</strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>Nơi làm việc</span>
                                        <strong>{{ $job?->workplace?->name ?? '-' }}</strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>Địa điểm</span>
                                        <strong>{{ $job?->branch?->name ?? '-' }}</strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>Trạng thái tin</span>
                                        <strong>{{ $jobStatusLabel }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success mb-0">{{ session('status') }}</div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger mb-0">{{ session('error') }}</div>
                        @endif

                        <div class="application-card">
                            <h4>Mô tả vị trí</h4>
                            <p style="white-space: pre-line; margin-bottom: 0; color: #334155; line-height: 1.8;">
                                {{ $job?->description ?: 'Chưa có mô tả chi tiết.' }}
                            </p>
                        </div>

                        <div class="application-card">
                            <h4>Snapshot hồ sơ tại thời điểm nộp</h4>
                            <p class="portal-subtitle mb-4" style="font-size: 14px;">
                                Đây là dữ liệu nhà tuyển dụng sẽ nhìn thấy trong quá trình đánh giá.
                            </p>

                            <div class="application-grid">
                                <div class="application-list">
                                    <div class="application-list__item">
                                        <span>Họ tên</span>
                                        <strong>{{ $snapshotName }}</strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>Email</span>
                                        <strong>{{ $snapshotEmail }}</strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>Số điện thoại</span>
                                        <strong>{{ $snapshotPhone }}</strong>
                                    </div>
                                </div>

                                <div class="application-list">
                                    <div class="application-list__item">
                                        <span>Kinh nghiệm</span>
                                        <strong>{{ $snapshotExperience }}</strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>Tiêu đề hồ sơ</span>
                                        <strong>{{ $snapshotTitle }}</strong>
                                    </div>
                                    <div class="application-list__item">
                                        <span>File CV</span>
                                        <strong>{{ $submittedCvName }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

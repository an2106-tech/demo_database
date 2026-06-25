<div class="bj2-page">
    @php
        /** @var \Illuminate\Support\Collection<int, \App\Models\Department>|\App\Models\Department[] $departments */
        $isListView = ($display ?? 'grid') === 'list';
    @endphp

    <section class="home-hero home-premium-hero browse-hero">
        <div class="home-premium-hero-banner">
            <img src="{{ asset('assets/img/BannerWeb_Tiensi-03.jpg') }}" alt="Tuyển dụng nội bộ FPT" class="browse-hero__bg-img">
        </div>

        <div class="browse-hero__overlay">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <div class="breadcromb-box browse-hero__content text-white">
                            <p class="text-uppercase text-warning fw-semibold mb-3">Hệ thống quản lý tuyển dụng nội bộ FPT</p>
                            <h1 class="mb-4">Tìm việc nội bộ nhanh chóng và đồng bộ</h1>
                            <p class="mb-4 text-white-75">Tập trung các cơ hội tuyển dụng dành cho nhân viên FPT và ứng viên nội bộ. Lọc theo phòng ban, khu vực và yêu cầu để tìm đúng vị trí phù hợp, đồng bộ với hệ thống quản lý tuyển dụng.</p>
                            <a href="{{ route('home') }}" class="btn btn-warning rounded-pill px-5 py-3 fw-bold">Trở về hệ thống</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box-pagin">
                            <ul>
                                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                                <li><a href="#">Ứng viên</a></li>
                                <li class="active-breadcromb"><a href="#">Tìm việc làm</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bj2-section section_70">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <p class="text-uppercase text-muted mb-2">Tổng số cơ hội</p>
                            <h2 class="fw-bold">{{ $jobs->total() }}</h2>
                            <p class="mb-0 text-muted">Việc làm đang tuyển dụng nội bộ</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <p class="text-uppercase text-muted mb-2">Phòng ban</p>
                            <h2 class="fw-bold">{{ $departments->count() }}</h2>
                            <p class="mb-0 text-muted">Lọc theo phòng ban hiện có</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <p class="text-uppercase text-muted mb-2">Xem nhanh</p>
                            <h2 class="fw-bold">Ứng tuyển ngay</h2>
                            <p class="mb-0 text-muted">Thao tác trực tiếp trên hệ thống</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bj2-filters">
                <div class="bj2-filters__grid">
                    <div class="bj2-field">
                        <i class="fa fa-search"></i>
                        <input
                            type="search"
                            class="form-control"
                            placeholder="Từ khóa (Laravel, Kế toán...)"
                            wire:model.live.debounce.400ms="q"
                        >
                    </div>

                    <div class="bj2-field">
                        <i class="fa fa-map-marker"></i>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Khu vực (Hà Nội, TP.HCM...)"
                            wire:model.live.debounce.400ms="city"
                        >
                    </div>

                    <div class="bj2-field">
                        <i class="fa fa-sitemap"></i>
                        <select class="form-select" wire:model.live="department_id">
                            <option value="">Tất cả phòng ban</option>
                            @foreach(($departments ?? []) as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button
                        type="button"
                        class="bj2-reset"
                        wire:click="clearFilters"
                    >
                        <i class="fa fa-refresh"></i> Xóa lọc
                    </button>
                </div>

                <div class="bj2-filters__bar">
                    <div class="bj2-count">
                        Có <span class="bj2-count__num">{{ $jobs->total() }}</span> kết quả phù hợp
                    </div>

                    <div class="bj2-view">
                        <button
                            type="button"
                            class="bj2-view__btn {{ ! $isListView ? 'active' : '' }}"
                            wire:click="setDisplay('grid')"
                            title="Dạng lưới"
                        >
                            <i class="fa fa-th"></i>
                        </button>
                        <button
                            type="button"
                            class="bj2-view__btn {{ $isListView ? 'active' : '' }}"
                            wire:click="setDisplay('list')"
                            title="Dạng danh sách"
                        >
                            <i class="fa fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-4 bj2-results {{ $isListView ? 'bj2-results--list' : '' }}">
                @forelse ($jobs as $job)
                    @php
                        $detailUrl = route('jobs.public', ['slug' => $job->slug]);
                        $applyUrl = route('candidates.apply_job', ['job' => $job->id]);
                        $logoSrc = $job->branch?->image
                            ? '/storage/' . ltrim($job->branch->image, '/')
                            : asset('assets/img/company-logo-1.png');
                        $branchName = trim((string) ($job->branch?->name ?? ''));
                        $cityText = \App\Enums\VietnamProvince::tryFrom($job->branch?->city ?? '')?->label()
                            ?? ($job->branch?->city ?? 'Chưa cập nhật');

                        if (is_array($job->salary_range) && isset($job->salary_range['min'], $job->salary_range['max'])) {
                            $salaryText = number_format($job->salary_range['min']) . ' - ' . number_format($job->salary_range['max']) . ' VND';
                        } elseif (is_array($job->salary_range) && ! empty($job->salary_range)) {
                            $salaryText = implode(' - ', $job->salary_range);
                        } elseif (! empty($job->salary_range)) {
                            $salaryText = (string) $job->salary_range;
                        } else {
                            $salaryText = 'Thỏa thuận';
                        }

                        $deadlineText = $job->deadline?->format('d/m/Y') ?? 'Liên hệ';
                    @endphp

                    <div class="col-12 {{ $isListView ? '' : 'col-md-6 col-lg-4' }}">
                        @php
                            $excerpt = trim(strip_tags($job->description ?? ''));
                            $excerpt = \Illuminate\Support\Str::limit($excerpt, 120, '...');
                            $departmentName = $job->department?->name ?? 'Chưa có phòng ban';
                            $workplaceName = $job->workplace?->name ?? 'Không rõ';
                        @endphp

                        <article class="bj2-card {{ $isListView ? 'bj2-card--list' : '' }}">
                            <div class="bj2-card__top d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div class="d-flex align-items-start gap-3 flex-grow-1">
                                    <a class="bj2-card__logo" href="{{ $detailUrl }}">
                                        <img src="{{ $logoSrc }}" alt="{{ $branchName !== '' ? $branchName : 'Chi nhánh' }}">
                                    </a>

                                    <div class="flex-grow-1">
                                        <div class="bj2-chips d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <span class="bj2-chip bj2-chip--brand">Nội bộ FPT</span>
                                            <span class="bj2-chip" title="{{ $departmentName }}">{{ $departmentName }}</span>
                                            <span class="bj2-chip bj2-chip--muted" title="{{ $workplaceName }}">{{ $workplaceName }}</span>
                                        </div>

                                        <h3 class="bj2-card__title mt-0 mb-2">
                                            <a href="{{ $detailUrl }}">{{ $job->title }}</a>
                                        </h3>

                                        <div class="bj2-card__meta d-flex flex-wrap gap-3 align-items-center mb-3">
                                            <div><i class="fa fa-building-o me-1"></i>{{ $branchName !== '' ? $branchName : 'Doanh nghiệp nội bộ' }}</div>
                                            <div><i class="fa fa-map-marker me-1"></i>{{ $cityText }}</div>
                                        </div>
                                    </div>
                                </div>

                                <span class="bj2-deadline">Hạn nộp: {{ $deadlineText }}</span>
                            </div>

                            <div class="bj2-card__content pt-0">
                                <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
                                    <div class="bj2-salary">{{ $salaryText }}</div>
                                </div>

                                @if ($excerpt !== '')
                                    <p class="bj2-excerpt mb-0">{{ $excerpt }}</p>
                                @endif
                            </div>

                            <div class="bj2-card__cta d-flex flex-wrap gap-3">
                                <a href="{{ $detailUrl }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">Xem chi tiết</a>
                                <a href="{{ $applyUrl }}" class="bj2-apply">Ứng tuyển ngay</a>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="bj2-empty">Không có việc làm nào.</div>
                    </div>
                @endforelse
            </div>

            @if ($jobs->hasPages())
                <div class="mt-5 d-flex justify-content-center">
                    {{ $jobs->links() }}
                </div>
            @endif
        </div>
    </section>
</div>

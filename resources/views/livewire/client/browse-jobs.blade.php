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
                    <div class="card bj2-stat-card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <p class="text-uppercase text-muted mb-2">Tổng số cơ hội</p>
                            <h2 class="fw-bold">{{ $jobs->total() }}</h2>
                            <p class="mb-0 text-muted">Việc làm đang tuyển dụng nội bộ</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card bj2-stat-card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <p class="text-uppercase text-muted mb-2">Phòng ban</p>
                            <h2 class="fw-bold">{{ $departments->count() }}</h2>
                            <p class="mb-0 text-muted">Lọc theo phòng ban hiện có</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card bj2-stat-card bj2-stat-card--action h-100 border-0 shadow-sm rounded-4">
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

                        <article class="bj2-card {{ $isListView ? 'bj2-card--list' : '' }} d-flex flex-column h-100 p-4 bg-white border rounded-4" style="border-color: #eaeaea;">
                            <!-- Top: Logo & Deadline -->
                            <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                                <a href="{{ $detailUrl }}" class="border rounded p-2" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; background: #fff; flex-shrink: 0;">
                                    <img src="{{ $logoSrc }}" alt="{{ $branchName !== '' ? $branchName : 'Chi nhánh' }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                </a>
                                <span class="badge rounded-pill bg-dark text-white px-3 py-2 fw-normal d-flex align-items-center" style="font-size: 0.85rem;">
                                    Hạn nộp: {{ $deadlineText }}
                                </span>
                            </div>

                            <!-- Title -->
                            <h3 class="mt-0 mb-2 fw-bold" style="font-size: 1.2rem; line-height: 1.4;">
                                <a href="{{ $detailUrl }}" class="text-dark text-decoration-none">{{ $job->title }}</a>
                            </h3>

                            <!-- Company -->
                            <div class="text-muted mb-3 d-flex align-items-center" style="font-size: 0.95rem;">
                                <i class="fa fa-building-o me-2" style="width: 16px; text-align: center;"></i>{{ $branchName !== '' ? $branchName : 'Doanh nghiệp nội bộ' }}
                            </div>

                            <!-- Chips (Tags) - allowing wrap so they don't get cut off -->
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <span class="badge rounded fw-normal text-start" style="background-color: rgba(243, 112, 33, 0.1); color: #f37021; border: 1px solid rgba(243, 112, 33, 0.2); font-size: 0.85rem; padding: 0.5rem 0.8rem; white-space: normal; line-height: 1.4;">
                                    Nội bộ FPT
                                </span>
                                <span class="badge rounded bg-light text-secondary border fw-normal text-start" title="{{ $departmentName }}" style="font-size: 0.85rem; padding: 0.5rem 0.8rem; white-space: normal; line-height: 1.4;">
                                    {{ $departmentName }}
                                </span>
                                <span class="badge rounded bg-light text-secondary border fw-normal text-start" title="{{ $workplaceName }}" style="font-size: 0.85rem; padding: 0.5rem 0.8rem; white-space: normal; line-height: 1.4;">
                                    {{ $workplaceName }}
                                </span>
                            </div>

                            <!-- Meta: Location & Salary -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <div class="d-flex align-items-center text-muted" style="font-size: 0.95rem;">
                                    <i class="fa fa-map-marker me-2" style="color: #999;"></i> {{ $cityText }}
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge fw-bold" style="background-color: rgba(243, 112, 33, 0.1); color: #f37021; font-size: 0.95rem; padding: 0.4rem 0.8rem; border-radius: 6px;">
                                        <i class="fa fa-money me-1"></i> {{ $salaryText }}
                                    </span>
                                </div>
                            </div>

                            <!-- Excerpt -->
                            <div class="flex-grow-1">
                                @if ($excerpt !== '')
                                    <p class="text-muted mb-4" style="font-size: 0.95rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.6;">
                                        {{ $excerpt }}
                                    </p>
                                @endif
                            </div>

                            <!-- CTA -->
                            <div class="d-flex flex-wrap gap-2 w-100 mt-2">
                                <a href="{{ $detailUrl }}" class="btn rounded-pill fw-medium text-center" style="flex: 1; min-width: 120px; padding: 0.6rem 0; background-color: #f1f5f9; color: #475569; border: none;">Xem chi tiết</a>
                                <a href="{{ $applyUrl }}" class="btn rounded-pill fw-medium text-white text-center" style="flex: 2; min-width: 150px; padding: 0.6rem 0; background-color: #f37021; border-color: #f37021;">Ứng tuyển ngay</a>
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
                <div class="bj2-pagination mt-5 d-flex justify-content-center">
                    {{ $jobs->links() }}
                </div>
            @endif
        </div>
    </section>
</div>

<div class="bj2-page">
    @php
        /** @var \Illuminate\Support\Collection<int, \App\Models\Department>|\App\Models\Department[] $departments */
        $isListView = ($display ?? 'grid') === 'list';
    @endphp

    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Tìm việc làm</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                            placeholder="Khu vực"
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
                        wire:click="$set('q', ''); $set('city', ''); $set('department_id', null)"
                    >
                        <i class="fa fa-refresh"></i> Xóa lọc
                    </button>
                </div>

                <div class="bj2-filters__bar">
                    <div class="bj2-count">
                        Có <span class="bj2-count__num">{{ $jobs->count() }}</span> việc làm
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
                        $detailUrl = route('candidates.job_detail', ['id' => $job->id]);
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
                        <article class="bj2-card {{ $isListView ? 'bj2-card--list' : '' }}">
                            <div class="bj2-card__content">
                                <header class="bj2-card__header">
                                    <a class="bj2-card__logo" href="{{ $detailUrl }}">
                                        <img src="{{ $logoSrc }}" alt="{{ $branchName !== '' ? $branchName : 'Chi nhánh' }}">
                                    </a>

                                    <p class="bj2-card__school">
                                        {{ $branchName !== '' ? $branchName : 'Doanh nghiệp' }}
                                    </p>

                                    <p class="bj2-card__place">
                                        <i class="fa fa-map-marker"></i>
                                        <span>{{ $cityText }}</span>
                                    </p>

                                    <h3 class="bj2-card__title">
                                        <a href="{{ $detailUrl }}">{{ $job->title }}</a>
                                    </h3>
                                </header>
                            </div>

                            <div class="bj2-card__cta">
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
        </div>
    </section>
</div>

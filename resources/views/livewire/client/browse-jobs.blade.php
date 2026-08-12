<div class="bj2-page">
    @php
        /** @var \Illuminate\Support\Collection<int, \App\Models\Department>|\App\Models\Department[] $departments */
        $isListView = ($display ?? 'grid') === 'list';
    @endphp

    <style>
        .bj2-page {
            --bj2-bg: #f7f8fb;
            --bj2-surface: #ffffff;
            --bj2-line: rgba(226, 232, 240, .95);
            --bj2-text: #0f172a;
            --bj2-muted: #64748b;
            --bj2-accent: #f37021;
            --bj2-accent-soft: rgba(243, 112, 33, .08);
        }

        .bj2-page .bj2-card {
            position: relative;
            border: 1px solid #e2e8f0 !important;
            border-radius: 20px !important;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        .bj2-page .bj2-card:hover {
            transform: translateY(-4px);
            border-color: rgba(243, 112, 33, 0.35) !important;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.09);
        }

        .bj2-page .bj2-card__inner {
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 1.25rem;
        }

        .bj2-page .bj2-card--list .bj2-card__inner {
            padding: 1.25rem;
        }

        .bj2-page .bj2-card__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 0.85rem;
        }

        .bj2-page .bj2-card__logo {
            width: 60px;
            height: 60px;
            flex-shrink: 0;
            border-radius: 14px;
            border: 1px solid #f1f5f9;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease;
        }

        .bj2-page .bj2-card:hover .bj2-card__logo {
            transform: scale(1.04);
        }

        .bj2-page .bj2-card__logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 8px;
        }

        .bj2-page .bj2-card__badges {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }

        .bj2-page .bj2-card__label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .02em;
            padding: .35rem .75rem;
            border-radius: 999px;
            white-space: nowrap;
            line-height: 1;
        }

        .bj2-page .bj2-card__deadline {
            font-size: .75rem;
            font-weight: 500;
            letter-spacing: .01em;
            padding: .35rem .75rem;
            border-radius: 999px;
            white-space: nowrap;
            background: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
            box-shadow: none;
        }

        .bj2-page .bj2-card__title {
            font-size: 1.12rem;
            font-weight: 700;
            line-height: 1.35;
            letter-spacing: -.01em;
            margin-bottom: .45rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.7em;
        }

        .bj2-page .bj2-card__title a {
            color: #0f172a;
            transition: color 0.2s ease;
        }

        .bj2-page .bj2-card:hover .bj2-card__title a {
            color: var(--bj2-accent);
        }

        .bj2-page .bj2-card__company {
            font-size: .88rem;
            color: #64748b;
            margin-bottom: .85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .bj2-page .bj2-card__company .fa {
            color: #94a3b8;
        }

        .bj2-page .bj2-card__info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: .85rem;
        }

        .bj2-page .bj2-card__location {
            font-size: .88rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .bj2-page .bj2-card__location .fa {
            color: var(--bj2-accent);
        }

        .bj2-page .bj2-card__salary {
            padding: .35rem .75rem;
            border-radius: 999px;
            background: rgba(243, 112, 33, .08);
            color: var(--bj2-accent);
            border: 1px solid rgba(243, 112, 33, .16);
            font-weight: 700;
            font-size: .88rem;
            white-space: nowrap;
            box-shadow: none;
            font-variant-numeric: tabular-nums;
            flex-shrink: 0;
        }

        .bj2-page .bj2-card__tags {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-bottom: .85rem;
        }

        .bj2-page .bj2-card__tag {
            display: inline-flex;
            align-items: center;
            padding: .3rem .7rem;
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 500;
            line-height: 1.2;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .bj2-page .bj2-card__tag--accent {
            background: rgba(243, 112, 33, .08);
            color: var(--bj2-accent);
            border-color: rgba(243, 112, 33, .14);
            font-weight: 600;
        }

        .bj2-page .bj2-card__tag--soft {
            background: #f8fafc;
            color: #64748b;
            border-color: #e2e8f0;
        }

        .bj2-page .bj2-card__actions {
            margin-top: auto;
            padding-top: .85rem;
            border-top: 1px solid #f1f5f9;
        }

        .bj2-page .bj2-card__actions .btn-detail {
            flex: 1;
            padding: .6rem .8rem;
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            font-weight: 600;
            font-size: .88rem;
            text-align: center;
            text-decoration: none;
            transition: all .2s ease;
        }

        .bj2-page .bj2-card__actions .btn-detail:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .bj2-page .bj2-card__actions .btn-apply {
            flex: 1.5;
            padding: .6rem 1rem;
            background: linear-gradient(135deg, #f37021 0%, #ff8c42 100%);
            color: #ffffff;
            border: none;
            border-radius: 999px;
            font-weight: 600;
            font-size: .88rem;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(243, 112, 33, 0.28);
            transition: all .2s ease;
        }

        .bj2-page .bj2-card__actions .btn-apply:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(243, 112, 33, 0.38);
            color: #ffffff;
        }

        @media (max-width: 575.98px) {
            .bj2-page .bj2-card__top {
                flex-direction: row;
            }

            .bj2-page .bj2-card__badges {
                align-items: flex-end;
            }

            .bj2-page .bj2-card__info {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* Pagination overrides */
        .bj2-pagination span[aria-current="page"] > span,
        .bj2-pagination .page-item.active .page-link,
        .bj2-pagination .active > .page-link {
            background-color: var(--bj2-accent) !important;
            border-color: var(--bj2-accent) !important;
            color: #ffffff !important;
            font-weight: 600;
        }

        .bj2-pagination button:hover,
        .bj2-pagination a:hover,
        .bj2-pagination .page-link:hover {
            background-color: var(--bj2-accent-soft) !important;
            border-color: var(--bj2-accent) !important;
            color: var(--bj2-accent) !important;
        }
    </style>

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
                            $departmentName = $job->department?->name;
                        @endphp

                        <article class="bj2-card {{ $isListView ? 'bj2-card--list' : '' }} d-flex flex-column h-100">
                            <div class="bj2-card__inner">
                                <div class="bj2-card__top">
                                    <a href="{{ $detailUrl }}" class="bj2-card__logo" aria-label="{{ $job->title }}">
                                        <img src="{{ $logoSrc }}" alt="{{ $branchName !== '' ? $branchName : 'Chi nhánh' }}">
                                    </a>

                                    <div class="bj2-card__badges">
                                        @php
                                            $matchLabel = $jobMatchLabels[$job->id] ?? null;
                                            $matchStyle = match ($matchLabel) {
                                                'Phù hợp cao' => ['bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
                                                'Phù hợp vừa' => ['bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a'],
                                                'Phù hợp thấp' => ['bg' => '#f8fafc', 'text' => '#64748b', 'border' => '#e2e8f0'],
                                                default => null,
                                            };
                                        @endphp

                                        @if ($matchLabel && $matchStyle)
                                            <span class="bj2-card__label badge rounded-pill" style="background: {{ $matchStyle['bg'] }}; color: {{ $matchStyle['text'] }}; border: 1px solid {{ $matchStyle['border'] }};">
                                                {{ $matchLabel }}
                                            </span>
                                        @endif

                                        <span class="bj2-card__deadline d-inline-flex align-items-center">
                                            <i class="fa fa-clock-o me-1"></i> Hạn nộp: {{ $deadlineText }}
                                        </span>
                                    </div>
                                </div>

                                <h3 class="bj2-card__title">
                                    <a href="{{ $detailUrl }}">{{ $job->title }}</a>
                                </h3>

                                <div class="bj2-card__company d-flex align-items-center" title="{{ $branchName !== '' ? $branchName : 'Doanh nghiệp nội bộ' }}">
                                    <i class="fa fa-building-o me-2"></i>
                                    <span>{{ $branchName !== '' ? $branchName : 'Doanh nghiệp nội bộ' }}</span>
                                </div>

                                <div class="bj2-card__info">
                                    <div class="bj2-card__location d-flex align-items-center" title="{{ $cityText }}">
                                        <i class="fa fa-map-marker me-2"></i>
                                        <span>{{ $cityText }}</span>
                                    </div>

                                    <span class="bj2-card__salary d-inline-flex align-items-center">
                                        <i class="fa fa-money me-1"></i> {{ $salaryText }}
                                    </span>
                                </div>

                                <div class="bj2-card__actions d-flex gap-2 w-100">
                                    <a href="{{ $detailUrl }}" class="btn-detail">Xem chi tiết</a>
                                    <a href="{{ $applyUrl }}" class="btn-apply">Ứng tuyển ngay</a>
                                </div>
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

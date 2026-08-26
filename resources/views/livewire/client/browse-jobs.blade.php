<div class="fpt-browse-page">
    @php
        /** @var \Illuminate\Support\Collection<int, \App\Models\Department>|\App\Models\Department[] $departments */
        $isListView = ($display ?? 'grid') === 'list';
        $hasActiveFilters = filled($q) || filled($city) || filled($department_id) || filled($category_id);
    @endphp

    <style>
        .fpt-browse-page {
            --fpt-bg: #f8fafc;
            --fpt-surface: #ffffff;
            --fpt-surface-subtle: #f1f5f9;
            --fpt-ink: #0f172a;
            --fpt-muted: #64748b;
            --fpt-line: #e2e8f0;
            --fpt-line-subtle: #f1f5f9;
            --fpt-primary: #f37021;
            --fpt-primary-hover: #ea580c;
            --fpt-primary-soft: rgba(243, 112, 33, 0.08);
            --fpt-primary-glow: rgba(243, 112, 33, 0.22);
            --fpt-ease: cubic-bezier(0.16, 1, 0.3, 1);
            
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--fpt-ink);
            background: var(--fpt-bg);
            min-height: 100vh;
            padding-top: 105px;
            padding-bottom: 80px;
        }

        .fpt-browse-page .fa,
        .fpt-browse-page i.fa {
            font-family: 'FontAwesome', FontAwesome !important;
            font-style: normal;
        }

        /* Hero Search Hub - Double-Bezel */
        .fpt-search-hero {
            position: relative;
            margin-bottom: 32px;
        }

        .fpt-search-hero__header {
            margin-bottom: 24px;
        }

        .fpt-search-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 999px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 12px;
            border: 1px solid rgba(243, 112, 33, 0.16);
        }

        .fpt-search-hero__title {
            font-size: clamp(24px, 3.5vw, 34px);
            font-weight: 900;
            color: var(--fpt-ink);
            letter-spacing: -0.03em;
            margin: 0 0 8px;
            line-height: 1.25;
        }

        .fpt-search-hero__subtitle {
            color: var(--fpt-muted);
            font-size: 14.5px;
            margin: 0;
            max-width: 680px;
            line-height: 1.55;
        }

        /* Outer Double-Bezel Tray */
        .fpt-search-tray {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 24px;
            padding: 8px;
            box-shadow: 0 20px 50px -10px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .fpt-search-inner {
            display: grid;
            grid-template-columns: 2fr 1.3fr 1.3fr auto;
            gap: 8px;
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--fpt-line-subtle);
            border-radius: 18px;
            padding: 6px;
        }

        @media (max-width: 991.98px) {
            .fpt-search-inner {
                grid-template-columns: 1fr 1fr;
            }
            .fpt-search-inner .fpt-search-item--submit {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 575.98px) {
            .fpt-search-inner {
                grid-template-columns: 1fr;
            }
        }

        .fpt-search-field {
            position: relative;
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 12px;
            padding: 0 14px;
            height: 50px;
            transition: all 0.2s var(--fpt-ease);
        }

        .fpt-search-field:focus-within {
            border-color: var(--fpt-primary);
            box-shadow: 0 0 0 3px rgba(243, 112, 33, 0.12);
        }

        .fpt-search-field i {
            color: #94a3b8;
            font-size: 14px;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .fpt-search-field input,
        .fpt-search-field select {
            width: 100%;
            border: none;
            background: transparent;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--fpt-ink);
            outline: none;
            padding: 0;
        }

        .fpt-search-field select {
            cursor: pointer;
        }

        .fpt-search-field input::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        .fpt-search-btn-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .fpt-search-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            height: 50px;
            padding: 0 24px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            border: none;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 8px 20px -4px rgba(243, 112, 33, 0.4);
            transition: all 0.25s var(--fpt-ease);
            white-space: nowrap;
        }

        .fpt-search-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -4px rgba(243, 112, 33, 0.5);
        }

        .fpt-search-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 50px;
            padding: 0 16px;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 12px;
            color: var(--fpt-muted);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s var(--fpt-ease);
            white-space: nowrap;
        }

        .fpt-search-reset:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fca5a5;
        }

        /* Quick Filter Chips */
        .fpt-quick-chips {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
            padding: 0 4px;
        }

        .fpt-quick-label {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--fpt-muted);
            margin-right: 4px;
        }

        .fpt-quick-chip {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s var(--fpt-ease);
            text-decoration: none !important;
        }

        .fpt-quick-chip:hover {
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            border-color: rgba(243, 112, 33, 0.3);
        }

        /* Toolbar Bar */
        .fpt-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            padding: 16px 20px;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
        }

        .fpt-toolbar__count {
            font-size: 14px;
            color: var(--fpt-muted);
            font-weight: 600;
        }

        .fpt-toolbar__count strong {
            color: var(--fpt-ink);
            font-weight: 800;
        }

        .fpt-view-toggle {
            display: inline-flex;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 10px;
            border: 1px solid var(--fpt-line);
            gap: 3px;
        }

        .fpt-view-btn {
            background: transparent;
            border: none;
            color: var(--fpt-muted);
            width: 34px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fpt-view-btn.is-active {
            background: #ffffff;
            color: var(--fpt-primary);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            font-weight: 700;
        }

        /* Job Cards Grid & Double-Bezel */
        .fpt-job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 22px;
        }

        .fpt-job-grid.is-list {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        @media (max-width: 575.98px) {
            .fpt-job-grid {
                grid-template-columns: 1fr;
            }
        }

        .fpt-card-shell {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 20px;
            padding: 6px;
            transition: all 0.3s var(--fpt-ease);
            position: relative;
            box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.04);
            display: flex;
            flex-direction: column;
        }

        .fpt-card-shell:hover {
            transform: translateY(-4px);
            border-color: rgba(243, 112, 33, 0.35);
            box-shadow: 0 20px 40px -10px rgba(243, 112, 33, 0.12), 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        .fpt-card-core {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .fpt-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }

        .fpt-card-logo-wrap {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            flex-shrink: 0;
            overflow: hidden;
            transition: transform 0.2s ease;
        }

        .fpt-card-shell:hover .fpt-card-logo-wrap {
            transform: scale(1.04);
        }

        .fpt-card-logo-wrap img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .fpt-card-status-badges {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }

        .fpt-match-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 999px;
            letter-spacing: 0.02em;
        }

        .fpt-match-badge.high {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .fpt-match-badge.medium {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .fpt-match-badge.low {
            background: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .fpt-card-deadline {
            font-size: 11.5px;
            color: #94a3b8;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .fpt-card-title {
            font-size: 16.5px;
            font-weight: 800;
            line-height: 1.35;
            margin: 0 0 8px;
        }

        .fpt-card-title a {
            color: var(--fpt-ink) !important;
            text-decoration: none !important;
            transition: color 0.2s ease;
        }

        .fpt-card-title a:hover {
            color: var(--fpt-primary) !important;
        }

        .fpt-card-company {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 14px;
        }

        .fpt-card-company i {
            color: #94a3b8;
            font-size: 12px;
        }

        .fpt-card-meta-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        .fpt-meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 8px;
            background: #f8fafc;
            color: #475569;
            border: 1px solid var(--fpt-line-subtle);
        }

        .fpt-meta-pill i {
            color: var(--fpt-primary);
            font-size: 11px;
        }

        .fpt-meta-pill.salary {
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            border-color: rgba(243, 112, 33, 0.16);
            font-weight: 700;
        }

        .fpt-card-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 18px;
        }

        .fpt-skill-tag {
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #475569;
        }

        .fpt-card-actions {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid var(--fpt-line-subtle);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .fpt-btn-detail {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 14px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            border-radius: 10px;
            color: #334155 !important;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }

        .fpt-btn-detail:hover {
            background: #f1f5f9;
            color: var(--fpt-ink) !important;
            border-color: #cbd5e1;
        }

        .fpt-btn-apply {
            flex: 1.4;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 16px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            border: none;
            border-radius: 10px;
            color: #ffffff !important;
            font-size: 12.5px;
            font-weight: 800;
            text-decoration: none !important;
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.25);
            transition: all 0.2s var(--fpt-ease);
        }

        .fpt-btn-apply:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(243, 112, 33, 0.35);
        }

        /* List View Overrides */
        .fpt-job-grid.is-list .fpt-card-core {
            display: grid;
            grid-template-columns: 54px minmax(0, 1fr) auto;
            gap: 20px;
            align-items: center;
            padding: 16px 20px;
        }

        .fpt-job-grid.is-list .fpt-card-top {
            display: contents;
        }

        .fpt-job-grid.is-list .fpt-card-content {
            min-width: 0;
        }

        .fpt-job-grid.is-list .fpt-card-actions {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
            flex-direction: column;
            min-width: 140px;
        }

        .fpt-job-grid.is-list .fpt-btn-detail,
        .fpt-job-grid.is-list .fpt-btn-apply {
            width: 100%;
        }

        @media (max-width: 767.98px) {
            .fpt-job-grid.is-list .fpt-card-core {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .fpt-job-grid.is-list .fpt-card-actions {
                flex-direction: row;
            }
        }

        /* Empty State */
        .fpt-empty-box {
            text-align: center;
            padding: 60px 24px;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
        }

        .fpt-empty-icon {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 16px;
        }

        .fpt-empty-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--fpt-ink);
            margin-bottom: 6px;
        }

        .fpt-empty-desc {
            color: var(--fpt-muted);
            font-size: 14px;
            max-width: 480px;
            margin: 0 auto 20px;
        }

        /* Custom Pagination */
        .fpt-pagination-wrap {
            margin-top: 40px;
            display: flex;
            justify-content: center;
        }

        .fpt-pagination-wrap .pagination {
            display: flex;
            gap: 6px;
        }

        .fpt-pagination-wrap .page-item .page-link {
            border-radius: 10px !important;
            border: 1px solid var(--fpt-line);
            color: #475569;
            font-weight: 600;
            padding: 8px 16px;
            font-size: 13.5px;
            transition: all 0.2s ease;
        }

        .fpt-pagination-wrap .page-item.active .page-link {
            background-color: var(--fpt-primary) !important;
            border-color: var(--fpt-primary) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.3);
        }

        .fpt-pagination-wrap .page-item .page-link:hover:not(.active) {
            background-color: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            border-color: var(--fpt-primary);
        }
    </style>

    <div class="container">
        {{-- Unified FPT Breadcrumb Bar --}}
        <div class="fpt-breadcrumb-bar" style="margin-bottom: 24px; padding-top: 0;">
            <div class="fpt-breadcrumb-inner">
                <ul class="fpt-breadcrumb-trail">
                    <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li class="current">Cơ hội việc làm FPT Education</li>
                </ul>

                <a href="{{ route('home') }}" class="fpt-back-btn">
                    <i class="fa fa-arrow-left"></i> Về trang chủ
                </a>
            </div>
        </div>

        {{-- Hero Search Hub (Double-Bezel Architecture) --}}
        <div class="fpt-search-hero">
            <div class="fpt-search-hero__header">
                <span class="fpt-search-hero__eyebrow">
                    <i class="fa fa-briefcase"></i> Tuyển dụng nội bộ & Đối ngoại
                </span>
                <h1 class="fpt-search-hero__title">Khám phá cơ hội nghề nghiệp tại FPT</h1>
                <p class="fpt-search-hero__subtitle">
                    Tìm kiếm vị trí phù hợp với năng lực của bạn tại các cơ sở đào tạo và đơn vị thành viên FPT Education trên toàn quốc.
                </p>
            </div>

            <div class="fpt-search-tray">
                <div class="fpt-search-inner">
                    {{-- Keyword Input --}}
                    <div class="fpt-search-field">
                        <i class="fa fa-search"></i>
                        <input
                            type="search"
                            placeholder="Vị trí tuyển dụng, kỹ năng (Laravel, AI, Kế toán...)"
                            wire:model.live.debounce.400ms="q"
                        >
                    </div>

                    {{-- Location City Dropdown --}}
                    <div class="fpt-search-field">
                        <i class="fa fa-map-marker"></i>
                        <select wire:model.live="city">
                            <option value="">Tất cả địa điểm</option>
                            @foreach(\App\Enums\VietnamProvince::cases() as $province)
                                <option value="{{ $province->value }}">{{ $province->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Action Button Group --}}
                    <div class="fpt-search-btn-group fpt-search-item--submit">
                        <button type="button" class="fpt-search-submit">
                            <i class="fa fa-search"></i>
                            <span>Tìm kiếm</span>
                        </button>

                        @if($hasActiveFilters)
                            <button
                                type="button"
                                class="fpt-search-reset"
                                wire:click="clearFilters"
                                title="Xóa tất cả bộ lọc"
                            >
                                <i class="fa fa-times"></i> Xóa lọc
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick Filter Chips --}}
            <div class="fpt-quick-chips">
                <span class="fpt-quick-label"><i class="fa fa-bolt text-warning"></i> Gợi ý tìm nhanh:</span>
                <button type="button" class="fpt-quick-chip" wire:click="$set('q', 'Lập trình viên')">Lập trình viên</button>
                <button type="button" class="fpt-quick-chip" wire:click="$set('q', 'Giảng viên')">Giảng viên Công nghệ</button>
                <button type="button" class="fpt-quick-chip" wire:click="$set('q', 'Tuyển sinh')">Cán bộ Tuyển sinh</button>
                <button type="button" class="fpt-quick-chip" wire:click="$set('q', 'Marketing')">Truyền thông & Marketing</button>
                <button type="button" class="fpt-quick-chip" wire:click="$set('q', 'Kế toán')">Kế toán / Tài chính</button>
            </div>
        </div>

        {{-- Toolbar Count & Layout View Switcher --}}
        <div class="fpt-toolbar">
            <div class="fpt-toolbar__count">
                Tìm thấy <strong>{{ number_format($jobs->total()) }}</strong> vị trí tuyển dụng phù hợp
                @if($hasActiveFilters)
                    <span class="badge ms-2" style="background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; font-size: 11.5px; font-weight: 700;">Đang lọc kết quả</span>
                @endif
            </div>

            <div class="fpt-view-toggle">
                <button
                    type="button"
                    class="fpt-view-btn {{ ! $isListView ? 'is-active' : '' }}"
                    wire:click="setDisplay('grid')"
                    title="Dạng lưới"
                >
                    <i class="fa fa-th-large"></i>
                </button>
                <button
                    type="button"
                    class="fpt-view-btn {{ $isListView ? 'is-active' : '' }}"
                    wire:click="setDisplay('list')"
                    title="Dạng danh sách"
                >
                    <i class="fa fa-bars"></i>
                </button>
            </div>
        </div>

        {{-- Job Cards Grid / List --}}
        <div class="fpt-job-grid {{ $isListView ? 'is-list' : '' }}">
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

                    $salaryText = $job->formatted_salary;
                    $deadlineText = $job->deadline?->format('d/m/Y') ?? 'Tuyển liên tục';
                    $matchLabel = $jobMatchLabels[$job->id] ?? null;
                    $matchClass = match ($matchLabel) {
                        'Phù hợp cao' => 'high',
                        'Phù hợp vừa' => 'medium',
                        default => 'low',
                    };
                @endphp

                <div class="fpt-card-shell">
                    <div class="fpt-card-core">
                        {{-- Top logo & status --}}
                        <div class="fpt-card-top">
                            <a href="{{ $detailUrl }}" class="fpt-card-logo-wrap" aria-label="{{ $job->title }}">
                                <img src="{{ $logoSrc }}" alt="{{ $branchName !== '' ? $branchName : 'Logo' }}">
                            </a>

                            <div class="fpt-card-status-badges">
                                @if ($matchLabel)
                                    <span class="fpt-match-badge {{ $matchClass }}">
                                        <i class="fa fa-check-circle"></i> {{ $matchLabel }}
                                    </span>
                                @endif

                                <span class="fpt-card-deadline">
                                    <i class="fa fa-clock-o"></i> Hạn: {{ $deadlineText }}
                                </span>
                            </div>
                        </div>

                        {{-- Middle content --}}
                        <div class="fpt-card-content">
                            <h3 class="fpt-card-title">
                                <a href="{{ $detailUrl }}">{{ $job->title }}</a>
                            </h3>

                            <div class="fpt-card-company" title="{{ $branchName !== '' ? $branchName : 'FPT Education' }}">
                                <i class="fa fa-building-o"></i>
                                <span>{{ $branchName !== '' ? $branchName : 'FPT Education' }}</span>
                            </div>

                            <div class="fpt-card-meta-row">
                                <span class="fpt-meta-pill" title="Địa điểm làm việc">
                                    <i class="fa fa-map-marker"></i> {{ $cityText }}
                                </span>

                                <span class="fpt-meta-pill salary" title="Mức thu nhập">
                                    <i class="fa fa-tag"></i> {{ $salaryText }}
                                </span>

                                @if($job->department?->name)
                                    <span class="fpt-meta-pill" title="Phòng ban">
                                        <i class="fa fa-folder-open-o"></i> {{ $job->department->name }}
                                    </span>
                                @endif
                            </div>

                            {{-- Skills tags --}}
                            @if($job->skills->isNotEmpty())
                                <div class="fpt-card-skills">
                                    @foreach($job->skills->take(3) as $skill)
                                        <span class="fpt-skill-tag">{{ $skill->name }}</span>
                                    @endforeach
                                    @if($job->skills->count() > 3)
                                        <span class="fpt-skill-tag">+{{ $job->skills->count() - 3 }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Footer Action Buttons --}}
                        <div class="fpt-card-actions">
                            <a href="{{ $detailUrl }}" class="fpt-btn-detail">Xem chi tiết</a>
                            <a href="{{ $applyUrl }}" class="fpt-btn-apply">
                                <span>Ứng tuyển ngay</span>
                                <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12" style="grid-column: 1 / -1;">
                    <div class="fpt-empty-box">
                        <div class="fpt-empty-icon">
                            <i class="fa fa-search"></i>
                        </div>
                        <h3 class="fpt-empty-title">Không tìm thấy việc làm phù hợp</h3>
                        <p class="fpt-empty-desc">
                            Không có kết quả nào khớp với tiêu chí tìm kiếm của bạn. Hãy thử thay đổi từ khóa hoặc xóa bớt các bộ lọc để xem nhiều cơ hội hơn.
                        </p>
                        @if($hasActiveFilters)
                            <button type="button" class="fpt-search-submit" wire:click="clearFilters">
                                <i class="fa fa-refresh"></i> Xem tất cả việc làm
                            </button>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Custom Pagination --}}
        @if ($jobs->hasPages())
            <div class="fpt-pagination-wrap">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>
</div>

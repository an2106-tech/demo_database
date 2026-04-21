<div>
    <div class="browse-companies-page">
        <style>
            .browse-companies-page {
                --company-primary: #2f7ff7;
                --company-accent: #ff6b35;
                --company-ink: #0f172a;
                --company-muted: #64748b;
                --company-line: rgba(226, 232, 240, .9);
                --company-soft: #f8fafc;
            }

            .browse-companies-page .filter-sidebar {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            .browse-companies-page .filter-card,
            .browse-companies-page .toolbar-card,
            .browse-companies-page .company-card,
            .browse-companies-page .summary-card {
                background: #fff;
                border: 1px solid var(--company-line);
                border-radius: 20px;
                box-shadow: 0 20px 50px rgba(15, 23, 42, .06);
            }

            .browse-companies-page .filter-card {
                padding: 22px 20px;
            }

            .browse-companies-page .filter-card h3 {
                margin: 0 0 18px;
                font-size: 17px;
                font-weight: 800;
                color: var(--company-ink);
            }

            .browse-companies-page .filter-helper {
                margin: -10px 0 16px;
                color: var(--company-muted);
                font-size: 13px;
                line-height: 1.6;
            }

            .browse-companies-page .filter-option {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 12px;
                border-radius: 14px;
                border: 1px solid transparent;
                transition: .2s ease;
            }

            .browse-companies-page .filter-option:hover {
                background: var(--company-soft);
                border-color: rgba(47, 127, 247, .14);
            }

            .browse-companies-page .filter-option+.filter-option {
                margin-top: 8px;
            }

            .browse-companies-page .filter-option input[type="radio"] {
                accent-color: var(--company-primary);
            }

            .browse-companies-page .filter-option label {
                margin: 0;
                color: #334155;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
            }

            .browse-companies-page .salary-value {
                width: 100%;
                height: 48px;
                border-radius: 14px;
                border: 1px solid rgba(148, 163, 184, .26);
                background: var(--company-soft);
                color: var(--company-accent);
                font-weight: 800;
                padding: 0 16px;
                margin-bottom: 18px;
            }

            .browse-companies-page .toolbar-card {
                padding: 24px;
                margin-bottom: 24px;
            }

            .browse-companies-page .toolbar-top {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 16px;
                align-items: center;
            }

            .browse-companies-page .search-row {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 230px;
                gap: 14px;
                align-items: stretch;
            }

            .browse-companies-page .search-box form {
                display: flex;
                align-items: stretch;
            }

            .browse-companies-page .search-box input,
            .browse-companies-page .city-search-input,
            .browse-companies-page .toolbar-button {
                height: 50px;
                border-radius: 14px;
                border: 1px solid rgba(148, 163, 184, .28);
                background: var(--company-soft);
                transition: .2s ease;
            }

            .browse-companies-page .search-box input,
            .browse-companies-page .city-search-input {
                width: 100%;
                padding: 0 16px;
                color: var(--company-ink);
                font-size: 14px;
            }

            .browse-companies-page .search-box input {
                border-radius: 14px 0 0 14px;
                border-right: 0;
            }

            .browse-companies-page .search-box button {
                width: 58px;
                border: 1px solid var(--company-primary);
                border-radius: 0 14px 14px 0;
                background: var(--company-primary);
                color: #fff;
            }

            .browse-companies-page .search-box input:focus,
            .browse-companies-page .city-search-input:focus,
            .browse-companies-page .toolbar-button:focus {
                outline: none;
                background: #fff;
                border-color: var(--company-primary);
                box-shadow: 0 0 0 4px rgba(47, 127, 247, .1);
            }

            .browse-companies-page .toolbar-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                width: 100%;
                padding: 0 16px;
                background: #fff;
                font-size: 14px;
                font-weight: 700;
                color: var(--company-ink);
            }

            .browse-companies-page .toolbar-summary {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                flex-wrap: wrap;
                margin-top: 18px;
                padding-top: 18px;
                border-top: 1px solid rgba(226, 232, 240, .8);
            }

            .browse-companies-page .summary-badge {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 10px 16px;
                border-radius: 999px;
                background: rgba(47, 127, 247, .08);
                color: var(--company-primary);
                font-size: 13px;
                font-weight: 800;
            }

            .browse-companies-page .active-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .browse-companies-page .filter-chip {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 8px 12px;
                border-radius: 999px;
                background: rgba(255, 107, 53, .08);
                color: #c2410c;
                font-size: 12px;
                font-weight: 700;
            }

            .browse-companies-page .city-dropdown-menu {
                width: 320px;
                padding: 0;
                border: 1px solid var(--company-line);
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 24px 50px rgba(15, 23, 42, .14);
            }

            .browse-companies-page .city-dropdown-head {
                padding: 16px 16px 12px;
                border-bottom: 1px solid rgba(226, 232, 240, .85);
                background: #fff;
            }

            .browse-companies-page .city-search-wrap {
                position: relative;
            }

            .browse-companies-page .city-search-wrap i {
                position: absolute;
                top: 50%;
                left: 14px;
                transform: translateY(-50%);
                color: var(--company-primary);
            }

            .browse-companies-page .city-search-input {
                padding-left: 40px;
                border-radius: 12px;
            }

            .browse-companies-page .city-list {
                max-height: 260px;
                overflow-y: auto;
                background: #fff;
            }

            .browse-companies-page .city-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px 16px;
                transition: background .2s ease;
            }

            .browse-companies-page .city-item:hover {
                background: var(--company-soft);
            }

            .browse-companies-page .city-item input {
                width: 16px;
                height: 16px;
                accent-color: var(--company-primary);
            }

            .browse-companies-page .city-item label {
                margin: 0;
                flex: 1;
                font-size: 14px;
                color: #334155;
                cursor: pointer;
            }

            .browse-companies-page .city-dropdown-foot {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 14px 16px;
                border-top: 1px solid rgba(226, 232, 240, .85);
                background: var(--company-soft);
            }

            .browse-companies-page .btn-clear-filter {
                background: transparent;
                border: 0;
                color: var(--company-muted);
                font-size: 13px;
                font-weight: 700;
                padding: 0;
            }

            .browse-companies-page .btn-apply-filter {
                height: 40px;
                padding: 0 18px;
                border-radius: 999px;
                border: 0;
                background: var(--company-accent);
                color: #fff;
                font-size: 13px;
                font-weight: 800;
            }

            .browse-companies-page .summary-card {
                padding: 18px 20px;
                margin-bottom: 18px;
            }

            .browse-companies-page .summary-card h4 {
                margin: 0;
                font-size: 16px;
                font-weight: 800;
                color: var(--company-ink);
            }

            .browse-companies-page .summary-card p {
                margin: 6px 0 0;
                color: var(--company-muted);
                font-size: 13px;
            }

            .browse-companies-page .company-card {
                padding: 24px;
                margin-bottom: 20px;
                transition: .2s ease;
            }

            .browse-companies-page .company-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 26px 60px rgba(15, 23, 42, .09);
                border-color: rgba(47, 127, 247, .26);
            }

            .browse-companies-page .company-card-inner {
                display: grid;
                grid-template-columns: 108px minmax(0, 1fr);
                gap: 22px;
                align-items: start;
            }

            .browse-companies-page .company-logo {
                width: 108px;
                height: 108px;
                border-radius: 24px;
                border: 1px solid rgba(226, 232, 240, .9);
                background: linear-gradient(180deg, #fff, #f8fafc);
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }

            .browse-companies-page .company-logo img {
                width: 76px;
                height: 76px;
                object-fit: contain;
            }

            .browse-companies-page .company-header {
                margin-bottom: 16px;
            }

            .browse-companies-page .company-title {
                margin: 0 0 8px;
                font-size: 22px;
                line-height: 1.35;
                font-weight: 900;
            }

            .browse-companies-page .company-title a {
                color: var(--company-ink);
            }

            .browse-companies-page .company-title a:hover {
                color: var(--company-primary);
            }

            .browse-companies-page .company-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .browse-companies-page .meta-pill {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                min-height: 38px;
                padding: 8px 14px;
                border-radius: 999px;
                font-size: 13px;
                font-weight: 700;
            }

            .browse-companies-page .meta-pill.jobs {
                background: rgba(47, 127, 247, .1);
                color: var(--company-primary);
            }

            .browse-companies-page .meta-pill.location {
                background: rgba(15, 23, 42, .05);
                color: #334155;
            }

            .browse-companies-page .meta-pill.status {
                background: rgba(16, 185, 129, .1);
                color: #047857;
            }

            .browse-companies-page .company-contact {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
                margin-bottom: 18px;
            }

            .browse-companies-page .contact-box {
                padding: 14px 16px;
                border-radius: 16px;
                background: var(--company-soft);
                border: 1px solid rgba(226, 232, 240, .8);
            }

            .browse-companies-page .contact-box span {
                display: block;
                margin-bottom: 4px;
                color: var(--company-muted);
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .04em;
            }

            .browse-companies-page .contact-box p {
                margin: 0;
                color: var(--company-ink);
                font-size: 14px;
                font-weight: 700;
                line-height: 1.5;
                word-break: break-word;
            }

            .browse-companies-page .featured-job {
                border-radius: 18px;
                border: 1px solid rgba(226, 232, 240, .85);
                background: linear-gradient(180deg, rgba(248, 250, 252, .95), #fff);
                padding: 18px;
            }

            .browse-companies-page .featured-job-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 14px;
            }

            .browse-companies-page .featured-job-head h5 {
                margin: 0;
                font-size: 15px;
                font-weight: 800;
                color: var(--company-ink);
            }

            .browse-companies-page .featured-job-head span {
                color: var(--company-muted);
                font-size: 12px;
                font-weight: 700;
            }

            .browse-companies-page .featured-job-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
            }

            .browse-companies-page .featured-job-title {
                min-width: 0;
            }

            .browse-companies-page .featured-job-title a {
                display: block;
                color: var(--company-ink);
                font-size: 15px;
                font-weight: 800;
                line-height: 1.5;
            }

            .browse-companies-page .featured-job-title a:hover {
                color: var(--company-primary);
            }

            .browse-companies-page .featured-job-sub {
                margin-top: 4px;
                color: var(--company-muted);
                font-size: 13px;
                line-height: 1.5;
            }

            .browse-companies-page .featured-job-side {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-shrink: 0;
            }

            .browse-companies-page .job-badge {
                padding: 9px 12px;
                border-radius: 12px;
                background: rgba(255, 107, 53, .08);
                color: #c2410c;
                font-size: 12px;
                font-weight: 800;
                white-space: nowrap;
            }

            .browse-companies-page .job-deadline {
                color: var(--company-muted);
                font-size: 12px;
                font-weight: 700;
                white-space: nowrap;
            }

            .browse-companies-page .company-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-top: 18px;
                padding-top: 18px;
                border-top: 1px solid rgba(226, 232, 240, .8);
            }

            .browse-companies-page .company-footer-note {
                color: var(--company-muted);
                font-size: 13px;
                font-weight: 600;
            }

            .browse-companies-page .jobguru-btn-2 {
                border-radius: 14px;
                padding: 11px 20px;
                font-weight: 800;
            }

            .browse-companies-page .empty-state {
                padding: 48px 20px;
                text-align: center;
                background: #fff;
                border: 1px solid var(--company-line);
                border-radius: 22px;
                box-shadow: 0 20px 50px rgba(15, 23, 42, .06);
            }

            .browse-companies-page .empty-state img {
                width: 150px;
                max-width: 100%;
            }

            .browse-companies-page .empty-state h4 {
                margin: 20px 0 10px;
                color: var(--company-ink);
                font-size: 20px;
                font-weight: 900;
            }

            .browse-companies-page .empty-state p {
                margin: 0;
                color: var(--company-muted);
                font-size: 14px;
                line-height: 1.7;
            }

            .browse-companies-page .pagination-wrap {
                margin-top: 28px;
            }

            @media (max-width: 1199px) {
                .browse-companies-page .search-row {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 991px) {

                .browse-companies-page .company-card-inner,
                .browse-companies-page .toolbar-top,
                .browse-companies-page .company-contact {
                    grid-template-columns: 1fr;
                }

                .browse-companies-page .company-logo {
                    width: 96px;
                    height: 96px;
                }
            }

            @media (max-width: 767px) {

                .browse-companies-page .featured-job-row,
                .browse-companies-page .company-footer,
                .browse-companies-page .toolbar-summary,
                .browse-companies-page .city-dropdown-foot {
                    flex-direction: column;
                    align-items: stretch;
                }

                .browse-companies-page .featured-job-side {
                    justify-content: space-between;
                }

                .browse-companies-page .city-dropdown-menu {
                    width: min(320px, calc(100vw - 32px));
                }
            }
        </style>

        <section class="jobguru-breadcromb-area">
            <div class="breadcromb-top section_100">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="breadcromb-box">
                                <h3>Danh sách chi nhánh tuyển dụng</h3>
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
                                    <li class="active-breadcromb"><a href="#">Danh sách chi nhánh</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="jobguru-top-job-area browse-page section_70">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-3">
                        <div class="filter-sidebar">
                            <div class="filter-card">
                                <h3>Ngày đăng tuyển</h3>
                                <p class="filter-helper">Lọc chi nhánh đang có tin mới nhất theo khoảng thời gian bạn quan
                                    tâm.</p>

                                <div class="filter-option">
                                    <input id="date_hour" name="date_filter" type="radio" value="hour"
                                        wire:model.live="date_filter">
                                    <label for="date_hour">Trong 1 giờ qua</label>
                                </div>
                                <div class="filter-option">
                                    <input id="date_24h" name="date_filter" type="radio" value="24h"
                                        wire:model.live="date_filter">
                                    <label for="date_24h">Trong 24 giờ qua</label>
                                </div>
                                <div class="filter-option">
                                    <input id="date_7d" name="date_filter" type="radio" value="7d"
                                        wire:model.live="date_filter">
                                    <label for="date_7d">Trong 7 ngày qua</label>
                                </div>
                                <div class="filter-option">
                                    <input id="date_14d" name="date_filter" type="radio" value="14d"
                                        wire:model.live="date_filter">
                                    <label for="date_14d">Trong 14 ngày qua</label>
                                </div>
                                <div class="filter-option">
                                    <input id="date_30d" name="date_filter" type="radio" value="30d"
                                        wire:model.live="date_filter">
                                    <label for="date_30d">Trong 30 ngày qua</label>
                                </div>
                                <div class="filter-option">
                                    <input id="date_all" name="date_filter" type="radio" value="all"
                                        wire:model.live="date_filter">
                                    <label for="date_all">Tất cả thời gian</label>
                                </div>
                            </div>

                            <div class="filter-card" wire:ignore>
                                <h3>Mức lương tối thiểu</h3>
                                <p class="filter-helper">Chỉ hiển thị chi nhánh có việc làm với mức lương tối đa phù hợp.
                                </p>
                                <input type="text" id="amount" class="salary-value" readonly>
                                <div id="slider-single"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="toolbar-card">
                            <div class="toolbar-top">
                                <div class="search-row">
                                    <div class="search-box">
                                        <form wire:submit.prevent="">
                                            <input type="search" wire:model.live.debounce.500ms="search"
                                                placeholder="Tìm chi nhánh, vị trí tuyển dụng hoặc địa điểm...">
                                            <button type="button" aria-label="Tìm kiếm">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="dropdown custom-location-dropdown" id="cityDropdown">
                                        <button class="toolbar-button dropdown-toggle" type="button"
                                            id="cityDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                            <i class="fa fa-map-marker"></i>
                                            {{ count($applied_cities) > 0 ? 'Địa điểm (' . count($applied_cities) . ')' : 'Chọn địa điểm' }}
                                        </button>

                                        <div class="dropdown-menu city-dropdown-menu" wire:ignore.self>
                                            <div class="city-dropdown-head">
                                                <div class="city-search-wrap">
                                                    <i class="fa fa-search"></i>
                                                    <input type="text" class="city-search-input"
                                                        wire:model.live.debounce.300ms="search_city_keyword"
                                                        placeholder="Nhập tỉnh/thành phố">
                                                </div>
                                            </div>

                                            <div class="city-list">
                                                @forelse ($provincesList as $value => $label)
                                                    <div class="city-item">
                                                        <input type="checkbox" wire:model="selected_cities"
                                                            id="loc_{{ $value }}" value="{{ $value }}">
                                                        <label for="loc_{{ $value }}">{{ $label }}</label>
                                                    </div>
                                                @empty
                                                    <div class="px-3 py-4 text-center text-muted">Không tìm thấy địa điểm
                                                        phù hợp.</div>
                                                @endforelse
                                            </div>

                                            <div class="city-dropdown-foot">
                                                <button type="button" wire:click="clearAllCities"
                                                    class="btn-clear-filter">Bỏ chọn tất cả</button>
                                                <button type="button" wire:click="applyCityFilter"
                                                    class="btn-apply-filter" onclick="closeCityDropdown()">
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="summary-badge">
                                    <i class="fa fa-building-o"></i>
                                    {{ $branches->total() }} chi nhánh phù hợp
                                </div>
                            </div>

                            <div class="toolbar-summary">
                                <div class="active-filters">
                                    @if (!empty($search))
                                        <span class="filter-chip">
                                            <i class="fa fa-search"></i>
                                            {{ $search }}
                                        </span>
                                    @endif

                                    @if (!empty($applied_cities))
                                        <span class="filter-chip">
                                            <i class="fa fa-map-marker"></i>
                                            {{ count($applied_cities) }} địa điểm đã chọn
                                        </span>
                                    @endif

                                    @if (($salary_min ?? 0) > 0)
                                        <span class="filter-chip">
                                            <i class="fa fa-money"></i>
                                            Từ {{ number_format($salary_min) }} VND
                                        </span>
                                    @endif
                                </div>

                                <div class="company-footer-note">
                                    Cập nhật danh sách chi nhánh đang có tin tuyển dụng còn hiệu lực.
                                </div>
                            </div>
                        </div>

                        <div class="summary-card">
                            <h4>Khám phá nhà tuyển dụng đang hoạt động</h4>
                            <p>Thông tin được sắp theo chi nhánh mới cập nhật gần đây, kèm vị trí tuyển dụng nổi bật và
                                mức lương tham khảo.</p>
                        </div>

                        @php
                            $visibleBranches = $branches->filter(fn($branch) => ((int) ($branch->published_jobs_count ?? 0)) > 0);
                        @endphp

                        @forelse ($visibleBranches as $branch)
                            @php
                                $featuredJob = $branch->recruitmentJobs->first();
                                $salaryText = 'Thỏa thuận';

                                if ($featuredJob) {
                                    if (is_array($featuredJob->salary_range) && isset($featuredJob->salary_range['min'], $featuredJob->salary_range['max'])) {
                                        $salaryText = number_format($featuredJob->salary_range['min']) . ' - ' . number_format($featuredJob->salary_range['max']) . ' VND';
                                    } elseif (is_array($featuredJob->salary_range) && count($featuredJob->salary_range) > 0) {
                                        $salaryText = implode(' - ', $featuredJob->salary_range);
                                    } elseif (!empty($featuredJob->salary_range)) {
                                        $salaryText = (string) $featuredJob->salary_range;
                                    }
                                }

                                $cityLabel = \App\Enums\VietnamProvince::tryFrom($branch->city)?->label() ?? $branch->city;
                            @endphp

                            <div class="company-card">
                                <div class="company-card-inner">
                                    <div class="company-logo">
                                        <img src="{{ $branch->image ? '/storage/' . ltrim($branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                            alt="{{ $branch->name }}">
                                    </div>

                                    <div>
                                        <div class="company-header">
                                            <h3 class="company-title">
                                                <a
                                                    href="{{ $featuredJob ? route('candidates.job_detail', ['id' => $featuredJob->id]) : '#' }}">
                                                    {{ $branch->name }}
                                                </a>
                                            </h3>

                                            <div class="company-meta">
                                                <span class="meta-pill jobs">
                                                    <i class="fa fa-briefcase"></i>
                                                    {{ $branch->published_jobs_count }} vị trí đang tuyển
                                                </span>
                                                <span class="meta-pill location">
                                                    <i class="fa fa-map-marker"></i>
                                                    {{ $cityLabel ?: 'Chưa cập nhật địa điểm' }}
                                                </span>
                                                <span class="meta-pill status">
                                                    <i class="fa fa-check-circle"></i>
                                                    {{ $branch->is_active ? 'Đang hoạt động' : 'Tạm ngưng' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="company-contact">
                                            <div class="contact-box">
                                                <span>Email liên hệ</span>
                                                <p>{{ $branch->email_contact ?: 'Chưa cập nhật email liên hệ' }}</p>
                                            </div>
                                            <div class="contact-box">
                                                <span>Địa chỉ</span>
                                                <p>{{ $branch->address ?: ($cityLabel ?: 'Chưa cập nhật địa chỉ') }}</p>
                                            </div>
                                        </div>

                                        @if ($featuredJob)
                                            <div class="featured-job">
                                                <div class="featured-job-head">
                                                    <h5>Vị trí nổi bật</h5>
                                                    <span>{{ $branch->recruitmentJobs->count() }} tin hiển thị</span>
                                                </div>

                                                <div class="featured-job-row">
                                                    <div class="featured-job-title">
                                                        <a
                                                            href="{{ route('candidates.job_detail', ['id' => $featuredJob->id]) }}">
                                                            {{ $featuredJob->title }}
                                                        </a>
                                                        <div class="featured-job-sub">
                                                            Tin tuyển dụng mới nhất từ công ty này.
                                                        </div>
                                                    </div>

                                                    <div class="featured-job-side">
                                                        <span class="job-badge">{{ $salaryText }}</span>
                                                        @if (!empty($featuredJob->deadline))
                                                            <span class="job-deadline">Hạn nộp:
                                                                {{ $featuredJob->deadline?->format('d/m/Y') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="company-footer">
                                            <div class="company-footer-note">
                                                Doanh nghiệp đang có nhu cầu tuyển dụng và còn hạn nhận hồ sơ.
                                            </div>

                                            @if ($featuredJob)
                                                <a href="{{ route('candidates.job_detail', ['id' => $featuredJob->id]) }}"
                                                    class="jobguru-btn-2">
                                                    Xem chi tiết
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <img src="{{ asset('assets/img/no-results.png') }}" alt="Không có dữ liệu">
                                <h4>Không tìm thấy công ty phù hợp</h4>
                                <p>Hãy thử thay đổi từ khóa, mở rộng khu vực tìm kiếm hoặc giảm mức lương tối thiểu để xem
                                    thêm kết quả.</p>
                            </div>
                        @endforelse

                        @if ($branches->hasPages())
                            <div class="pagination-wrap">
                                {{ $branches->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        @push('scripts')
            <script>
                function closeCityDropdown() {
                    const dropdownBtn = document.getElementById('cityDropdownBtn');
                    if (!dropdownBtn || typeof bootstrap === 'undefined') return;

                    const dropdownInstance = bootstrap.Dropdown.getInstance(dropdownBtn);
                    if (dropdownInstance) {
                        dropdownInstance.hide();
                    }
                }

                function initCompanySalarySlider() {
                    if (typeof $ === 'undefined' || !$("#slider-single").length || typeof $("#slider-single").slider !== 'function') {
                        return;
                    }

                    const currentValue = @json($salary_min ?? 0);
                    const slider = $("#slider-single");

                    if (slider.hasClass("ui-slider")) {
                        slider.slider("destroy");
                    }

                    slider.slider({
                        range: "min",
                        min: 0,
                        max: 10000,
                        value: currentValue,
                        step: 100,
                        slide: function (event, ui) {
                            $("#amount").val(ui.value.toLocaleString('vi-VN') + " VND");
                        },
                        stop: function (event, ui) {
                            const componentId = slider.closest('[wire\\:id]').attr('wire:id');
                            if (window.Livewire && componentId) {
                                window.Livewire.find(componentId)?.set('salary_min', ui.value);
                            }
                        }
                    });

                    $("#amount").val(currentValue.toLocaleString('vi-VN') + " VND");
                }

                document.addEventListener('livewire:init', initCompanySalarySlider);
                document.addEventListener('livewire:navigated', initCompanySalarySlider);
                window.addEventListener('load', initCompanySalarySlider);
                window.addEventListener('close-city-dropdown', closeCityDropdown);
            </script>
        @endpush
    </div>

</div>

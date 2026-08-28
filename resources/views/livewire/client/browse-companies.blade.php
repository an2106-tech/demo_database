<div class="fpt-browse-companies-page" x-data="{ cityDropdownOpen: false }">
    {{-- Top Unified Breadcrumb Bar --}}
    <div class="fpt-breadcrumb-bar">
        <div class="container-fluid px-lg-5">
            <div class="fpt-breadcrumb-inner">
                <ul class="fpt-breadcrumb-trail">
                    <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('candidates.browse_job') }}">Khám phá nghề nghiệp</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li class="current">Cơ sở & Đơn vị thành viên</li>
                </ul>

                <a href="{{ route('candidates.browse_job') }}" class="fpt-back-btn">
                    <i class="fa fa-search"></i> Tìm việc làm
                </a>
            </div>
        </div>
    </div>

    {{-- Hero Spotlight Section --}}
    <section class="fpt-companies-hero">
        <div class="container-fluid px-lg-5">
            <div class="fpt-hero-card">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="fpt-hero-eyebrow">
                            <i class="fa fa-building-o"></i> Mạng lưới đào tạo FPT Education
                        </span>
                        <h1 class="fpt-hero-title">
                            Hệ thống Cơ sở & Đơn vị Thành viên
                        </h1>
                        <p class="fpt-hero-desc">
                            Khám phá môi trường làm việc sáng tạo, năng động tại các trường đại học, cao đẳng, viện đào tạo quốc tế và trung tâm công nghệ thuộc Tập đoàn FPT trên toàn quốc.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div class="fpt-hero-badge">
                            <div class="fpt-hero-badge-icon"><i class="fa fa-map-marker"></i></div>
                            <div>
                                <strong>{{ $branches->total() }} Cơ sở tuyển dụng</strong>
                                <span>Đang mở các đợt tuyển dụng nhân sự</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Double-Bezel Search Tray --}}
                <div class="fpt-search-tray">
                    <div class="fpt-search-input-col keyword">
                        <i class="fa fa-search fpt-search-icon"></i>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Tìm theo tên trường, chi nhánh, địa chỉ hoặc vị trí tuyển..."
                            class="fpt-search-input"
                        >
                        @if($search)
                            <button type="button" class="fpt-clear-input" wire:click="$set('search', '')">
                                <i class="fa fa-times"></i>
                            </button>
                        @endif
                    </div>

                    {{-- City Filter Capsule --}}
                    <div class="fpt-search-input-col city" @click.outside="cityDropdownOpen = false">
                        <button
                            type="button"
                            class="fpt-city-dropdown-trigger"
                            @click="cityDropdownOpen = !cityDropdownOpen"
                        >
                            <i class="fa fa-map-marker fpt-search-icon text-primary"></i>
                            <span class="fpt-city-label">
                                @if(count($applied_cities) > 0)
                                    {{ count($applied_cities) }} Tỉnh/Thành phố đã chọn
                                @else
                                    Tất cả khu vực (Toàn quốc)
                                @endif
                            </span>
                            <i class="fa fa-angle-down ms-auto text-muted"></i>
                        </button>

                        {{-- Dropdown Panel --}}
                        <div class="fpt-city-dropdown-panel" x-show="cityDropdownOpen" x-transition.opacity.duration.150ms style="display: none;">
                            <div class="p-3 border-bottom">
                                <div class="position-relative">
                                    <input
                                        type="text"
                                        wire:model.live.debounce.200ms="search_city_keyword"
                                        placeholder="Tìm nhanh tỉnh thành..."
                                        class="fpt-city-search-box"
                                    >
                                    <i class="fa fa-search position-absolute text-muted" style="left: 12px; top: 12px; font-size: 12px;"></i>
                                </div>
                            </div>

                            <div class="fpt-city-options-list custom-scrollbar">
                                @forelse($provincesList as $code => $label)
                                    <label class="fpt-city-option">
                                        <input
                                            type="checkbox"
                                            wire:model="selected_cities"
                                            value="{{ $code }}"
                                        >
                                        <span>{{ $label }}</span>
                                    </label>
                                @empty
                                    <div class="p-3 text-muted text-center" style="font-size: 12.5px;">Không tìm thấy tỉnh thành phù hợp</div>
                                @endforelse
                            </div>

                            <div class="p-3 border-top bg-light d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-sm btn-link text-danger p-0" wire:click="clearAllCities" style="font-size: 12px; text-decoration: none;">
                                    Xóa chọn
                                </button>
                                <button type="button" class="btn btn-sm fpt-btn-apply-cities" wire:click="applyCityFilter">
                                    Áp dụng ({{ count($selected_cities) }})
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Explorer Workspace --}}
    <section class="fpt-companies-body">
        <div class="container-fluid px-lg-5">
            <div class="row">
                {{-- Left Filter Sidebar --}}
                <aside class="col-lg-3 col-md-4 mb-4 mb-md-0">
                    <div class="fpt-filter-panel">
                        <div class="fpt-filter-header">
                            <h3 class="fpt-filter-heading">
                                <i class="fa fa-sliders text-primary me-2"></i> Bộ lọc cơ sở
                            </h3>
                            @if($search || count($applied_cities) > 0 || $date_filter !== 'all' || (int)$salary_min > 0)
                                <button
                                    type="button"
                                    class="fpt-btn-reset-filters"
                                    wire:click="$set('search', ''); $set('date_filter', 'all'); $set('salary_min', 0); clearAllCities();"
                                >
                                    Đặt lại
                                </button>
                            @endif
                        </div>

                        {{-- Date Posted Filter --}}
                        <div class="fpt-filter-group">
                            <label class="fpt-filter-title">Thời gian đăng tuyển</label>
                            <div class="fpt-radio-stack">
                                <label class="fpt-radio-item {{ $date_filter === 'all' ? 'is-checked' : '' }}">
                                    <input type="radio" wire:model.live="date_filter" value="all">
                                    <span>Tất cả thời gian</span>
                                </label>
                                <label class="fpt-radio-item {{ $date_filter === '24h' ? 'is-checked' : '' }}">
                                    <input type="radio" wire:model.live="date_filter" value="24h">
                                    <span>24 giờ qua</span>
                                </label>
                                <label class="fpt-radio-item {{ $date_filter === '7d' ? 'is-checked' : '' }}">
                                    <input type="radio" wire:model.live="date_filter" value="7d">
                                    <span>7 ngày qua</span>
                                </label>
                                <label class="fpt-radio-item {{ $date_filter === '30d' ? 'is-checked' : '' }}">
                                    <input type="radio" wire:model.live="date_filter" value="30d">
                                    <span>30 ngày qua</span>
                                </label>
                            </div>
                        </div>

                        {{-- Minimum Salary Range Filter --}}
                        <div class="fpt-filter-group">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="fpt-filter-title mb-0">Mức lương tối thiểu</label>
                                <span class="fpt-salary-display">
                                    {{ (int)$salary_min > 0 ? number_format((int)$salary_min, 0, ',', '.') . ' VNĐ' : 'Tất cả' }}
                                </span>
                            </div>
                            <input
                                type="range"
                                min="0"
                                max="50000000"
                                step="2000000"
                                wire:model.live.debounce.300ms="salary_min"
                                class="fpt-salary-slider"
                            >
                            <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 11px;">
                                <span>0đ</span>
                                <span>25M</span>
                                <span>50M+</span>
                            </div>
                        </div>

                        {{-- Applied Cities Tags --}}
                        @if(count($applied_cities) > 0)
                            <div class="fpt-filter-group mb-0">
                                <label class="fpt-filter-title">Khu vực đã chọn</label>
                                <div class="fpt-applied-tags">
                                    @foreach($applied_cities as $cityCode)
                                        @php
                                            $cityName = \App\Enums\VietnamProvince::tryFrom((string)$cityCode)?->label() ?? $cityCode;
                                        @endphp
                                        <span class="fpt-applied-tag">
                                            <span>{{ $cityName }}</span>
                                            <button
                                                type="button"
                                                wire:click="$set('selected_cities', {{ json_encode(array_values(array_diff($selected_cities, [$cityCode]))) }}); $set('applied_cities', {{ json_encode(array_values(array_diff($applied_cities, [$cityCode]))) }});"
                                            >
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </aside>

                {{-- Right Main Branch Cards List --}}
                <main class="col-lg-9 col-md-8">
                    {{-- Toolbar Summary --}}
                    <div class="fpt-results-toolbar">
                        <div class="fpt-results-count">
                            Hiển thị <strong>{{ $branches->count() }}</strong> trên tổng số <strong>{{ $branches->total() }}</strong> cơ sở giáo dục
                        </div>
                        <div class="fpt-active-indicators">
                            <span class="fpt-indicator-dot"></span> Đang nhận hồ sơ tuyển dụng trực tiếp
                        </div>
                    </div>

                    {{-- Branch Cards Grid --}}
                    <div class="fpt-branches-grid" wire:loading.class="is-loading" wire:target="search,date_filter,salary_min,applyCityFilter,clearAllCities">
                        @forelse($branches as $branch)
                            @php
                                $cityLabel = \App\Enums\VietnamProvince::tryFrom((string) $branch->city)?->label() ?? $branch->city;
                                $logoUrl = $branch->image ? asset('storage/' . ltrim($branch->image, '/')) : asset('assets/img/company-logo-1.png');
                                $jobCount = $branch->published_jobs_count ?? $branch->recruitmentJobs->count();
                                $activeJobs = $branch->recruitmentJobs->take(3);
                            @endphp

                            <article class="fpt-branch-card">
                                <div class="fpt-branch-card-inner">
                                    {{-- Branch Top Identity Row --}}
                                    <div class="fpt-branch-head">
                                        <div class="fpt-branch-logo-wrap">
                                            <img
                                                src="{{ $logoUrl }}"
                                                alt="{{ $branch->name }}"
                                                onerror="this.onerror=null; this.src='{{ asset('assets/img/company-logo-1.png') }}';"
                                            >
                                        </div>

                                        <div class="fpt-branch-meta">
                                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-1">
                                                <span class="fpt-branch-city-badge">
                                                    <i class="fa fa-map-marker text-primary me-1"></i> {{ $cityLabel ?: 'Toàn quốc' }}
                                                </span>
                                                <span class="fpt-branch-open-badge">
                                                    <i class="fa fa-briefcase me-1 text-success"></i> <strong>{{ $jobCount }}</strong> vị trí đang mở
                                                </span>
                                            </div>

                                            <h2 class="fpt-branch-name">
                                                <a href="{{ route('employers.single_company', ['branch' => $branch->id]) }}">
                                                    {{ $branch->name }}
                                                </a>
                                            </h2>

                                            <p class="fpt-branch-address">
                                                <i class="fa fa-location-arrow text-muted me-1"></i> {{ $branch->address ?: 'Đang cập nhật địa chỉ cơ sở' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Active Jobs Preview Tray --}}
                                    @if($activeJobs->isNotEmpty())
                                        <div class="fpt-branch-jobs-tray">
                                            <span class="fpt-tray-label">Cơ hội việc làm nổi bật:</span>
                                            <div class="fpt-tray-list">
                                                @foreach($activeJobs as $job)
                                                    @php
                                                        $salary = $job->formatted_salary;
                                                    @endphp
                                                    <a
                                                        href="{{ route('candidates.job_detail', ['id' => $job->id]) }}"
                                                        class="fpt-job-capsule"
                                                        title="{{ $job->title }}"
                                                    >
                                                        <span class="fpt-job-capsule-title">{{ $job->title }}</span>
                                                        <span class="fpt-job-capsule-salary">{{ $salary }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Branch Footer Actions --}}
                                    <div class="fpt-branch-foot">
                                        <div class="fpt-branch-contacts">
                                            @if($branch->email_contact)
                                                <span class="fpt-contact-item" title="{{ $branch->email_contact }}">
                                                    <i class="fa fa-envelope-o me-1 text-muted"></i> {{ $branch->email_contact }}
                                                </span>
                                            @endif
                                        </div>

                                        <a
                                            href="{{ route('employers.single_company', ['branch' => $branch->id]) }}"
                                            class="fpt-btn-view-branch"
                                        >
                                            <span>Xem chi tiết cơ sở</span>
                                            <i class="fa fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="fpt-empty-branch-state">
                                <div class="fpt-empty-icon"><i class="fa fa-building-o"></i></div>
                                <h4 class="fpt-empty-title">Không tìm thấy cơ sở phù hợp</h4>
                                <p class="fpt-empty-desc">
                                    Thử điều chỉnh từ khóa tìm kiếm hoặc bỏ chọn các tiêu chí lọc tỉnh thành để hiển thị thêm các cơ sở giáo dục khác.
                                </p>
                                <button
                                    type="button"
                                    class="fpt-btn-clear-filters"
                                    wire:click="$set('search', ''); $set('date_filter', 'all'); $set('salary_min', 0); clearAllCities();"
                                >
                                    <i class="fa fa-refresh me-1"></i> Xóa tất cả bộ lọc
                                </button>
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    @if($branches->hasPages())
                        <div class="fpt-pagination-wrap">
                            {{ $branches->links() }}
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </section>

    {{-- Scoped High-End CSS --}}
    <style>
        .fpt-browse-companies-page {
            --fpt-bg: #f8fafc;
            --fpt-surface: #ffffff;
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
            background: #f8fafc;
            min-height: 100vh;
        }

        .fpt-browse-companies-page .fa {
            font-family: 'FontAwesome', FontAwesome !important;
            font-style: normal;
        }

        /* Hero Spotlight Section */
        .fpt-companies-hero {
            padding: 30px 0 20px;
        }

        .fpt-hero-card {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 24px;
            padding: 36px 40px;
            box-shadow: 0 16px 40px -8px rgba(15, 23, 42, 0.05);
            position: relative;
            overflow: visible;
        }

        .fpt-hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 12px;
            border: 1px solid rgba(243, 112, 33, 0.16);
        }

        .fpt-hero-title {
            font-size: clamp(24px, 2.8vw, 34px);
            font-weight: 900;
            color: var(--fpt-ink);
            letter-spacing: -0.025em;
            margin: 0 0 10px;
            line-height: 1.25;
        }

        .fpt-hero-desc {
            font-size: 14.5px;
            color: var(--fpt-muted);
            line-height: 1.6;
            margin: 0;
            max-width: 680px;
        }

        .fpt-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 14px 20px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            text-align: left;
        }

        .fpt-hero-badge-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .fpt-hero-badge strong {
            display: block;
            font-size: 15px;
            font-weight: 850;
            color: var(--fpt-ink);
        }

        .fpt-hero-badge span {
            font-size: 12px;
            color: var(--fpt-muted);
        }

        /* Search Tray */
        .fpt-search-tray {
            margin-top: 28px;
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            padding: 8px;
            border-radius: 16px;
        }

        @media (max-width: 767.98px) {
            .fpt-search-tray {
                grid-template-columns: 1fr;
            }
        }

        .fpt-search-input-col {
            position: relative;
            display: flex;
            align-items: center;
        }

        .fpt-search-icon {
            position: absolute;
            left: 16px;
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
        }

        .fpt-search-input {
            width: 100%;
            height: 48px;
            border-radius: 12px;
            border: 1px solid transparent;
            background: #ffffff;
            padding: 0 40px 0 44px;
            font-size: 14px;
            color: var(--fpt-ink);
            outline: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .fpt-search-input:focus {
            border-color: var(--fpt-primary);
            box-shadow: 0 0 0 3px rgba(243, 112, 33, 0.12);
        }

        .fpt-clear-input {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            font-size: 12px;
        }

        .fpt-city-dropdown-trigger {
            width: 100%;
            height: 48px;
            border-radius: 12px;
            border: 1px solid transparent;
            background: #ffffff;
            padding: 0 16px 0 44px;
            font-size: 13.5px;
            font-weight: 700;
            color: #334155;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .fpt-city-dropdown-trigger:hover {
            border-color: var(--fpt-line);
        }

        .fpt-city-dropdown-panel {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 320px;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 16px;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.18);
            z-index: 1000;
            overflow: hidden;
        }

        .fpt-city-search-box {
            width: 100%;
            height: 38px;
            border: 1px solid var(--fpt-line);
            border-radius: 10px;
            padding: 0 12px 0 34px;
            font-size: 12.5px;
            outline: none;
        }

        .fpt-city-options-list {
            max-height: 220px;
            overflow-y: auto;
            padding: 6px 12px;
        }

        .fpt-city-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            transition: background 0.15s ease;
            margin-bottom: 2px;
        }

        .fpt-city-option:hover {
            background: #f8fafc;
        }

        .fpt-city-option input {
            accent-color: var(--fpt-primary);
        }

        .fpt-btn-apply-cities {
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            font-weight: 800;
            font-size: 12px;
            border-radius: 8px;
            padding: 5px 14px;
            border: none;
        }

        /* Body Explorer Area */
        .fpt-companies-body {
            padding: 30px 0 70px;
        }

        /* Filter Panel */
        .fpt-filter-panel {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
            position: sticky;
            top: 100px;
        }

        .fpt-filter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--fpt-line-subtle);
        }

        .fpt-filter-heading {
            font-size: 15px;
            font-weight: 850;
            color: var(--fpt-ink);
            margin: 0;
        }

        .fpt-btn-reset-filters {
            background: transparent;
            border: none;
            color: #ef4444;
            font-size: 12px;
            font-weight: 750;
            cursor: pointer;
            padding: 0;
        }

        .fpt-filter-group {
            margin-bottom: 22px;
        }

        .fpt-filter-title {
            display: block;
            font-size: 12.5px;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 10px;
        }

        .fpt-radio-stack {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .fpt-radio-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid transparent;
            cursor: pointer;
            font-size: 13px;
            font-weight: 650;
            color: #475569;
            transition: all 0.18s ease;
        }

        .fpt-radio-item:hover {
            background: #f8fafc;
            border-color: var(--fpt-line);
        }

        .fpt-radio-item.is-checked {
            background: #fff8f3;
            border-color: rgba(243, 112, 33, 0.25);
            color: var(--fpt-primary);
            font-weight: 800;
        }

        .fpt-radio-item input {
            accent-color: var(--fpt-primary);
        }

        .fpt-salary-display {
            font-size: 12px;
            font-weight: 850;
            color: var(--fpt-primary);
        }

        .fpt-salary-slider {
            width: 100%;
            accent-color: var(--fpt-primary);
            cursor: pointer;
        }

        .fpt-applied-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .fpt-applied-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 8px;
            background: #fff8f3;
            border: 1px solid #fed7aa;
            font-size: 11.5px;
            font-weight: 750;
            color: #c2410c;
        }

        .fpt-applied-tag button {
            background: transparent;
            border: none;
            color: #c2410c;
            cursor: pointer;
            padding: 0;
            font-size: 10px;
        }

        /* Results Toolbar */
        .fpt-results-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            padding: 0 4px;
        }

        .fpt-results-count {
            font-size: 14px;
            color: var(--fpt-muted);
        }

        .fpt-results-count strong {
            color: var(--fpt-ink);
            font-weight: 800;
        }

        .fpt-active-indicators {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 750;
            color: #16a34a;
            background: #f0fdf4;
            padding: 4px 12px;
            border-radius: 999px;
            border: 1px solid #dcfce7;
        }

        .fpt-indicator-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);
        }

        /* Branch Cards Grid */
        .fpt-branches-grid {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .fpt-branches-grid.is-loading {
            opacity: 0.6;
        }

        .fpt-branch-card {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 20px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
            transition: all 0.25s var(--fpt-ease);
            overflow: hidden;
        }

        .fpt-branch-card:hover {
            transform: translateY(-2px);
            border-color: rgba(243, 112, 33, 0.3);
            box-shadow: 0 12px 30px -4px rgba(15, 23, 42, 0.08);
        }

        .fpt-branch-card-inner {
            padding: 24px;
        }

        .fpt-branch-head {
            display: flex;
            align-items: flex-start;
            gap: 18px;
        }

        .fpt-branch-logo-wrap {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        }

        .fpt-branch-logo-wrap img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .fpt-branch-meta {
            flex: 1;
            min-width: 0;
        }

        .fpt-branch-city-badge {
            display: inline-flex;
            align-items: center;
            font-size: 11.5px;
            font-weight: 750;
            color: #475569;
            background: #f1f5f9;
            padding: 3px 10px;
            border-radius: 6px;
        }

        .fpt-branch-open-badge {
            font-size: 12px;
            color: #16a34a;
            font-weight: 700;
        }

        .fpt-branch-name {
            font-size: 18px;
            font-weight: 850;
            margin: 6px 0 4px;
            line-height: 1.35;
        }

        .fpt-branch-name a {
            color: var(--fpt-ink) !important;
            text-decoration: none !important;
            transition: color 0.2s ease;
        }

        .fpt-branch-name a:hover {
            color: var(--fpt-primary) !important;
        }

        .fpt-branch-address {
            font-size: 13px;
            color: var(--fpt-muted);
            margin: 0;
            line-height: 1.5;
        }

        /* Active Jobs Preview Tray */
        .fpt-branch-jobs-tray {
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px solid var(--fpt-line-subtle);
        }

        .fpt-tray-label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            margin-bottom: 8px;
        }

        .fpt-tray-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .fpt-job-capsule {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            border-radius: 10px;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }

        .fpt-job-capsule:hover {
            background: #fff8f3;
            border-color: #fed7aa;
            transform: translateY(-1px);
        }

        .fpt-job-capsule-title {
            font-size: 12.5px;
            font-weight: 750;
            color: #1e293b;
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fpt-job-capsule-salary {
            font-size: 11.5px;
            font-weight: 800;
            color: var(--fpt-primary);
            background: #ffedd5;
            padding: 2px 6px;
            border-radius: 6px;
        }

        /* Branch Card Foot */
        .fpt-branch-foot {
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px solid var(--fpt-line-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .fpt-contact-item {
            font-size: 12px;
            color: #64748b;
        }

        .fpt-btn-view-branch {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            font-size: 12.5px;
            font-weight: 800;
            text-decoration: none !important;
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.25);
            transition: all 0.2s ease;
            margin-left: auto;
        }

        .fpt-btn-view-branch:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(243, 112, 33, 0.35);
        }

        /* Empty State */
        .fpt-empty-branch-state {
            padding: 60px 24px;
            text-align: center;
            background: #ffffff;
            border: 1px dashed var(--fpt-line);
            border-radius: 20px;
        }

        .fpt-empty-icon {
            width: 64px;
            height: 64px;
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
            font-weight: 850;
            color: var(--fpt-ink);
            margin: 0 0 6px;
        }

        .fpt-empty-desc {
            font-size: 13.5px;
            color: var(--fpt-muted);
            max-width: 440px;
            margin: 0 auto 20px;
            line-height: 1.6;
        }

        .fpt-btn-clear-filters {
            display: inline-flex;
            align-items: center;
            padding: 8px 20px;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid var(--fpt-line);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 750;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fpt-btn-clear-filters:hover {
            background: #e2e8f0;
            color: var(--fpt-ink);
        }

        .fpt-pagination-wrap {
            margin-top: 28px;
            display: flex;
            justify-content: center;
        }

        .fpt-pagination-wrap .pagination {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 0;
        }

        .fpt-pagination-wrap .page-link {
            min-width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 12px;
            border: 1px solid var(--fpt-line);
            border-radius: 9px !important;
            background: var(--fpt-surface);
            color: #475569;
            font-size: 13px;
            font-weight: 650;
            line-height: 1;
            text-decoration: none;
            transition: border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .fpt-pagination-wrap .page-link:hover {
            border-color: var(--fpt-primary);
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary-hover);
            transform: translateY(-1px);
        }

        .fpt-pagination-wrap .page-link:focus-visible {
            outline: 3px solid var(--fpt-primary-glow);
            outline-offset: 2px;
            box-shadow: none;
        }

        .fpt-pagination-wrap .page-item.active .page-link {
            border-color: var(--fpt-primary);
            background: var(--fpt-primary);
            color: #ffffff;
            box-shadow: 0 5px 14px var(--fpt-primary-glow);
        }

        .fpt-pagination-wrap .page-item.disabled .page-link {
            border-color: var(--fpt-line-subtle);
            background: #f8fafc;
            color: #94a3b8;
            transform: none;
        }
    </style>
</div>

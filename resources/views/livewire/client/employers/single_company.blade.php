@php
    $cityLabel = \App\Enums\VietnamProvince::tryFrom((string) $branch?->city)?->label() ?? $branch?->city;
    $logoUrl = $branch?->image ? asset('storage/' . ltrim($branch->image, '/')) : asset('assets/img/company-logo-1.png');
    $websiteUrl = $branch?->website && ! str_starts_with($branch->website, 'http')
        ? 'https://' . $branch->website
        : $branch?->website;
@endphp

<div class="fpt-single-company-page">
    {{-- Top Unified Breadcrumb Bar --}}
    <div class="fpt-breadcrumb-bar">
        <div class="container-fluid px-lg-5">
            <div class="fpt-breadcrumb-inner">
                <ul class="fpt-breadcrumb-trail">
                    <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('candidates.browse_companies') }}">Cơ sở & Đơn vị</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li class="current">{{ $branch->name }}</li>
                </ul>

                <a href="{{ route('candidates.browse_companies') }}" class="fpt-back-btn">
                    <i class="fa fa-arrow-left"></i> Tất cả cơ sở
                </a>
            </div>
        </div>
    </div>

    {{-- Company Vanguard Identity Hero --}}
    <section class="fpt-branch-hero-area">
        <div class="container-fluid px-lg-5">
            <div class="fpt-branch-hero-card">
                {{-- Profile Main Strip --}}
                <div class="fpt-branch-profile-strip">
                    <div class="fpt-branch-logo-frame">
                        <img
                            src="{{ $logoUrl }}"
                            alt="{{ $branch->name }}"
                            onerror="this.onerror=null; this.src='{{ asset('assets/img/company-logo-1.png') }}';"
                        >
                    </div>

                    <div class="fpt-branch-main-meta">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <span class="fpt-verified-badge">
                                <i class="fa fa-check-circle"></i> Đơn vị thành viên FPT Education
                            </span>
                            <span class="fpt-city-pill">
                                <i class="fa fa-map-marker text-primary me-1"></i> {{ $cityLabel ?: 'Toàn quốc' }}
                            </span>
                        </div>

                        <h1 class="fpt-branch-profile-title">{{ $branch->name }}</h1>

                        <p class="fpt-branch-profile-address">
                            <i class="fa fa-location-arrow text-muted me-1"></i> {{ $branch->address ?: 'Địa chỉ đang được cập nhật' }}
                        </p>
                    </div>

                    {{-- Hero Actions --}}
                    <div class="fpt-branch-hero-cta">
                        @if($jobs->isNotEmpty())
                            <a href="#active-jobs" class="fpt-btn-hero-apply">
                                <i class="fa fa-briefcase me-1"></i>
                                <span>Ứng tuyển ({{ $jobs->count() }} vị trí)</span>
                            </a>
                        @endif

                        @if($websiteUrl)
                            <a href="{{ $websiteUrl }}" target="_blank" rel="noopener noreferrer" class="fpt-btn-hero-web">
                                <i class="fa fa-external-link me-1"></i>
                                <span>Website đơn vị</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Key Stats Matrix --}}
                <div class="fpt-branch-stats-grid">
                    <div class="fpt-stat-item">
                        <div class="fpt-stat-icon"><i class="fa fa-briefcase"></i></div>
                        <div>
                            <strong>{{ $jobs->count() }}</strong>
                            <span>Vị trí đang mở tuyển</span>
                        </div>
                    </div>

                    <div class="fpt-stat-item">
                        <div class="fpt-stat-icon"><i class="fa fa-users"></i></div>
                        <div>
                            <strong>{{ $branch->employee_count ? number_format($branch->employee_count, 0, ',', '.') : '500+' }}</strong>
                            <span>Cán bộ giảng viên</span>
                        </div>
                    </div>

                    <div class="fpt-stat-item">
                        <div class="fpt-stat-icon"><i class="fa fa-shield"></i></div>
                        <div>
                            <strong>{{ $branch->code ?: 'FPT-EDU' }}</strong>
                            <span>Mã đơn vị / Chi nhánh</span>
                        </div>
                    </div>

                    <div class="fpt-stat-item">
                        <div class="fpt-stat-icon"><i class="fa fa-globe"></i></div>
                        <div>
                            <strong>{{ $branch->is_active ? 'Đang hoạt động' : 'Tạm dừng' }}</strong>
                            <span>Trạng thái đào tạo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Workspace Area --}}
    <section class="fpt-single-company-body">
        <div class="container-fluid px-lg-5">
            <div class="row">
                {{-- Left Main Details Column --}}
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="fpt-left-workspace">
                        {{-- About Branch Card --}}
                        <article class="fpt-card-widget">
                            <div class="fpt-widget-head">
                                <h3 class="fpt-widget-title">
                                    <i class="fa fa-info-circle text-primary me-2"></i> Giới thiệu về Cơ sở & Môi trường Giáo dục
                                </h3>
                            </div>
                            <div class="fpt-widget-body">
                                <div class="fpt-editorial-content">
                                    {!! nl2br(e($branch->description ?: 'Cơ sở thuộc Tổ chức Giáo dục FPT - FPT Education với sứ mệnh cung cấp năng lực cạnh tranh toàn cầu cho đông đảo người học, góp phần mở mang bờ cõi trí tuệ đất nước. Tại đây, đội ngũ cán bộ giảng viên được làm việc trong môi trường tôn trọng sự khác biệt, khuyến khích sáng tạo và đổi mới phương pháp giảng dạy.')) !!}
                                </div>

                                {{-- Highlights Grid --}}
                                <div class="fpt-highlights-grid mt-4">
                                    <div class="fpt-highlight-box">
                                        <i class="fa fa-graduation-cap text-primary"></i>
                                        <div>
                                            <strong>Chuẩn Quốc Tế</strong>
                                            <p>Chương trình đào tạo gắn liền với thực tiễn doanh nghiệp.</p>
                                        </div>
                                    </div>
                                    <div class="fpt-highlight-box">
                                        <i class="fa fa-heartbeat text-danger"></i>
                                        <div>
                                            <strong>Chăm Sóc Toàn Diện</strong>
                                            <p>Gói bảo hiểm FPT Care cho nhân viên và người thân.</p>
                                        </div>
                                    </div>
                                    <div class="fpt-highlight-box">
                                        <i class="fa fa-rocket text-success"></i>
                                        <div>
                                            <strong>Lộ Trình Rõ Ràng</strong>
                                            <p>Cơ hội thăng tiến và đào tạo nâng cao trình độ chuyên môn.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>

                        {{-- Active Recruitment Vacancies Card --}}
                        <article id="active-jobs" class="fpt-card-widget mt-4">
                            <div class="fpt-widget-head d-flex align-items-center justify-content-between">
                                <h3 class="fpt-widget-title">
                                    <i class="fa fa-briefcase text-primary me-2"></i> {{ $jobs->count() }} Vị trí đang mở tuyển dụng
                                </h3>
                                <span class="badge bg-light text-muted border px-3 py-2" style="font-size: 12px; font-weight: 750;">
                                    Cập nhật mới nhất
                                </span>
                            </div>

                            <div class="fpt-widget-body">
                                <div class="fpt-jobs-stack">
                                    @forelse($jobs as $job)
                                        @php
                                            $salaryText = $job->formatted_salary;
                                        @endphp

                                        <div class="fpt-single-job-item">
                                            <div class="fpt-job-item-header">
                                                <div>
                                                    <h4 class="fpt-job-item-title">
                                                        <a href="{{ route('candidates.job_detail', ['id' => $job->id]) }}">
                                                            {{ $job->title }}
                                                        </a>
                                                    </h4>
                                                    <div class="fpt-job-tags-line">
                                                        <span class="fpt-job-salary-tag">
                                                            <i class="fa fa-money me-1"></i> {{ $salaryText }}
                                                        </span>
                                                        <span class="fpt-job-type-tag">
                                                            <i class="fa fa-clock-o me-1"></i> {{ $job->workplace?->name ?: 'Toàn thời gian' }}
                                                        </span>
                                                        @if($job->department)
                                                            <span class="fpt-job-dept-tag">
                                                                <i class="fa fa-sitemap me-1"></i> {{ $job->department->name }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <a href="{{ route('candidates.job_detail', ['id' => $job->id]) }}" class="fpt-btn-apply-fast">
                                                    <span>Ứng tuyển ngay</span>
                                                    <i class="fa fa-arrow-right"></i>
                                                </a>
                                            </div>

                                            <p class="fpt-job-item-desc">
                                                {{ \Illuminate\Support\Str::limit(strip_tags((string) $job->description), 180) ?: 'Chi tiết công việc và các yêu cầu chuyên môn đang được cập nhật.' }}
                                            </p>

                                            <div class="fpt-job-item-footer">
                                                <span class="text-muted" style="font-size: 12px;">
                                                    <i class="fa fa-calendar-o me-1"></i> Hạn nộp: {{ $job->deadline ? $job->deadline->format('d/m/Y') : 'Tuyển liên tục' }}
                                                </span>
                                                <a href="{{ route('candidates.job_detail', ['id' => $job->id]) }}" class="fpt-link-more">
                                                    Chi tiết JD & Phúc lợi <i class="fa fa-angle-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="fpt-empty-jobs-notice">
                                            <i class="fa fa-clock-o mb-2" style="font-size: 28px; color: #94a3b8;"></i>
                                            <p class="mb-0">Hiện tại cơ sở chưa có vị trí mở tuyển mới. Vui lòng quay lại sau hoặc theo dõi trang tuyển dụng chung.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                {{-- Right Sidebar Info Column --}}
                <aside class="col-lg-4">
                    <div class="fpt-right-sidebar-stack">
                        {{-- Contact Box --}}
                        <div class="fpt-side-widget">
                            <h4 class="fpt-side-widget-title">
                                <i class="fa fa-address-book-o text-primary me-2"></i> Liên hệ tuyển dụng
                            </h4>
                            <ul class="fpt-side-contact-list">
                                <li>
                                    <div class="fpt-side-icon"><i class="fa fa-envelope-o"></i></div>
                                    <div class="fpt-side-text">
                                        <small>Email nhận hồ sơ</small>
                                        <span>{{ $branch->email_contact ?: 'tuyendung@fe.edu.vn' }}</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="fpt-side-icon"><i class="fa fa-phone"></i></div>
                                    <div class="fpt-side-text">
                                        <small>Hotline hỗ trợ</small>
                                        <span>{{ $branch->phone ?: '024 7300 1866' }}</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="fpt-side-icon"><i class="fa fa-globe"></i></div>
                                    <div class="fpt-side-text">
                                        <small>Trang thông tin</small>
                                        @if($websiteUrl)
                                            <a href="{{ $websiteUrl }}" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold text-decoration-none">
                                                {{ $branch->website }} <i class="fa fa-external-link ms-1" style="font-size: 10px;"></i>
                                            </a>
                                        @else
                                            <span>https://fpt.edu.vn</span>
                                        @endif
                                    </div>
                                </li>
                                <li>
                                    <div class="fpt-side-icon"><i class="fa fa-map-marker"></i></div>
                                    <div class="fpt-side-text">
                                        <small>Địa chỉ cơ sở</small>
                                        <span>{{ $branch->address ?: 'Đang cập nhật địa chỉ' }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        {{-- Social Links --}}
                        <div class="fpt-side-widget">
                            <h4 class="fpt-side-widget-title">
                                <i class="fa fa-share-alt text-primary me-2"></i> Kết nối mạng xã hội
                            </h4>
                            <div class="fpt-social-links-grid">
                                @if($branch->facebook_url)
                                    <a href="{{ $branch->facebook_url }}" target="_blank" rel="noopener noreferrer" class="fpt-social-btn fb">
                                        <i class="fa fa-facebook"></i> Facebook
                                    </a>
                                @endif
                                @if($branch->twitter_url)
                                    <a href="{{ $branch->twitter_url }}" target="_blank" rel="noopener noreferrer" class="fpt-social-btn tw">
                                        <i class="fa fa-twitter"></i> Twitter
                                    </a>
                                @endif
                                @if($branch->linkedin_url)
                                    <a href="{{ $branch->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="fpt-social-btn in">
                                        <i class="fa fa-linkedin"></i> LinkedIn
                                    </a>
                                @endif
                                @unless($branch->facebook_url || $branch->twitter_url || $branch->linkedin_url)
                                    <span class="text-muted" style="font-size: 13px;">Theo dõi Fanpage chính thức của FPT Education để cập nhật tin tức mới nhất.</span>
                                @endunless
                            </div>
                        </div>

                        {{-- Fast Campus Facts Box --}}
                        <div class="fpt-side-widget fpt-side-highlight-widget">
                            <h4 class="fpt-side-widget-title">
                                <i class="fa fa-star text-warning me-2"></i> Vì sao chọn FPT Education?
                            </h4>
                            <ul class="fpt-why-list">
                                <li><i class="fa fa-check text-primary me-2"></i> Môi trường giáo dục năng động, đề cao giá trị <strong>Tôn - Đổi - Đồng</strong>.</li>
                                <li><i class="fa fa-check text-primary me-2"></i> Thưởng hiệu quả kinh doanh & đãi ngộ nhân tài hấp dẫn.</li>
                                <li><i class="fa fa-check text-primary me-2"></i> Hỗ trợ học phí lên đến 50% cho con em cán bộ nhân viên FPT.</li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- Scoped High-End CSS --}}
    <style>
        .fpt-single-company-page {
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

        .fpt-single-company-page .fa {
            font-family: 'FontAwesome', FontAwesome !important;
            font-style: normal;
        }

        /* Hero Area */
        .fpt-branch-hero-area {
            padding: 30px 0 20px;
        }

        .fpt-branch-hero-card {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 16px 40px -8px rgba(15, 23, 42, 0.05);
        }

        .fpt-branch-profile-strip {
            padding: 32px 36px;
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
            background: linear-gradient(135deg, #ffffff 0%, #fffaf5 100%);
            border-bottom: 1px solid var(--fpt-line-subtle);
        }

        .fpt-branch-logo-frame {
            width: 84px;
            height: 84px;
            border-radius: 18px;
            background: #ffffff;
            border: 1.5px solid #fed7aa;
            padding: 8px;
            box-shadow: 0 4px 14px rgba(243, 112, 33, 0.08);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fpt-branch-logo-frame img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .fpt-branch-main-meta {
            flex: 1;
            min-width: 0;
        }

        .fpt-verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 800;
            color: #16a34a;
            background: #f0fdf4;
            padding: 3px 10px;
            border-radius: 999px;
            border: 1px solid #dcfce7;
        }

        .fpt-city-pill {
            display: inline-flex;
            align-items: center;
            font-size: 11.5px;
            font-weight: 750;
            color: #475569;
            background: #f1f5f9;
            padding: 3px 10px;
            border-radius: 999px;
        }

        .fpt-branch-profile-title {
            font-size: clamp(22px, 2.5vw, 30px);
            font-weight: 900;
            color: var(--fpt-ink);
            letter-spacing: -0.02em;
            margin: 6px 0 4px;
            line-height: 1.25;
        }

        .fpt-branch-profile-address {
            font-size: 13.5px;
            color: var(--fpt-muted);
            margin: 0;
            line-height: 1.5;
        }

        .fpt-branch-hero-cta {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .fpt-btn-hero-apply {
            display: inline-flex;
            align-items: center;
            padding: 11px 22px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            font-size: 13.5px;
            font-weight: 800;
            text-decoration: none !important;
            box-shadow: 0 4px 14px rgba(243, 112, 33, 0.3);
            transition: all 0.2s ease;
        }

        .fpt-btn-hero-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(243, 112, 33, 0.4);
        }

        .fpt-btn-hero-web {
            display: inline-flex;
            align-items: center;
            padding: 11px 18px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            color: #334155 !important;
            font-size: 13.5px;
            font-weight: 750;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }

        .fpt-btn-hero-web:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        /* Stats Grid */
        .fpt-branch-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            border-top: 1px solid var(--fpt-line);
            background: #f8fafc;
        }

        @media (max-width: 991.98px) {
            .fpt-branch-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .fpt-branch-stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .fpt-stat-item {
            padding: 18px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-right: 1px solid var(--fpt-line);
        }

        .fpt-stat-item:last-child {
            border-right: none;
        }

        .fpt-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            color: var(--fpt-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .fpt-stat-item strong {
            display: block;
            font-size: 16px;
            font-weight: 850;
            color: var(--fpt-ink);
            line-height: 1.2;
        }

        .fpt-stat-item span {
            font-size: 12px;
            color: var(--fpt-muted);
        }

        /* Workspace */
        .fpt-single-company-body {
            padding: 24px 0 70px;
        }

        .fpt-card-widget {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 20px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
            overflow: hidden;
        }

        .fpt-widget-head {
            padding: 20px 24px;
            border-bottom: 1px solid var(--fpt-line-subtle);
        }

        .fpt-widget-title {
            font-size: 16px;
            font-weight: 850;
            color: var(--fpt-ink);
            margin: 0;
        }

        .fpt-widget-body {
            padding: 24px;
        }

        .fpt-editorial-content {
            font-size: 14.5px;
            line-height: 1.75;
            color: #334155;
        }

        .fpt-highlights-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        @media (max-width: 767.98px) {
            .fpt-highlights-grid {
                grid-template-columns: 1fr;
            }
        }

        .fpt-highlight-box {
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            border-radius: 14px;
            padding: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .fpt-highlight-box i {
            font-size: 20px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .fpt-highlight-box strong {
            display: block;
            font-size: 13.5px;
            font-weight: 800;
            color: var(--fpt-ink);
            margin-bottom: 2px;
        }

        .fpt-highlight-box p {
            font-size: 12px;
            color: var(--fpt-muted);
            margin: 0;
            line-height: 1.45;
        }

        /* Jobs Stack */
        .fpt-jobs-stack {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .fpt-single-job-item {
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            border-radius: 16px;
            padding: 20px;
            transition: all 0.22s var(--fpt-ease);
        }

        .fpt-single-job-item:hover {
            background: #ffffff;
            border-color: rgba(243, 112, 33, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.06);
        }

        .fpt-job-item-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .fpt-job-item-title {
            font-size: 16px;
            font-weight: 850;
            margin: 0 0 6px;
            line-height: 1.35;
        }

        .fpt-job-item-title a {
            color: var(--fpt-ink) !important;
            text-decoration: none !important;
            transition: color 0.2s ease;
        }

        .fpt-job-item-title a:hover {
            color: var(--fpt-primary) !important;
        }

        .fpt-job-tags-line {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .fpt-job-salary-tag {
            font-size: 12px;
            font-weight: 800;
            color: #c2410c;
            background: #ffedd5;
            padding: 3px 10px;
            border-radius: 6px;
            border: 1px solid #fed7aa;
        }

        .fpt-job-type-tag, .fpt-job-dept-tag {
            font-size: 12px;
            font-weight: 650;
            color: #475569;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            padding: 3px 10px;
            border-radius: 6px;
        }

        .fpt-btn-apply-fast {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            font-size: 12.5px;
            font-weight: 800;
            text-decoration: none !important;
            box-shadow: 0 4px 10px rgba(243, 112, 33, 0.25);
            transition: all 0.2s ease;
        }

        .fpt-btn-apply-fast:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(243, 112, 33, 0.35);
        }

        .fpt-job-item-desc {
            font-size: 13.5px;
            color: #475569;
            margin: 12px 0;
            line-height: 1.6;
        }

        .fpt-job-item-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid rgba(226, 232, 240, 0.6);
        }

        .fpt-link-more {
            font-size: 12.5px;
            font-weight: 750;
            color: var(--fpt-primary) !important;
            text-decoration: none !important;
        }

        /* Sidebar Stack */
        .fpt-right-sidebar-stack {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .fpt-side-widget {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
        }

        .fpt-side-widget-title {
            font-size: 15px;
            font-weight: 850;
            color: var(--fpt-ink);
            margin: 0 0 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--fpt-line-subtle);
        }

        .fpt-side-contact-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .fpt-side-contact-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .fpt-side-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            color: var(--fpt-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .fpt-side-text small {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .fpt-side-text span, .fpt-side-text a {
            font-size: 13.5px;
            color: var(--fpt-ink);
            word-break: break-word;
            line-height: 1.4;
        }

        .fpt-social-links-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .fpt-social-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 750;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }

        .fpt-social-btn.fb { background: #eff6ff; color: #1d4ed8 !important; border: 1px solid #dbeafe; }
        .fpt-social-btn.tw { background: #f0f9ff; color: #0284c7 !important; border: 1px solid #e0f2fe; }
        .fpt-social-btn.in { background: #f8fafc; color: #0f172a !important; border: 1px solid #e2e8f0; }

        .fpt-social-btn:hover {
            transform: translateY(-1px);
        }

        /* Why FPT Education Highlight Widget */
        .fpt-side-highlight-widget {
            background: linear-gradient(135deg, #fffaf5 0%, #ffffff 100%);
            border: 1.5px solid #fed7aa;
            box-shadow: 0 4px 16px rgba(243, 112, 33, 0.06);
        }

        .fpt-side-highlight-widget .fpt-side-widget-title {
            color: #c2410c !important;
            border-bottom-color: #ffedd5;
        }

        .fpt-why-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 13px;
            line-height: 1.55;
            color: #334155;
        }
    </style>
</div>

<div>
    @php
        /** @var \Illuminate\Support\Collection<int, \App\Models\Department>|\App\Models\Department[] $departments */
        /** @var \Illuminate\Support\Collection<int, \App\Models\RecruitmentJob>|\App\Models\RecruitmentJob[] $featuredJobs */
    @endphp
    <section class="home-hero home-premium-hero">
        <div class="container-fluid px-0">
            <div class="row g-0">
                <div class="col-12">
                    <div class="home-premium-hero-banner">
                        <div class="banner-slider owl-carousel">
                            <div class="banner-single-slider slider-item-1"></div>
                            <div class="banner-single-slider slider-item-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="jobguru-categories-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="site-heading text-center mb-5">
                        <span class="badge badge-subtle mb-2">DANH MỤC NGÀNH NGHỀ</span>
                        <h2 class="fw-bold mb-3">Khám phá các lĩnh vực <span class="text-primary">nổi bật</span></h2>
                        <p class="text-muted lead">Chọn ngành nghề phù hợp với kinh nghiệm và tham vọng của bạn. Khám phá các cơ hội tuyển dụng đa dạng từ các công ty hàng đầu.</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                @forelse($categories as $category)
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <a href="{{ route('candidates.browse_job', ['category_id' => $category->id]) }}" class="category-card h-100 d-flex flex-column position-relative overflow-hidden">
                            <!-- Background Image -->
                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="category-card__bg" loading="lazy" decoding="async" />
                            
                            <!-- Gradient Overlay -->
                            <div class="category-card__overlay"></div>
                            
                            <!-- Content -->
                            <div class="category-card__content position-relative z-2 d-flex flex-column h-100">
                                <div class="category-card__icon mb-3">
                                    @php
                                        $icon = trim((string) ($category->icon ?? ''));
                                    @endphp
                                    <i class="{{ $icon !== '' ? (\Illuminate\Support\Str::startsWith($icon, 'bi') ? $icon : 'bi bi-' . $icon) : 'bi bi-grid' }}"></i>
                                </div>
                                
                                <div class="flex-grow-1">
                                    <h3 class="category-card__title fw-bold mb-2">{{ $category->name }}</h3>
                                </div>
                                
                                <div class="category-card__meta d-flex align-items-center justify-content-between">
                                    <span class="category-card__count">
                                        <i class="bi bi-briefcase-fill me-1"></i>
                                        {{ $category->recruitment_jobs_count ?? 0 }}
                                    </span>
                                    <span class="category-card__arrow">
                                        <i class="bi bi-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center py-4">
                            <i class="bi bi-info-circle me-2"></i>
                            Không có danh mục nào
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="row mt-5">
                <div class="col-md-12 text-center">
                    <a href="{{ route('candidates.browse_categories') }}" class="btn btn-primary btn-lg px-5 rounded-pill fw-semibold">
                        <i class="bi bi-compass me-2"></i>
                        Khám phá tất cả ngành nghề
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="home-premium-why section_70">
        <div class="container">
            <div class="row mb-4">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-2">Vì sao chọn <span class="text-primary">nền tảng của chúng tôi</span></h2>
                    <p class="text-muted mb-0">Trải nghiệm tìm việc &amp; tuyển dụng được tối ưu cho người dùng Việt Nam
                        — nhanh, minh bạch, có quy trình.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="home-premium-feature h-100">
                        <div class="home-premium-feature__icon"><i class="fa fa-bolt"></i></div>
                        <h3 class="h5 fw-bold">Ứng tuyển nhanh</h3>
                        <p class="text-muted small mb-0">Hồ sơ tập trung, nộp đơn vài bước, theo dõi trạng thái rõ ràng.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="home-premium-feature h-100">
                        <div class="home-premium-feature__icon home-premium-feature__icon--blue"><i
                                class="fa fa-filter"></i></div>
                        <h3 class="h5 fw-bold">Lọc thông minh</h3>
                        <p class="text-muted small mb-0">Từ khóa, khu vực, phòng ban — tìm đúng vị trí bạn cần.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="home-premium-feature h-100">
                        <div class="home-premium-feature__icon home-premium-feature__icon--green"><i
                                class="fa fa-shield"></i></div>
                        <h3 class="h5 fw-bold">Dữ liệu an toàn</h3>
                        <p class="text-muted small mb-0">Tài khoản phân quyền, bảo mật thông tin ứng viên &amp; doanh
                            nghiệp.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="home-premium-feature h-100">
                        <div class="home-premium-feature__icon"><i class="fa fa-building"></i></div>
                        <h3 class="h5 fw-bold">Đa chi nhánh</h3>
                        <p class="text-muted small mb-0">Tập đoàn quản lý nhiều điểm tuyển, thống nhất quy trình.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="home-premium-feature h-100">
                        <div class="home-premium-feature__icon home-premium-feature__icon--blue"><i
                                class="fa fa-comments"></i></div>
                        <h3 class="h5 fw-bold">Trao đổi tập trung</h3>
                        <p class="text-muted small mb-0">Kết nối nhà tuyển dụng — ứng viên trong một luồng xử lý.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="home-premium-feature h-100">
                        <div class="home-premium-feature__icon home-premium-feature__icon--green"><i
                                class="fa fa-line-chart"></i></div>
                        <h3 class="h5 fw-bold">Tối ưu tuyển dụng</h3>
                        <p class="text-muted small mb-0">Theo dõi pipeline, hạn nộp hồ sơ và hiệu quả từng tin đăng.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-ecosystem section_70" style="background: #fff;">
        <div class="container">
            <div class="row align-items-center mb-5">
                <div class="col-lg-7">
                    <h2 class="fw-bold display-5 mb-3">Hệ sinh thái <span class="text-orange">công nghệ nhân sự</span></h2>
                    <p class="mb-0 text-muted">Kết nối ứng viên, nhà tuyển dụng và dữ liệu tuyển dụng trong một nền tảng thông minh — nhanh chóng, minh bạch và tối ưu mọi bước tuyển dụng.</p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <a href="{{ route('pages.contact') }}" class="btn btn-orange btn-lg rounded-pill px-4 fw-semibold">Liên hệ tư vấn</a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="ecosystem-stat-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm">
                        <div class="fw-bold display-6 text-orange">540.000+</div>
                        <p class="mb-0 text-muted">Hồ sơ ứng viên đã đăng</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="ecosystem-stat-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm">
                        <div class="fw-bold display-6 text-orange">200.000+</div>
                        <p class="mb-0 text-muted">Doanh nghiệp đang tìm kiếm nhân sự</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="ecosystem-stat-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm">
                        <div class="fw-bold display-6 text-orange">2.000.000+</div>
                        <p class="mb-0 text-muted">Lượt kết nối công việc mỗi năm</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="ecosystem-stat-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm">
                        <div class="fw-bold display-6 text-orange">1.200.000+</div>
                        <p class="mb-0 text-muted">Ứng viên và nhà tuyển dụng đã tiếp cận</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="ecosystem-product-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm border border-1 border-orange-light">
                        <div class="mb-3">
                            <span class="badge badge-orange">1</span>
                        </div>
                        <h3 class="h5 fw-bold">Jobguru.vn</h3>
                        <p class="text-muted small">Kênh tìm việc và tuyển dụng nhanh dành cho ứng viên và nhà tuyển dụng.</p>
                        <a href="{{ route('candidates.browse_job') }}" class="stretched-link text-decoration-none text-orange">Khám phá ngay</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="ecosystem-product-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm border border-1 border-orange-light">
                        <div class="mb-3">
                            <span class="badge badge-orange">2</span>
                        </div>
                        <h3 class="h5 fw-bold">Hồ sơ ứng viên</h3>
                        <p class="text-muted small">Tạo CV, cập nhật kỹ năng và đăng hồ sơ dễ dàng trong tài khoản cá nhân.</p>
                        <a href="{{ route('candidates.submit_resume') }}" class="stretched-link text-decoration-none text-orange">Cập nhật CV</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="ecosystem-product-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm border border-1 border-orange-light">
                        <div class="mb-3">
                            <span class="badge badge-orange">3</span>
                        </div>
                        <h3 class="h5 fw-bold">Tin tức tuyển dụng</h3>
                        <p class="text-muted small">Nội dung ngành nghề, tips phỏng vấn và cập nhật xu hướng việc làm.</p>
                        <a href="{{ route('pages.blog') }}" class="stretched-link text-decoration-none text-orange">Xem bài viết</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="ecosystem-product-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm border border-1 border-orange-light">
                        <div class="mb-3">
                            <span class="badge badge-orange">4</span>
                        </div>
                        <h3 class="h5 fw-bold">Liên hệ doanh nghiệp</h3>
                        <p class="text-muted small">Hỗ trợ kết nối tuyển dụng, hợp tác tuyển dụng và tư vấn doanh nghiệp.</p>
                        <a href="{{ route('pages.contact') }}" class="stretched-link text-decoration-none text-orange">Liên hệ ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .home-ecosystem {
            background: #fff;
            color: #212529;
        }
        .home-ecosystem h2 {
            color: #111;
        }
        .home-ecosystem .text-orange {
            color: #f37021 !important;
        }
        .home-ecosystem p.text-muted,
        .home-ecosystem .ecosystem-product-card p,
        .home-ecosystem .ecosystem-stat-card p {
            color: #6c757d !important;
        }
        .home-ecosystem .btn-orange {
            background-color: #f37021;
            border-color: #f37021;
            color: #fff;
        }
        .home-ecosystem .btn-orange:hover,
        .home-ecosystem .btn-orange:focus {
            background-color: #d95b10;
            border-color: #d95b10;
            color: #fff;
        }
        .home-ecosystem .border-orange-light {
            border-color: rgba(243, 112, 33, .15) !important;
        }
        .home-ecosystem .badge-orange {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 12px;
            background-color: #f37021;
            color: #fff;
            font-weight: 700;
        }
        .home-ecosystem .ecosystem-stat-card {
            min-height: 160px;
            border: 1px solid rgba(243, 112, 33, .15);
        }
        .home-ecosystem .ecosystem-stat-card .display-6 {
            font-size: 2.4rem;
            line-height: 1;
        }
        .home-ecosystem .ecosystem-product-card {
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .home-ecosystem .ecosystem-product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 22px 45px rgba(243, 112, 33, .15);
        }
        .home-ecosystem .ecosystem-product-card h3 {
            min-height: 3rem;
        }
        .home-ecosystem a.stretched-link::after {
            z-index: 1;
        }
    </style>

    <section class="home-premium-spotlight section_70">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Việc làm <span class="text-primary">nổi bật</span></h2>
                    <p class="text-muted mb-0">Tuyển gấp, lương cạnh tranh — cập nhật liên tục.</p>
                </div>
                <a href="{{ route('candidates.browse_job') }}" class="jobguru-btn">Xem tất cả</a>
            </div>
            <div class="row g-4">
                @php
                    $homeFeaturedJobs = isset($featuredJobs) ? $featuredJobs : collect();
                @endphp
                @if ($homeFeaturedJobs->isEmpty())
                    <div class="col-12">
                        <div class="alert alert-light border text-center mb-0">Chưa có việc làm nổi bật. Quay lại sau nhé!
                        </div>
                    </div>
                @else
                    @foreach ($homeFeaturedJobs as $spotlight)
                        @php
                            $sr = is_array($spotlight->salary_range) ? $spotlight->salary_range : [];
                            $salaryLabel = isset($sr['min'], $sr['max'])
                                ? number_format((float) $sr['min']) . ' - ' . number_format((float) $sr['max']) . ' đ'
                                : (!empty($spotlight->salary_range) ? 'Xem chi tiết' : 'Thỏa thuận');
                            $citySpot = \App\Enums\VietnamProvince::tryFrom((string) ($spotlight->branch?->city ?? ''))?->label()
                                ?? ($spotlight->branch?->city ?? '—');
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <article class="home-premium-job-card h-100">
                                <div class="home-premium-job-card__top">
                                    <div class="home-premium-job-card__logo">
                                        <img src="{{ $spotlight->branch?->image ? '/storage/' . ltrim($spotlight->branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                            alt="" width="48" height="48" loading="lazy">
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <h3 class="home-premium-job-card__title">
                                            <a
                                                href="{{ route('jobs.public', ['slug' => $spotlight->slug]) }}">{{ $spotlight->title }}</a>
                                        </h3>
                                        <p class="home-premium-job-card__company text-truncate mb-0">
                                            {{ $spotlight->branch?->name ?? 'Chi nhánh' }}</p>
                                    </div>
                                </div>
                                <ul class="home-premium-job-card__meta list-unstyled mb-0">
                                    <li><i class="fa fa-map-marker"></i>{{ $citySpot }}</li>
                                    @if ($spotlight->department)
                                        <li><i class="fa fa-sitemap"></i>{{ $spotlight->department->name }}</li>
                                    @endif
                                    <li><i class="fa fa-money"></i>{{ $salaryLabel }}</li>
                                </ul>
                                <div class="home-premium-job-card__actions">
                                    <a href="{{ route('jobs.public', ['slug' => $spotlight->slug]) }}"
                                        class="btn btn-outline-secondary">Chi tiết</a>
                                    <a href="{{ route('candidates.apply_job', ['job' => $spotlight->id]) }}"
                                        class="btn btn-primary home-premium-btn-apply">Ứng tuyển</a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>


    <section class="how-works-area section_100" style="background: #f8fafc;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center mb-5">
                    <div class="site-heading">
                        <h2 class="fw-bold display-6 mb-3">Quy Trình <span style="color: #f37021;">3 Bước</span></h2>
                        <p class="text-muted fs-5">Khám phá lộ trình đơn giản để kết nối nhân tài và cơ hội việc làm mơ ước.</p>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <!-- Bước 1 -->
                <div class="col-lg-4">
                    <div class="how-works-item border-0 shadow-sm transition-all h-100 p-4 rounded-4 bg-white text-md-center text-lg-start">
                        <div class="d-flex align-items-center mb-4">
                            <div class="step-number text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; min-width: 40px; background-color: #f37021;">1</div>
                            <div class="ms-3 h-px bg-light flex-grow-1 d-none d-lg-block"></div>
                        </div>
                        <div class="mb-3" style="color: #f37021;">
                            <i class="fa fa-user-plus fa-2x"></i>
                        </div>
                        <h3 class="fw-bold h4 mb-3">Tạo tài khoản</h3>
                        <p class="text-muted mb-0">Đăng ký nhanh chóng với vai trò Ứng viên hoặc Nhà tuyển dụng để bắt đầu tham gia hệ sinh thái.</p>
                    </div>
                </div>
                <!-- Bước 2 -->
                <div class="col-lg-4">
                    <div class="how-works-item border-0 shadow-sm transition-all h-100 p-4 rounded-4 bg-white text-md-center text-lg-start">
                        <div class="d-flex align-items-center mb-4">
                            <div class="step-number text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; min-width: 40px; background-color: #f37021;">2</div>
                            <div class="ms-3 h-px bg-light flex-grow-1 d-none d-lg-block"></div>
                        </div>
                        <div class="mb-3" style="color: #f37021;">
                            <i class="fa fa-search fa-2x"></i>
                        </div>
                        <h3 class="fw-bold h4 mb-3">Tìm kiếm / Đăng tuyển</h3>
                        <p class="text-muted mb-0">Ứng viên tìm kiếm công việc phù hợp, Nhà tuyển dụng đăng tin và quản lý hồ sơ ứng tuyển dễ dàng.</p>
                    </div>
                </div>
                <!-- Bước 3 -->
                <div class="col-lg-4">
                    <div class="how-works-item border-0 shadow-sm transition-all h-100 p-4 rounded-4 bg-white text-md-center text-lg-start">
                        <div class="d-flex align-items-center mb-4">
                            <div class="step-number text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; min-width: 40px; background-color: #f37021;">3</div>
                        </div>
                        <div class="mb-3" style="color: #f37021;">
                            <i class="fa fa-handshake-o fa-2x"></i>
                        </div>
                        <h3 class="fw-bold h4 mb-3">Phỏng vấn & Kết nối</h3>
                        <p class="text-muted mb-0">Tiến hành phỏng vấn trực tiếp và chốt thỏa thuận. Chúng tôi đồng hành cùng bạn trong mọi quy trình.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
    .how-works-item {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .how-works-item:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    .h-px { height: 1px; }
    </style>

    <section class="jobguru-job-tab-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="site-heading">
                        <h2>Chi nhánh &amp; <span>việc làm mới</span></h2>
                        <p>Xem doanh nghiệp đang tuyển mạnh và danh sách tin mới được duyệt.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class=" job-tab">
                        <ul class="nav nav-pills job-tab-switch" id="pills-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pills-companies-tab" data-bs-toggle="pill"
                                    href="#pills-companies" role="tab" aria-controls="pills-companies"
                                    aria-selected="true">Chi nhánh hàng đầu</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-job-tab" data-bs-toggle="pill" href="#pills-job"
                                    role="tab" aria-controls="pills-job" aria-selected="false">Việc làm mới nhất</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-companies" role="tabpanel"
                            aria-labelledby="pills-companies-tab">
                            <div class="top-company-tab">
                                <ul>
                                    <style>
                                        #pills-companies .branch-card {
                                            background: #fff;
                                            border: 1px solid #eef0f3;
                                            border-radius: 12px;
                                            padding: 18px;
                                            margin: 0 0 18px;
                                            box-shadow: 0 1px 2px rgba(0, 0, 0, .03)
                                        }
                                        #pills-companies .branch-header {
                                            display: flex;
                                            gap: 16px;
                                            align-items: flex-start
                                        }
                                        #pills-companies .branch-logo {
                                            flex: 0 0 auto
                                        }
                                        #pills-companies .branch-logo img {
                                            width: 86px;
                                            height: 64px;
                                            object-fit: contain
                                        }
                                        #pills-companies .branch-main {
                                            flex: 1;
                                            min-width: 0
                                        }
                                        #pills-companies .branch-title {
                                            font-size: 18px;
                                            font-weight: 600;
                                            margin: 0 0 6px
                                        }
                                        #pills-companies .branch-meta {
                                            display: flex;
                                            flex-wrap: wrap;
                                            gap: 14px;
                                            font-size: 13px;
                                            color: #6b7280;
                                            align-items: center
                                        }
                                        #pills-companies .branch-action {
                                            flex: 0 0 auto;
                                            display: flex;
                                            align-items: center;
                                            gap: 12px;
                                            white-space: nowrap
                                        }
                                        #pills-companies .branch-rating {
                                            background: #f5a623;
                                            color: #fff;
                                            font-weight: 700;
                                            font-size: 12px;
                                            border-radius: 6px;
                                            padding: 2px 6px;
                                            line-height: 18px
                                        }
                                        #pills-companies .branch-jobs {
                                            margin-top: 14px;
                                            border-top: 1px solid #eef0f3;
                                            padding-top: 12px
                                        }
                                        #pills-companies .branch-job-row {
                                            display: flex;
                                            align-items: center;
                                            gap: 12px;
                                            background: #fff;
                                            border: 1px solid #edf0f3;
                                            border-radius: 10px;
                                            padding: 12px 14px;
                                            margin-top: 10px
                                        }
                                        #pills-companies .branch-job-title {
                                            flex: 1;
                                            min-width: 0;
                                            overflow: hidden;
                                            white-space: nowrap;
                                            text-overflow: ellipsis
                                        }
                                        #pills-companies .branch-job-title a {
                                            font-weight: 500
                                        }
                                        #pills-companies .branch-job-salary {
                                            width: 220px;
                                            text-align: right;
                                            font-size: 12px;
                                            color: #6b7280;
                                            white-space: nowrap
                                        }
                                        #pills-companies .branch-job-deadline {
                                            width: 70px;
                                            text-align: right;
                                            font-size: 12px;
                                            color: #6b7280;
                                            white-space: nowrap
                                        }
                                    </style>
                                    @forelse($branches as $branch)
                                                                        @continue(((int) ($branch->published_jobs_count ?? 0)) < 1)
                                                                        <?php
                                        $cityLabel = \App\Enums\VietnamProvince::tryFrom($branch->city ?? '')?->label()
                                            ?? ($branch->city ?? null);
                                        $branchTitle = (string) ($branch->name ?? '');
                                        $titleLower = function_exists('mb_strtolower')
                                            ? mb_strtolower($branchTitle, 'UTF-8')
                                            : strtolower($branchTitle);
                                        $cityLower = $cityLabel
                                            ? (function_exists('mb_strtolower') ? mb_strtolower($cityLabel, 'UTF-8') : strtolower($cityLabel))
                                            : '';
                                        if ($cityLabel && $cityLower !== '' && !str_contains($titleLower, $cityLower)) {
                                            $branchTitle .= ' - ' . $cityLabel;
                                        }
                                                                            ?>
                                                                        <li>
                                                                            <div class="branch-card">
                                                                                <div class="branch-header">
                                                                                    <div class="branch-logo">
                                                                                        <a href="#">
                                                                                            <img src="{{ $branch->image ? asset('storage/' . ltrim($branch->image, '/')) : asset('assets/img/company-logo-1.png') }}"
                                                                                                alt="{{ $branchTitle }}">
                                                                                        </a>
                                                                                    </div>
                                                                                    <div class="branch-main">
                                                                                        <div class="branch-title"><a href="#">{{ $branchTitle }}</a></div>
                                                                                        <div class="branch-meta">
                                                                                            <span><i class="fa fa-map-marker"></i>
                                                                                                {{ $cityLabel ?? 'Chưa cập nhật' }}</span>
                                                                                            <?php    if (!empty($branch->address)): ?>
                                                                                            <span><i class="fa fa-location-arrow"></i>
                                                                                                {{ $branch->address }}</span>
                                                                                            <?php    endif; ?>
                                                                                            <span><i class="fa fa-briefcase"></i>
                                                                                                {{ (int) ($branch->published_jobs_count ?? 0) }} vị trí đang
                                                                                                tuyển</span>
                                                                                            <span><i class="fa fa-check"></i>
                                                                                                {{ $branch->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động' }}</span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="branch-action">
                                                                                        <span
                                                                                            class="branch-rating">{{ number_format(rand(37, 50) / 10, 1) }}</span>
                                                                                        <a href="#" class="jobguru-btn">Xem hồ sơ</a>
                                                                                    </div>
                                                                                </div>
                                                                                @if(($branch->recruitmentJobs ?? collect())->isNotEmpty())
                                                                                    <div class="branch-jobs">
                                                                                        @forelse(($branch->recruitmentJobs ?? collect())->values() as $job)
                                                                                            <div class="branch-job-row">
                                                                                                <i class="fa fa-heart-o" style="color:#a3a3a3;"></i>
                                                                                                <div class="branch-job-title">
                                                                                                    <a
                                                                                                        href="{{ route('jobs.public', ['slug' => $job->slug]) }}">{{ $job->title }}</a>
                                                                                                </div>
                                                                                                <div class="branch-job-salary">
                                                                                                    <i class="fa fa-money"></i>
                                                                                                    @if(is_array($job->salary_range) && isset($job->salary_range['min'], $job->salary_range['max']))
                                                                                                        {{ number_format($job->salary_range['min']) }} -
                                                                                                        {{ number_format($job->salary_range['max']) }} VND
                                                                                                    @elseif(is_array($job->salary_range) && count($job->salary_range) > 0)
                                                                                                        {{ implode(' - ', $job->salary_range) }}
                                                                                                    @elseif(!empty($job->salary_range))
                                                                                                        {{ $job->salary_range }}
                                                                                                    @else
                                                                                                        Thỏa thuận
                                                                                                    @endif
                                                                                                </div>
                                                                                                <div class="branch-job-deadline"><i class="fa fa-clock-o"></i>
                                                                                                    {{ $job->deadline?->format('d/m') ?? '' }}</div>
                                                                                            </div>
                                                                                        @empty
                                                                                        @endforelse
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </li>
                                    @empty
                                        <li>Không có chi nhánh nào.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-job" role="tabpanel" aria-labelledby="pills-job-tab">
                            <div class="top-company-tab">
                                <ul>
                                    @forelse ($jobs as $job)
                                        <li>
                                            <div class="top-company-list">
                                                <div class="company-list-logo">
                                                    <a href="{{ route('jobs.public', ['slug' => $job->slug]) }}">
                                                        <img src="{{ $job->branch?->image ? '/storage/' . ltrim($job->branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                                            alt="{{ $job->branch?->name ?? 'Chi nhánh' }}">
                                                    </a>
                                                </div>
                                                <div class="company-list-details">
                                                    <h3><a
                                                            href="{{ route('jobs.public', ['slug' => $job->slug]) }}">{{ $job->title }}</a>
                                                    </h3>
                                                    <p class="company-state"><i class="fa fa-building-o"></i>
                                                        {{ $job->branch?->name ?? 'Chi nhánh' }}
                                                    </p>
                                                    <div class="meta-row">
                                                        <p class="company-state"><i class="fa fa-map-marker"></i>
                                                            {{ \App\Enums\VietnamProvince::tryFrom($job->branch?->city ?? '')?->label() ?? ($job->branch?->city ?? 'Địa điểm chưa xác định') }}
                                                        </p>
                                                        <p class="open-icon"><i class="fa fa-clock-o"></i>
                                                            {{ $job->created_at?->diffForHumans() }}</p>
                                                        <p class="varify"><i class="fa fa-money"></i>
                                                            @if (is_array($job->salary_range) && isset($job->salary_range['min'], $job->salary_range['max']))
                                                                {{ number_format($job->salary_range['min']) }} -
                                                                {{ number_format($job->salary_range['max']) }} VND
                                                            @elseif (is_array($job->salary_range))
                                                                {{ implode(' - ', $job->salary_range) }}
                                                            @elseif (!empty($job->salary_range))
                                                                {{ $job->salary_range }}
                                                            @else
                                                                Thỏa thuận
                                                            @endif
                                                        </p>
                                                        <span class="rating-company"><i class="fa fa-star"></i> {{ number_format(rand(37, 50) / 10, 1) }}</span>
                                                    </div>
                                                </div>
                                                <div class="company-list-btn">
                                                    <a href="{{ route('jobs.public', ['slug' => $job->slug]) }}"
                                                        class="jobguru-btn">Xem chi tiết</a>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <li>Không có công việc nào</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="load-more">
                        <a href="{{ route('candidates.browse_job') }}" class="jobguru-btn">Xem thêm danh sách</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="jobguru-blog-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="site-heading">
                        <h2>Bài viết &amp; <span>career tips</span></h2>
                        <p>Kỹ năng phỏng vấn, xu hướng ngành và câu chuyện nghề nghiệp.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                @forelse($posts as $post)
                    <div class="col-lg-4 col-md-12 mb-4">
                        <a href="{{ route('pages.blog') }}" class="h-100 d-block">
                            <div class="single-blog h-100 d-flex flex-column">
                                <div class="blog-image">
                                    <img src="{{ asset($post->image) }}" alt="{{$post->title}}" style="height: 200px; object-fit: cover;" />
                                    <h5>{{$post->title}}</h5>
                                    <p>{{$post->created_at->format('d/m') }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <p>Không có bài viết nào</p>
                    </div>
                @endforelse
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('pages.blog') }}" class="jobguru-btn">Xem tất cả bài viết</a>
            </div>
        </div>
    </section>

    <section class="home-premium-newsletter section_50">
        <div class="container">
            <div class="row align-items-center g-4 home-premium-newsletter__row">
                <div class="col-lg-7">
                    <h3 class="fw-bold mb-2">Nhận cập nhật việc làm &amp; sự kiện tuyển dụng</h3>
                    <p class="text-muted mb-0">Chúng tôi sẽ gửi những thông tin hữu ích, không spam. Bạn cũng có thể
                        liên hệ trực tiếp để hợp tác.</p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <a href="{{ route('pages.contact') }}"
                        class="btn btn-primary btn-lg rounded-pill px-5 fw-semibold home-premium-btn-apply">Liên hệ /
                        Đăng ký nhận tin</a>
                </div>
            </div>
        </div>
    </section>
</div>

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
    <section class="home-search-strip">
        <div class="container">
            <form class="home-search-panel" wire:submit.prevent="searchJobs">
                <div class="home-search-field home-search-field--keyword">
                    <i class="fa fa-briefcase"></i>
                    <input
                        type="search"
                        class="form-control"
                        placeholder="Từ khóa việc làm"
                        wire:model="searchKeyword"
                    >
                </div>

                <div class="home-search-field">
                    <i class="fa fa-map-marker"></i>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Khu vực hoặc chi nhánh"
                        wire:model="searchCity"
                    >
                </div>

                <div class="home-search-field">
                    <i class="fa fa-sitemap"></i>
                    <select class="form-select" wire:model="searchDepartmentId">
                        <option value="">Tất cả phòng ban</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="home-search-submit">
                    <i class="fa fa-search"></i>
                    <span>Tìm kiếm</span>
                </button>
            </form>

            <div class="home-search-suggestions">
                <span>Gợi ý nhanh:</span>
                <a href="{{ route('candidates.browse_job', ['q' => 'Giảng viên']) }}">Giảng viên</a>
                <a href="{{ route('candidates.browse_job', ['q' => 'Tuyển sinh']) }}">Tuyển sinh</a>
                <a href="{{ route('candidates.browse_job', ['q' => 'Marketing']) }}">Marketing</a>
                <a href="{{ route('candidates.browse_job', ['city' => 'Hà Nội']) }}">Hà Nội</a>
                <a href="{{ route('candidates.browse_job', ['city' => 'Hồ Chí Minh']) }}">Hồ Chí Minh</a>
            </div>
        </div>
    </section>

    <section class="home-ai-quick-access">
        <div class="container">
            <div class="home-ai-quick-access__panel">
                <div class="home-ai-quick-access__content">
                    <span class="home-ai-quick-access__eyebrow">AI job matching</span>
                    <h2>Quét việc làm phù hợp từ hồ sơ của bạn</h2>
                    <p>
                        Một lần bấm để mở dashboard và tự đối chiếu CV với các vị trí đang tuyển.
                        Kết quả vẫn được giữ ở dashboard để bạn xem gọn hơn.
                    </p>
                </div>

                <div class="home-ai-quick-access__actions">
                    @if ($hasCandidateAccess)
                        <button
                            type="button"
                            class="btn btn-orange btn-lg rounded-pill px-4 fw-semibold home-ai-quick-access__primary"
                            wire:click="openJobMatching"
                            wire:loading.attr="disabled"
                            wire:target="openJobMatching"
                        >
                            <span wire:loading.remove wire:target="openJobMatching">Quét ngay</span>
                            <span wire:loading wire:target="openJobMatching">
                                <i class="fa fa-circle-o-notch fa-spin"></i> Đang mở...
                            </span>
                        </button>
                        <a href="{{ route('candidates.candidate_profile') }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4 fw-semibold">
                            Cập nhật hồ sơ
                        </a>
                    @else
                        <a href="{{ route('candidates.login') }}" class="btn btn-orange btn-lg rounded-pill px-4 fw-semibold home-ai-quick-access__primary">
                            Đăng nhập để quét
                        </a>
                        <a href="{{ route('candidates.register') }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4 fw-semibold">
                            Tạo tài khoản
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <style>
        .home-search-strip {
            background: #ffffff;
            margin-top: 0;
            padding-top: 24px;
            padding-bottom: 24px;
            position: relative;
            z-index: 20;
        }

        .home-search-panel {
            display: grid;
            grid-template-columns: minmax(220px, 1.25fr) minmax(200px, 1fr) minmax(190px, .9fr) auto;
            gap: 14px;
            align-items: center;
            padding: 22px 20px;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .12);
        }

        .home-search-field {
            position: relative;
            min-width: 0;
        }

        .home-search-field i {
            position: absolute;
            left: 22px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #f37021 !important;
            font-size: 17px;
            line-height: 1;
            pointer-events: none;
            z-index: 2;
        }

        body.client-app .home-search-field .form-control,
        body.client-app .home-search-field .form-select {
            width: 100%;
            height: 52px !important;
            padding-left: 74px !important;
            padding-right: 18px !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            background-color: #f8fafc !important;
            color: #0f172a !important;
            font-size: 14px;
            font-weight: 600 !important;
            line-height: 1.2;
            box-shadow: none !important;
        }

        body.client-app .home-search-field .form-select {
            padding-right: 42px !important;
        }

        body.client-app .home-search-field .form-control:focus,
        body.client-app .home-search-field .form-select:focus {
            border-color: #f37021 !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 4px rgba(243, 112, 33, .12) !important;
        }

        .home-search-submit {
            height: 52px;
            min-width: 148px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            border-radius: 999px;
            background: #f37021;
            color: #fff;
            font-weight: 800;
            padding: 0 24px;
            box-shadow: 0 12px 24px rgba(243, 112, 33, .25);
            transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        .home-search-submit:hover,
        .home-search-submit:focus {
            background: #e05f12;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(224, 95, 18, .28);
        }

        .home-search-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            margin-top: 12px;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        .home-search-suggestions a {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #fff7ed;
            color: #c2410c;
            text-decoration: none;
            border: 1px solid rgba(243, 112, 33, .18);
        }

        .home-search-suggestions a:hover,
        .home-search-suggestions a:focus {
            background: #f37021;
            color: #fff !important;
            border-color: #f37021;
        }

        .home-ai-quick-access {
            background: #fff;
            padding: 12px 0 28px;
        }

        .home-ai-quick-access__panel {
            align-items: center;
            display: grid;
            gap: 20px;
            grid-template-columns: minmax(0, 1.3fr) auto;
            padding: 22px 24px;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 22px;
            background: linear-gradient(135deg, #fffaf5 0%, #ffffff 58%);
            box-shadow: 0 18px 48px rgba(15, 23, 42, .08);
        }

        .home-ai-quick-access__eyebrow {
            color: #f37021;
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .12em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .home-ai-quick-access__content h2 {
            color: #0f172a;
            font-size: clamp(24px, 2.8vw, 34px);
            font-weight: 900;
            line-height: 1.15;
            margin: 0 0 10px;
        }

        .home-ai-quick-access__content p {
            color: #64748b;
            font-size: 15px;
            line-height: 1.7;
            margin: 0;
            max-width: 64ch;
        }

        .home-ai-quick-access__actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: flex-end;
        }

        .home-ai-quick-access__primary {
            box-shadow: 0 12px 24px rgba(243, 112, 33, .18);
        }

        .home-ai-quick-access__actions .btn {
            min-height: 52px;
        }

        .home-ai-quick-access__actions .btn-outline-secondary {
            border-color: #dbe4ee;
            color: #334155;
            background: #fff;
        }

        .home-ai-quick-access__actions .btn-outline-secondary:hover,
        .home-ai-quick-access__actions .btn-outline-secondary:focus {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        @media (max-width: 1199px) {
            .home-search-panel {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .home-search-submit {
                width: 100%;
            }
        }

        @media (max-width: 767px) {
            .home-search-strip {
                margin-top: 0;
                padding-top: 16px;
                padding-bottom: 16px;
            }

            .home-search-panel {
                grid-template-columns: 1fr;
                padding: 14px;
                border-radius: 14px;
            }

            .home-ai-quick-access__panel {
                grid-template-columns: 1fr;
                padding: 18px;
                border-radius: 18px;
            }

            .home-ai-quick-access__actions {
                justify-content: stretch;
            }

            .home-ai-quick-access__actions .btn {
                width: 100%;
            }
        }
    </style>
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
                    <p class="text-muted mb-0">Trải nghiệm tìm việc được tối ưu cho ứng viên Việt Nam: dễ tìm vị trí phù hợp, nộp hồ sơ nhanh và theo dõi tiến độ rõ ràng.</p>
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
                        <p class="text-muted small mb-0">Tài khoản cá nhân được bảo vệ, thông tin hồ sơ và CV chỉ dùng cho đúng quy trình ứng tuyển.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="home-premium-feature h-100">
                        <div class="home-premium-feature__icon"><i class="fa fa-building"></i></div>
                        <h3 class="h5 fw-bold">Cơ hội đa khu vực</h3>
                        <p class="text-muted small mb-0">Khám phá vị trí theo chi nhánh, địa điểm làm việc và phòng ban phù hợp.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="home-premium-feature h-100">
                        <div class="home-premium-feature__icon home-premium-feature__icon--blue"><i
                                class="fa fa-comments"></i></div>
                        <h3 class="h5 fw-bold">Theo dõi phản hồi</h3>
                        <p class="text-muted small mb-0">Nhận cập nhật từ nhà tuyển dụng và nắm rõ tiến độ sau khi ứng tuyển.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="home-premium-feature h-100">
                        <div class="home-premium-feature__icon home-premium-feature__icon--green"><i
                                class="fa fa-line-chart"></i></div>
                        <h3 class="h5 fw-bold">Chủ động sự nghiệp</h3>
                        <p class="text-muted small mb-0">Lưu việc phù hợp, cập nhật CV và chuẩn bị tốt hơn cho từng vòng xét tuyển.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-ecosystem section_70" style="background: #fff;">
        <div class="container">
            <div class="row align-items-center mb-5">
                <div class="col-lg-7">
                    <h2 class="fw-bold display-5 mb-3">Không gian <span class="text-orange">tìm việc có định hướng</span></h2>
                    <p class="mb-0 text-muted">Tập trung việc làm, hồ sơ cá nhân và trạng thái ứng tuyển trong một trải nghiệm rõ ràng để ứng viên dễ ra quyết định hơn.</p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <a href="{{ route('candidates.browse_job') }}" class="btn btn-orange btn-lg rounded-pill px-4 fw-semibold">Tìm việc phù hợp</a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="ecosystem-stat-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm">
                        <div class="fw-bold display-6 text-orange">{{ number_format($stats['candidates'] ?? 0) }}</div>
                        <p class="mb-0 text-muted">Hồ sơ ứng viên đã đăng</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="ecosystem-stat-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm">
                        <div class="fw-bold display-6 text-orange">{{ number_format($stats['active_branches'] ?? 0) }}</div>
                        <p class="mb-0 text-muted">Chi nhánh đang có tin tuyển</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="ecosystem-stat-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm">
                        <div class="fw-bold display-6 text-orange">{{ number_format($stats['applications'] ?? 0) }}</div>
                        <p class="mb-0 text-muted">Lượt ứng tuyển đã ghi nhận</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="ecosystem-stat-card p-4 rounded-4 bg-white text-dark h-100 shadow-sm">
                        <div class="fw-bold display-6 text-orange">{{ number_format($stats['users'] ?? 0) }}</div>
                        <p class="mb-0 text-muted">Tài khoản đang sử dụng hệ thống</p>
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
                        <p class="text-muted small">Kênh tìm việc giúp ứng viên lọc vị trí, xem chi nhánh và nộp hồ sơ nhanh.</p>
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
                        <h3 class="h5 fw-bold">Hỗ trợ ứng viên</h3>
                        <p class="text-muted small">Gửi câu hỏi khi cần hỗ trợ tài khoản, hồ sơ hoặc quá trình ứng tuyển.</p>
                        <a href="{{ route('pages.contact') }}" class="stretched-link text-decoration-none text-orange">Nhận hỗ trợ</a>
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

        .home-premium-job-card__actions {
            display: flex !important;
            gap: 10px !important;
            margin-top: 20px !important;
        }

        .btn-detail-spotlight {
            flex: 1 !important;
            padding: 0.6rem 0.8rem !important;
            background: #f8fafc !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 999px !important;
            font-weight: 600 !important;
            font-size: 0.88rem !important;
            text-align: center !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
            display: inline-block !important;
        }

        .btn-detail-spotlight:hover {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }

        .btn-apply-spotlight {
            flex: 1.5 !important;
            padding: 0.6rem 1rem !important;
            background: linear-gradient(135deg, #f37021 0%, #ff8c42 100%) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 999px !important;
            font-weight: 600 !important;
            font-size: 0.88rem !important;
            text-align: center !important;
            text-decoration: none !important;
            box-shadow: 0 4px 14px rgba(243, 112, 33, 0.28) !important;
            transition: all 0.2s ease !important;
            display: inline-block !important;
        }

        .btn-apply-spotlight:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 18px rgba(243, 112, 33, 0.38) !important;
            color: #ffffff !important;
        }

        .btn-view-all-spotlight {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0.5rem 1.25rem !important;
            border-radius: 999px !important;
            background: #ffffff !important;
            color: #f37021 !important;
            border: 1.5px solid #f37021 !important;
            font-weight: 700 !important;
            font-size: 0.9rem !important;
            text-decoration: none !important;
            box-shadow: 0 2px 8px rgba(243, 112, 33, 0.1) !important;
            transition: all 0.2s ease !important;
            line-height: 1.4 !important;
        }

        .btn-view-all-spotlight:hover {
            background: #f37021 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(243, 112, 33, 0.3) !important;
            transform: translateY(-1px) !important;
        }
    </style>

    <section class="home-premium-spotlight section_70">
        <div class="container">
            <div class="home-premium-section-head d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Việc làm <span class="text-primary">nổi bật</span></h2>
                    <p class="text-muted mb-0">Tuyển gấp, lương cạnh tranh — cập nhật liên tục.</p>
                </div>
                <a href="{{ route('candidates.browse_job') }}" class="btn-view-all-spotlight">Xem tất cả <i class="fa fa-angle-right ms-1"></i></a>
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
                                <div class="home-premium-job-card__accent"></div>
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
                                        class="btn-detail-spotlight">Chi tiết</a>
                                    <a href="{{ route('candidates.apply_job', ['job' => $spotlight->id]) }}"
                                        class="btn-apply-spotlight">Ứng tuyển</a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>


    <section class="how-works-area section_100 home-how-works">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center mb-5">
                    <div class="site-heading">
                        <h2 class="fw-bold display-6 mb-3">Quy Trình <span style="color: #f37021;">3 Bước</span></h2>
                    <p class="text-muted fs-5">Khám phá lộ trình đơn giản để tìm việc, nộp hồ sơ và theo dõi phản hồi từ nhà tuyển dụng.</p>
                    </div>
                </div>
            </div>
            <div class="row g-4 g-lg-5">
                <!-- Bước 1 -->
                <div class="col-lg-4">
                    <div class="how-works-item border-0 shadow-sm transition-all h-100 rounded-4 bg-white text-dark text-center text-lg-start position-relative overflow-hidden d-flex flex-column">
                        <div class="how-works-step">1</div>
                        <div class="how-works-icon">
                            <i class="fa fa-user-plus" aria-hidden="true"></i>
                        </div>
                        <h3 class="fw-bold h4 mb-3 text-dark">Tạo tài khoản</h3>
                        <p class="text-muted mb-0">Tạo tài khoản ứng viên để lưu hồ sơ, ứng tuyển nhanh và theo dõi từng đơn đã nộp.</p>
                    </div>
                </div>
                <!-- Bước 2 -->
                <div class="col-lg-4">
                    <div class="how-works-item border-0 shadow-sm transition-all h-100 rounded-4 bg-white text-dark text-center text-lg-start position-relative overflow-hidden d-flex flex-column">
                        <div class="how-works-step">2</div>
                        <div class="how-works-icon">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </div>
                        <h3 class="fw-bold h4 mb-3 text-dark">Tìm kiếm việc làm</h3>
                        <p class="text-muted mb-0">Tìm vị trí phù hợp theo ngành, địa điểm, mức lương và thông tin chi nhánh tuyển dụng.</p>
                    </div>
                </div>
                <!-- Bước 3 -->
                <div class="col-lg-4">
                    <div class="how-works-item border-0 shadow-sm transition-all h-100 rounded-4 bg-white text-dark text-center text-lg-start position-relative overflow-hidden d-flex flex-column">
                        <div class="how-works-step">3</div>
                        <div class="how-works-icon">
                            <i class="fa fa-handshake-o" aria-hidden="true"></i>
                        </div>
                        <h3 class="fw-bold h4 mb-3 text-dark">Phỏng vấn & Kết nối</h3>
                        <p class="text-muted mb-0">Tiến hành phỏng vấn trực tiếp và chốt thỏa thuận. Chúng tôi đồng hành cùng bạn trong mọi quy trình.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
    .home-how-works {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 70%);
    }
    .how-works-item {
        padding: 32px;
        border: 1px solid rgba(15, 23, 42, 0.06);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .how-works-item::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #f37021 0%, #ff9a57 100%);
        opacity: 0.95;
    }
    .how-works-step {
        position: absolute;
        top: 16px;
        left: 16px;
        width: 36px;
        height: 36px;
        border-radius: 999px;
        background: #ffffff;
        color: #f37021;
        border: 1px solid rgba(243, 112, 33, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }
    .how-works-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        background: rgba(243, 112, 33, 0.12);
        color: #f37021;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 24px auto 14px;
    }
    .how-works-icon i { font-size: 26px; }
    .how-works-item p {
        font-size: 0.95rem;
        line-height: 1.65;
    }
    .how-works-item:hover {
        transform: translateY(-10px);
        border-color: rgba(243, 112, 33, 0.22);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12) !important;
    }
    @media (min-width: 992px) {
        .how-works-icon { margin: 32px 0 14px; }
    }
    </style>

    <section class="jobguru-job-tab-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="site-heading">
                        <h2>Chi nhánh &amp; <span>việc làm mới</span></h2>
                        <p>Xem các chi nhánh đang tuyển mạnh và danh sách việc làm mới được duyệt.</p>
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
                                                                                            class="branch-rating">{{ (int) ($branch->published_jobs_count ?? 0) }}</span>
                                                                                        <a href="#" class="jobguru-btn">Xem tin tuyển</a>
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
                                                        <span class="rating-company"><i class="fa fa-briefcase"></i> {{ (int) ($job->positions_count ?? 1) }}</span>
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

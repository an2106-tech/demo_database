<div>
    @php
        $user = auth()->user();
        $metadata = is_array($user?->metadata) ? $user->metadata : [];
        $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];
        $hasEmployerAccess = (bool) $user && (in_array($user->role, ['hr', 'admin'], true) || in_array('employer', $accountTypes, true));
    @endphp
    <section class="employer-portal-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="employer-portal-hero__badge">
                        <span class="pulse-dot"></span> Dành cho nhà tuyển dụng
                    </div>
                    <h1 class="employer-portal-hero__title">
                        Tuyển đúng người,<br> <span class="text-gradient">Nhanh hơn 2 lần</span>
                    </h1>
                    <p class="employer-portal-hero__subtitle">
                        Giải pháp quản trị tuyển dụng tập trung. Đăng tin, lọc hồ sơ và quản lý quy trình phỏng vấn chuyên nghiệp chỉ trên một nền tảng duy nhất.
                    </p>

                    <div class="employer-portal-hero__actions">
                        @if(! $hasEmployerAccess)
                            <a class="btn employer-btn-primary" href="{{ route('employers.login') }}">
                                <i class="fa fa-plus-circle me-2"></i> Đăng tin ngay
                            </a>
                            <a class="btn employer-btn-secondary" href="{{ route('employers.register') }}">
                                Tạo tài khoản
                            </a>
                        @else
                            <a class="btn employer-btn-primary" href="{{ route('employers.post_job') }}">
                                <i class="fa fa-plus-circle me-2"></i> Đăng bài tuyển dụng
                            </a>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="employer-portal-hero__panel">
                        <div class="panel-glass-header">
                            <span class="dot"></span><span class="dot"></span><span class="dot"></span>
                            <small class="ms-2 text-muted">Hệ thống quản lý ứng viên v2.0</small>
                        </div>
                        <div class="employer-portal-kpi">
                            <div class="employer-portal-kpi__icon"><i class="fa fa-briefcase"></i></div>
                            <div>
                                <div class="employer-portal-kpi__title">Tin tuyển dụng</div>
                                <div class="employer-portal-kpi__desc">Tự động hóa quy trình đăng tin đa kênh</div>
                            </div>
                        </div>
                        <div class="employer-portal-kpi">
                            <div class="employer-portal-kpi__icon icon-blue"><i class="fa fa-users"></i></div>
                            <div>
                                <div class="employer-portal-kpi__title">AI Screening</div>
                                <div class="employer-portal-kpi__desc">Gợi ý ứng viên phù hợp nhất dựa trên kỹ năng</div>
                            </div>
                        </div>
                        <div class="employer-portal-kpi">
                            <div class="employer-portal-kpi__icon icon-green"><i class="fa fa-comments"></i></div>
                            <div>
                                <div class="employer-portal-kpi__title">Trao đổi trực tiếp</div>
                                <div class="employer-portal-kpi__desc">Kết nối ứng viên ngay lập tức qua Chat</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="employer-portal-stats section_padding">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="employer-stat-card">
                        <div class="employer-stat-card__icon"><i class="fa fa-briefcase"></i></div>
                        <div class="employer-stat-card__number">{{ number_format($totalJobs) }}</div>
                        <div class="employer-stat-card__label">Việc làm đang tuyển</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="employer-stat-card">
                        <div class="employer-stat-card__icon icon-blue"><i class="fa fa-file-text"></i></div>
                        <div class="employer-stat-card__number">{{ number_format($totalApplications) }}</div>
                        <div class="employer-stat-card__label">Hồ sơ đã nhận</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="employer-stat-card">
                        <div class="employer-stat-card__icon icon-purple"><i class="fa fa-map-marker"></i></div>
                        <div class="employer-stat-card__number">{{ $totalBranches }}</div>
                        <div class="employer-stat-card__label">Chi nhánh</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="employer-stat-card">
                        <div class="employer-stat-card__icon icon-green"><i class="fa fa-check-circle"></i></div>
                        <div class="employer-stat-card__number">{{ count($categories) }}</div>
                        <div class="employer-stat-card__label">Lĩnh vực tuyển</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($recentJobs->count() > 0)
    <section class="employer-portal-recent-jobs section_padding bg-light">
        <div class="container">
            <div class="employer-portal-section-title">
                <h2>Việc làm đang tuyển</h2>
                <p>Quản lý các vị trí đang mở và tối ưu hóa nguồn lực HR</p>
            </div>
            <div class="row g-4">
                @foreach($recentJobs as $job)
                <div class="col-md-6 col-lg-4">
                    <div class="employer-job-card h-100">
                        <div class="employer-job-card__header">
                            <h3 class="employer-job-card__title">{{ $job->title }}</h3>
                            <span class="status-badge {{ $job->status->value == 'Active' ? 'active' : '' }}">
                                {{ $job->status->value }}
                            </span>
                        </div>
                        <div class="employer-job-card__body">
                            @if($job->branch)
                            <div class="employer-job-card__info">
                                <i class="fa fa-building-o"></i> <span>{{ $job->branch->name }}</span>
                            </div>
                            @endif
                            @if($job->salary_range)
                            <div class="employer-job-card__info">
                                <i class="fa fa-money"></i> <span>{{ number_format($job->salary_range['min']/1000000, 1) }}M - {{ number_format($job->salary_range['max']/1000000, 1) }}M đ</span>
                            </div>
                            @endif
                            <div class="employer-job-card__info">
                                <i class="fa fa-clock-o"></i> <span>Hạn: {{ $job->deadline ? $job->deadline->format('d/m/Y') : 'Không thời hạn' }}</span>
                            </div>
                        </div>
                        <div class="employer-job-card__footer">
                            <a href="#" class="btn-text">Chi tiết quản lý <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="text-center mt-5">
                <a href="#" class="employer-btn-secondary">Xem tất cả hồ sơ quản lý</a>
            </div>
        </div>
    </section>
    @endif
<section class="employer-benefits section_padding">
    <div class="container">
        <div class="employer-portal-section-title">
            <h2>V? sao ??i ng? tuy?n d?ng ch?n n?n t?ng n?y?</h2>
            <p>Chúng tôi thấu hiểu những khó khăn trong quy trình tuyển dụng của bạn</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="benefit-card">
                    <div class="benefit-card__icon"><i class="fa fa-bolt"></i></div>
                    <h4>Tiết kiệm 50% thời gian</h4>
                    <p>Tự động hóa việc phân loại hồ sơ và gửi email phản hồi cho ứng viên chỉ với 1 click.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-card">
                    <div class="benefit-card__icon"><i class="fa fa-bullseye"></i></div>
                    <h4>Đúng người, đúng việc</h4>
                    <p>Thuật toán gợi ý ứng viên dựa trên kinh nghiệm và kỹ năng thực tế, giảm tỷ lệ tuyển sai.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-card">
                    <div class="benefit-card__icon"><i class="fa fa-line-chart"></i></div>
                    <h4>Tối ưu chi phí</h4>
                    <p>Chi phí đăng tin linh hoạt, hiệu quả chuyển đổi cao hơn so với các nền tảng truyền thống.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="employer-steps section_padding bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5">
                <h2 class="fw-bold mb-4">Bắt đầu tuyển dụng chỉ trong vài phút</h2>
                <div class="step-item active">
                    <div class="step-number">01</div>
                    <div class="step-content">
                        <h5>Tạo tài khoản doanh nghiệp</h5>
                        <p>Cập nhật thông tin công ty để xây dựng thương hiệu tuyển dụng uy tín.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">02</div>
                    <div class="step-content">
                        <h5>Đăng tin tuyển dụng</h5>
                        <p>Sử dụng trình soạn thảo thông minh để tạo mô tả công việc hấp dẫn.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">03</div>
                    <div class="step-content">
                        <h5>Nhận hồ sơ & Phỏng vấn</h5>
                        <p>Theo dõi trạng thái ứng viên và sắp xếp lịch hẹn ngay trên hệ thống.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="step-visual">
                    <img src="{{ asset('assets/img/anh-tuyen-dung-lao-dong_7bd6561d-6c9b-43d6-9328-6049f732b2aa.jpg') }}" alt="Workflow" class="img-fluid rounded-4 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>
<section class="employer-faq section_padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <h2 class="fw-bold">Giải đáp thắc mắc</h2>
                <p class="text-muted">Bạn vẫn còn câu hỏi? Liên hệ đội ngũ hỗ trợ của chúng tôi ngay.</p>
                <a href="#" class="btn-text">Gửi yêu cầu hỗ trợ <i class="fa fa-arrow-right"></i></a>
            </div>
            <div class="col-lg-8">
                <div class="accordion border-0" id="faqAccordion">
                    <div class="accordion-item mb-3 border rounded-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Tin tuyển dụng của tôi sẽ hiển thị trong bao lâu?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Thông thường, tin tuyển dụng sẽ được hiển thị trong vòng 30-45 ngày tùy vào gói dịch vụ bạn chọn. Bạn có thể gia hạn bất cứ lúc nào.
                            </div>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>
</section>
    @if(! $hasEmployerAccess)
    <section class="employer-portal-cta section_padding">
        <div class="container">
            <div class="cta-box text-center">
                <h2>Sẵn sàng bứt phá hiệu quả tuyển dụng?</h2>
                <p class="mt-3 mb-4 opacity-75">B?t ??u x?y d?ng quy tr?nh tuy?n d?ng r? r?ng v? d? qu?n l? h?n ngay h?m nay.</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="{{ route('employers.register') }}" class="btn employer-btn-primary btn-lg px-5">
                        Bắt đầu ngay miễn phí
                    </a>
                </div>
            </div>
        </div>
    </section>
    @endif
</div>

<style>
    /* Benefit Card */
    .benefit-card {
        padding: 40px;
        background: white;
        border-radius: 20px;
        height: 100%;
        transition: all 0.3s;
    }
    .benefit-card__icon {
        width: 60px; height: 60px;
        background: var(--slate-100);
        color: var(--primary);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; margin-bottom: 20px;
    }

    /* Steps */
    .step-item {
        display: flex; gap: 20px; margin-bottom: 30px;
        padding: 20px; border-radius: 15px; transition: 0.3s;
    }
    .step-item.active { background: white; shadow: var(--shadow-md); }
    .step-number {
        font-size: 24px; font-weight: 800; color: var(--primary); opacity: 0.3;
    }
    .active .step-number { opacity: 1; }

    /* Gray-scale Logos */
    .gray-scale img { filter: grayscale(100%); transition: 0.3s; }
    .gray-scale img:hover { filter: grayscale(0%); }

    /* Pricing */
    .pricing-card {
        padding: 40px; background: white; border-radius: 24px;
        border: 1px solid var(--slate-100); position: relative;
    }
    .pricing-card.featured {
        border: 2px solid var(--primary); transform: scale(1.05);
        box-shadow: var(--shadow-lg);
    }
    .pricing-badge {
        position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
        background: var(--primary); color: white; padding: 4px 15px;
        border-radius: 20px; font-size: 12px; font-weight: 700;
    }
    .price { font-size: 2.5rem; font-weight: 800; color: var(--slate-900); margin: 20px 0; }
    .price span { font-size: 1rem; color: var(--slate-500); }
    .pricing-features { list-style: none; padding: 0; margin: 30px 0; }
    .pricing-features li { margin-bottom: 12px; font-size: 15px; }
    .pricing-features li i { color: #10b981; margin-right: 10px; }
    .pricing-features li.disabled { color: var(--slate-500); opacity: 0.6; }

    /* Accordion */
    .accordion-button:not(.collapsed) {
        background-color: transparent; color: var(--primary); box-shadow: none;
    }
    :root {
        --primary: #FF9500;
        --primary-dark: #E68600;
        --slate-900: #0F172A;
        --slate-700: #334155;
        --slate-500: #64748B;
        --slate-100: #F1F5F9;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }

    /* Global Tweaks */
    body { font-family: 'Inter', system-ui, sans-serif; color: var(--slate-700); }
    .section_padding { padding: 80px 0; }
    .bg-light { background-color: #F8FAFC !important; }

    /* Hero */
    .employer-portal-hero {
        padding: 100px 0;
        background: radial-gradient(circle at 10% 20%, rgba(255, 149, 0, 0.05) 0%, transparent 40%),
                    radial-gradient(circle at 90% 80%, rgba(59, 130, 246, 0.05) 0%, transparent 40%);
        border-bottom: 1px solid var(--slate-100);
    }

    .employer-portal-hero__badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: white;
        border: 1px solid var(--slate-100);
        color: var(--primary);
        border-radius: 100px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }

    .pulse-dot {
        width: 8px; height: 8px;
        background: var(--primary);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 149, 0, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(255, 149, 0, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 149, 0, 0); }
    }

    .employer-portal-hero__title {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--slate-900);
        margin-bottom: 24px;
    }

    .text-gradient {
        background: linear-gradient(to right, var(--primary), #FF5E00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .employer-portal-hero__subtitle {
        font-size: 1.15rem;
        line-height: 1.6;
        color: var(--slate-500);
        margin-bottom: 35px;
        max-width: 90%;
    }

    /* Buttons */
    .employer-btn-primary {
        background: var(--primary);
        color: white;
        padding: 14px 30px;
        border-radius: 12px;
        font-weight: 600;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(255, 149, 0, 0.2);
    }

    .employer-btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 149, 0, 0.3);
        color: white;
    }

    .employer-btn-secondary {
        background: white;
        color: var(--slate-700);
        padding: 14px 30px;
        border-radius: 12px;
        font-weight: 600;
        border: 1px solid var(--slate-100);
        transition: all 0.3s;
    }

    .employer-btn-secondary:hover {
        background: var(--slate-100);
        border-color: var(--slate-500);
    }

    /* Panel & Cards */
    .employer-portal-hero__panel {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(20px);
        border: 1px solid white;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
    }

    .panel-glass-header {
        margin-bottom: 30px;
        display: flex;
        align-items: center;
    }

    .panel-glass-header .dot {
        width: 10px; height: 10px; border-radius: 50%; background: #e2e8f0; margin-right: 6px;
    }

    .employer-portal-kpi {
        display: flex; gap: 20px; align-items: center;
        margin-bottom: 25px;
    }

    .employer-portal-kpi__icon {
        width: 54px; height: 54px;
        background: #FFF3E0; color: var(--primary);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }

    .icon-blue { background: #E3F2FD; color: #1E88E5; }
    .icon-green { background: #E8F5E9; color: #43A047; }
    .icon-purple { background: #F3E5F5; color: #8E24AA; }

    .employer-stat-card {
        padding: 30px;
        background: white;
        border-radius: 20px;
        text-align: center;
        transition: transform 0.3s;
    }

    .employer-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .employer-stat-card__icon {
        margin: 0 auto 15px;
        width: 60px; height: 60px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px;
        background: #FFF3E0; color: var(--primary);
    }

    .employer-stat-card__number {
        font-size: 1.8rem; font-weight: 800; color: var(--slate-900);
    }

    .employer-job-card {
        background: white;
        padding: 24px;
        border-radius: 20px;
        border: 1px solid transparent;
        transition: all 0.3s;
        box-shadow: var(--shadow-sm);
    }

    .employer-job-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-lg);
    }

    .status-badge {
        font-size: 11px; font-weight: 700; padding: 4px 10px;
        border-radius: 6px; background: #f1f5f9; color: #64748b;
    }
    .status-badge.active { background: #dcfce7; color: #166534; }

    .employer-job-card__title {
        font-size: 1.15rem; font-weight: 700; color: var(--slate-900);
        margin: 0;
    }

    .employer-job-card__info {
        display: flex; align-items: center; gap: 10px;
        font-size: 14px; color: var(--slate-500); margin-top: 10px;
    }

    .employer-job-card__info i { color: var(--primary); width: 16px; }

    .employer-job-card__footer {
        margin-top: 20px; padding-top: 15px; border-top: 1px solid #f1f5f9;
    }

    .btn-text {
        color: var(--primary); font-weight: 700; text-decoration: none; font-size: 14px;
    }

    .btn-text:hover { text-decoration: underline; }

    /* Section Titles */
    .employer-portal-section-title {
        text-align: center; margin-bottom: 50px;
    }

    .employer-portal-section-title h2 {
        font-weight: 800; color: var(--slate-900); font-size: 2.2rem;
    }

    /* CTA */
    .cta-box {
        background: var(--slate-900);
        padding: 60px;
        border-radius: 30px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .cta-box h2 { font-size: 2.5rem; font-weight: 800; }
</style>

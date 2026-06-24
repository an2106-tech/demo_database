<div class="employer-home">
    @php
        $user = auth()->user();
        $metadata = is_array($user?->metadata) ? $user->metadata : [];
        $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];
        $hasEmployerAccess = (bool) $user && (in_array($user->role, ['hr', 'admin'], true) || in_array('employer', $accountTypes, true));

        $primaryHref = $hasEmployerAccess ? route('employers.post_job') : route('employers.login');
        $primaryLabel = $hasEmployerAccess ? 'Đăng tin tuyển dụng' : 'Đăng tin ngay';
    @endphp

    <section class="employer-home-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="employer-home-eyebrow">
                        <span></span>
                        Nhà tuyển dụng
                    </div>
                    <h1>Tuyển dụng rõ ràng. Ra quyết định nhanh.</h1>
                    <p class="employer-home-lead">
                        Đăng tin, nhận hồ sơ, theo dõi phỏng vấn và offer trong một không gian làm việc gọn gàng.
                    </p>
                    <div class="employer-home-actions">
                        <a class="employer-home-btn employer-home-btn--primary" href="{{ $primaryHref }}">
                            <span>{{ $primaryLabel }}</span>
                            <i class="fa fa-arrow-right"></i>
                        </a>
                        @if(! $hasEmployerAccess)
                            <a class="employer-home-btn employer-home-btn--ghost" href="{{ route('employers.register') }}">
                                Tạo tài khoản
                            </a>
                        @else
                            <a class="employer-home-btn employer-home-btn--ghost" href="{{ route('employers.dashboard') }}">
                                Vào dashboard
                            </a>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="employer-home-panel-shell">
                        <div class="employer-home-panel">
                            <div class="employer-home-panel__top">
                                <div>
                                    <small>Workspace</small>
                                    <strong>Recruitment Board</strong>
                                </div>
                                <span>{{ now()->format('d/m') }}</span>
                            </div>

                            <div class="employer-home-metric-grid">
                                <div>
                                    <strong>{{ number_format($totalJobs) }}</strong>
                                    <span>Tin đang mở</span>
                                </div>
                                <div>
                                    <strong>{{ number_format($totalApplications) }}</strong>
                                    <span>Hồ sơ đã nhận</span>
                                </div>
                                <div>
                                    <strong>{{ number_format($totalBranches) }}</strong>
                                    <span>Chi nhánh</span>
                                </div>
                            </div>

                            <div class="employer-home-pipeline">
                                <div class="employer-home-pipeline__row">
                                    <span>Sàng lọc</span>
                                    <b>CV mới</b>
                                </div>
                                <div class="employer-home-pipeline__bar"><span style="width: 72%"></span></div>
                                <div class="employer-home-pipeline__row">
                                    <span>Phỏng vấn</span>
                                    <b>Lịch hẹn</b>
                                </div>
                                <div class="employer-home-pipeline__bar employer-home-pipeline__bar--blue"><span style="width: 54%"></span></div>
                                <div class="employer-home-pipeline__row">
                                    <span>Offer</span>
                                    <b>Chờ duyệt</b>
                                </div>
                                <div class="employer-home-pipeline__bar employer-home-pipeline__bar--green"><span style="width: 38%"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="employer-home-section">
        <div class="container">
            <div class="employer-home-section-head">
                <span>Quy trình</span>
                <h2>Ít bước hơn, dễ kiểm soát hơn.</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <article class="employer-home-card">
                        <span>01</span>
                        <h3>Đăng tin</h3>
                        <p>Tạo vị trí, chọn chi nhánh, gửi duyệt và xuất bản.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="employer-home-card">
                        <span>02</span>
                        <h3>Nhận hồ sơ</h3>
                        <p>Xem ứng viên theo từng vị trí, trạng thái và thời điểm nộp.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="employer-home-card">
                        <span>03</span>
                        <h3>Chốt offer</h3>
                        <p>Theo dõi phỏng vấn, kết quả và thư mời nhận việc.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="employer-home-cta">
        <div class="container">
            <div class="employer-home-cta__box">
                <div>
                    <span>Bắt đầu hôm nay</span>
                    <h2>Sẵn sàng mở vị trí mới?</h2>
                </div>
                <a class="employer-home-btn employer-home-btn--primary" href="{{ $primaryHref }}">
                    <span>{{ $primaryLabel }}</span>
                    <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <style>
        .employer-home {
            --home-bg: #f7f4ef;
            --home-ink: #17120d;
            --home-muted: #746b61;
            --home-line: rgba(23, 18, 13, .1);
            --home-orange: #f37021;
            --home-green: #1f8a5b;
            --home-blue: #2563eb;
            background: var(--home-bg);
            color: var(--home-ink);
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        }

        .employer-home-hero {
            min-height: 680px;
            padding: 118px 0 86px;
            background:
                linear-gradient(115deg, rgba(255, 255, 255, .82), rgba(255, 255, 255, .36)),
                url("{{ asset('assets/img/auth-employer-register-side.png') }}") right 8% bottom / min(44vw, 620px) auto no-repeat,
                var(--home-bg);
        }

        .employer-home-eyebrow,
        .employer-home-section-head span,
        .employer-home-cta__box > div > span {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 18px;
            color: var(--home-orange);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .employer-home-eyebrow span {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--home-orange);
            box-shadow: 0 0 0 8px rgba(243, 112, 33, .12);
        }

        .employer-home h1 {
            max-width: 660px;
            margin: 0;
            color: var(--home-ink);
            font-size: clamp(44px, 6vw, 82px);
            font-weight: 900;
            letter-spacing: 0;
            line-height: .98;
        }

        .employer-home-lead {
            max-width: 560px;
            margin: 28px 0 0;
            color: var(--home-muted);
            font-size: 19px;
            line-height: 1.65;
        }

        .employer-home-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 34px;
        }

        .employer-home-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 52px;
            gap: 12px;
            border-radius: 999px;
            padding: 10px 18px 10px 24px;
            font-weight: 800;
            text-decoration: none !important;
            transition: transform .65s cubic-bezier(.32, .72, 0, 1), box-shadow .65s cubic-bezier(.32, .72, 0, 1);
        }

        .employer-home-btn--primary {
            background: var(--home-ink);
            color: #fff !important;
            box-shadow: 0 22px 44px rgba(23, 18, 13, .18);
        }

        .employer-home-btn--primary span {
            margin: 0;
            color: #fff !important;
            font-size: inherit;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: none;
        }

        .employer-home-btn--primary i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .14);
            transition: transform .65s cubic-bezier(.32, .72, 0, 1);
        }

        .employer-home-btn--primary:hover {
            transform: translateY(-3px);
            color: #fff !important;
        }

        .employer-home-btn--primary:hover i {
            transform: translateX(3px);
        }

        .employer-home-btn--ghost {
            background: rgba(255, 255, 255, .72);
            color: var(--home-ink) !important;
            box-shadow: inset 0 0 0 1px var(--home-line);
        }

        .employer-home-panel-shell {
            padding: 8px;
            border-radius: 32px;
            background: rgba(255, 255, 255, .45);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .8), 0 34px 90px rgba(23, 18, 13, .14);
        }

        .employer-home-panel {
            padding: 28px;
            border-radius: 25px;
            background: rgba(255, 255, 255, .9);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .92);
        }

        .employer-home-panel__top,
        .employer-home-pipeline__row,
        .employer-home-section-head--split,
        .employer-home-cta__box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .employer-home-panel__top small {
            display: block;
            color: var(--home-muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .employer-home-panel__top strong {
            color: var(--home-ink);
            font-size: 22px;
            font-weight: 900;
        }

        .employer-home-panel__top > span {
            border-radius: 999px;
            padding: 9px 13px;
            background: #fff3e9;
            color: var(--home-orange);
            font-weight: 900;
        }

        .employer-home-metric-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 26px;
        }

        .employer-home-metric-grid div,
        .employer-home-card {
            border-radius: 22px;
            background: #fff;
            box-shadow: inset 0 0 0 1px rgba(23, 18, 13, .08);
        }

        .employer-home-metric-grid div {
            min-height: 112px;
            padding: 18px;
        }

        .employer-home-metric-grid strong {
            display: block;
            color: var(--home-ink);
            font-size: 30px;
            font-weight: 900;
        }

        .employer-home-metric-grid span,
        .employer-home-card p,
        .employer-home-pipeline__row span {
            color: var(--home-muted);
        }

        .employer-home-pipeline {
            margin-top: 24px;
            padding: 20px;
            border-radius: 24px;
            background: #17120d;
        }

        .employer-home-pipeline__row {
            color: #fff;
            font-size: 14px;
            font-weight: 800;
        }

        .employer-home-pipeline__row span {
            color: rgba(255, 255, 255, .68);
        }

        .employer-home-pipeline__bar {
            height: 8px;
            margin: 10px 0 18px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(255, 255, 255, .1);
        }

        .employer-home-pipeline__bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: var(--home-orange);
        }

        .employer-home-pipeline__bar--blue span {
            background: var(--home-blue);
        }

        .employer-home-pipeline__bar--green span {
            background: var(--home-green);
        }

        .employer-home-section {
            padding: 96px 0;
        }

        .employer-home-section--soft {
            background: rgba(255, 255, 255, .46);
        }

        .employer-home-section-head {
            max-width: 760px;
            margin-bottom: 32px;
        }

        .employer-home-section-head h2,
        .employer-home-cta__box h2 {
            margin: 0;
            color: var(--home-ink);
            font-size: clamp(30px, 4vw, 52px);
            font-weight: 900;
            letter-spacing: 0;
            line-height: 1.08;
        }

        .employer-home-section-head--split {
            max-width: none;
        }

        .employer-home-section-head--split a {
            color: var(--home-ink);
            font-weight: 900;
        }

        .employer-home-card {
            min-height: 230px;
            padding: 30px;
            transition: transform .65s cubic-bezier(.32, .72, 0, 1), box-shadow .65s cubic-bezier(.32, .72, 0, 1);
        }

        .employer-home-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 70px rgba(23, 18, 13, .1), inset 0 0 0 1px rgba(243, 112, 33, .2);
        }

        .employer-home-card span {
            color: var(--home-orange);
            font-size: 13px;
            font-weight: 900;
        }

        .employer-home-card h3 {
            margin: 18px 0 10px;
            color: var(--home-ink);
            font-size: 24px;
            font-weight: 900;
        }

        .employer-home-card p {
            margin: 0;
            line-height: 1.65;
        }

        .employer-home-cta {
            padding: 34px 0 96px;
        }

        .employer-home-cta__box {
            padding: 42px;
            border-radius: 32px;
            background: #fff;
            box-shadow: inset 0 0 0 1px rgba(23, 18, 13, .08), 0 30px 80px rgba(23, 18, 13, .1);
        }

        @media (max-width: 991.98px) {
            .employer-home-hero {
                min-height: auto;
                padding: 84px 0 62px;
                background:
                    linear-gradient(115deg, rgba(255, 255, 255, .9), rgba(255, 255, 255, .58)),
                    var(--home-bg);
            }

            .employer-home-panel-shell {
                margin-top: 8px;
            }
        }

        @media (max-width: 767.98px) {
            .employer-home-hero,
            .employer-home-section,
            .employer-home-cta {
                padding-left: 4px;
                padding-right: 4px;
            }

            .employer-home-actions,
            .employer-home-section-head--split,
            .employer-home-cta__box {
                align-items: stretch;
                flex-direction: column;
            }

            .employer-home-btn,
            .employer-home-section-head--split a {
                width: 100%;
            }

            .employer-home-metric-grid {
                grid-template-columns: 1fr;
            }

            .employer-home-panel,
            .employer-home-card,
            .employer-home-cta__box {
                padding: 22px;
            }
        }
    </style>
</div>

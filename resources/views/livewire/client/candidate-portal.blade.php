<div>
    <section class="candidate-portal-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="candidate-portal-hero__badge">
                        Dành cho ứng viên tìm việc
                    </div>
                    <h1 class="candidate-portal-hero__title">
                        Tìm việc làm phù hợp, nhanh chóng — quản lý ứng tuyển tập trung
                    </h1>
                    <p class="candidate-portal-hero__subtitle">
                        Tìm kiếm công việc theo ngành nghề, địa điểm, mức lương. Nộp hồ sơ, theo dõi trạng thái ứng tuyển và trao đổi với nhà tuyển dụng trong một giao diện thân thiện.
                    </p>

                    <div class="candidate-portal-hero__actions">
                        @guest
                            <a class="btn candidate-btn-primary" href="{{ route('auth.sign_up', ['role' => 'candidate']) }}">
                                Tạo tài khoản ứng viên
                            </a>
                            <a class="btn candidate-btn-secondary" href="{{ route('auth.login', ['role' => 'candidate']) }}">
                                Đăng nhập
                            </a>
                        @else
                            <a class="btn candidate-btn-primary" href="{{ route('candidates.candidate_dashboard') }}">
                                Vào trang quản lý ứng tuyển
                            </a>
                        @endguest
                    </div>

                    <div class="candidate-portal-hero__stats">
                        <div class="candidate-stat">
                            <div class="candidate-stat__value">01</div>
                            <div class="candidate-stat__label">Tìm việc</div>
                        </div>
                        <div class="candidate-stat">
                            <div class="candidate-stat__value">02</div>
                            <div class="candidate-stat__label">Ứng tuyển</div>
                        </div>
                        <div class="candidate-stat">
                            <div class="candidate-stat__value">03</div>
                            <div class="candidate-stat__label">Được tuyển dụng</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="candidate-portal-hero__panel">
                        <div class="candidate-portal-kpi">
                            <div class="candidate-portal-kpi__icon"><i class="fa fa-briefcase"></i></div>
                            <div>
                                <div class="candidate-portal-kpi__title">Công việc</div>
                                <div class="candidate-portal-kpi__desc">Tìm kiếm và lọc công việc theo điều kiện</div>
                            </div>
                        </div>
                        <div class="candidate-portal-kpi">
                            <div class="candidate-portal-kpi__icon"><i class="fa fa-file-text"></i></div>
                            <div>
                                <div class="candidate-portal-kpi__title">Hồ sơ</div>
                                <div class="candidate-portal-kpi__desc">Quản lý thông tin cá nhân và CV của bạn</div>
                            </div>
                        </div>
                        <div class="candidate-portal-kpi">
                            <div class="candidate-portal-kpi__icon"><i class="fa fa-envelope-open"></i></div>
                            <div>
                                <div class="candidate-portal-kpi__title">Ứng tuyển</div>
                                <div class="candidate-portal-kpi__desc">Theo dõi trạng thái ứng tuyển của bạn</div>
                            </div>
                        </div>
                        <div class="candidate-portal-hero__hint">
                            Giao diện tìm việc thân thiện, tập trung vào trải nghiệm ứng viên.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="candidate-portal-features" id="features">
        <div class="container">
            <div class="candidate-portal-section-title">
                <h2>Tính năng tìm việc toàn diện</h2>
                <p>Công cụ tìm kiếm mạnh mẽ, quản lý ứng tuyển dễ dàng, theo dõi quy trình rõ ràng.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="candidate-feature-card">
                        <div class="candidate-feature-card__icon"><i class="fa fa-search"></i></div>
                        <h3>Tìm kiếm thông minh</h3>
                        <p>Lọc theo ngành nghề, khu vực, mức lương, loại hợp đồng.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="candidate-feature-card">
                        <div class="candidate-feature-card__icon"><i class="fa fa-mouse-pointer"></i></div>
                        <h3>Ứng tuyển nhanh</h3>
                        <p>Nộp hồ sơ với vài click, theo dõi trạng thái ngay lập tức.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="candidate-feature-card">
                        <div class="candidate-feature-card__icon"><i class="fa fa-comments"></i></div>
                        <h3>Trao đổi trực tiếp</h3>
                        <p>Liên hệ với nhà tuyển dụng, nhận feedback và cập nhật quá trình.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .candidate-portal-hero__badge {
            display: inline-block;
            padding: 8px 16px;
            background: #e8f4f8;
            color: #0066cc;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .candidate-portal-hero__title {
            font-size: 40px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 16px;
            color: #1b1b18;
        }

        .candidate-portal-hero__subtitle {
            font-size: 16px;
            line-height: 1.6;
            color: #706f6c;
            margin-bottom: 24px;
        }

        .candidate-portal-hero__actions {
            display: flex;
            gap: 12px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .candidate-btn-primary {
            background-color: #0066cc;
            color: #fff;
            padding: 12px 28px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }

        .candidate-btn-primary:hover {
            background-color: #0052a3;
            color: #fff;
        }

        .candidate-btn-secondary {
            background-color: transparent;
            color: #0066cc;
            padding: 12px 28px;
            border: 2px solid #0066cc;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .candidate-btn-secondary:hover {
            background-color: #0066cc;
            color: #fff;
        }

        .candidate-portal-hero__stats {
            display: flex;
            gap: 24px;
        }

        .candidate-stat {
            text-align: center;
        }

        .candidate-stat__value {
            font-size: 24px;
            font-weight: 800;
            color: #0066cc;
        }

        .candidate-stat__label {
            font-size: 12px;
            color: #706f6c;
            margin-top: 4px;
        }

        .candidate-portal-hero__panel {
            background: #f0f8ff;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .candidate-portal-kpi {
            display: flex;
            gap: 12px;
        }

        .candidate-portal-kpi__icon {
            font-size: 24px;
            color: #0066cc;
            min-width: 32px;
        }

        .candidate-portal-kpi__title {
            font-weight: 600;
            color: #1b1b18;
            font-size: 14px;
        }

        .candidate-portal-kpi__desc {
            font-size: 12px;
            color: #706f6c;
            margin-top: 2px;
        }

        .candidate-portal-hero__hint {
            font-size: 12px;
            color: #706f6c;
            padding-top: 16px;
            border-top: 1px solid rgba(0, 102, 204, 0.1);
            margin-top: 16px;
        }

        .candidate-portal-section-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .candidate-portal-section-title h2 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 12px;
            color: #1b1b18;
        }

        .candidate-portal-section-title p {
            font-size: 16px;
            color: #706f6c;
        }

        .candidate-feature-card {
            background: #fff;
            border: 1px solid #e3e3e0;
            border-radius: 12px;
            padding: 28px;
            text-align: center;
            transition: all 0.3s;
        }

        .candidate-feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
            border-color: #0066cc;
        }

        .candidate-feature-card__icon {
            font-size: 32px;
            color: #0066cc;
            margin-bottom: 16px;
        }

        .candidate-feature-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1b1b18;
            margin-bottom: 12px;
        }

        .candidate-feature-card p {
            font-size: 14px;
            color: #706f6c;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .candidate-portal-hero__title {
                font-size: 28px;
            }

            .candidate-portal-hero__actions {
                flex-direction: column;
            }

            .candidate-btn-primary,
            .candidate-btn-secondary {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</div>

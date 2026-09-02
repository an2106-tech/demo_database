@php
    $branch = $job->branch;
    $department = $job->department;
    $workplace = $job->workplace;
    $salaryLabel = $job->formatted_salary;

    $cityLabel = \App\Enums\VietnamProvince::tryFrom((string) ($branch?->city ?? ''))?->label() ?? ($branch?->city ?? 'Toàn quốc');
    $companyInitials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($branch?->name ?? 'FPT', 0, 2));
    $deadlineText = $job->deadline?->format('d/m/Y') ?? 'Không giới hạn';
@endphp

<div class="fpt-apply-page">
    <style>
        /* === High-End Clean Design System (FPT Signature Orange & White) === */
        :root {
            --fpt-orange: #f37021;
            --fpt-orange-hover: #ea580c;
            --fpt-orange-light: #fff7ed;
            --fpt-orange-border: #fed7aa;
            --fpt-dark: #0f172a;
            --fpt-slate-800: #1e293b;
            --fpt-slate-600: #475569;
            --fpt-slate-500: #64748b;
            --fpt-slate-400: #94a3b8;
            --fpt-slate-200: #e2e8f0;
            --fpt-slate-100: #f1f5f9;
            --fpt-slate-50: #f8fafc;
            --fpt-white: #ffffff;
            --fpt-radius-lg: 20px;
            --fpt-radius-md: 14px;
            --fpt-radius-sm: 10px;
            --fpt-shadow-card: 0 10px 30px -10px rgba(15, 23, 42, 0.05), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            --fpt-shadow-hover: 0 20px 40px -15px rgba(243, 112, 33, 0.18);
            --fpt-ease: cubic-bezier(0.16, 1, 0.3, 1);
        }

        .fpt-apply-page {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(circle at 100% 0%, rgba(243, 112, 33, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 0% 10%, rgba(243, 112, 33, 0.03) 0%, transparent 30%);
            color: var(--fpt-slate-800);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            padding-top: 105px;
            padding-bottom: 70px;
        }

        /* === Breadcrumbs === */
        .fpt-breadcrumb-bar {
            background: transparent;
            border-bottom: 1px solid var(--fpt-slate-200);
            padding: 16px 0;
            margin-bottom: 28px;
        }

        .fpt-breadcrumb-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .fpt-breadcrumb-trail {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            font-size: 13.5px;
            font-weight: 500;
            gap: 8px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .fpt-breadcrumb-trail a {
            color: var(--fpt-slate-500);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .fpt-breadcrumb-trail a:hover {
            color: var(--fpt-orange);
        }

        .fpt-breadcrumb-trail .sep {
            color: var(--fpt-slate-400);
            font-size: 11px;
        }

        .fpt-breadcrumb-trail .current {
            color: var(--fpt-dark);
            font-weight: 600;
            max-width: 280px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .fpt-back-btn {
            align-items: center;
            color: var(--fpt-slate-600);
            display: inline-flex;
            font-size: 13px;
            font-weight: 600;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s var(--fpt-ease);
        }

        .fpt-back-btn:hover {
            color: var(--fpt-orange);
            transform: translateX(-2px);
        }

        /* === Grid Layout === */
        .fpt-apply-layout {
            display: grid;
            grid-template-columns: 360px minmax(0, 1fr);
            gap: 28px;
            align-items: start;
        }

        /* === Left Sidebar Job Card === */
        .fpt-job-sidebar-card {
            background: var(--fpt-white);
            border: 1px solid var(--fpt-slate-200);
            border-radius: var(--fpt-radius-lg);
            box-shadow: var(--fpt-shadow-card);
            overflow: hidden;
            position: sticky;
            top: 24px;
        }

        .fpt-job-sidebar-top {
            background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
            border-bottom: 1px solid var(--fpt-slate-200);
            padding: 28px 24px 20px;
            text-align: center;
            position: relative;
        }

        .fpt-sidebar-logo {
            align-items: center;
            background: #ffffff;
            border: 1px solid var(--fpt-slate-200);
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: inline-flex;
            height: 76px;
            justify-content: center;
            margin-bottom: 16px;
            overflow: hidden;
            padding: 8px;
            width: 76px;
        }

        .fpt-sidebar-logo img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        .fpt-sidebar-logo-fallback {
            background: linear-gradient(135deg, #f37021, #ea580c);
            color: #ffffff;
            font-size: 22px;
            font-weight: 800;
            height: 100%;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .fpt-sidebar-job-title {
            color: var(--fpt-dark);
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.3;
            margin: 0 0 6px;
        }

        .fpt-sidebar-company {
            color: var(--fpt-slate-500);
            font-size: 13.5px;
            font-weight: 600;
            margin: 0;
        }

        .fpt-sidebar-meta-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 20px 24px;
        }

        .fpt-sidebar-meta-item {
            align-items: flex-start;
            display: flex;
            gap: 12px;
        }

        .fpt-sidebar-meta-icon {
            align-items: center;
            background: var(--fpt-slate-50);
            border: 1px solid var(--fpt-slate-200);
            border-radius: 10px;
            color: var(--fpt-slate-600);
            display: inline-flex;
            flex-shrink: 0;
            font-size: 13px;
            height: 34px;
            justify-content: center;
            width: 34px;
        }

        .fpt-sidebar-meta-info span {
            color: var(--fpt-slate-500);
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .fpt-sidebar-meta-info strong {
            color: var(--fpt-dark);
            font-size: 14px;
            font-weight: 600;
        }

        .fpt-sidebar-meta-info .salary-highlight {
            color: #c2410c;
            font-weight: 800;
        }

        .fpt-sidebar-tips {
            background: var(--fpt-slate-50);
            border-top: 1px solid var(--fpt-slate-200);
            padding: 18px 24px;
        }

        .fpt-sidebar-tips-title {
            color: var(--fpt-dark);
            font-size: 12.5px;
            font-weight: 800;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .fpt-sidebar-tips-list {
            color: var(--fpt-slate-500);
            font-size: 12.5px;
            line-height: 1.6;
            margin: 0;
            padding-left: 18px;
        }

        .fpt-sidebar-tips-list li {
            margin-bottom: 4px;
        }

        /* === Right Form Card === */
        .fpt-form-card {
            background: var(--fpt-white);
            border: 1px solid var(--fpt-slate-200);
            border-radius: var(--fpt-radius-lg);
            box-shadow: var(--fpt-shadow-card);
            padding: 36px 40px;
        }

        .fpt-form-head {
            border-bottom: 1px solid var(--fpt-slate-100);
            margin-bottom: 30px;
            padding-bottom: 20px;
        }

        .fpt-form-title {
            color: var(--fpt-dark);
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0 0 6px;
        }

        .fpt-form-title span {
            color: var(--fpt-orange);
        }

        .fpt-form-sub {
            color: var(--fpt-slate-500);
            font-size: 14.5px;
            margin: 0;
        }

        /* === Form Section Dividers === */
        .fpt-form-section {
            align-items: center;
            color: var(--fpt-dark);
            display: flex;
            font-size: 15px;
            font-weight: 800;
            gap: 12px;
            letter-spacing: -0.01em;
            margin: 32px 0 18px;
        }

        .fpt-form-section::after {
            background: var(--fpt-slate-200);
            content: "";
            flex-grow: 1;
            height: 1px;
        }

        .fpt-form-section-icon {
            align-items: center;
            background: var(--fpt-orange-light);
            border: 1px solid var(--fpt-orange-border);
            border-radius: 8px;
            color: var(--fpt-orange);
            display: inline-flex;
            font-size: 13px;
            height: 28px;
            justify-content: center;
            width: 28px;
        }

        /* === Fields & Inputs === */
        .fpt-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .fpt-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .fpt-field label {
            color: var(--fpt-dark);
            font-size: 13.5px;
            font-weight: 700;
        }

        .fpt-field label .required {
            color: #ef4444;
        }

        .fpt-input {
            background: #ffffff;
            border: 1.5px solid var(--fpt-slate-200);
            border-radius: var(--fpt-radius-sm);
            color: var(--fpt-dark);
            font-family: inherit;
            font-size: 14.5px;
            font-weight: 500;
            height: 48px;
            padding: 0 16px;
            transition: all 0.2s var(--fpt-ease);
            width: 100%;
        }

        .fpt-input:focus {
            background: #ffffff;
            border-color: var(--fpt-orange);
            box-shadow: 0 0 0 3px rgba(243, 112, 33, 0.12);
            outline: none;
        }

        textarea.fpt-input {
            height: auto;
            min-height: 110px;
            padding: 14px 16px;
            resize: vertical;
        }

        .fpt-error-text {
            color: #dc2626;
            font-size: 12.5px;
            font-weight: 600;
            margin-top: 2px;
        }

        /* === CV Selection Cards === */
        .fpt-cv-selector {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 14px;
        }

        .fpt-cv-card {
            align-items: center;
            background: #ffffff;
            border: 1.5px solid var(--fpt-slate-200);
            border-radius: var(--fpt-radius-md);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            padding: 14px 18px;
            transition: all 0.2s var(--fpt-ease);
        }

        .fpt-cv-card:hover {
            border-color: var(--fpt-orange-border);
            background: #fffcf9;
        }

        .fpt-cv-card.is-selected {
            background: #fff9f4;
            border-color: var(--fpt-orange);
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.08);
        }

        .fpt-cv-card-left {
            align-items: center;
            display: flex;
            gap: 14px;
        }

        .fpt-cv-radio {
            accent-color: var(--fpt-orange);
            height: 18px;
            width: 18px;
            cursor: pointer;
        }

        .fpt-cv-card-title {
            color: var(--fpt-dark);
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .fpt-cv-card-sub {
            color: var(--fpt-slate-500);
            font-size: 12px;
            margin-top: 2px;
        }

        .fpt-badge-star {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 6px;
            color: #92400e;
            font-size: 10.5px;
            font-weight: 800;
            padding: 2px 6px;
        }

        .fpt-preview-link {
            align-items: center;
            background: var(--fpt-slate-100);
            border-radius: 8px;
            color: var(--fpt-slate-600);
            display: inline-flex;
            font-size: 12px;
            font-weight: 600;
            gap: 5px;
            padding: 6px 12px;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .fpt-preview-link:hover {
            background: var(--fpt-slate-200);
            color: var(--fpt-dark);
        }

        /* === Upload Dropzone === */
        .fpt-upload-zone {
            background: var(--fpt-slate-50);
            border: 2px dashed #cbd5e1;
            border-radius: var(--fpt-radius-md);
            cursor: pointer;
            margin-top: 10px;
            padding: 28px 20px;
            text-align: center;
            transition: all 0.2s var(--fpt-ease);
        }

        .fpt-upload-zone:hover {
            background: #fff7ed;
            border-color: var(--fpt-orange);
        }

        .fpt-upload-icon {
            align-items: center;
            background: var(--fpt-orange-light);
            border: 1px solid var(--fpt-orange-border);
            border-radius: 50%;
            color: var(--fpt-orange);
            display: inline-flex;
            font-size: 20px;
            height: 48px;
            justify-content: center;
            margin-bottom: 12px;
            width: 48px;
        }

        .fpt-upload-title {
            color: var(--fpt-dark);
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .fpt-upload-hint {
            color: var(--fpt-slate-500);
            font-size: 12.5px;
            margin: 0;
        }

        .fpt-selected-file-tag {
            align-items: center;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            color: #065f46;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            gap: 6px;
            margin-top: 12px;
            padding: 6px 14px;
        }

        /* === Sync Checkboxes === */
        .fpt-sync-box {
            background: var(--fpt-slate-50);
            border: 1px solid var(--fpt-slate-200);
            border-radius: var(--fpt-radius-md);
            margin: 24px 0;
            padding: 16px 20px;
        }

        .fpt-check-item {
            align-items: center;
            display: flex;
            gap: 10px;
            cursor: pointer;
        }

        .fpt-check-item + .fpt-check-item {
            margin-top: 10px;
        }

        .fpt-check-item input[type="checkbox"] {
            accent-color: var(--fpt-orange);
            height: 17px;
            width: 17px;
            cursor: pointer;
        }

        .fpt-check-item span {
            color: var(--fpt-dark);
            font-size: 13.5px;
            font-weight: 600;
        }

        .fpt-sync-hint {
            color: var(--fpt-slate-500);
            font-size: 12px;
            margin-top: 8px;
            padding-left: 27px;
        }

        /* === Submit Button === */
        .fpt-submit-btn {
            align-items: center;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            border: none;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(243, 112, 33, 0.3);
            color: #ffffff !important;
            cursor: pointer;
            display: flex;
            font-size: 15.5px;
            font-weight: 800;
            justify-content: center;
            gap: 10px;
            height: 54px;
            margin-top: 28px;
            text-decoration: none !important;
            transition: all 0.25s var(--fpt-ease);
            width: 100%;
        }

        .fpt-submit-btn:hover {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            box-shadow: 0 12px 30px rgba(243, 112, 33, 0.4);
            transform: translateY(-2px);
        }

        .fpt-submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* === Success Modal (Celebration) === */
        .fpt-modal-backdrop {
            align-items: center;
            backdrop-filter: blur(8px);
            background: rgba(15, 23, 42, 0.75);
            display: flex;
            inset: 0;
            justify-content: center;
            padding: 20px;
            position: fixed;
            z-index: 9999;
        }

        .fpt-modal-card {
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
            max-width: 480px;
            padding: 40px 36px;
            text-align: center;
            width: 100%;
            animation: modalFadeIn 0.3s var(--fpt-ease);
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .fpt-modal-icon {
            align-items: center;
            background: #ecfdf5;
            border: 2px solid #a7f3d0;
            border-radius: 50%;
            color: #059669;
            display: inline-flex;
            font-size: 36px;
            height: 80px;
            justify-content: center;
            margin-bottom: 20px;
            width: 80px;
        }

        .fpt-modal-title {
            color: var(--fpt-dark);
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0 0 10px;
        }

        .fpt-modal-desc {
            color: var(--fpt-slate-600);
            font-size: 14.5px;
            line-height: 1.6;
            margin: 0 0 26px;
        }

        /* === Responsive === */
        @media (max-width: 991px) {
            .fpt-apply-page {
                padding-top: 90px;
            }

            .fpt-apply-layout {
                grid-template-columns: 1fr;
            }

            .fpt-job-sidebar-card {
                position: static;
            }

            .fpt-form-card {
                padding: 28px 22px;
            }

            .fpt-form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }
    </style>

    {{-- Breadcrumb Navigation Bar --}}
    <div class="fpt-breadcrumb-bar">
        <div class="container">
            <div class="fpt-breadcrumb-inner">
                <ul class="fpt-breadcrumb-trail">
                    <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('candidates.browse_job') }}">Việc làm</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('jobs.public', ['slug' => $job->slug]) }}">{{ $job->title }}</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li class="current">Ứng tuyển</li>
                </ul>

                <a href="{{ route('jobs.public', ['slug' => $job->slug]) }}" class="fpt-back-btn">
                    <i class="fa fa-arrow-left"></i> Xem lại tin tuyển dụng
                </a>
            </div>
        </div>
    </div>

    {{-- Success Modal --}}
    @if ($showSuccessModal)
        <div class="fpt-modal-backdrop" wire:click="closeSuccessModal">
            <div class="fpt-modal-card" wire:click.stop>
                <div class="fpt-modal-icon">
                    <i class="fa fa-check"></i>
                </div>
                <h3 class="fpt-modal-title">Nộp hồ sơ thành công!</h3>
                <p class="fpt-modal-desc">
                    Hồ sơ ứng tuyển vị trí <strong>{{ $job->title }}</strong> đã được chuyển trực tiếp đến bộ phận nhân sự <strong>{{ $branch?->name }}</strong>. Chúng tôi sẽ phản hồi trong vòng 24 - 48 giờ làm việc.
                </p>
                <button type="button" class="fpt-submit-btn" wire:click="closeSuccessModal" style="margin-top: 0;">
                    <span>Hoàn tất & Đóng</span>
                </button>
            </div>
        </div>
    @endif

    <div class="container">
        <div class="fpt-apply-layout">
            {{-- Left Column: Job Summary Sidebar --}}
            <aside class="fpt-job-sidebar-card">
                <div class="fpt-job-sidebar-top">
                    <div class="fpt-sidebar-logo">
                        @if ($branch?->image)
                            <img src="{{ asset('storage/' . $branch->image) }}" alt="{{ $branch->name }}">
                        @else
                            <div class="fpt-sidebar-logo-fallback">{{ $companyInitials }}</div>
                        @endif
                    </div>
                    <h2 class="fpt-sidebar-job-title">{{ $job->title }}</h2>
                    <p class="fpt-sidebar-company">{{ $branch?->name ?? 'FPT Education' }}</p>
                </div>

                <div class="fpt-sidebar-meta-list">
                    <div class="fpt-sidebar-meta-item">
                        <div class="fpt-sidebar-meta-icon"><i class="fa fa-money"></i></div>
                        <div class="fpt-sidebar-meta-info">
                            <span>Mức lương</span>
                            <strong class="salary-highlight">{{ $salaryLabel }}</strong>
                        </div>
                    </div>

                    <div class="fpt-sidebar-meta-item">
                        <div class="fpt-sidebar-meta-icon"><i class="fa fa-map-marker"></i></div>
                        <div class="fpt-sidebar-meta-info">
                            <span>Địa điểm</span>
                            <strong>{{ $cityLabel }} @if($workplace?->name) · {{ $workplace->name }} @endif</strong>
                        </div>
                    </div>

                    <div class="fpt-sidebar-meta-item">
                        <div class="fpt-sidebar-meta-icon"><i class="fa fa-calendar"></i></div>
                        <div class="fpt-sidebar-meta-info">
                            <span>Hạn nộp hồ sơ</span>
                            <strong>{{ $deadlineText }}</strong>
                        </div>
                    </div>
                </div>

                <div class="fpt-sidebar-tips">
                    <div class="fpt-sidebar-tips-title">
                        <i class="fa fa-lightbulb-o" style="color: var(--fpt-orange);"></i> Lưu ý quan trọng
                    </div>
                    <ul class="fpt-sidebar-tips-list">
                        <li>Kiểm tra kỹ số điện thoại và email để nhận thông báo lịch phỏng vấn.</li>
                        <li>Đính kèm CV định dạng PDF để hiển thị chuẩn xác nhất.</li>
                        <li>Quy trình tuyển dụng hoàn toàn minh bạch và trực tiếp từ FPT.</li>
                    </ul>
                </div>
            </aside>

            {{-- Right Column: Application Form --}}
            <main class="fpt-form-card">
                <div class="fpt-form-head">
                    <h1 class="fpt-form-title">Đơn ứng tuyển <span>trực tuyến</span></h1>
                    <p class="fpt-form-sub">Điền đầy đủ thông tin bên dưới để gửi hồ sơ trực tiếp đến nhà tuyển dụng.</p>
                </div>

                @if($this->requiresCandidateActivation)
                    <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 16px; padding: 22px 24px; margin-bottom: 24px;">
                        <h4 style="color: #9a3412; font-size: 16px; font-weight: 800; margin: 0 0 6px;">Tài khoản chưa kích hoạt hồ sơ ứng viên</h4>
                        <p style="color: #c2410c; font-size: 14px; line-height: 1.6; margin: 0 0 16px;">Vui lòng kích hoạt hồ sơ ứng viên để nộp đơn ứng tuyển cho vị trí này.</p>
                        <a href="{{ $this->candidateActivationUrl }}" class="fpt-submit-btn" style="height: 46px; font-size: 14px; margin-top: 0;">
                            <span>Kích hoạt hồ sơ ứng viên</span>
                        </a>
                    </div>
                @else
                    <form wire:submit.prevent="submit">
                        @error('application')
                            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; color: #1d4ed8; font-size: 14px; font-weight: 700; padding: 14px 18px; margin-bottom: 20px;">
                                {{ $message }}
                            </div>
                        @enderror

                        {{-- Section 1: Personal Info --}}
                        <div class="fpt-form-section">
                            <div class="fpt-form-section-icon"><i class="fa fa-user"></i></div>
                            <span>Thông tin cá nhân</span>
                        </div>

                        <div class="fpt-form-grid">
                            <div class="fpt-field">
                                <label>Họ và tên <span class="required">*</span></label>
                                <input type="text" wire:model="name" class="fpt-input" placeholder="Ví dụ: Nguyễn Văn A">
                                @error('name') <span class="fpt-error-text">{{ $message }}</span> @enderror
                            </div>

                            <div class="fpt-field">
                                <label>Địa chỉ Email <span class="required">*</span></label>
                                <input type="email" wire:model="email" class="fpt-input" placeholder="example@email.com">
                                @error('email') <span class="fpt-error-text">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="fpt-form-grid">
                            <div class="fpt-field">
                                <label>Số điện thoại</label>
                                <input type="text" wire:model="phone" class="fpt-input" placeholder="09xx xxx xxx">
                                @error('phone') <span class="fpt-error-text">{{ $message }}</span> @enderror
                            </div>

                            <div class="fpt-field">
                                <label>Số năm kinh nghiệm</label>
                                <input type="number" wire:model="experience_years" class="fpt-input" placeholder="Ví dụ: 2">
                                @error('experience_years') <span class="fpt-error-text">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Section 2: Professional Profile --}}
                        <div class="fpt-form-section">
                            <div class="fpt-form-section-icon"><i class="fa fa-briefcase"></i></div>
                            <span>Chuyên môn & Hồ sơ</span>
                        </div>

                        <div class="fpt-field" style="margin-bottom: 20px;">
                            <label>Vị trí hiện tại / Tiêu đề hồ sơ</label>
                            <input type="text" wire:model="profile_title" class="fpt-input" placeholder="Ví dụ: Senior Laravel Developer / Chuyên viên Tuyển dụng">
                            @error('profile_title') <span class="fpt-error-text">{{ $message }}</span> @enderror
                        </div>

                        {{-- Section 3: CV Selection --}}
                        <div class="fpt-field" style="margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <label style="margin: 0;">
                                    <i class="fa fa-file-text-o" style="color: var(--fpt-orange); margin-right: 4px;"></i>
                                    Đính kèm CV ứng tuyển <span class="required">*</span>
                                </label>
                                @if(Auth::check())
                                    <a href="{{ route('candidates.manage_cv') }}" target="_blank" style="color: var(--fpt-orange); font-size: 13px; font-weight: 700; text-decoration: none;">
                                        <i class="fa fa-cog"></i> Quản lý CV
                                    </a>
                                @endif
                            </div>

                            @if(Auth::check())
                                {{-- Authenticated users choose a saved CV; template selection stays in the CV builder. --}}
                                <div class="fpt-cv-selector">
                                    @if($onlineCv)
                                        @php
                                            $val = 'online_' . $onlineCv['template'];
                                        @endphp
                                        <label class="fpt-cv-card {{ $selectedCvOption === $val ? 'is-selected' : '' }}">
                                            <div class="fpt-cv-card-left">
                                                <input type="radio" wire:model.live="selectedCvOption" value="{{ $val }}" class="fpt-cv-radio">
                                                <div>
                                                    <div class="fpt-cv-card-title">
                                                        <i class="fa fa-desktop" style="color: #2563eb;"></i>
                                                        <span>CV online của tôi</span>
                                                        @if($onlineCv['is_primary'])
                                                            <span class="fpt-badge-star">⭐ CV CHÍNH</span>
                                                        @endif
                                                    </div>
                                                    <div class="fpt-cv-card-sub">Mẫu đang áp dụng: {{ $onlineCv['name'] }} · Hệ thống sẽ kết xuất PDF khi gửi</div>
                                                </div>
                                            </div>

                                            <a href="{{ route('candidates.cv.download', ['template' => $onlineCv['template'], 'mode' => 'stream', 'action' => 'view']) }}" target="_blank" class="fpt-preview-link" onclick="event.stopPropagation();">
                                                <i class="fa fa-eye"></i> Xem trước
                                            </a>
                                        </label>
                                    @endif

                                    {{-- Uploaded Attachments --}}
                                    @if(isset($attachments) && $attachments->isNotEmpty())
                                        @foreach($attachments as $att)
                                            @php
                                                $val = 'attachment_' . $att->id;
                                                $isPrimary = (data_get($primaryCv, 'type') === 'attachment' && (int)data_get($primaryCv, 'attachment_id') === $att->id);
                                            @endphp
                                            <label class="fpt-cv-card {{ $selectedCvOption === $val ? 'is-selected' : '' }}">
                                                <div class="fpt-cv-card-left">
                                                    <input type="radio" wire:model.live="selectedCvOption" value="{{ $val }}" class="fpt-cv-radio">
                                                    <div>
                                                        <div class="fpt-cv-card-title">
                                                            <i class="fa fa-file-pdf-o" style="color: #dc2626;"></i>
                                                            <span>{{ $att->original_filename }}</span>
                                                            @if($isPrimary)
                                                                <span class="fpt-badge-star">⭐ CV CHÍNH</span>
                                                            @endif
                                                        </div>
                                                        <div class="fpt-cv-card-sub">File đính kèm đã lưu ({{ round($att->size_bytes / 1024) }} KB)</div>
                                                    </div>
                                                </div>

                                                <a href="{{ Storage::disk('public')->url($att->path) }}" target="_blank" class="fpt-preview-link" onclick="event.stopPropagation();">
                                                    <i class="fa fa-eye"></i> Xem file
                                                </a>
                                            </label>
                                        @endforeach
                                    @endif

                                    {{-- Upload Brand New CV --}}
                                    <label class="fpt-cv-card {{ $selectedCvOption === 'new_upload' ? 'is-selected' : '' }}">
                                        <div class="fpt-cv-card-left">
                                            <input type="radio" wire:model.live="selectedCvOption" value="new_upload" class="fpt-cv-radio">
                                            <div>
                                                <div class="fpt-cv-card-title">
                                                    <i class="fa fa-cloud-upload" style="color: var(--fpt-orange);"></i>
                                                    <span>Tải lên file CV mới từ máy tính</span>
                                                </div>
                                                <div class="fpt-cv-card-sub">Chọn file PDF/DOC/DOCX riêng cho đợt ứng tuyển này</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @else
                                {{-- Guest User: Show clean notice to login if they have saved CVs --}}
                                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 12px 16px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fa fa-info-circle" style="color: #2563eb; font-size: 16px;"></i>
                                        <span style="font-size: 13px; color: #1e40af; font-weight: 500;">
                                            Bạn đã có tài khoản FPT Careers?
                                        </span>
                                    </div>
                                    <a href="{{ route('candidates.login') }}" style="color: var(--fpt-orange); font-size: 13px; font-weight: 700; text-decoration: none;">
                                        <i class="fa fa-sign-in"></i> Đăng nhập để dùng CV đã lưu
                                    </a>
                                </div>
                            @endif

                            {{-- Dropzone (Always visible for Guest, or shown when new_upload is selected for Auth) --}}
                            <div x-show="! @json(Auth::check()) || $wire.selectedCvOption === 'new_upload'" x-transition>
                                <div
                                    class="fpt-upload-zone"
                                    onclick="document.getElementById('cv-file').click()"
                                    x-data="{ selectedCvName: '' }"
                                >
                                    <input
                                        type="file"
                                        id="cv-file"
                                        wire:model="cv"
                                        hidden
                                        accept="{{ \App\Support\CvUpload::acceptAttribute() }}"
                                        x-on:change="selectedCvName = $event.target.files?.[0]?.name || ''"
                                    >
                                    <div class="fpt-upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                    <h4 class="fpt-upload-title">Nhấp để chọn file hồ sơ CV từ thiết bị</h4>
                                    <p class="fpt-upload-hint">Định dạng hỗ trợ: PDF, DOC, DOCX (Dung lượng tối đa 10MB)</p>

                                    <div
                                        x-show="selectedCvName"
                                        x-cloak
                                        class="fpt-selected-file-tag"
                                    >
                                        <i class="fa fa-check-circle"></i>
                                        <span>Đã chọn: <strong x-text="selectedCvName"></strong></span>
                                    </div>

                                    @if($cv && method_exists($cv, 'getClientOriginalName'))
                                        <div class="fpt-selected-file-tag">
                                            <i class="fa fa-check-circle"></i>
                                            <span>Đã tải lên tạm thời: <strong>{{ $cv->getClientOriginalName() }}</strong></span>
                                        </div>
                                    @endif
                                </div>
                                @error('cv') <span class="fpt-error-text">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Section 4: Profile Sync Checkboxes --}}
                        @if(Auth::check() && ! $this->requiresCandidateActivation)
                            <div class="fpt-sync-box">
                                <label class="fpt-check-item">
                                    <input type="checkbox" wire:model="sync_profile_to_candidate">
                                    <span>Cập nhật thông tin trên vào hồ sơ ứng viên của tôi</span>
                                </label>
                                <label class="fpt-check-item">
                                    <input type="checkbox" wire:model="use_cv_as_primary">
                                    <span>Đặt bản CV này làm CV chính của tài khoản</span>
                                </label>
                                <div class="fpt-sync-hint">
                                    Nếu không chọn, thông tin chỉ được áp dụng riêng cho lượt ứng tuyển này.
                                </div>
                            </div>
                        @endif

                        {{-- Section 5: Career Objective / Note --}}
                        <div class="fpt-field">
                            <label>Thư giới thiệu ngắn gọn / Mục tiêu nghề nghiệp</label>
                            <textarea wire:model="career_objective" class="fpt-input" placeholder="Chia sẻ ngắn gọn về thế mạnh, kinh nghiệm hoặc lý do bạn mong muốn đồng hành cùng FPT Education..."></textarea>
                            @error('career_objective') <span class="fpt-error-text">{{ $message }}</span> @enderror
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="fpt-submit-btn" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="fa fa-paper-plane-o"></i> Xác nhận và gửi đơn ứng tuyển
                            </span>
                            <span wire:loading>
                                <i class="fa fa-spinner fa-spin"></i> Đang xử lý hồ sơ...
                            </span>
                        </button>
                    </form>
                @endif
            </main>
        </div>
    </div>
</div>

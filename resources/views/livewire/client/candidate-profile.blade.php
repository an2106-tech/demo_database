@php
    $sections = [
        ['id' => 'personal-info', 'label' => 'Thông tin cá nhân', 'index' => '01', 'icon' => 'fa-user-o'],
        ['id' => 'career-objective', 'label' => 'Mục tiêu nghề nghiệp', 'index' => '02', 'icon' => 'fa-bullseye'],
        ['id' => 'desired-job', 'label' => 'Công việc mong muốn', 'index' => '03', 'icon' => 'fa-briefcase'],
        ['id' => 'experiences', 'label' => 'Kinh nghiệm làm việc', 'index' => '04', 'icon' => 'fa-history'],
        ['id' => 'educations', 'label' => 'Học vấn & Bằng cấp', 'index' => '05', 'icon' => 'fa-graduation-cap'],
        ['id' => 'skills', 'label' => 'Kỹ năng chuyên môn', 'index' => '06', 'icon' => 'fa-cogs'],
        ['id' => 'languages', 'label' => 'Ngôn ngữ & Ngoại ngữ', 'index' => '07', 'icon' => 'fa-language'],
        ['id' => 'certifications', 'label' => 'Chứng chỉ & Giải thưởng', 'index' => '08', 'icon' => 'fa-certificate'],
        ['id' => 'extra-info', 'label' => 'CV & Thông tin bổ sung', 'index' => '09', 'icon' => 'fa-file-text-o'],
    ];

    $isApplicationReady = empty($missingApplicationFields);
@endphp

<div class="candidate-profile-vanguard" x-data="{ activeSection: $wire.entangle('activeSection'), aiReportOpen: false }">
    <style>
        /* Vanguard High-End Visual Design System Scoped */
        .candidate-profile-vanguard {
            --vg-bg: #f8fafc;
            --vg-surface: #ffffff;
            --vg-surface-alt: #f1f5f9;
            --vg-ink: #0f172a;
            --vg-muted: #64748b;
            --vg-line: #e2e8f0;
            --vg-line-subtle: #f1f5f9;
            --vg-primary: #f37021;
            --vg-primary-hover: #ea580c;
            --vg-primary-glow: rgba(243, 112, 33, 0.25);
            --vg-success: #10b981;
            --vg-danger: #ef4444;
            --vg-ease: cubic-bezier(0.16, 1, 0.3, 1);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--vg-ink);
            padding: 105px 0 64px;
            background: #f8fafc;
            min-height: 100vh;
        }

        .vg-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Layout Split */
        .vg-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 28px;
            align-items: start;
        }

        @media (max-width: 991.98px) {
            .vg-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Double-Bezel Shell */
        .vg-shell {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid var(--vg-line);
            border-radius: 24px;
            padding: 6px;
            box-shadow: 0 16px 36px -8px rgba(15, 23, 42, 0.06);
            backdrop-filter: blur(12px);
        }

        .vg-core {
            background: var(--vg-surface);
            border-radius: 18px;
            border: 1px solid var(--vg-line-subtle);
            padding: 24px;
        }

        /* Back to Dashboard Link */
        .vg-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid var(--vg-line);
            color: var(--vg-muted) !important;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
            transition: all 0.2s var(--vg-ease);
            text-decoration: none !important;
        }

        .vg-back-btn:hover {
            color: var(--vg-primary) !important;
            border-color: var(--vg-primary);
            transform: translateX(-3px);
            background: #fff7ed;
        }

        /* Readiness Status Card (Clean Light / Pastel Edition) */
        .vg-readiness-card {
            background: linear-gradient(135deg, #ffffff 0%, #fff7ed 100%);
            border: 1px solid rgba(243, 112, 33, 0.2);
            border-radius: 18px;
            padding: 18px 20px;
            color: #0f172a;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px -2px rgba(243, 112, 33, 0.08);
            position: relative;
            overflow: hidden;
        }

        .vg-readiness-card::after {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(243, 112, 33, 0.12) 0%, rgba(243, 112, 33, 0) 70%);
            pointer-events: none;
        }

        .vg-readiness-eyebrow {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            display: block;
            margin-bottom: 8px;
        }

        .vg-readiness-score {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .vg-readiness-score strong {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: -0.02em;
            color: #0f172a;
        }

        .vg-readiness-score span {
            font-size: 11.5px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .vg-readiness-score span.is-ready {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        .vg-progress-bar {
            height: 8px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .vg-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #f37021 0%, #ea580c 100%);
            border-radius: 999px;
            transition: width 0.6s var(--vg-ease);
        }

        .vg-missing-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .vg-missing-tag {
            font-size: 11px;
            font-weight: 600;
            background: #fef2f2;
            color: #dc2626;
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid #fecaca;
        }

        .vg-readiness-note {
            font-size: 12px;
            color: #059669;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Sidebar Nav List */
        .vg-nav-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .vg-nav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 14px;
            border-radius: 12px;
            background: transparent;
            border: 1px solid transparent;
            color: #475569;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s var(--vg-ease);
            text-align: left;
            width: 100%;
        }

        .vg-nav-item:hover {
            background: #f8fafc;
            color: var(--vg-ink);
            transform: translateX(3px);
        }

        .vg-nav-item.is-active {
            background: linear-gradient(135deg, #ffffff 0%, #fff7ed 100%);
            border-color: rgba(243, 112, 33, 0.25);
            color: var(--vg-primary);
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(243, 112, 33, 0.1);
        }

        .vg-nav-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .vg-nav-num {
            font-size: 11px;
            font-weight: 800;
            color: #94a3b8;
            width: 22px;
            letter-spacing: 0.05em;
        }

        .vg-nav-item.is-active .vg-nav-num {
            color: var(--vg-primary);
        }

        .vg-nav-icon {
            font-size: 14px;
            width: 16px;
            text-align: center;
            color: #94a3b8;
        }

        .vg-nav-item.is-active .vg-nav-icon {
            color: var(--vg-primary);
        }

        /* Hero Header Card */
        .vg-hero-card {
            background: #ffffff;
            border: 1px solid var(--vg-line);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px -6px rgba(15, 23, 42, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .vg-hero-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .vg-avatar-wrap {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #ffffff;
            box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.15);
            overflow: hidden;
            flex-shrink: 0;
            background: #f1f5f9;
        }

        .vg-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .vg-avatar-upload-btn {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
        }

        .vg-avatar-wrap:hover .vg-avatar-upload-btn {
            opacity: 1;
        }

        .vg-hero-copy h1 {
            font-size: 22px;
            font-weight: 800;
            color: var(--vg-ink) !important;
            margin: 0 0 4px;
            letter-spacing: -0.01em;
        }

        .vg-hero-copy p {
            font-size: 13.5px;
            color: var(--vg-muted);
            margin: 0 0 10px;
            line-height: 1.4;
        }

        .vg-meta-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .vg-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            background: #f8fafc;
            border: 1px solid var(--vg-line);
            padding: 4px 10px;
            border-radius: 8px;
            color: #334155;
        }

        /* Button-in-Button CTA */
        .vg-btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 10px 18px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none !important;
            box-shadow: 0 8px 24px -4px rgba(243, 112, 33, 0.35);
            border: none;
            cursor: pointer;
            transition: all 0.25s var(--vg-ease);
        }

        .vg-btn-cta:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            box-shadow: 0 12px 28px -4px rgba(243, 112, 33, 0.45);
        }

        .vg-btn-icon-circle {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .vg-btn-cta:hover .vg-btn-icon-circle {
            transform: scale(1.1) rotate(15deg);
        }

        /* AI Insights Collapsible Card */
        .vg-ai-card {
            background: #ffffff;
            border: 1px solid var(--vg-line);
            border-radius: 20px;
            padding: 16px 20px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px -6px rgba(15, 23, 42, 0.05);
            transition: all 0.3s var(--vg-ease);
        }

        .vg-ai-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            cursor: pointer;
            user-select: none;
        }

        .vg-ai-score-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: var(--vg-primary);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 13.5px;
            font-weight: 800;
        }

        .vg-ai-toggle-btn {
            background: #f8fafc;
            border: 1px solid var(--vg-line);
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .vg-ai-toggle-btn:hover {
            background: #f1f5f9;
            color: var(--vg-ink);
        }

        /* Form Panels & Field Layouts */
        .vg-panel {
            background: #ffffff;
            border: 1px solid var(--vg-line);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 10px 30px -6px rgba(15, 23, 42, 0.05);
        }

        .vg-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--vg-line-subtle);
        }

        .vg-panel-head h2 {
            font-size: 18px;
            font-weight: 800;
            color: var(--vg-ink) !important;
            margin: 0;
        }

        .vg-panel-num {
            font-size: 12px;
            font-weight: 800;
            color: var(--vg-primary);
            background: #fff7ed;
            padding: 3px 8px;
            border-radius: 6px;
            margin-right: 8px;
        }

        .vg-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .vg-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        @media (max-width: 767.98px) {
            .vg-grid-2, .vg-grid-3 {
                grid-template-columns: 1fr;
            }
        }

        .vg-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .vg-field-full {
            grid-column: 1 / -1;
        }

        .vg-label {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }

        .vg-input, .vg-select, .vg-textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            color: #0f172a;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.2s var(--vg-ease);
            outline: none;
        }

        .vg-input:focus, .vg-select:focus, .vg-textarea:focus {
            background: #ffffff;
            border-color: var(--vg-primary);
            box-shadow: 0 0 0 4px rgba(243, 112, 33, 0.12);
        }

        .vg-error {
            color: #ef4444;
            font-size: 12px;
            font-weight: 600;
            margin-top: 2px;
        }

        /* Repeatable Stack Cards */
        .vg-repeat-card {
            background: #f8fafc;
            border: 1px solid var(--vg-line);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            position: relative;
            transition: border-color 0.2s ease;
        }

        .vg-repeat-card:hover {
            border-color: #cbd5e1;
        }

        .vg-repeat-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .vg-repeat-badge {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            background: #ffffff;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .vg-btn-delete {
            background: #fee2e2;
            color: #ef4444;
            border: 1px solid #fca5a5;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .vg-btn-delete:hover {
            background: #ef4444;
            color: #ffffff;
        }

        .vg-btn-add {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            border: 1.5px dashed #cbd5e1;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 18px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .vg-btn-add:hover {
            border-color: var(--vg-primary);
            color: var(--vg-primary);
            background: #fff7ed;
        }

        /* Action Footer */
        .vg-action-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--vg-line-subtle);
            flex-wrap: wrap;
            gap: 16px;
        }

        .vg-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 20px -4px rgba(243, 112, 33, 0.4);
            transition: all 0.25s var(--vg-ease);
        }

        .vg-btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -4px rgba(243, 112, 33, 0.5);
        }

        .vg-btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* CV Drop Zone */
        .vg-drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 18px;
            padding: 32px 20px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s var(--vg-ease);
        }

        .vg-drop-zone:hover, .vg-drop-zone.is-drag {
            border-color: var(--vg-primary);
            background: #fff7ed;
        }
    </style>

    <div class="vg-container">
        <div class="fpt-breadcrumb-bar" style="margin-bottom: 24px; padding-top: 0;">
            <div class="fpt-breadcrumb-inner">
                <ul class="fpt-breadcrumb-trail">
                    <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('candidates.candidate_dashboard') }}">Ứng viên</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li class="current">Hồ sơ cá nhân</li>
                </ul>

                <a href="{{ route('candidates.candidate_dashboard') }}" class="fpt-back-btn">
                    <i class="fa fa-arrow-left"></i> Bảng điều khiển
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4" style="border-radius: 14px; font-weight: 600; font-size: 14px;">
                <i class="fa fa-check-circle"></i> {{ session('status') }}
            </div>
        @endif

        <div class="vg-layout">
            <!-- SIDEBAR RAIL (Double-Bezel Architecture) -->
            <aside>
                <div class="vg-shell">
                    <div class="vg-core" style="padding: 16px;">
                        <a href="{{ route('candidates.candidate_dashboard') }}" class="vg-back-btn w-100 justify-content-center">
                            <i class="fa fa-arrow-left"></i> Về Bảng điều khiển
                        </a>

                        <!-- Readiness Status Box -->
                        <div class="vg-readiness-card">
                            <span class="vg-readiness-eyebrow">Điều kiện ứng tuyển</span>
                            <div class="vg-readiness-score">
                                <strong>{{ $applicationCompletion }}%</strong>
                                <span class="{{ $isApplicationReady ? 'is-ready' : '' }}">
                                    {{ $isApplicationReady ? '✓ Sẵn sàng apply' : 'Cần bổ sung' }}
                                </span>
                            </div>
                            <div class="vg-progress-bar">
                                <div class="vg-progress-fill" style="width: {{ $applicationCompletion }}%"></div>
                            </div>
                            @if ($isApplicationReady)
                                <div class="vg-readiness-note">
                                    <i class="fa fa-check-circle"></i> Đã đầy đủ thông tin cốt lõi và CV.
                                </div>
                            @else
                                <div class="vg-missing-tags">
                                    @foreach ($missingApplicationFields as $field)
                                        <span class="vg-missing-tag">{{ $field }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Navigation Items (01 - 09) -->
                        <nav class="vg-nav-list" aria-label="Danh mục hồ sơ">
                            @foreach ($sections as $section)
                                <button
                                    type="button"
                                    wire:click="switchSection('{{ $section['id'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="switchSection,saveSection,save"
                                    class="vg-nav-item {{ $activeSection === $section['id'] ? 'is-active' : '' }}"
                                >
                                    <div class="vg-nav-item-left">
                                        <span class="vg-nav-num">{{ $section['index'] }}</span>
                                        <i class="fa {{ $section['icon'] }} vg-nav-icon"></i>
                                        <span>{{ $section['label'] }}</span>
                                    </div>
                                    <i class="fa fa-angle-right" style="font-size: 12px; color: #cbd5e1;"></i>
                                </button>
                            @endforeach
                        </nav>
                    </div>
                </div>
            </aside>

            <!-- MAIN CONTENT AREA -->
            <main>
                <!-- Hero Header Card -->
                <div class="vg-hero-card">
                    <div class="vg-hero-left">
                        <div class="vg-avatar-wrap">
                            <img src="{{ $avatar ? $avatar->temporaryUrl() : $this->currentAvatarUrl }}" alt="Avatar" class="vg-avatar-img" onerror="this.onerror=null; this.src='{{ asset('assets/img/avatar_detail.jpg') }}';">
                            <input type="file" id="avatar_upload_hero" wire:model="avatar" accept="image/png,image/jpeg,image/jpg,image/webp" class="d-none">
                            <label for="avatar_upload_hero" class="vg-avatar-upload-btn" title="Cập nhật ảnh đại diện">
                                <span wire:loading.remove wire:target="avatar"><i class="fa fa-camera mb-1"></i> Đổi ảnh</span>
                                <span wire:loading wire:target="avatar"><i class="fa fa-circle-o-notch fa-spin"></i></span>
                            </label>
                        </div>
                        <div class="vg-hero-copy">
                            <h1>{{ $name ?: 'Hồ sơ Ứng viên' }}</h1>
                            <p>{{ $profile_title ?: 'Cập nhật đầy đủ hồ sơ để nhà tuyển dụng đánh giá năng lực và mức độ phù hợp của bạn.' }}</p>
                            <div class="vg-meta-chips">
                                <div class="vg-meta-chip">
                                    <i class="fa fa-envelope-o text-muted"></i> {{ $email ?: 'Chưa cập nhật email' }}
                                </div>
                                <div class="vg-meta-chip">
                                    <i class="fa fa-phone text-muted"></i> {{ $phone ?: 'Chưa có số điện thoại' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('candidates.cv_builder') }}" class="vg-btn-cta">
                        <span>Tạo CV Online AI (CV Builder)</span>
                        <div class="vg-btn-icon-circle">
                            <i class="fa fa-magic"></i>
                        </div>
                    </a>
                </div>

                <!-- AI Insights Collapsible Card -->
                <div class="vg-ai-card">
                    @if($aiScore === null)
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 42px; height: 42px; border-radius: 12px; background: #fff7ed; color: #f37021; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                    <i class="fa fa-lightbulb-o"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">Đánh giá chất lượng hồ sơ & Chuẩn ATS</h4>
                                    <div style="font-size: 12.5px; color: #64748b;">Sử dụng Trí tuệ Nhân tạo để phân tích điểm mạnh, điểm yếu và mức độ sẵn sàng của CV.</div>
                                </div>
                            </div>
                            <button type="button" wire:click="analyzeCvWithAi" class="vg-btn-primary" wire:loading.attr="disabled" style="padding: 9px 18px; font-size: 13px;">
                                <span wire:loading.remove wire:target="analyzeCvWithAi"><i class="fa fa-bolt"></i> Chấm điểm CV bằng AI</span>
                                <span wire:loading wire:target="analyzeCvWithAi"><i class="fa fa-circle-o-notch fa-spin"></i> Đang phân tích...</span>
                            </button>
                        </div>
                    @else
                        <!-- Compact Header with Toggle -->
                        <div class="vg-ai-card-header" @click="aiReportOpen = !aiReportOpen">
                            <div class="d-flex align-items-center gap-3">
                                <div class="vg-ai-score-pill">
                                    <i class="fa fa-tachometer"></i> Điểm ATS: {{ $aiScore }}/100
                                </div>
                                <div>
                                    <div style="font-size: 14px; font-weight: 800; color: #0f172a;">Báo cáo phân tích chất lượng hồ sơ AI</div>
                                    <div style="font-size: 12px; color: #64748b;">Bấm để xem chi tiết điểm mạnh, điểm yếu và từ khóa chuẩn ATS</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" wire:click.stop="analyzeCvWithAi" class="vg-ai-toggle-btn" wire:loading.attr="disabled" title="Phân tích lại">
                                    <i class="fa fa-refresh"></i> Phân tích lại
                                </button>
                                <button type="button" class="vg-ai-toggle-btn">
                                    <span x-show="!aiReportOpen">Xem chi tiết <i class="fa fa-chevron-down ms-1"></i></span>
                                    <span x-show="aiReportOpen" x-cloak>Thu gọn <i class="fa fa-chevron-up ms-1"></i></span>
                                </button>
                            </div>
                        </div>

                        <!-- Expanded Content -->
                        <div x-show="aiReportOpen" x-transition.opacity.duration.250ms class="mt-3 pt-3 border-top" x-cloak>
                            @if(!empty($aiSummary))
                                <div class="p-3 mb-3 bg-light rounded-3" style="font-size: 13.5px; line-height: 1.5; color: #334155;">
                                    <strong>Nhận xét tổng quan:</strong> {{ $aiSummary }}
                                </div>
                            @endif

                            <div class="row">
                                @if(!empty($aiStrengths))
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 rounded-3" style="background: #f0fdf4; border: 1px solid #bbf7d0; height: 100%;">
                                            <h6 style="color: #16a34a; font-weight: 800; font-size: 13px; margin-bottom: 8px;"><i class="fa fa-check-circle"></i> Điểm mạnh nổi bật</h6>
                                            <ul class="mb-0 ps-3" style="font-size: 13px; color: #1e293b;">
                                                @foreach($aiStrengths as $st)
                                                    <li>{{ $st }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($aiWeaknesses))
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 rounded-3" style="background: #fef2f2; border: 1px solid #fecaca; height: 100%;">
                                            <h6 style="color: #dc2626; font-weight: 800; font-size: 13px; margin-bottom: 8px;"><i class="fa fa-exclamation-circle"></i> Điểm cần cải thiện</h6>
                                            <ul class="mb-0 ps-3" style="font-size: 13px; color: #1e293b;">
                                                @foreach($aiWeaknesses as $wk)
                                                    <li>{{ $wk }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($aiAtsKeywords))
                                    <div class="col-12 mb-2">
                                        <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">Từ khóa chuẩn ATS phát hiện trong hồ sơ:</div>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($aiAtsKeywords as $kw)
                                                <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 11.5px;">{{ $kw }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- MAIN FORM CONTAINER -->
                <form wire:submit.prevent="save" novalidate>
                    <!-- 01. THÔNG TIN CÁ NHÂN -->
                    <div x-show="activeSection === 'personal-info'" x-transition.opacity class="vg-panel">
                        <div class="vg-panel-head">
                            <h2><span class="vg-panel-num">01</span> Thông tin cá nhân</h2>
                        </div>

                        <div class="vg-grid-2">
                            <div class="vg-field vg-field-full">
                                <label class="vg-label">Tiêu đề hồ sơ / Vị trí chuyên môn</label>
                                <input type="text" class="vg-input" wire:model.defer="profile_title" placeholder="VD: Senior Frontend Developer / Kỹ sư phần mềm" />
                                @error('profile_title') <span class="vg-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="vg-field">
                                <label class="vg-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="vg-input" wire:model.defer="name" placeholder="Nguyễn Văn A" />
                                @error('name') <span class="vg-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="vg-field">
                                <label class="vg-label">Email liên hệ <span class="text-danger">*</span></label>
                                <input type="email" class="vg-input" wire:model.defer="email" placeholder="example@email.com" />
                                @error('email') <span class="vg-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="vg-field">
                                <label class="vg-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" class="vg-input" wire:model.defer="phone" placeholder="0901234567" />
                                @error('phone') <span class="vg-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="vg-field">
                                <label class="vg-label">Số năm kinh nghiệm</label>
                                <input type="number" class="vg-input" wire:model.defer="experience_years" placeholder="VD: 2" min="0" />
                                @error('experience_years') <span class="vg-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="vg-field">
                                <label class="vg-label">Giới tính</label>
                                <select class="vg-select" wire:model.defer="personal_info.gender">
                                    <option value="">-- Chọn giới tính --</option>
                                    <option value="Nam">Nam</option>
                                    <option value="Nữ">Nữ</option>
                                    <option value="Khác">Khác</option>
                                </select>
                            </div>

                            <div class="vg-field">
                                <label class="vg-label">Ngày sinh</label>
                                <input type="date" class="vg-input" wire:model.defer="personal_info.date_of_birth" />
                            </div>

                            <div class="vg-field vg-field-full">
                                <label class="vg-label">Địa chỉ hiện tại</label>
                                <input type="text" class="vg-input" wire:model.defer="personal_info.address" placeholder="Quận / Huyện, Tỉnh / Thành phố" />
                            </div>
                        </div>

                        <div class="vg-action-footer">
                            <small class="text-muted"><i class="fa fa-info-circle"></i> Họ tên, email, SĐT là thông tin bắt buộc để ứng tuyển.</small>
                            <button type="button" wire:click="saveSection('career-objective')" class="vg-btn-primary" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & Tiếp theo <i class="fa fa-arrow-right ms-1"></i></span>
                                <span wire:loading wire:target="saveSection"><i class="fa fa-circle-o-notch fa-spin"></i> Đang lưu...</span>
                            </button>
                        </div>
                    </div>

                    <!-- 02. MỤC TIÊU NGHỀ NGHIỆP -->
                    <div x-show="activeSection === 'career-objective'" x-transition.opacity class="vg-panel" x-cloak>
                        <div class="vg-panel-head">
                            <h2><span class="vg-panel-num">02</span> Mục tiêu nghề nghiệp</h2>
                        </div>

                        <div class="vg-field">
                            <label class="vg-label">Giới thiệu bản thân & Định hướng phát triển sự nghiệp</label>
                            <textarea class="vg-textarea" wire:model.defer="career_objective" rows="7" placeholder="Nêu bật thế mạnh chuyên môn, các giá trị bạn có thể đóng góp cho doanh nghiệp và mục tiêu nghề nghiệp trong 1-3 năm tới..."></textarea>
                            @error('career_objective') <span class="vg-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="vg-action-footer">
                            <div></div>
                            <button type="button" wire:click="saveSection('desired-job')" class="vg-btn-primary" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & Tiếp theo <i class="fa fa-arrow-right ms-1"></i></span>
                                <span wire:loading wire:target="saveSection"><i class="fa fa-circle-o-notch fa-spin"></i> Đang lưu...</span>
                            </button>
                        </div>
                    </div>

                    <!-- 03. CÔNG VIỆC MONG MUỐN -->
                    <div x-show="activeSection === 'desired-job'" x-transition.opacity class="vg-panel" x-cloak>
                        <div class="vg-panel-head">
                            <h2><span class="vg-panel-num">03</span> Công việc mong muốn</h2>
                        </div>

                        <div class="vg-grid-2">
                            <div class="vg-field">
                                <label class="vg-label">Vị trí mong muốn</label>
                                <input type="text" class="vg-input" wire:model.defer="desired_job.position" placeholder="VD: Lập trình viên Fullstack / Tester" />
                            </div>

                            <div class="vg-field">
                                <label class="vg-label">Cấp bậc mong muốn</label>
                                <select class="vg-select" wire:model.defer="desired_job.level">
                                    <option value="">-- Chọn cấp bậc --</option>
                                    <option value="Thực tập sinh / Sinh viên">Thực tập sinh / Sinh viên</option>
                                    <option value="Mới tốt nghiệp (Fresher)">Mới tốt nghiệp (Fresher)</option>
                                    <option value="Nhân viên (Junior / Mid-level)">Nhân viên (Junior / Mid-level)</option>
                                    <option value="Chuyên viên / Trưởng nhóm (Senior / Lead)">Chuyên viên / Trưởng nhóm (Senior / Lead)</option>
                                    <option value="Quản lý / Trưởng phòng (Manager)">Quản lý / Trưởng phòng (Manager)</option>
                                    <option value="Giám đốc / Cấp cao (Director / C-Level)">Giám đốc / Cấp cao (Director / C-Level)</option>
                                </select>
                            </div>

                            <div class="vg-field">
                                <label class="vg-label">Mức lương kỳ vọng (VNĐ)</label>
                                <input type="text" class="vg-input" wire:model.defer="desired_job.expected_salary" placeholder="VD: 15.000.000 hoặc Thỏa thuận" />
                            </div>

                            <div class="vg-field">
                                <label class="vg-label">Địa điểm làm việc mong muốn</label>
                                <input type="text" class="vg-input" wire:model.defer="desired_job.location" placeholder="VD: Hà Nội, TP. Hồ Chí Minh, Đà Nẵng, Cần Thơ, Quy Nhơn" />
                            </div>
                        </div>

                        <div class="vg-action-footer">
                            <div></div>
                            <button type="button" wire:click="saveSection('experiences')" class="vg-btn-primary" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & Tiếp theo <i class="fa fa-arrow-right ms-1"></i></span>
                                <span wire:loading wire:target="saveSection"><i class="fa fa-circle-o-notch fa-spin"></i> Đang lưu...</span>
                            </button>
                        </div>
                    </div>

                    <!-- 04. KINH NGHIỆM LÀM VIỆC -->
                    <div x-show="activeSection === 'experiences'" x-transition.opacity class="vg-panel" x-cloak>
                        <div class="vg-panel-head">
                            <h2><span class="vg-panel-num">04</span> Kinh nghiệm làm việc</h2>
                            <button type="button" wire:click="addExperience" class="vg-btn-add">
                                <i class="fa fa-plus"></i> Thêm kinh nghiệm
                            </button>
                        </div>

                        <div>
                            @forelse($experiences as $index => $exp)
                                <div class="vg-repeat-card">
                                    <div class="vg-repeat-card-head">
                                        <span class="vg-repeat-badge"><i class="fa fa-building-o me-1"></i> Kinh nghiệm #{{ $index + 1 }}</span>
                                        <button type="button" wire:click="removeExperience({{ $index }})" class="vg-btn-delete">
                                            <i class="fa fa-trash-o"></i> Xóa mục này
                                        </button>
                                    </div>
                                    <div class="vg-grid-2">
                                        <div class="vg-field">
                                            <label class="vg-label">Tên công ty / Tổ chức</label>
                                            <input type="text" class="vg-input" wire:model.defer="experiences.{{ $index }}.company" placeholder="Tên công ty" />
                                        </div>
                                        <div class="vg-field">
                                            <label class="vg-label">Chức danh / Vị trí</label>
                                            <input type="text" class="vg-input" wire:model.defer="experiences.{{ $index }}.position" placeholder="Vị trí công việc" />
                                        </div>
                                        <div class="vg-field">
                                            <label class="vg-label">Thời gian bắt đầu</label>
                                            <input type="text" class="vg-input" wire:model.defer="experiences.{{ $index }}.from" placeholder="VD: 06/2023" />
                                        </div>
                                        <div class="vg-field">
                                            <label class="vg-label">Thời gian kết thúc</label>
                                            <input type="text" class="vg-input" wire:model.defer="experiences.{{ $index }}.to" placeholder="VD: Hiện tại hoặc 05/2024" />
                                        </div>
                                        <div class="vg-field vg-field-full">
                                            <label class="vg-label">Mô tả công việc & Thành tích đạt được</label>
                                            <textarea class="vg-textarea" wire:model.defer="experiences.{{ $index }}.description" rows="3" placeholder="Mô tả trách nhiệm chính, công nghệ sử dụng và kết quả công việc..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 bg-light rounded-3 mb-3">
                                    <i class="fa fa-briefcase text-muted" style="font-size: 24px;"></i>
                                    <div class="mt-2 text-muted" style="font-size: 13.5px;">Chưa có dữ liệu kinh nghiệm làm việc.</div>
                                    <button type="button" wire:click="addExperience" class="vg-btn-add mt-2">
                                        <i class="fa fa-plus"></i> Thêm kinh nghiệm đầu tiên
                                    </button>
                                </div>
                            @endforelse
                        </div>

                        <div class="vg-action-footer">
                            <div></div>
                            <button type="button" wire:click="saveSection('educations')" class="vg-btn-primary" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & Tiếp theo <i class="fa fa-arrow-right ms-1"></i></span>
                                <span wire:loading wire:target="saveSection"><i class="fa fa-circle-o-notch fa-spin"></i> Đang lưu...</span>
                            </button>
                        </div>
                    </div>

                    <!-- 05. HỌC VẤN & BẰNG CẤP -->
                    <div x-show="activeSection === 'educations'" x-transition.opacity class="vg-panel" x-cloak>
                        <div class="vg-panel-head">
                            <h2><span class="vg-panel-num">05</span> Học vấn & Bằng cấp</h2>
                            <button type="button" wire:click="addEducation" class="vg-btn-add">
                                <i class="fa fa-plus"></i> Thêm học vấn
                            </button>
                        </div>

                        <div>
                            @forelse($educations as $index => $edu)
                                <div class="vg-repeat-card">
                                    <div class="vg-repeat-card-head">
                                        <span class="vg-repeat-badge"><i class="fa fa-graduation-cap me-1"></i> Học vấn #{{ $index + 1 }}</span>
                                        <button type="button" wire:click="removeEducation({{ $index }})" class="vg-btn-delete">
                                            <i class="fa fa-trash-o"></i> Xóa mục này
                                        </button>
                                    </div>
                                    <div class="vg-grid-2">
                                        <div class="vg-field">
                                            <label class="vg-label">Trường học / Cơ sở đào tạo</label>
                                            <input type="text" class="vg-input" wire:model.defer="educations.{{ $index }}.school" placeholder="VD: Đại học FPT / FPT Polytechnic" />
                                        </div>
                                        <div class="vg-field">
                                            <label class="vg-label">Chuyên ngành / Bằng cấp</label>
                                            <input type="text" class="vg-input" wire:model.defer="educations.{{ $index }}.degree" placeholder="VD: Cử nhân Công nghệ Thông tin" />
                                        </div>
                                        <div class="vg-field">
                                            <label class="vg-label">Bắt đầu</label>
                                            <input type="text" class="vg-input" wire:model.defer="educations.{{ $index }}.from" placeholder="VD: 2020" />
                                        </div>
                                        <div class="vg-field">
                                            <label class="vg-label">Kết thúc</label>
                                            <input type="text" class="vg-input" wire:model.defer="educations.{{ $index }}.to" placeholder="VD: 2024" />
                                        </div>
                                        <div class="vg-field vg-field-full">
                                            <label class="vg-label">Mô tả bổ sung (GPA, đề tài tốt nghiệp...)</label>
                                            <textarea class="vg-textarea" wire:model.defer="educations.{{ $index }}.description" rows="2" placeholder="Ghi chú thành tích hoặc chứng chỉ liên quan..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 bg-light rounded-3 mb-3">
                                    <i class="fa fa-graduation-cap text-muted" style="font-size: 24px;"></i>
                                    <div class="mt-2 text-muted" style="font-size: 13.5px;">Chưa có dữ liệu học vấn.</div>
                                    <button type="button" wire:click="addEducation" class="vg-btn-add mt-2">
                                        <i class="fa fa-plus"></i> Thêm học vấn đầu tiên
                                    </button>
                                </div>
                            @endforelse
                        </div>

                        <div class="vg-action-footer">
                            <div></div>
                            <button type="button" wire:click="saveSection('skills')" class="vg-btn-primary" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & Tiếp theo <i class="fa fa-arrow-right ms-1"></i></span>
                                <span wire:loading wire:target="saveSection"><i class="fa fa-circle-o-notch fa-spin"></i> Đang lưu...</span>
                            </button>
                        </div>
                    </div>

                    <!-- 06. KỸ NĂNG CHUYÊN MÔN -->
                    <div x-show="activeSection === 'skills'" x-transition.opacity class="vg-panel" x-cloak>
                        <div class="vg-panel-head">
                            <h2><span class="vg-panel-num">06</span> Kỹ năng chuyên môn</h2>
                            <button type="button" wire:click="addSkill" class="vg-btn-add">
                                <i class="fa fa-plus"></i> Thêm kỹ năng
                            </button>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            @forelse($skills as $index => $skill)
                                <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3 border">
                                    <div class="flex-grow-1">
                                        <input type="text" class="vg-input bg-white" wire:model.defer="skills.{{ $index }}.name" placeholder="Tên kỹ năng (VD: ReactJS, PHP, SQL, Figma)" />
                                    </div>
                                    <div style="width: 180px;">
                                        <input type="text" class="vg-input bg-white" wire:model.defer="skills.{{ $index }}.level" placeholder="Mức độ (VD: Khá / Giỏi / 2 năm)" />
                                    </div>
                                    <button type="button" wire:click="removeSkill({{ $index }})" class="vg-btn-delete" style="height: 48px; padding: 0 14px;">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
                            @empty
                                <div class="text-center py-4 bg-light rounded-3 mb-3">
                                    <i class="fa fa-cogs text-muted" style="font-size: 24px;"></i>
                                    <div class="mt-2 text-muted" style="font-size: 13.5px;">Chưa có kỹ năng chuyên môn nào.</div>
                                    <button type="button" wire:click="addSkill" class="vg-btn-add mt-2">
                                        <i class="fa fa-plus"></i> Thêm kỹ năng đầu tiên
                                    </button>
                                </div>
                            @endforelse
                        </div>

                        <div class="vg-action-footer">
                            <div></div>
                            <button type="button" wire:click="saveSection('languages')" class="vg-btn-primary" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & Tiếp theo <i class="fa fa-arrow-right ms-1"></i></span>
                                <span wire:loading wire:target="saveSection"><i class="fa fa-circle-o-notch fa-spin"></i> Đang lưu...</span>
                            </button>
                        </div>
                    </div>

                    <!-- 07. NGÔN NGỮ & NGOẠI NGỮ -->
                    <div x-show="activeSection === 'languages'" x-transition.opacity class="vg-panel" x-cloak>
                        <div class="vg-panel-head">
                            <h2><span class="vg-panel-num">07</span> Ngôn ngữ & Ngoại ngữ</h2>
                            <button type="button" wire:click="addLanguage" class="vg-btn-add">
                                <i class="fa fa-plus"></i> Thêm ngôn ngữ
                            </button>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            @forelse($languages as $index => $lang)
                                <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3 border">
                                    <div class="flex-grow-1">
                                        <input type="text" class="vg-input bg-white" wire:model.defer="languages.{{ $index }}.name" placeholder="Ngôn ngữ (VD: Tiếng Anh, Tiếng Nhật)" />
                                    </div>
                                    <div style="width: 200px;">
                                        <input type="text" class="vg-input bg-white" wire:model.defer="languages.{{ $index }}.level" placeholder="Trình độ (VD: IELTS 7.0, N2, Thành thạo)" />
                                    </div>
                                    <button type="button" wire:click="removeLanguage({{ $index }})" class="vg-btn-delete" style="height: 48px; padding: 0 14px;">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
                            @empty
                                <div class="text-center py-4 bg-light rounded-3 mb-3">
                                    <i class="fa fa-language text-muted" style="font-size: 24px;"></i>
                                    <div class="mt-2 text-muted" style="font-size: 13.5px;">Chưa có dữ liệu ngôn ngữ.</div>
                                    <button type="button" wire:click="addLanguage" class="vg-btn-add mt-2">
                                        <i class="fa fa-plus"></i> Thêm ngôn ngữ đầu tiên
                                    </button>
                                </div>
                            @endforelse
                        </div>

                        <div class="vg-action-footer">
                            <div></div>
                            <button type="button" wire:click="saveSection('certifications')" class="vg-btn-primary" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & Tiếp theo <i class="fa fa-arrow-right ms-1"></i></span>
                                <span wire:loading wire:target="saveSection"><i class="fa fa-circle-o-notch fa-spin"></i> Đang lưu...</span>
                            </button>
                        </div>
                    </div>

                    <!-- 08. CHỨNG CHỈ & GIẢI THƯỞNG -->
                    <div x-show="activeSection === 'certifications'" x-transition.opacity class="vg-panel" x-cloak>
                        <div class="vg-panel-head">
                            <h2><span class="vg-panel-num">08</span> Chứng chỉ & Giải thưởng</h2>
                            <button type="button" wire:click="addCertification" class="vg-btn-add">
                                <i class="fa fa-plus"></i> Thêm chứng chỉ
                            </button>
                        </div>

                        <div>
                            @forelse($certifications as $index => $cert)
                                <div class="vg-repeat-card">
                                    <div class="vg-repeat-card-head">
                                        <span class="vg-repeat-badge"><i class="fa fa-certificate me-1"></i> Chứng chỉ #{{ $index + 1 }}</span>
                                        <button type="button" wire:click="removeCertification({{ $index }})" class="vg-btn-delete">
                                            <i class="fa fa-trash-o"></i> Xóa mục này
                                        </button>
                                    </div>
                                    <div class="vg-grid-3">
                                        <div class="vg-field">
                                            <label class="vg-label">Tên chứng chỉ</label>
                                            <input type="text" class="vg-input" wire:model.defer="certifications.{{ $index }}.name" placeholder="VD: AWS Certified Cloud Practitioner" />
                                        </div>
                                        <div class="vg-field">
                                            <label class="vg-label">Tổ chức cấp</label>
                                            <input type="text" class="vg-input" wire:model.defer="certifications.{{ $index }}.issuer" placeholder="VD: Amazon Web Services" />
                                        </div>
                                        <div class="vg-field">
                                            <label class="vg-label">Thời gian cấp</label>
                                            <input type="text" class="vg-input" wire:model.defer="certifications.{{ $index }}.date" placeholder="VD: 2024" />
                                        </div>
                                        <div class="vg-field vg-field-full">
                                            <label class="vg-label">Mô tả thêm</label>
                                            <textarea class="vg-textarea" wire:model.defer="certifications.{{ $index }}.description" rows="2" placeholder="Ghi chú thêm về chứng chỉ hoặc giải thưởng..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 bg-light rounded-3 mb-3">
                                    <i class="fa fa-certificate text-muted" style="font-size: 24px;"></i>
                                    <div class="mt-2 text-muted" style="font-size: 13.5px;">Chưa có chứng chỉ hoặc giải thưởng nào.</div>
                                    <button type="button" wire:click="addCertification" class="vg-btn-add mt-2">
                                        <i class="fa fa-plus"></i> Thêm chứng chỉ đầu tiên
                                    </button>
                                </div>
                            @endforelse
                        </div>

                        <div class="vg-action-footer">
                            <div></div>
                            <button type="button" wire:click="saveSection('extra-info')" class="vg-btn-primary" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & Tiếp theo <i class="fa fa-arrow-right ms-1"></i></span>
                                <span wire:loading wire:target="saveSection"><i class="fa fa-circle-o-notch fa-spin"></i> Đang lưu...</span>
                            </button>
                        </div>
                    </div>

                    <!-- 09. CV & THÔNG TIN BỔ SUNG -->
                    <div x-show="activeSection === 'extra-info'" x-transition.opacity class="vg-panel" x-cloak>
                        <div class="vg-panel-head">
                            <h2><span class="vg-panel-num">09</span> CV & Thông tin bổ sung</h2>
                        </div>

                        <div
                            class="vg-drop-zone mb-4"
                            x-data="{ selectedCvName: '', isDragging: false }"
                            x-on:dragover.prevent="isDragging = true"
                            x-on:dragleave.prevent="isDragging = false"
                            x-on:drop.prevent="
                                isDragging = false;
                                const file = $event.dataTransfer.files[0];
                                if (file) {
                                    selectedCvName = file.name;
                                    const dt = new DataTransfer();
                                    dt.items.add(file);
                                    $refs.cvInput.files = dt.files;
                                    $refs.cvInput.dispatchEvent(new Event('change'));
                                }
                            "
                            :class="{ 'is-drag': isDragging }"
                            x-on:click="$refs.cvInput.click()"
                        >
                            <div wire:loading wire:target="cv">
                                <i class="fa fa-circle-o-notch fa-spin text-primary" style="font-size: 28px;"></i>
                                <div class="mt-2 font-weight-bold">Đang tải file lên...</div>
                            </div>

                            <div wire:loading.remove wire:target="cv">
                                @if($cv && method_exists($cv, 'getClientOriginalName'))
                                    <div class="d-flex align-items-center justify-content-center gap-3">
                                        <div style="width: 48px; height: 48px; border-radius: 12px; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                            <i class="fa fa-file-pdf-o"></i>
                                        </div>
                                        <div class="text-start">
                                            <span class="badge bg-success">✓ Sẵn sàng lưu</span>
                                            <div style="font-weight: 700; color: #0f172a; font-size: 14px;">{{ $cv->getClientOriginalName() }}</div>
                                            <small class="text-muted">Bấm "Lưu tất cả thay đổi" để cập nhật CV vào hồ sơ.</small>
                                        </div>
                                    </div>
                                @else
                                    <div x-show="!selectedCvName">
                                        <div style="width: 52px; height: 52px; border-radius: 50%; background: #fff7ed; color: #f37021; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 10px;">
                                            <i class="fa fa-cloud-upload"></i>
                                        </div>
                                        <div style="font-weight: 800; font-size: 15px; color: #0f172a;">Kéo thả file CV của bạn vào đây</div>
                                        <div style="font-size: 12.5px; color: #64748b; margin-top: 4px;">Hỗ trợ PDF, DOC, DOCX (Dưới 10MB) · Hoặc bấm để chọn file từ máy tính</div>
                                    </div>
                                    <div x-show="selectedCvName" x-cloak>
                                        <div style="font-weight: 700; color: #f37021;" x-text="selectedCvName"></div>
                                        <small class="text-muted">File đã được chọn · Bấm lưu để hoàn tất</small>
                                    </div>
                                @endif
                            </div>

                            <input
                                type="file"
                                id="cv_upload_vanguard"
                                x-ref="cvInput"
                                wire:model="cv"
                                class="d-none"
                                accept="{{ \App\Support\CvUpload::acceptAttribute() }}"
                                x-on:change="selectedCvName = $event.target.files?.[0]?.name || ''"
                            >
                        </div>

                        @if($this->currentCvUrl)
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-4">
                                <a href="{{ $this->currentCvUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="fa fa-eye me-1"></i> Xem CV hiện tại đã tải lên
                                </a>
                            </div>
                        @endif

                        @error('cv') <div class="vg-error text-center mb-3">{{ $message }}</div> @enderror

                        <div class="vg-field mb-4">
                            <label class="vg-label">Ghi chú & Thông tin bổ sung</label>
                            <textarea class="vg-textarea" wire:model.defer="extra" rows="4" placeholder="Hoạt động ngoại khóa, sở thích hoặc các lưu ý khác cho nhà tuyển dụng..."></textarea>
                        </div>

                        <div class="vg-action-footer">
                            <small class="text-muted"><i class="fa fa-shield"></i> Dữ liệu hồ sơ được mã hóa và bảo vệ an toàn trên hệ thống.</small>
                            <button type="submit" class="vg-btn-primary" wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save"><i class="fa fa-check-circle"></i> Lưu tất cả thay đổi</span>
                                <span wire:loading wire:target="save"><i class="fa fa-circle-o-notch fa-spin"></i> Đang lưu hồ sơ...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>
</div>

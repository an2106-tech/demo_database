<div class="candidate-dashboard-wrapper">
    <style>
        /* Scoped Candidate Dashboard Styling - Enterprise Taste */
        .candidate-dashboard-wrapper {
            background-color: #f8fafc;
            min-height: 100vh;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        /* Prevent global heading override on dark cards */
        .candidate-dashboard-wrapper .cd-ai-studio-card h1,
        .candidate-dashboard-wrapper .cd-ai-studio-card h2,
        .candidate-dashboard-wrapper .cd-ai-studio-card h3,
        .candidate-dashboard-wrapper .cd-ai-studio-card h4,
        .candidate-dashboard-wrapper .cd-ai-studio-card h5 {
            color: #ffffff !important;
        }

        .cd-hero-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 28px 32px;
            color: #0f172a;
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.05), 0 2px 6px -2px rgba(15, 23, 42, 0.02);
            margin-bottom: 24px;
        }

        .cd-hero-card::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -40px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(243, 112, 33, 0.08) 0%, rgba(243, 112, 33, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .cd-avatar-pill {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f37021, #ea580c);
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(243, 112, 33, 0.25);
            flex-shrink: 0;
        }

        .cd-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f37021;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 12px;
            border: 1px solid transparent;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none !important;
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.25);
        }

        .cd-btn-primary:hover {
            background: #e05f12;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(243, 112, 33, 0.35);
            color: #ffffff !important;
        }

        .cd-btn-primary:active {
            transform: scale(0.98);
        }

        .cd-btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            color: #334155 !important;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 18px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none !important;
        }

        .cd-btn-secondary:hover {
            background: #f1f5f9;
            color: #0f172a !important;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }

        .cd-btn-secondary:active {
            transform: scale(0.98);
        }

        /* Metric Bento Cards */
        .cd-bento-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1199px) {
            .cd-bento-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575px) {
            .cd-bento-grid {
                grid-template-columns: 1fr;
            }
        }

        .cd-stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px -2px rgba(15, 23, 42, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none !important;
            color: inherit;
            position: relative;
            overflow: hidden;
        }

        .cd-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08);
            border-color: #cbd5e1;
        }

        .cd-stat-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 14px;
        }

        .cd-stat-val {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
            margin-bottom: 4px;
            letter-spacing: -0.02em;
        }

        .cd-stat-lbl {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
        }

        .cd-stat-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Panel Common */
        .cd-panel {
            background: #ffffff;
            border-radius: 18px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 12px -2px rgba(15, 23, 42, 0.03);
            margin-bottom: 24px;
        }

        .cd-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .cd-panel-title {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a !important;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.01em;
        }

        .cd-panel-title i {
            color: #f37021;
        }

        .cd-ai-badge {
            background: linear-gradient(135deg, #eff6ff 0%, #ede9fe 100%);
            color: #4338ca;
            border: 1px solid #c7d2fe;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        /* AI Job Card */
        .cd-job-match-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .cd-job-match-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.06);
            transform: translateY(-2px);
        }

        .cd-match-badge-emerald {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-size: 12.5px;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .cd-match-badge-amber {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
            font-size: 12.5px;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .cd-ai-reason-box {
            background: #f8fafc;
            border-left: 3px solid #f37021;
            padding: 10px 14px;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
            color: #334155;
            line-height: 1.55;
            margin-top: 12px;
            margin-bottom: 14px;
        }

        .cd-tag-match {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #d1fae5;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 6px;
            margin-right: 6px;
            margin-bottom: 6px;
        }

        .cd-tag-missing {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #ffedd5;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 6px;
            margin-right: 6px;
            margin-bottom: 6px;
        }

        /* Recent Activity Table/List */
        .cd-app-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            background: #fcfdfe;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }

        .cd-app-item:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
            transform: translateX(3px);
        }

        .cd-app-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        /* Checklist Widget */
        .cd-checklist-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 6px;
            background: #f8fafc;
            font-size: 13px;
            transition: background 0.2s ease;
            text-decoration: none !important;
            color: inherit;
        }

        .cd-checklist-item:hover {
            background: #f1f5f9;
        }

        .cd-check-done {
            color: #10b981;
            font-size: 15px;
        }

        .cd-check-pending {
            color: #94a3b8;
            font-size: 15px;
        }

        /* Dark AI Studio Card */
        .cd-ai-studio-card {
            background: radial-gradient(circle at top right, #312e81 0%, #0f172a 70%);
            border-radius: 18px;
            padding: 24px;
            color: #ffffff !important;
            position: relative;
            overflow: hidden;
            border: 1px solid #3730a3;
            box-shadow: 0 10px 25px -5px rgba(49, 46, 129, 0.3);
            margin-bottom: 24px;
        }

        .cd-ai-studio-card::after {
            content: '';
            position: absolute;
            bottom: -30px;
            right: -30px;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, rgba(243, 112, 33, 0.2) 0%, transparent 70%);
            border-radius: 50%;
        }

        /* Shimmer Loading Skeleton */
        @keyframes cdShimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .cd-skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: cdShimmer 1.5s infinite;
            border-radius: 8px;
        }
    </style>

    <div class="fpt-breadcrumb-bar">
        <div class="container-fluid px-lg-5">
            <div class="fpt-breadcrumb-inner">
                <ul class="fpt-breadcrumb-trail">
                    <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('candidates.candidate_dashboard') }}">Ứng viên</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li class="current">Bảng điều khiển</li>
                </ul>

                <a href="{{ route('home') }}" class="fpt-back-btn">
                    <i class="fa fa-arrow-left"></i> Về trang chủ
                </a>
            </div>
        </div>
    </div>

    <section class="candidate-dashboard-area section_70 pt-2">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <!-- Left Sidebar -->
                <div class="col-lg-3 col-md-4 dashboard-left-border mb-4 mb-md-0">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <!-- Right Main Content -->
                <div class="col-lg-9 col-md-8">
                    <!-- Welcome Hero Header -->
                    <div class="cd-hero-card">
                        <div class="row align-items-center g-3">
                            <div class="col-lg-8 col-md-7">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="cd-avatar-pill">
                                        @if(Auth::user()?->avatar)
                                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ $userName }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 14px;" onerror="this.onerror=null; this.src='{{ asset('assets/img/avatar_detail.jpg') }}';">
                                        @else
                                            {{ mb_substr($userName ?: 'U', 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                            <h3 class="m-0 fw-bold cd-hero-title" style="color: #0f172a !important; font-size: 22px; letter-spacing: -0.02em;">
                                                {{ $greeting }}, {{ $userName }}!
                                            </h3>
                                            <span class="badge" style="background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                                Ứng viên FPT
                                            </span>
                                        </div>
                                        <p class="m-0 text-muted" style="font-size: 13.5px; line-height: 1.5;">
                                            Hồ sơ của bạn đã hoàn thiện <strong class="text-dark">{{ $profileCompletion }}%</strong> • Hiện có <strong class="text-dark">{{ number_format($publishedJobsCount) }}</strong> việc làm đang mở trên hệ thống.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-5 text-md-end d-flex justify-content-md-end gap-2 flex-wrap">
                                <a href="{{ route('candidates.browse_job') }}" class="cd-btn-primary">
                                    <i class="fa fa-search"></i> Tìm việc ngay
                                </a>
                                <a href="{{ route('candidates.cv_builder') }}" class="cd-btn-secondary">
                                    <i class="fa fa-magic text-warning"></i> Tạo CV AI
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Bento Stat Grid -->
                    <div class="cd-bento-grid">
                        <!-- Card 1: Việc làm đang tuyển -->
                        <a href="{{ route('candidates.browse_job') }}" class="cd-stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="cd-stat-icon-wrap" style="background: #eff6ff; color: #2563eb;">
                                    <i class="fa fa-briefcase"></i>
                                </div>
                                <span class="cd-stat-badge" style="background: #eff6ff; color: #2563eb;">
                                    Khám phá <i class="fa fa-angle-right"></i>
                                </span>
                            </div>
                            <div>
                                <div class="cd-stat-val">{{ number_format($publishedJobsCount) }}</div>
                                <div class="cd-stat-lbl">Việc làm đang tuyển</div>
                            </div>
                        </a>

                        <!-- Card 2: Đã nộp hồ sơ -->
                        <a href="{{ route('candidates.manage_jobs') }}" class="cd-stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="cd-stat-icon-wrap" style="background: #f0fdf4; color: #16a34a;">
                                    <i class="fa fa-paper-plane"></i>
                                </div>
                                <span class="cd-stat-badge" style="background: #f0fdf4; color: #16a34a;">
                                    Theo dõi <i class="fa fa-angle-right"></i>
                                </span>
                            </div>
                            <div>
                                <div class="cd-stat-val">{{ number_format($appliedCount) }}</div>
                                <div class="cd-stat-lbl">Hồ sơ đã ứng tuyển</div>
                            </div>
                        </a>

                        <!-- Card 3: Phỏng vấn & Lời mời -->
                        <a href="{{ route('candidates.manage_jobs') }}" class="cd-stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="cd-stat-icon-wrap" style="background: #fdf4ff; color: #c026d3;">
                                    <i class="fa fa-calendar-check-o"></i>
                                </div>
                                @if($interviewCount > 0 || $offeredCount > 0)
                                    <span class="cd-stat-badge" style="background: #fef2f2; color: #dc2626;">
                                        Mới cập nhật
                                    </span>
                                @else
                                    <span class="cd-stat-badge" style="background: #f8fafc; color: #64748b;">
                                        Tiến độ
                                    </span>
                                @endif
                            </div>
                            <div>
                                <div class="cd-stat-val">{{ number_format($interviewCount + $offeredCount) }}</div>
                                <div class="cd-stat-lbl">Phỏng vấn & Lời mời</div>
                            </div>
                        </a>

                        <!-- Card 4: Hoàn thiện hồ sơ -->
                        <a href="{{ route('candidates.candidate_profile') }}" class="cd-stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="cd-stat-icon-wrap" style="background: rgba(243, 112, 33, 0.1); color: #f37021;">
                                    <i class="fa fa-id-card-o"></i>
                                </div>
                                <span class="cd-stat-badge" style="background: rgba(243, 112, 33, 0.1); color: #f37021;">
                                    {{ $profileCompletion >= 80 ? 'Tốt' : 'Cần tối ưu' }}
                                </span>
                            </div>
                            <div>
                                <div class="cd-stat-val">{{ $profileCompletion }}%</div>
                                <div class="cd-stat-lbl">Hoàn thiện hồ sơ</div>
                            </div>
                        </a>
                    </div>

                    <!-- Main 2-Column Section Split -->
                    <div class="row g-4">
                        <!-- Left Main Column (AI Jobs & Recent Applications) -->
                        <div class="col-lg-8">
                            <!-- Section: AI Job Recommendations -->
                            <div class="cd-panel">
                                <div class="cd-panel-header">
                                    <div>
                                        <h4 class="cd-panel-title">
                                            <i class="fa fa-bolt"></i> Việc làm đề xuất với AI
                                        </h4>
                                        <p class="text-muted m-0 mt-1" style="font-size: 13px;">
                                            Tự động đối soát kinh nghiệm và kỹ năng trong CV với các tin tuyển dụng đang mở.
                                        </p>
                                    </div>
                                    <button 
                                        wire:click="findMatchingJobsWithAi" 
                                        class="cd-btn-primary" 
                                        style="font-size: 13px; padding: 8px 16px; border-radius: 10px; white-space: nowrap;"
                                        wire:loading.attr="disabled"
                                        wire:target="findMatchingJobsWithAi"
                                    >
                                        <span wire:loading.remove wire:target="findMatchingJobsWithAi">
                                            <i class="fa fa-refresh"></i> Quét tìm việc phù hợp
                                        </span>
                                        <span wire:loading wire:target="findMatchingJobsWithAi">
                                            <i class="fa fa-circle-o-notch fa-spin"></i> Đang phân tích...
                                        </span>
                                    </button>
                                </div>

                                <!-- Loading Skeleton State -->
                                <div wire:loading wire:target="findMatchingJobsWithAi" class="w-100 py-3">
                                    <div class="p-3 mb-3 border rounded-3 bg-light">
                                        <div class="d-flex justify-content-between mb-2">
                                            <div class="cd-skeleton" style="width: 50%; height: 22px;"></div>
                                            <div class="cd-skeleton" style="width: 20%; height: 22px;"></div>
                                        </div>
                                        <div class="cd-skeleton mb-2" style="width: 80%; height: 16px;"></div>
                                        <div class="cd-skeleton" style="width: 100%; height: 40px;"></div>
                                    </div>
                                    <div class="p-3 border rounded-3 bg-light">
                                        <div class="d-flex justify-content-between mb-2">
                                            <div class="cd-skeleton" style="width: 45%; height: 22px;"></div>
                                            <div class="cd-skeleton" style="width: 20%; height: 22px;"></div>
                                        </div>
                                        <div class="cd-skeleton mb-2" style="width: 70%; height: 16px;"></div>
                                        <div class="cd-skeleton" style="width: 100%; height: 40px;"></div>
                                    </div>
                                </div>

                                <!-- Actual Jobs List -->
                                <div wire:loading.remove wire:target="findMatchingJobsWithAi">
                                    @if(!empty($aiRecommendedJobs))
                                        <div class="cd-jobs-stream">
                                            @foreach($aiRecommendedJobs as $recJob)
                                                @php
                                                    $matchScore = (int) ($recJob['match_percentage'] ?? 0);
                                                    $isHighMatch = $matchScore >= 80;
                                                @endphp
                                                <div class="cd-job-match-card">
                                                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                                                        <div>
                                                            <h5 class="m-0 fw-bold" style="font-size: 16px; line-height: 1.3;">
                                                                <a href="{{ $recJob['public_url'] ?? route('candidates.browse_job') }}" target="_blank" style="color: #0f172a; text-decoration: none;">
                                                                    {{ $recJob['title'] }}
                                                                </a>
                                                            </h5>
                                                        </div>
                                                        <span class="{{ $isHighMatch ? 'cd-match-badge-emerald' : 'cd-match-badge-amber' }}">
                                                            <i class="fa {{ $isHighMatch ? 'fa-check-circle' : 'fa-star' }}"></i>
                                                            {{ $matchScore }}% Phù hợp
                                                        </span>
                                                    </div>

                                                    @if(!empty($recJob['reason']))
                                                        <div class="cd-ai-reason-box">
                                                            <strong style="color: #0f172a;">Đánh giá của AI:</strong> "{{ $recJob['reason'] }}"
                                                        </div>
                                                    @endif

                                                    @if(!empty($recJob['matched_requirements']) || !empty($recJob['missing_requirements']))
                                                        <div class="d-flex flex-wrap align-items-center mb-3">
                                                            @if(!empty($recJob['matched_requirements']))
                                                                @foreach(array_slice($recJob['matched_requirements'], 0, 3) as $req)
                                                                    <span class="cd-tag-match">
                                                                        <i class="fa fa-check text-success"></i> {{ $req }}
                                                                    </span>
                                                                @endforeach
                                                            @endif

                                                            @if(!empty($recJob['missing_requirements']))
                                                                @foreach(array_slice($recJob['missing_requirements'], 0, 2) as $missing)
                                                                    <span class="cd-tag-missing">
                                                                        <i class="fa fa-info-circle text-warning"></i> Cần bổ sung: {{ $missing }}
                                                                    </span>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                                        <span class="text-muted" style="font-size: 12px;">
                                                            <i class="fa fa-building-o me-1"></i> FPT Careers Ecosystem
                                                        </span>
                                                        <a href="{{ $recJob['public_url'] ?? route('candidates.browse_job') }}" target="_blank" class="btn btn-sm" style="background: #0f172a; color: #fff; font-weight: 600; font-size: 12.5px; border-radius: 8px; padding: 6px 14px;">
                                                            Xem chi tiết & Nộp đơn <i class="fa fa-arrow-right ms-1"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <!-- Empty State for AI Jobs -->
                                        <div class="text-center py-5 px-3" style="background: #f8fafc; border-radius: 14px; border: 1px dashed #cbd5e1;">
                                            <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(243, 112, 33, 0.1); color: #f37021; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 14px;">
                                                <i class="fa fa-magic"></i>
                                            </div>
                                            <h5 class="fw-bold mb-1" style="font-size: 16px; color: #0f172a;">Chưa có đề xuất việc làm</h5>
                                            <p class="text-muted mx-auto mb-3" style="max-width: 420px; font-size: 13px; line-height: 1.5;">
                                                Hệ thống AI sẽ tự động phân tích CV và kỹ năng của bạn để tìm kiếm các vị trí có độ tương thích cao nhất.
                                            </p>
                                            <button wire:click="findMatchingJobsWithAi" class="cd-btn-primary" style="font-size: 13px;">
                                                <i class="fa fa-search-plus"></i> Khám phá việc làm phù hợp ngay
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Section: Recent Applications -->
                            <div class="cd-panel">
                                <div class="cd-panel-header">
                                    <h4 class="cd-panel-title">
                                        <i class="fa fa-history"></i> Lịch sử ứng tuyển gần đây
                                    </h4>
                                    <a href="{{ route('candidates.manage_jobs') }}" style="color: #f37021; font-size: 13.5px; font-weight: 700;">
                                        Xem tất cả <i class="fa fa-angle-right"></i>
                                    </a>
                                </div>

                                @if($recentApplications->isNotEmpty())
                                    <div class="cd-recent-apps-list">
                                        @foreach($recentApplications as $app)
                                            @php
                                                $statusEnum = $app->status instanceof \App\Enums\StatusApplicationEnum 
                                                    ? $app->status 
                                                    : \App\Enums\StatusApplicationEnum::tryFrom($app->status);
                                                $statusLabel = $statusEnum ? $statusEnum->getLabel() : ($app->status ?? 'Đang xét duyệt');
                                                $statusColor = $statusEnum ? $statusEnum->getColor() : 'primary';
                                            @endphp
                                            <div class="cd-app-item">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="cd-app-icon">
                                                        <i class="fa fa-file-text-o"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark" style="font-size: 14.5px;">
                                                            {{ $app->job?->title ?? 'Vị trí đã đóng' }}
                                                        </div>
                                                        <div class="text-muted d-flex align-items-center gap-2 mt-1 flex-wrap" style="font-size: 12px;">
                                                            <span><i class="fa fa-calendar-o me-1"></i> {{ optional($app->applied_at ?? $app->created_at)->diffForHumans() }}</span>
                                                            @if($app->job?->workplace)
                                                                <span>•</span>
                                                                <span><i class="fa fa-map-marker me-1"></i> {{ $app->job->workplace->name }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge" style="font-size: 11.5px; font-weight: 700; padding: 5px 10px; border-radius: 6px; 
                                                        @if($statusColor === 'success') background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;
                                                        @elseif($statusColor === 'warning') background: #fffbeb; color: #b45309; border: 1px solid #fde68a;
                                                        @elseif($statusColor === 'danger') background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;
                                                        @else background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; @endif">
                                                        {{ $statusLabel }}
                                                    </span>
                                                    <a href="{{ route('candidates.manage_jobs') }}" class="btn btn-light btn-sm text-muted" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 12px; padding: 4px 8px;">
                                                        <i class="fa fa-chevron-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4" style="background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
                                        <p class="text-muted mb-2" style="font-size: 13.5px;">Bạn chưa gửi hồ sơ ứng tuyển nào.</p>
                                        <a href="{{ route('candidates.browse_job') }}" class="btn btn-sm" style="background: #f37021; color: white; font-weight: 700; border-radius: 8px; padding: 6px 16px;">
                                            Tìm kiếm cơ hội việc làm
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right Column (Profile Progress & Quick Studio Hub) -->
                        <div class="col-lg-4">
                            <!-- Profile Completion Widget -->
                            <div class="cd-panel">
                                <div class="cd-panel-header">
                                    <h4 class="cd-panel-title" style="font-size: 15px;">
                                        <i class="fa fa-tasks"></i> Tiến độ hồ sơ
                                    </h4>
                                    <span class="fw-bold" style="color: #f37021; font-size: 14px;">{{ $profileCompletion }}%</span>
                                </div>

                                <div class="progress mb-3" style="height: 8px; border-radius: 999px; background: #f1f5f9;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $profileCompletion }}%; background: linear-gradient(90deg, #f37021, #ea580c); border-radius: 999px;" aria-valuenow="{{ $profileCompletion }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>

                                <div class="cd-checklist-group mb-3">
                                    @foreach($checklistItems as $item)
                                        <a href="{{ $item['route'] }}" class="cd-checklist-item">
                                            <span class="d-flex align-items-center gap-2">
                                                @if($item['completed'])
                                                    <i class="fa fa-check-circle cd-check-done"></i>
                                                    <span style="color: #334155;">{{ $item['title'] }}</span>
                                                @else
                                                    <i class="fa fa-circle-o cd-check-pending"></i>
                                                    <span style="color: #64748b;">{{ $item['title'] }}</span>
                                                @endif
                                            </span>
                                            @if(!$item['completed'])
                                                <span class="badge bg-light text-primary" style="font-size: 10.5px; border: 1px solid #cbd5e1;">Thêm +</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>

                                <a href="{{ route('candidates.candidate_profile') }}" class="btn w-100" style="background: #f8fafc; border: 1px solid #e2e8f0; color: #0f172a; font-weight: 700; font-size: 13px; border-radius: 10px; padding: 9px;">
                                    Cập nhật thông tin chi tiết <i class="fa fa-arrow-right ms-1 text-muted"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

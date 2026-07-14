@php
    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Str;

    $branch = $job->branch;
    $department = $job->department;
    $workplace = $job->workplace;
    $skills = $job->skills ?? collect();
    $salary = is_array($job->salary_range) ? $job->salary_range : [];
    $salaryMin = $salary['min'] ?? null;
    $salaryMax = $salary['max'] ?? null;
    $currency = $salary['currency'] ?? 'VND';

    $salaryText = match (true) {
        $salaryMin && $salaryMax => number_format((float) $salaryMin, 0, ',', '.') . ' - ' . number_format((float) $salaryMax, 0, ',', '.') . ' ' . $currency,
        $salaryMin => 'Từ ' . number_format((float) $salaryMin, 0, ',', '.') . ' ' . $currency,
        $salaryMax => 'Đến ' . number_format((float) $salaryMax, 0, ',', '.') . ' ' . $currency,
        default => 'Thỏa thuận',
    };

    $cityLabel = \App\Enums\VietnamProvince::tryFrom((string) ($branch?->city ?? ''))?->label() ?? ($branch?->city ?? 'Chưa cập nhật');
    $branchImageRelativePath = filled($branch?->image ?? null) ? ltrim((string) $branch->image, '/') : null;
    $branchImagePath = $branchImageRelativePath ? storage_path('app/public/' . $branchImageRelativePath) : null;
    $branchImage = $branchImageRelativePath ? asset('storage/' . $branchImageRelativePath) : null;
    $shouldShowBranchImage = false;

    if ($branchImagePath && File::exists($branchImagePath)) {
        $dimensions = @getimagesize($branchImagePath);
        $width = (int) ($dimensions[0] ?? 0);
        $height = (int) ($dimensions[1] ?? 0);
        $fileName = strtolower(pathinfo($branchImagePath, PATHINFO_FILENAME));
        $looksLikePlaceholder = str_contains($fileName, 'placeholder') || preg_match('/^\d+x\d+$/', $fileName) === 1;

        $shouldShowBranchImage = ! $looksLikePlaceholder && $width >= 80 && $height >= 80;
    }

    $statusLabel = $job->status?->getLabel() ?? 'Đang tuyển';
    $applyUrl = route('candidates.apply_job', ['job' => $job->id]);
    $showApplyAction = request()->routeIs('candidates.*') || request()->routeIs('jobs.public');
    $description = trim((string) ($job->description ?? ''));
    $descriptionParagraphs = collect(preg_split("/\\r\\n|\\r|\\n/", $description))
        ->map(fn ($line) => trim((string) $line))
        ->filter()
        ->values();
    $publishedAt = $job->created_at?->format('d/m/Y') ?? 'Đang cập nhật';
    $publishedHuman = $job->created_at?->diffForHumans() ?? 'Mới đăng';
    $companyInitials = Str::upper(Str::substr($branch?->name ?? 'JD', 0, 2));
    $deadlineText = $job->deadline?->format('d/m/Y') ?? 'Không giới hạn';
    $daysUntilDeadline = $job->deadline ? now()->startOfDay()->diffInDays($job->deadline->copy()->startOfDay(), false) : null;
    $deadlineBadge = match (true) {
        is_null($job->deadline) => 'Nhận hồ sơ liên tục',
        $daysUntilDeadline < 0 => 'Đã hết hạn',
        $daysUntilDeadline === 0 => 'Hạn nộp hôm nay',
        $daysUntilDeadline <= 7 => 'Còn ' . $daysUntilDeadline . ' ngày',
        default => 'Đang nhận hồ sơ',
    };
    $skillCount = $skills->count();
    $aiScore = $jobFitAiResult['score'] ?? null;
    $aiScoreLabel = null;
    $aiMatchedRequirements = $jobFitAiResult['matched_requirements'] ?? [];
    $aiMissingRequirements = $jobFitAiResult['missing_requirements'] ?? [];
    $aiReason = $jobFitAiResult['reason'] ?? null;

    if ($aiScore !== null) {
        $aiScoreLabel = $aiScore <= 0
            ? 'Chưa đủ dữ liệu để đánh giá'
            : $aiScore . '% phù hợp';
    }
@endphp

<div>
    <style>
        .job-detail-page {
            --jd-bg: #f7f4ef;
            --jd-surface: #ffffff;
            --jd-surface-soft: #fbf5ee;
            --jd-line: rgba(111, 77, 48, 0.14);
            --jd-line-strong: rgba(196, 107, 45, 0.22);
            --jd-ink: #221810;
            --jd-text: #54453b;
            --jd-muted: #8f7968;
            --jd-primary: #c36b2d;
            --jd-primary-dark: #8f4618;
            --jd-shadow: 0 26px 60px rgba(86, 54, 29, 0.1);
            background:
                radial-gradient(circle at 0% 8%, rgba(228, 174, 125, 0.22), transparent 22%),
                radial-gradient(circle at 100% 0%, rgba(255, 242, 226, 0.85), transparent 30%),
                linear-gradient(180deg, #fdfaf6 0%, var(--jd-bg) 100%);
            position: relative;
        }

        .job-detail-page::before {
            background: linear-gradient(180deg, rgba(195, 107, 45, 0.14), rgba(195, 107, 45, 0));
            border-radius: 999px;
            content: "";
            filter: blur(18px);
            height: 280px;
            left: -120px;
            position: absolute;
            top: 180px;
            width: 280px;
            z-index: 0;
        }

        .jd-wrap {
            position: relative;
            z-index: 1;
        }

        .jd-top-card,
        .jd-block,
        .jd-sidebar {
            background: var(--jd-surface);
            border: 1px solid var(--jd-line);
            border-radius: 28px;
            box-shadow: var(--jd-shadow);
        }

        .jd-top-card {
            isolation: isolate;
            overflow: hidden;
            position: relative;
        }

        .jd-top-banner {
            background: #ffffff;
            min-height: 184px;
            position: relative;
        }

        .jd-top-banner::before {
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.5), transparent);
            content: "";
            inset: 0;
            position: absolute;
        }

        .jd-top-main {
            display: grid;
            gap: 28px;
            grid-template-columns: minmax(0, 1.45fr) minmax(290px, 0.72fr);
            margin-top: -58px;
            padding: 0 34px 34px;
            position: relative;
        }

        .jd-profile {
            align-items: flex-start;
            display: flex;
            gap: 22px;
            margin-bottom: 26px;
        }

        .jd-logo {
            align-items: center;
            background: #ffffff;
            border: 6px solid rgba(255, 255, 255, 0.98);
            border-radius: 30px;
            box-shadow: 0 22px 36px rgba(148, 80, 29, 0.2);
            color: #fff;
            display: inline-flex;
            flex: 0 0 112px;
            font-size: 34px;
            font-weight: 800;
            height: 112px;
            justify-content: center;
            letter-spacing: -0.04em;
            overflow: hidden;
            position: relative;
            transform: translateY(-4px);
            width: 112px;
        }

        .jd-logo--fallback {
            font-family: "Trebuchet MS", "Segoe UI", sans-serif;
            text-shadow: 0 8px 18px rgba(0, 0, 0, 0.14);
        }

        .jd-logo img {
            display: block;
            height: calc(100% - 16px);
            object-fit: contain;
            object-position: center;
            width: calc(100% - 16px);
        }

        .jd-top-copy {
            min-width: 0;
            padding-top: 12px;
        }

        .jd-kicker {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }

        .jd-status,
        .jd-posted {
            border-radius: 999px;
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            padding: 8px 12px;
        }

        .jd-status {
            background: var(--jd-surface-soft);
            border: 1px solid rgba(143, 70, 24, 0.12);
            color: var(--jd-primary-dark);
            text-transform: uppercase;
        }

        .jd-posted {
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(111, 77, 48, 0.1);
            color: var(--jd-muted);
            letter-spacing: 0;
        }

        .jd-title {
            color: var(--jd-ink);
            font-size: 42px;
            font-weight: 800;
            letter-spacing: -0.045em;
            line-height: 1.08;
            margin: 0 0 14px;
        }

        .jd-company {
            color: var(--jd-text);
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 10px;
        }

        .jd-subcopy {
            color: var(--jd-muted);
            font-size: 15px;
            line-height: 1.8;
            margin: 0 0 18px;
            max-width: 760px;
        }

        .jd-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .jd-meta-item {
            align-items: center;
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(111, 77, 48, 0.1);
            border-radius: 999px;
            color: var(--jd-text);
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            gap: 8px;
            padding: 11px 15px;
        }

        .jd-meta-item i {
            color: var(--jd-primary);
        }
        .jd-highlight-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 24px;
        }

        .jd-highlight {
            background: #ffffff;
            border: 1px solid rgba(111, 77, 48, 0.1);
            border-radius: 22px;
            padding: 18px;
        }

        .jd-highlight span {
            color: var(--jd-muted);
            display: block;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .jd-highlight strong,
        .jd-highlight div {
            color: var(--jd-ink);
            font-size: 16px;
            line-height: 1.6;
        }

        .jd-top-actions {
            align-self: stretch;
            background: #ffffff;
            border: 1px solid rgba(111, 77, 48, 0.12);
            border-radius: 26px;
            display: flex;
            flex-direction: column;
            padding: 24px;
        }

        .jd-top-eyebrow {
            color: var(--jd-primary-dark);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.12em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .jd-top-actions h3 {
            color: var(--jd-ink);
            font-size: 26px;
            line-height: 1.18;
            margin: 0 0 8px;
        }

        .jd-top-actions p {
            color: var(--jd-muted);
            font-size: 14px;
            line-height: 1.7;
            margin: 0 0 18px;
        }

        .jd-apply-btn,
        .jd-secondary-btn {
            align-items: center;
            border-radius: 16px;
            display: inline-flex;
            font-size: 14px;
            font-weight: 800;
            gap: 8px;
            justify-content: center;
            min-height: 54px;
            padding: 0 18px;
            text-decoration: none;
            transition: .22s ease;
            width: 100%;
        }

        .jd-apply-btn {
            background: #f37021;
            box-shadow: 0 8px 20px rgba(243, 112, 33, 0.25);
            color: #fff !important;
            border: none;
        }

        .jd-apply-btn:hover,
        .jd-apply-btn:focus {
            background: #e56314;
            color: #fff !important;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .jd-secondary-btn {
            background: #f1f5f9;
            border: none;
            color: #475569 !important;
            margin-top: 12px;
        }

        .jd-secondary-btn:hover,
        .jd-secondary-btn:focus {
            background: #e2e8f0;
            color: #334155 !important;
            text-decoration: none;
        }

        .jd-ai-btn {
            background: linear-gradient(135deg, #f37021 0%, #d95e11 100%);
            border: none;
            box-shadow: 0 12px 24px rgba(243, 112, 33, 0.22);
            color: #fff !important;
            margin-top: 12px;
        }

        .jd-ai-btn:hover,
        .jd-ai-btn:focus {
            color: #fff !important;
            transform: translateY(-2px);
        }

        .jd-ai-section {
            background: #fff;
            border: 1px solid rgba(243, 112, 33, 0.16);
            border-radius: 28px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.06);
            margin-top: 24px;
            padding: 24px;
        }

        .jd-ai-section__head {
            align-items: flex-start;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .jd-ai-section__title {
            color: var(--jd-ink);
            font-size: 24px;
            font-weight: 900;
            margin: 0 0 6px;
        }

        .jd-ai-section__score {
            align-items: center;
            background: #fff7ed;
            border: 1px solid rgba(243, 112, 33, 0.18);
            border-radius: 18px;
            color: #c2410c;
            display: inline-flex;
            font-size: 22px;
            font-weight: 900;
            line-height: 1.25;
            min-height: 60px;
            padding: 0 18px;
            white-space: nowrap;
        }

        .jd-ai-section__score--soft {
            background: #fffaf4;
            color: #9a3412;
            font-size: 17px;
            font-weight: 800;
            text-align: center;
            white-space: normal;
        }

        .jd-ai-section__reason {
            background: #fff7ed;
            border: 1px solid rgba(243, 112, 33, 0.16);
            border-radius: 18px;
            color: #9a3412;
            font-size: 14px;
            line-height: 1.8;
            padding: 16px 18px;
        }

        .jd-ai-section__grid {
            display: grid;
            gap: 16px;
            grid-template-columns: 1fr 1fr;
            margin-top: 16px;
        }

        .jd-ai-section__box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 18px;
        }

        .jd-ai-section__box span {
            color: #64748b;
            display: block;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .jd-ai-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .jd-ai-chip {
            align-items: center;
            background: #fff;
            border: 1px solid #dbe4ee;
            border-radius: 999px;
            color: #334155;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.4;
            padding: 8px 12px;
        }

        .jd-layout {
            display: grid;
            gap: 24px;
            grid-template-columns: minmax(0, 1.55fr) minmax(300px, 0.85fr);
            margin-top: 24px;
        }

        .jd-main {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .jd-block {
            padding: 26px;
        }

        .jd-block-head {
            align-items: center;
            display: flex;
            gap: 12px;
            margin-bottom: 18px;
        }

        .jd-block-icon {
            align-items: center;
            background: linear-gradient(135deg, rgba(195, 107, 45, 0.12), rgba(222, 160, 106, 0.22));
            border-radius: 16px;
            color: var(--jd-primary-dark);
            display: inline-flex;
            flex: 0 0 42px;
            font-size: 17px;
            height: 42px;
            justify-content: center;
            width: 42px;
        }

        .jd-block-head h3 {
            color: var(--jd-ink);
            font-size: 22px;
            margin: 0 0 4px;
        }

        .jd-block-head p {
            color: var(--jd-muted);
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
        }

        .jd-desc {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .jd-desc p {
            color: var(--jd-text);
            font-size: 15.5px;
            line-height: 1.8;
            margin: 0;
        }
        .jd-grid {
            display: grid;
            gap: 24px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .jd-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .jd-info span {
            color: var(--jd-muted);
            display: flex;
            align-items: center;
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .jd-info span::before {
            content: "";
            display: inline-block;
            width: 4px;
            height: 12px;
            background: var(--jd-primary);
            border-radius: 4px;
            margin-right: 8px;
        }

        .jd-info strong,
        .jd-info div {
            color: var(--jd-ink);
            font-size: 15.5px;
            font-weight: 500;
            line-height: 1.6;
        }

        .jd-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .jd-skill {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
            transition: all 0.2s ease;
        }

        .jd-skill:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .jd-empty {
            color: var(--jd-muted);
            font-size: 14px;
            line-height: 1.8;
            margin: 0;
        }

        .jd-sidebar {
            padding: 24px;
            position: sticky;
            top: 24px;
        }

        .jd-side-head {
            border-bottom: 1px solid rgba(111, 77, 48, 0.1);
            margin-bottom: 18px;
            padding-bottom: 16px;
        }

        .jd-side-head h3 {
            color: var(--jd-ink);
            font-size: 21px;
            margin: 0 0 6px;
        }

        .jd-side-head p {
            color: var(--jd-muted);
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
        }

        .jd-overview {
            display: grid;
            gap: 12px;
        }

        .jd-overview-item {
            align-items: flex-start;
            display: flex;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px dashed rgba(111, 77, 48, 0.15);
        }
        
        .jd-overview-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .jd-overview-icon {
            align-items: center;
            background: rgba(195, 107, 45, 0.08);
            border-radius: 12px;
            color: var(--jd-primary);
            display: inline-flex;
            flex: 0 0 40px;
            font-size: 16px;
            height: 40px;
            justify-content: center;
            width: 40px;
        }

        .jd-overview-item span {
            color: var(--jd-muted);
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .jd-overview-item strong,
        .jd-overview-item div {
            color: var(--jd-ink);
            font-size: 15px;
            line-height: 1.6;
            font-weight: 500;
        }

        @media (max-width: 1199px) {
            .jd-top-main,
            .jd-layout {
                grid-template-columns: 1fr;
            }

            .jd-highlight-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .jd-sidebar {
                position: static;
            }
        }

        @media (max-width: 767px) {
            .jd-top-main,
            .jd-block,
            .jd-sidebar {
                padding-left: 16px;
                padding-right: 16px;
            }

            .jd-top-main {
                gap: 20px;
                margin-top: -40px;
                padding-bottom: 20px;
            }

            .jd-top-banner {
                min-height: 152px;
            }

            .jd-profile {
                align-items: flex-start;
                flex-direction: column;
            }

            .jd-logo {
                flex-basis: 92px;
                font-size: 28px;
                height: 92px;
                transform: none;
                width: 92px;
            }

            .jd-title {
                font-size: 30px;
            }

            .jd-highlight-grid,
            .jd-grid {
                grid-template-columns: 1fr;
            }

            .jd-ai-columns {
                grid-template-columns: 1fr;
            }

            .jd-ai-section__head {
                flex-direction: column;
            }

            .jd-ai-section__grid {
                grid-template-columns: 1fr;
            }

            .jd-ai-chip {
                white-space: normal;
            }
        }
    </style>

    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Chi tiết tin tuyển dụng</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="breadcromb-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box-pagin">
                            <ul>
                                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                                <li><a href="{{ route('candidates.browse_job') }}">Việc làm</a></li>
                                <li class="active-breadcromb"><a href="#">Chi tiết công việc</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section_70 job-detail-page">
        <div class="container jd-wrap">
            <div class="jd-top-card">
                <div class="jd-top-banner"></div>

                <div class="jd-top-main">
                    <div>
                        <div class="jd-profile">
                            <div class="jd-logo{{ $shouldShowBranchImage ? '' : ' jd-logo--fallback' }}">
                                @if ($shouldShowBranchImage)
                                    <img src="{{ $branchImage }}" alt="{{ $branch?->name ?? $job->title }}">
                                @else
                                    {{ $companyInitials }}
                                @endif
                            </div>

                            <div class="jd-top-copy">
                                <div class="jd-kicker">
                                    <div class="jd-status">{{ $statusLabel }}</div>
                                    <div class="jd-posted">{{ $publishedAt }} · {{ $publishedHuman }}</div>
                                </div>

                                <h1 class="jd-title">{{ $job->title }}</h1>
                                <p class="jd-company">{{ $branch?->name ?? 'Chưa cập nhật chi nhánh' }}</p>
                                <div class="jd-meta">
                                    <div class="jd-meta-item" style="background-color: rgba(243, 112, 33, 0.1); color: #f37021; border: 1px solid rgba(243, 112, 33, 0.2); font-weight: 700;"><i class="fa fa-money" style="color: #f37021;"></i>{{ $salaryText }}</div>
                                    <div class="jd-meta-item"><i class="fa fa-map-marker"></i>{{ $cityLabel }}</div>
                                    <div class="jd-meta-item"><i class="fa fa-building-o"></i>{{ $department?->name ?? 'Chưa cập nhật phòng ban' }}</div>
                                    <div class="jd-meta-item"><i class="fa fa-clock-o"></i>{{ $deadlineText }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="jd-highlight-grid">
                            <div class="jd-highlight">
                                <span>Hạn ứng tuyển</span>
                                <strong>{{ $deadlineBadge }}</strong>
                                <div>{{ $deadlineText }}</div>
                            </div>
                            <div class="jd-highlight">
                                <span>Kỹ năng</span>
                                <strong>{{ $skillCount }} nhóm</strong>
                                <div>{{ $skillCount > 0 ? 'Đã có kỹ năng liên quan để đối chiếu nhanh' : 'Nhà tuyển dụng chưa khai báo cụ thể' }}</div>
                            </div>
                            <div class="jd-highlight">
                                <span>Nơi làm việc</span>
                                <strong>{{ $workplace?->name ?? 'Đang cập nhật' }}</strong>
                                <div>{{ $cityLabel }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="jd-top-actions">
                        <div class="jd-top-eyebrow">Quick Apply</div>
                        <h3>Ứng tuyển ngay nếu vị trí này phù hợp với bạn</h3>
                        <p>Hoàn tất hồ sơ sớm để nhà tuyển dụng dễ xem và phản hồi nhanh hơn trong đợt tuyển dụng hiện tại.</p>

                        @if ($showApplyAction)
                            <a href="{{ $applyUrl }}" class="jd-apply-btn">
                                <i class="fa fa-paper-plane-o"></i>
                                Ứng tuyển ngay
                            </a>
                        @else
                            <a href="{{ route('jobs.public', ['slug' => $job->slug]) }}" class="jd-apply-btn">
                                <i class="fa fa-external-link"></i>
                                Xem giao diện ứng viên
                            </a>
                        @endif

                        @if ($hasCandidateAccess)
                            @if ($hasCv)
                                <button
                                    type="button"
                                    wire:click="checkJobFitWithAi"
                                    wire:loading.attr="disabled"
                                    wire:target="checkJobFitWithAi"
                                    class="jd-apply-btn jd-ai-btn"
                                >
                                    <span wire:loading.remove wire:target="checkJobFitWithAi">
                                        <i class="fa fa-magic"></i>
                                        AI kiểm tra phù hợp
                                    </span>
                                    <span wire:loading wire:target="checkJobFitWithAi">
                                        <i class="fa fa-circle-o-notch fa-spin"></i>
                                        Đang kiểm tra...
                                    </span>
                                </button>
                            @else
                                <a href="{{ route('candidates.candidate_profile') }}" class="jd-secondary-btn">
                                    <i class="fa fa-upload"></i>
                                    Tải CV lên để dùng AI
                                </a>
                            @endif

                            <a href="{{ route('candidates.candidate_profile') }}" class="jd-secondary-btn">
                                <i class="fa fa-user-edit"></i>
                                Bổ sung hồ sơ
                            </a>
                        @else
                            <a href="{{ route('candidates.login') }}" class="jd-secondary-btn">
                                <i class="fa fa-sign-in"></i>
                                Đăng nhập để kiểm tra
                            </a>
                        @endif

                        <a href="{{ route('candidates.browse_job') }}" class="jd-secondary-btn">
                            <i class="fa fa-th-large"></i>
                            Xem thêm việc làm
                        </a>
                    </div>
                </div>
            </div>

            @if (is_array($jobFitAiResult))
                <section class="jd-ai-section">
                    <div class="jd-ai-section__head">
                        <div>
                            <h3 class="jd-ai-section__title">Kết quả AI</h3>
                        </div>
                        @if ($aiScoreLabel !== null)
                            <div class="jd-ai-section__score {{ $aiScore <= 0 ? 'jd-ai-section__score--soft' : '' }}">
                                {{ $aiScoreLabel }}
                            </div>
                        @endif
                    </div>

                    @if (filled($aiReason))
                        <div class="jd-ai-section__reason">
                            {{ $aiReason }}
                        </div>
                    @endif

                    <div class="jd-ai-section__grid">
                        <div class="jd-ai-section__box">
                            <span>Điểm phù hợp</span>
                            @if (!empty($aiMatchedRequirements))
                                <div class="jd-ai-chip-list">
                                    @foreach ($aiMatchedRequirements as $item)
                                        <div class="jd-ai-chip">{{ $item }}</div>
                                    @endforeach
                                </div>
                            @else
                                <div class="jd-empty mb-0">Chưa ghi nhận điểm phù hợp rõ ràng.</div>
                            @endif
                        </div>

                        <div class="jd-ai-section__box">
                            <span>Cần bổ sung / xác minh</span>
                            @if (!empty($aiMissingRequirements))
                                <div class="jd-ai-chip-list">
                                    @foreach ($aiMissingRequirements as $item)
                                        <div class="jd-ai-chip">{{ $item }}</div>
                                    @endforeach
                                </div>
                            @else
                                <div class="jd-empty mb-0">Chưa thấy khoảng trống lớn theo dữ liệu hiện tại.</div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            <div class="jd-layout">
                <div class="jd-main">
                    <div class="jd-block">
                        <div class="jd-block-head">
                            <div class="jd-block-icon"><i class="fa fa-file-text-o"></i></div>
                            <div>
                                <h3>Mô tả công việc</h3>
                            </div>
                        </div>

                        <div class="jd-desc">
                            @forelse ($descriptionParagraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @empty
                                <p>Nội dung mô tả công việc đang được cập nhật. Bạn vẫn có thể ứng tuyển để nhà tuyển dụng liên hệ và cung cấp thêm thông tin chi tiết.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="jd-block">
                        <div class="jd-block-head">
                            <div class="jd-block-icon"><i class="fa fa-briefcase"></i></div>
                            <div>
                                <h3>Thông tin chung</h3>
                            </div>
                        </div>

                        <div class="jd-grid">
                            <div class="jd-info">
                                <span>Mức lương</span>
                                <strong>{{ $salaryText }}</strong>
                            </div>
                            <div class="jd-info">
                                <span>Trạng thái</span>
                                <div>{{ $statusLabel }}</div>
                            </div>
                            <div class="jd-info">
                                <span>Hạn nộp hồ sơ</span>
                                <div>{{ $deadlineText }}</div>
                            </div>
                            <div class="jd-info">
                                <span>Ngày đăng</span>
                                <div>{{ $publishedAt }} · {{ $publishedHuman }}</div>
                            </div>
                            <div class="jd-info">
                                <span>Chi nhánh</span>
                                <div>{{ $branch?->name ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="jd-info">
                                <span>Phòng ban</span>
                                <div>{{ $department?->name ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="jd-info">
                                <span>Nơi làm việc</span>
                                <div>{{ $workplace?->name ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="jd-info">
                                <span>Khu vực</span>
                                <div>{{ $cityLabel }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="jd-block">
                        <div class="jd-block-head">
                            <div class="jd-block-icon"><i class="fa fa-th-large"></i></div>
                            <div>
                                <h3>Danh mục nghề nghiệp</h3>
                            </div>
                        </div>

                        @if ($job->categories->isNotEmpty())
                            <div class="jd-skills">
                                @foreach ($job->categories as $category)
                                    <div class="jd-skill">{{ $category->name }}</div>
                                @endforeach
                            </div>
                        @else
                            <p class="jd-empty">Tin đăng này chưa được phân loại danh mục cụ thể.</p>
                        @endif
                    </div>

                    <div class="jd-block">
                        <div class="jd-block-head">
                            <div class="jd-block-icon"><i class="fa fa-code"></i></div>
                            <div>
                                <h3>Kỹ năng liên quan</h3>
                            </div>
                        </div>

                        @if ($skills->isNotEmpty())
                            <div class="jd-skills">
                                @foreach ($skills as $skill)
                                    <div class="jd-skill">{{ $skill->name }}</div>
                                @endforeach
                            </div>
                        @else
                            <p class="jd-empty">Tin đăng này chưa có danh sách kỹ năng cụ thể. Bạn có thể dựa vào mô tả công việc và phòng ban để đánh giá mức độ phù hợp.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="jd-sidebar">
                        <div class="jd-side-head">
                            <h3>Tổng quan công việc</h3>
                        </div>

                        <div class="jd-overview">
                            <div class="jd-overview-item">
                                <div class="jd-overview-icon"><i class="fa fa-money"></i></div>
                                <div>
                                    <span>Mức lương</span>
                                    <strong>{{ $salaryText }}</strong>
                                </div>
                            </div>

                            <div class="jd-overview-item">
                                <div class="jd-overview-icon"><i class="fa fa-map-marker"></i></div>
                                <div>
                                    <span>Địa điểm</span>
                                    <div>{{ $cityLabel }}</div>
                                </div>
                            </div>

                            <div class="jd-overview-item">
                                <div class="jd-overview-icon"><i class="fa fa-building-o"></i></div>
                                <div>
                                    <span>Đơn vị</span>
                                    <div>{{ $branch?->name ?? 'Chưa cập nhật' }}</div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

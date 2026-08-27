@php
    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Str;

    $branch = $job->branch;
    $department = $job->department;
    $workplace = $job->workplace;
    $skills = $job->skills ?? collect();
    $salaryText = $job->formatted_salary;

    $cityLabel = \App\Enums\VietnamProvince::tryFrom((string) ($branch?->city ?? ''))?->label() ?? ($branch?->city ?? 'Toàn quốc');
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

    $statusLabel = $job->status?->getLabel() ?? 'Đang tuyển dụng';
    $applyUrl = route('candidates.apply_job', ['job' => $job->id]);
    $description = trim((string) ($job->description ?? ''));
    $publishedAt = $job->created_at?->format('d/m/Y') ?? 'Đang cập nhật';
    $publishedHuman = $job->created_at?->diffForHumans() ?? 'Mới đăng';
    $companyInitials = Str::upper(Str::substr($branch?->name ?? 'FPT', 0, 2));
    $deadlineText = $job->deadline?->format('d/m/Y') ?? 'Không giới hạn';
    $daysUntilDeadline = $job->deadline ? now()->startOfDay()->diffInDays($job->deadline->copy()->startOfDay(), false) : null;
    $deadlineBadge = match (true) {
        is_null($job->deadline) => 'Tuyển liên tục',
        $daysUntilDeadline < 0 => 'Đã hết hạn',
        $daysUntilDeadline === 0 => 'Hết hạn hôm nay',
        $daysUntilDeadline <= 7 => 'Còn ' . $daysUntilDeadline . ' ngày',
        default => 'Hạn nộp: ' . $deadlineText,
    };
    $skillCount = $skills->count();
    $aiScore = $jobFitAiResult['score'] ?? null;
    $aiScoreLabel = null;
    $aiScoreClass = '';
    $aiMatchedRequirements = $jobFitAiResult['matched_requirements'] ?? [];
    $aiMissingRequirements = $jobFitAiResult['missing_requirements'] ?? [];
    $aiReason = $jobFitAiResult['reason'] ?? null;
    $aiAdvice = $jobFitAiResult['advice'] ?? null;

    if ($aiScore !== null) {
        if ($aiScore <= 0) {
            $aiScoreLabel = 'Chưa đủ dữ liệu';
            $aiScoreClass = 'jd-score-soft';
        } elseif ($aiScore < 35) {
            $aiScoreLabel = $aiScore . '% — Mức phù hợp thấp';
            $aiScoreClass = 'jd-score-low';
        } elseif ($aiScore < 65) {
            $aiScoreLabel = $aiScore . '% — Phù hợp một phần';
            $aiScoreClass = 'jd-score-mid';
        } elseif ($aiScore < 85) {
            $aiScoreLabel = $aiScore . '% — Phù hợp';
            $aiScoreClass = 'jd-score-good';
        } else {
            $aiScoreLabel = $aiScore . '% — Rất phù hợp';
            $aiScoreClass = 'jd-score-high';
        }
    }
@endphp

<div class="fpt-job-page">
    <style>
        /* === High-End Clean Design System (FPT Orange & Pure White) === */
        :root {
            --fpt-orange: #f37021;
            --fpt-orange-hover: #e05e0f;
            --fpt-orange-light: #fff7ed;
            --fpt-orange-border: #fed7aa;
            --fpt-orange-glow: rgba(243, 112, 33, 0.2);
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
            --fpt-shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
            --fpt-shadow-card: 0 10px 30px -10px rgba(15, 23, 42, 0.05), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            --fpt-shadow-hover: 0 20px 40px -15px rgba(243, 112, 33, 0.15), 0 8px 20px -4px rgba(15, 23, 42, 0.04);
            --fpt-ease: cubic-bezier(0.16, 1, 0.3, 1);
        }

        .fpt-job-page {
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
            max-width: 320px;
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

        /* === Hero Job Header Card === */
        .fpt-hero-card {
            background: var(--fpt-white);
            border: 1px solid var(--fpt-slate-200);
            border-radius: var(--fpt-radius-lg);
            box-shadow: var(--fpt-shadow-card);
            margin-bottom: 28px;
            padding: 32px 36px;
            position: relative;
            overflow: hidden;
        }

        .fpt-hero-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f37021, #fb923c);
        }

        .fpt-hero-layout {
            align-items: flex-start;
            display: flex;
            gap: 28px;
        }

        .fpt-company-logo {
            align-items: center;
            background: #ffffff;
            border: 1px solid var(--fpt-slate-200);
            border-radius: 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-shrink: 0;
            height: 96px;
            justify-content: center;
            overflow: hidden;
            padding: 10px;
            width: 96px;
        }

        .fpt-company-logo img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        .fpt-company-logo-fallback {
            background: linear-gradient(135deg, #f37021, #ea580c);
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.02em;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .fpt-hero-content {
            flex-grow: 1;
            min-width: 0;
        }

        .fpt-hero-top-pills {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }

        .fpt-badge-live {
            align-items: center;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 9999px;
            color: #059669;
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            gap: 6px;
            padding: 4px 12px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .fpt-badge-live .pulse-dot {
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            height: 7px;
            width: 7px;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
            animation: pulseAnim 2s infinite;
        }

        @keyframes pulseAnim {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .fpt-badge-time {
            align-items: center;
            color: var(--fpt-slate-500);
            display: inline-flex;
            font-size: 12.5px;
            font-weight: 500;
            gap: 5px;
        }

        .fpt-badge-unit {
            align-items: center;
            background: var(--fpt-slate-100);
            border: 1px solid var(--fpt-slate-200);
            border-radius: 9999px;
            color: var(--fpt-slate-600);
            display: inline-flex;
            font-size: 12px;
            font-weight: 600;
            gap: 5px;
            padding: 4px 12px;
        }

        .fpt-job-title {
            color: var(--fpt-dark);
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.035em;
            line-height: 1.25;
            margin: 0 0 10px;
        }

        .fpt-company-line {
            align-items: center;
            color: var(--fpt-slate-600);
            display: flex;
            flex-wrap: wrap;
            font-size: 15px;
            font-weight: 600;
            gap: 8px;
            margin-bottom: 20px;
        }

        .fpt-company-line .verified-icon {
            color: #3b82f6;
            font-size: 14px;
        }

        .fpt-key-metrics {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .fpt-metric-pill {
            align-items: center;
            background: var(--fpt-slate-50);
            border: 1px solid var(--fpt-slate-200);
            border-radius: var(--fpt-radius-sm);
            color: var(--fpt-slate-800);
            display: inline-flex;
            font-size: 13.5px;
            font-weight: 600;
            gap: 8px;
            padding: 8px 14px;
            transition: all 0.2s ease;
        }

        .fpt-metric-pill i {
            color: var(--fpt-slate-500);
            font-size: 14px;
        }

        .fpt-metric-pill--salary {
            background: var(--fpt-orange-light);
            border-color: var(--fpt-orange-border);
            color: #c2410c;
            font-weight: 800;
            font-size: 14.5px;
        }

        .fpt-metric-pill--salary i {
            color: var(--fpt-orange);
        }

        /* === 2-Column Content Layout === */
        .fpt-main-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.85fr) minmax(320px, 1fr);
            gap: 28px;
            align-items: start;
        }

        /* === Left Content Cards === */
        .fpt-content-stack {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .fpt-card {
            background: var(--fpt-white);
            border: 1px solid var(--fpt-slate-200);
            border-radius: var(--fpt-radius-lg);
            box-shadow: var(--fpt-shadow-card);
            overflow: hidden;
            padding: 28px 32px;
        }

        .fpt-card-header {
            align-items: center;
            border-bottom: 1px solid var(--fpt-slate-100);
            display: flex;
            gap: 12px;
            margin-bottom: 22px;
            padding-bottom: 16px;
        }

        .fpt-card-icon {
            align-items: center;
            background: var(--fpt-orange-light);
            border: 1px solid var(--fpt-orange-border);
            border-radius: 12px;
            color: var(--fpt-orange);
            display: inline-flex;
            flex-shrink: 0;
            font-size: 16px;
            height: 38px;
            justify-content: center;
            width: 38px;
        }

        .fpt-card-title {
            color: var(--fpt-dark);
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0;
        }

        /* === Rich Job Description Body === */
        .fpt-job-body {
            color: var(--fpt-slate-800);
            font-size: 15px;
            line-height: 1.75;
        }

        .fpt-job-body h2, .fpt-job-body h3, .fpt-job-body h4 {
            color: var(--fpt-dark);
            font-size: 17px;
            font-weight: 700;
            margin: 22px 0 10px;
        }

        .fpt-job-body p {
            margin-bottom: 14px;
        }

        .fpt-job-body ul, .fpt-job-body ol {
            margin: 10px 0 18px 20px;
            padding: 0;
        }

        .fpt-job-body li {
            margin-bottom: 8px;
            padding-left: 4px;
        }

        /* === Skills Chips === */
        .fpt-skill-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .fpt-skill-pill {
            align-items: center;
            background: var(--fpt-slate-50);
            border: 1px solid var(--fpt-slate-200);
            border-radius: 9999px;
            color: var(--fpt-slate-800);
            display: inline-flex;
            font-size: 13.5px;
            font-weight: 600;
            gap: 6px;
            padding: 7px 16px;
            transition: all 0.2s var(--fpt-ease);
        }

        .fpt-skill-pill:hover {
            background: var(--fpt-orange-light);
            border-color: var(--fpt-orange-border);
            color: var(--fpt-orange);
            transform: translateY(-1px);
        }

        /* === AI Fit Match Scorecard === */
        .fpt-ai-card {
            background: #ffffff;
            border: 1px solid #fed7aa;
            border-radius: var(--fpt-radius-lg);
            box-shadow: 0 12px 32px -8px rgba(243, 112, 33, 0.12), 0 4px 12px -2px rgba(15, 23, 42, 0.03);
            overflow: hidden;
            padding: 28px 32px;
            position: relative;
        }

        .fpt-ai-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #f37021, #fb923c, #3b82f6);
        }

        .fpt-ai-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
        }

        .fpt-ai-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .fpt-ai-badge-icon {
            background: linear-gradient(135deg, #f37021, #ea580c);
            border-radius: 12px;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.3);
        }

        .fpt-ai-score-badge {
            align-items: center;
            border-radius: 9999px;
            display: inline-flex;
            font-size: 14.5px;
            font-weight: 800;
            letter-spacing: -0.01em;
            padding: 8px 18px;
        }

        .jd-score-high {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }

        .jd-score-good {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
        }

        .jd-score-mid {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #b45309;
        }

        .jd-score-low {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .jd-score-soft {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }

        .fpt-ai-progress-track {
            background: #f1f5f9;
            border-radius: 9999px;
            height: 8px;
            margin-bottom: 18px;
            overflow: hidden;
            width: 100%;
        }

        .fpt-ai-progress-fill {
            border-radius: 9999px;
            height: 100%;
            transition: width 0.8s var(--fpt-ease);
        }

        .fpt-ai-progress-fill.jd-score-high { background: #2563eb; }
        .fpt-ai-progress-fill.jd-score-good { background: #10b981; }
        .fpt-ai-progress-fill.jd-score-mid { background: #f59e0b; }
        .fpt-ai-progress-fill.jd-score-low { background: #ef4444; }

        .fpt-ai-reason-box {
            background: #fffaf5;
            border: 1px solid #fed7aa;
            border-left: 4px solid var(--fpt-orange);
            border-radius: var(--fpt-radius-sm);
            color: #431407;
            font-size: 14.5px;
            line-height: 1.65;
            margin-bottom: 20px;
            padding: 16px 20px;
        }

        .fpt-ai-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .fpt-ai-box {
            background: var(--fpt-slate-50);
            border: 1px solid var(--fpt-slate-200);
            border-radius: var(--fpt-radius-md);
            padding: 18px;
        }

        .fpt-ai-box-title {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .fpt-ai-box-title.match { color: #047857; }
        .fpt-ai-box-title.missing { color: #b91c1c; }

        .fpt-ai-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .fpt-ai-chip {
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            padding: 5px 12px;
        }

        .fpt-ai-chip--match {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .fpt-ai-chip--missing {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .fpt-ai-advice-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: var(--fpt-radius-md);
            padding: 16px 20px;
        }

        .fpt-ai-advice-box strong {
            color: #0369a1;
            display: block;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .fpt-ai-advice-box p {
            color: #0c4a6e;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        /* === Right Sticky Sidebar === */
        .fpt-sidebar {
            position: sticky;
            top: 24px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .fpt-apply-card {
            background: var(--fpt-white);
            border: 1px solid var(--fpt-slate-200);
            border-radius: var(--fpt-radius-lg);
            box-shadow: var(--fpt-shadow-card);
            padding: 28px 24px;
        }

        .fpt-apply-card-title {
            color: var(--fpt-dark);
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0 0 8px;
        }

        .fpt-apply-card-sub {
            color: var(--fpt-slate-500);
            font-size: 13.5px;
            line-height: 1.5;
            margin: 0 0 20px;
        }

        /* === High-End Button System === */
        .fpt-btn-primary {
            align-items: center;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            border: none;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(243, 112, 33, 0.28);
            color: #ffffff !important;
            cursor: pointer;
            display: flex;
            font-size: 15px;
            font-weight: 800;
            justify-content: space-between;
            min-height: 52px;
            padding: 0 16px 0 20px;
            text-decoration: none !important;
            transition: all 0.25s var(--fpt-ease);
            width: 100%;
        }

        .fpt-btn-primary:hover {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            box-shadow: 0 12px 28px rgba(243, 112, 33, 0.38);
            transform: translateY(-2px);
        }

        .fpt-btn-icon-bubble {
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: inline-flex;
            height: 34px;
            justify-content: center;
            transition: transform 0.25s var(--fpt-ease);
            width: 34px;
        }

        .fpt-btn-primary:hover .fpt-btn-icon-bubble {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(3px);
        }

        .fpt-btn-ai {
            align-items: center;
            background: #ffffff;
            border: 1.5px solid var(--fpt-orange);
            border-radius: 14px;
            color: var(--fpt-orange) !important;
            cursor: pointer;
            display: flex;
            font-size: 14px;
            font-weight: 700;
            gap: 8px;
            justify-content: center;
            margin-top: 12px;
            min-height: 48px;
            padding: 0 18px;
            text-decoration: none !important;
            transition: all 0.2s var(--fpt-ease);
            width: 100%;
        }

        .fpt-btn-ai:hover {
            background: var(--fpt-orange-light);
            border-color: var(--fpt-orange-hover);
            color: var(--fpt-orange-hover) !important;
            transform: translateY(-1px);
        }

        .fpt-btn-secondary {
            align-items: center;
            background: var(--fpt-slate-100);
            border: 1px solid var(--fpt-slate-200);
            border-radius: 14px;
            color: var(--fpt-slate-600) !important;
            cursor: pointer;
            display: flex;
            font-size: 13.5px;
            font-weight: 600;
            gap: 8px;
            justify-content: center;
            margin-top: 10px;
            min-height: 44px;
            padding: 0 16px;
            text-decoration: none !important;
            transition: all 0.2s ease;
            width: 100%;
        }

        .fpt-btn-secondary:hover {
            background: #e2e8f0;
            color: var(--fpt-dark) !important;
        }

        /* === Trust Badges / Quick Highlights === */
        .fpt-trust-signals {
            border-top: 1px solid var(--fpt-slate-100);
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
            padding-top: 18px;
        }

        .fpt-trust-item {
            align-items: center;
            color: var(--fpt-slate-500);
            display: flex;
            font-size: 13px;
            gap: 10px;
        }

        .fpt-trust-item i {
            color: #10b981;
            font-size: 14px;
        }

        /* === Overview Sidebar Card === */
        .fpt-overview-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .fpt-overview-row {
            align-items: flex-start;
            display: flex;
            gap: 14px;
        }

        .fpt-overview-icon {
            align-items: center;
            background: var(--fpt-slate-100);
            border-radius: 10px;
            color: var(--fpt-slate-600);
            display: inline-flex;
            flex-shrink: 0;
            font-size: 14px;
            height: 36px;
            justify-content: center;
            width: 36px;
        }

        .fpt-overview-meta span {
            color: var(--fpt-slate-500);
            display: block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .fpt-overview-meta strong {
            color: var(--fpt-dark);
            font-size: 14.5px;
            font-weight: 600;
        }

        /* === Recruitment Process Steps === */
        .fpt-steps-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
            position: relative;
        }

        .fpt-step-item {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .fpt-step-num {
            align-items: center;
            background: var(--fpt-orange-light);
            border: 1px solid var(--fpt-orange-border);
            border-radius: 50%;
            color: var(--fpt-orange);
            display: flex;
            flex-shrink: 0;
            font-size: 12px;
            font-weight: 800;
            height: 26px;
            justify-content: center;
            width: 26px;
        }

        .fpt-step-text {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--fpt-slate-800);
        }

        .fpt-step-desc {
            font-size: 12px;
            color: var(--fpt-slate-500);
            margin-top: 2px;
        }

        /* === Share Toast / Action === */
        .fpt-share-link {
            align-items: center;
            background: transparent;
            border: 1px dashed var(--fpt-slate-200);
            border-radius: 12px;
            color: var(--fpt-slate-500);
            cursor: pointer;
            display: flex;
            font-size: 13px;
            font-weight: 600;
            gap: 8px;
            justify-content: center;
            margin-top: 14px;
            padding: 10px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .fpt-share-link:hover {
            background: #ffffff;
            border-color: var(--fpt-orange);
            color: var(--fpt-orange);
        }

        /* === Responsive Layout === */
        @media (max-width: 991px) {
            .fpt-job-page {
                padding-top: 90px;
            }

            .fpt-main-grid {
                grid-template-columns: 1fr;
            }

            .fpt-hero-card {
                padding: 24px 20px;
            }

            .fpt-job-title {
                font-size: 26px;
            }

            .fpt-card {
                padding: 22px 20px;
            }

            .fpt-ai-card {
                padding: 22px 20px;
            }

            .fpt-ai-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .fpt-hero-layout {
                flex-direction: column;
                gap: 16px;
            }

            .fpt-company-logo {
                height: 72px;
                width: 72px;
            }

            .fpt-job-title {
                font-size: 22px;
            }

            .fpt-key-metrics {
                flex-direction: column;
                align-items: stretch;
            }

            .fpt-metric-pill {
                justify-content: flex-start;
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
                    <li class="current">{{ $job->title }}</li>
                </ul>

                <a href="{{ route('candidates.browse_job') }}" class="fpt-back-btn">
                    <i class="fa fa-arrow-left"></i> Quay lại danh sách
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        {{-- Hero Header Card --}}
        <div class="fpt-hero-card">
            <div class="fpt-hero-layout">
                <div class="fpt-company-logo">
                    @if ($shouldShowBranchImage)
                        <img src="{{ $branchImage }}" alt="{{ $branch?->name ?? $job->title }}">
                    @else
                        <div class="fpt-company-logo-fallback">{{ $companyInitials }}</div>
                    @endif
                </div>

                <div class="fpt-hero-content">
                    <div class="fpt-hero-top-pills">
                        <div class="fpt-badge-live">
                            <span class="pulse-dot"></span>
                            {{ $statusLabel }}
                        </div>
                        <div class="fpt-badge-time">
                            <i class="fa fa-clock-o"></i> {{ $publishedHuman }} ({{ $publishedAt }})
                        </div>
                        @if ($branch?->name)
                            <div class="fpt-badge-unit">
                                <i class="fa fa-building-o"></i> {{ $branch->name }}
                            </div>
                        @endif
                    </div>

                    <h1 class="fpt-job-title">{{ $job->title }}</h1>

                    <div class="fpt-company-line">
                        <span>{{ $branch?->name ?? 'FPT Education' }}</span>
                        <i class="fa fa-check-circle verified-icon" title="Đã xác thực"></i>
                        @if ($department?->name)
                            <span>·</span>
                            <span>{{ $department->name }}</span>
                        @endif
                    </div>

                    {{-- Key Highlights Badges --}}
                    <div class="fpt-key-metrics">
                        <div class="fpt-metric-pill fpt-metric-pill--salary">
                            <i class="fa fa-money"></i>
                            <span>{{ $salaryText }}</span>
                        </div>
                        <div class="fpt-metric-pill">
                            <i class="fa fa-map-marker"></i>
                            <span>{{ $cityLabel }} @if($workplace?->name) · {{ $workplace->name }} @endif</span>
                        </div>
                        <div class="fpt-metric-pill">
                            <i class="fa fa-calendar-check-o"></i>
                            <span>{{ $deadlineBadge }}</span>
                        </div>
                        @if($skillCount > 0)
                            <div class="fpt-metric-pill">
                                <i class="fa fa-code"></i>
                                <span>{{ $skillCount }} kỹ năng trọng tâm</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Main 2-Column Grid --}}
        <div class="fpt-main-grid">
            {{-- Left Column: Main Details --}}
            <div class="fpt-content-stack">

                {{-- AI Match Analysis Result Box (Rendered when checked) --}}
                @if (is_array($jobFitAiResult))
                    <div class="fpt-ai-card">
                        <div class="fpt-ai-head">
                            <div class="fpt-ai-title-wrap">
                                <div class="fpt-ai-badge-icon">
                                    <i class="fa fa-magic"></i>
                                </div>
                                <div>
                                    <h3 style="margin: 0; font-size: 19px; font-weight: 800; color: var(--fpt-dark);">Kết quả phân tích mức độ phù hợp (AI)</h3>
                                    <p style="margin: 3px 0 0; font-size: 13px; color: var(--fpt-slate-500);">Đánh giá đối chiếu giữa CV ứng viên và yêu cầu tuyển dụng</p>
                                </div>
                            </div>

                            @if ($aiScoreLabel !== null)
                                <div class="fpt-ai-score-badge {{ $aiScoreClass }}">
                                    {{ $aiScoreLabel }}
                                </div>
                            @endif
                        </div>

                        @if ($aiScore !== null && $aiScore > 0)
                            <div class="fpt-ai-progress-track">
                                <div class="fpt-ai-progress-fill {{ $aiScoreClass }}" style="width: {{ $aiScore }}%;"></div>
                            </div>
                        @endif

                        @if (filled($aiReason))
                            <div class="fpt-ai-reason-box">
                                <strong>Nhận định tổng quan:</strong> {{ $aiReason }}
                            </div>
                        @endif

                        <div class="fpt-ai-grid">
                            <div class="fpt-ai-box">
                                <div class="fpt-ai-box-title match">
                                    <i class="fa fa-check-circle"></i> Điểm phù hợp nổi bật
                                </div>
                                @if (!empty($aiMatchedRequirements))
                                    <div class="fpt-ai-chip-list">
                                        @foreach ($aiMatchedRequirements as $item)
                                            <div class="fpt-ai-chip fpt-ai-chip--match">{{ $item }}</div>
                                        @endforeach
                                    </div>
                                @else
                                    <p style="font-size: 13px; color: var(--fpt-slate-500); margin: 0;">Chưa ghi nhận điểm phù hợp rõ ràng.</p>
                                @endif
                            </div>

                            <div class="fpt-ai-box">
                                <div class="fpt-ai-box-title missing">
                                    <i class="fa fa-info-circle"></i> Điểm cần bổ sung / hoàn thiện
                                </div>
                                @if (!empty($aiMissingRequirements))
                                    <div class="fpt-ai-chip-list">
                                        @foreach ($aiMissingRequirements as $item)
                                            <div class="fpt-ai-chip fpt-ai-chip--missing">{{ $item }}</div>
                                        @endforeach
                                    </div>
                                @else
                                    <p style="font-size: 13px; color: var(--fpt-slate-500); margin: 0;">Hồ sơ đáp ứng trọn vẹn các yêu cầu cơ bản.</p>
                                @endif
                            </div>
                        </div>

                        @if (filled($aiAdvice))
                            <div class="fpt-ai-advice-box">
                                <strong><i class="fa fa-lightbulb-o"></i> Lời khuyên từ AI Career Coach</strong>
                                <p>{{ $aiAdvice }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Job Description --}}
                <div class="fpt-card">
                    <div class="fpt-card-header">
                        <div class="fpt-card-icon">
                            <i class="fa fa-file-text-o"></i>
                        </div>
                        <h2 class="fpt-card-title">Mô tả công việc & Yêu cầu chi tiết</h2>
                    </div>

                    <div class="fpt-job-body">
                        @if(filled($description))
                            {!! $description !!}
                        @else
                            <p>Nội dung mô tả công việc đang được cập nhật. Bạn có thể nộp hồ sơ trực tiếp để ban tuyển dụng hỗ trợ trao đổi chi tiết.</p>
                        @endif
                    </div>
                </div>

                {{-- Required Skills --}}
                @if ($skills->isNotEmpty())
                    <div class="fpt-card">
                        <div class="fpt-card-header">
                            <div class="fpt-card-icon">
                                <i class="fa fa-code"></i>
                            </div>
                            <h3 class="fpt-card-title">Kỹ năng & Chuyên môn trọng tâm</h3>
                        </div>

                        <div class="fpt-skill-wrap">
                            @foreach ($skills as $skill)
                                <div class="fpt-skill-pill">
                                    <i class="fa fa-tag" style="font-size: 11px; color: var(--fpt-orange);"></i>
                                    {{ $skill->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Job Categories --}}
                @if ($job->categories->isNotEmpty())
                    <div class="fpt-card">
                        <div class="fpt-card-header">
                            <div class="fpt-card-icon">
                                <i class="fa fa-th-large"></i>
                            </div>
                            <h3 class="fpt-card-title">Lĩnh vực & Danh mục nghề nghiệp</h3>
                        </div>

                        <div class="fpt-skill-wrap">
                            @foreach ($job->categories as $category)
                                <div class="fpt-skill-pill">
                                    <i class="fa fa-folder-open-o" style="font-size: 11px; color: var(--fpt-orange);"></i>
                                    {{ $category->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column: Sticky Action & Fast Details --}}
            <div>
                <div class="fpt-sidebar">
                    {{-- Primary Application Action Card --}}
                    <div class="fpt-apply-card">
                        <h3 class="fpt-apply-card-title">Sẵn sàng ứng tuyển?</h3>
                        <p class="fpt-apply-card-sub">Nộp hồ sơ ngay hôm nay để nhận phản hồi từ bộ phận tuyển dụng FPT Education trong thời gian sớm nhất.</p>

                        @if ($showApplyAction)
                            <a href="{{ $applyUrl }}" class="fpt-btn-primary">
                                <span>Ứng tuyển vị trí này</span>
                                <span class="fpt-btn-icon-bubble">
                                    <i class="fa fa-paper-plane-o"></i>
                                </span>
                            </a>
                        @else
                            <a href="{{ route('jobs.public', ['slug' => $job->slug]) }}" class="fpt-btn-primary">
                                <span>Xem giao diện ứng viên</span>
                                <span class="fpt-btn-icon-bubble">
                                    <i class="fa fa-external-link"></i>
                                </span>
                            </a>
                        @endif

                        {{-- AI Suite Tools --}}
                        @if ($hasCandidateAccess)
                            @if ($hasCv)
                                <button
                                    type="button"
                                    wire:click="checkJobFitWithAi"
                                    wire:loading.attr="disabled"
                                    wire:target="checkJobFitWithAi"
                                    class="fpt-btn-ai"
                                >
                                    <span wire:loading.remove wire:target="checkJobFitWithAi">
                                        <i class="fa fa-magic"></i> AI kiểm tra độ phù hợp
                                    </span>
                                    <span wire:loading wire:target="checkJobFitWithAi">
                                        <i class="fa fa-circle-o-notch fa-spin"></i> Đang phân tích hồ sơ...
                                    </span>
                                </button>
                            @else
                                <a href="{{ route('candidates.candidate_profile') }}" class="fpt-btn-secondary">
                                    <i class="fa fa-upload"></i> Tải CV để dùng AI kiểm tra
                                </a>
                            @endif

                            <button
                                type="button"
                                wire:click="startAiMockInterview"
                                wire:loading.attr="disabled"
                                wire:target="startAiMockInterview"
                                class="fpt-btn-secondary"
                            >
                                <span wire:loading.remove wire:target="startAiMockInterview">
                                    <i class="fa fa-microphone"></i> Phỏng vấn thử với AI
                                </span>
                                <span wire:loading wire:target="startAiMockInterview">
                                    <i class="fa fa-spinner fa-spin"></i> Đang tạo phòng phỏng vấn...
                                </span>
                            </button>
                        @else
                            <a href="{{ route('candidates.login') }}" class="fpt-btn-secondary">
                                <i class="fa fa-sign-in"></i> Đăng nhập để dùng AI & nộp nhanh
                            </a>
                        @endif

                        {{-- Share Link Button --}}
                        <button type="button" class="fpt-share-link" onclick="copyJobUrl()">
                            <i class="fa fa-share-alt"></i> Sao chép liên kết tin tuyển dụng
                        </button>

                        {{-- Trust Signals --}}
                        <div class="fpt-trust-signals">
                            <div class="fpt-trust-item">
                                <i class="fa fa-check-circle"></i>
                                <span>Tuyển dụng chính thức từ FPT</span>
                            </div>
                            <div class="fpt-trust-item">
                                <i class="fa fa-check-circle"></i>
                                <span>Phản hồi hồ sơ trong vòng 24 - 48h</span>
                            </div>
                            <div class="fpt-trust-item">
                                <i class="fa fa-check-circle"></i>
                                <span>Bảo mật tuyệt đối thông tin ứng viên</span>
                            </div>
                        </div>
                    </div>

                    {{-- Work Environment & Process Card --}}
                    <div class="fpt-card">
                        <div class="fpt-card-header" style="margin-bottom: 16px; padding-bottom: 12px;">
                            <div class="fpt-card-icon" style="width: 32px; height: 32px; font-size: 14px;">
                                <i class="fa fa-sliders"></i>
                            </div>
                            <h3 class="fpt-card-title" style="font-size: 17px;">Quy trình tuyển dụng</h3>
                        </div>

                        <div class="fpt-steps-list">
                            <div class="fpt-step-item">
                                <div class="fpt-step-num">1</div>
                                <div>
                                    <div class="fpt-step-text">Nộp hồ sơ trực tuyến</div>
                                    <div class="fpt-step-desc">Điền thông tin và đính kèm CV qua hệ thống.</div>
                                </div>
                            </div>
                            <div class="fpt-step-item">
                                <div class="fpt-step-num">2</div>
                                <div>
                                    <div class="fpt-step-text">Sàng lọc & Phỏng vấn</div>
                                    <div class="fpt-step-desc">HR liên hệ sắp xếp lịch trao đổi chuyên môn.</div>
                                </div>
                            </div>
                            <div class="fpt-step-item">
                                <div class="fpt-step-num">3</div>
                                <div>
                                    <div class="fpt-step-text">Thư mời nhận việc (Offer)</div>
                                    <div class="fpt-step-desc">Gửi thông báo kết quả và chính sách đãi ngộ.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyJobUrl() {
            const url = window.location.href;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(() => {
                    alert('Đã sao chép liên kết tin tuyển dụng vào bộ nhớ tạm!');
                }).catch(() => {
                    fallbackCopy(url);
                });
            } else {
                fallbackCopy(url);
            }
        }

        function fallbackCopy(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                alert('Đã sao chép liên kết tin tuyển dụng vào bộ nhớ tạm!');
            } catch (err) {
                prompt('Sao chép liên kết:', text);
            }
            document.body.removeChild(textArea);
        }
    </script>
</div>

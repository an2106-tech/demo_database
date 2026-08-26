<div class="fpt-home-page">
    @php
        /** @var \Illuminate\Support\Collection<int, \App\Models\Department>|\App\Models\Department[] $departments */
        /** @var \Illuminate\Support\Collection<int, \App\Models\RecruitmentJob>|\App\Models\RecruitmentJob[] $featuredJobs */
        /** @var \Illuminate\Support\Collection<int, \App\Models\RecruitmentJob>|\App\Models\RecruitmentJob[] $jobs */
        /** @var \Illuminate\Support\Collection<int, \App\Models\Branch>|\App\Models\Branch[] $branches */
        /** @var \Illuminate\Support\Collection<int, \App\Models\Category>|\App\Models\Category[] $categories */
        /** @var \Illuminate\Support\Collection<int, \App\Models\Post>|\App\Models\Post[] $posts */

        $totalJobs = $stats['published_jobs'] ?? $publishedJobsCount ?? 0;
        $totalBranches = $stats['active_branches'] ?? $activeBranchesCount ?? 0;
        $totalCandidates = $stats['candidates'] ?? $candidatesCount ?? 0;
        $totalApplications = $stats['applications'] ?? $applicationsCount ?? 0;
    @endphp

    <style>
        .fpt-home-page {
            --fpt-bg: #f8fafc;
            --fpt-surface: #ffffff;
            --fpt-surface-subtle: #f1f5f9;
            --fpt-ink: #0f172a;
            --fpt-muted: #64748b;
            --fpt-line: #e2e8f0;
            --fpt-line-subtle: #f1f5f9;
            --fpt-primary: #f37021;
            --fpt-primary-hover: #ea580c;
            --fpt-primary-soft: rgba(243, 112, 33, 0.08);
            --fpt-primary-glow: rgba(243, 112, 33, 0.22);
            --fpt-ease: cubic-bezier(0.16, 1, 0.3, 1);

            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--fpt-ink);
            background: var(--fpt-bg);
            padding-top: 105px;
            overflow-x: hidden;
        }

        .fpt-home-page .fa,
        .fpt-home-page i.fa {
            font-family: 'FontAwesome', FontAwesome !important;
            font-style: normal;
        }

        /* Generic Section Headers */
        .fpt-section-header {
            margin-bottom: 36px;
        }

        .fpt-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 14px;
            border-radius: 999px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 12px;
            border: 1px solid rgba(243, 112, 33, 0.16);
        }

        .fpt-section-title {
            font-size: clamp(26px, 3.2vw, 36px);
            font-weight: 900;
            color: var(--fpt-ink);
            letter-spacing: -0.025em;
            margin: 0 0 10px;
            line-height: 1.25;
        }

        .fpt-section-title span {
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .fpt-section-subtitle {
            color: var(--fpt-muted);
            font-size: 15px;
            max-width: 640px;
            line-height: 1.6;
            margin: 0;
        }

        /* ============================================================
           1. HERO SECTION & DOUBLE-BEZEL SEARCH
           ============================================================ */
        .fpt-hero-section {
            padding: 40px 0 60px;
            background: radial-gradient(circle at top right, rgba(243, 112, 33, 0.06) 0%, rgba(255, 255, 255, 0) 60%),
                        radial-gradient(circle at bottom left, rgba(243, 112, 33, 0.04) 0%, rgba(255, 255, 255, 0) 50%);
            position: relative;
        }

        .fpt-hero-headline {
            font-size: clamp(32px, 4.5vw, 52px);
            font-weight: 900;
            color: var(--fpt-ink);
            letter-spacing: -0.035em;
            line-height: 1.15;
            margin-bottom: 16px;
        }

        .fpt-hero-headline .highlight {
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .fpt-hero-subtext {
            color: #475569;
            font-size: clamp(15px, 1.8vw, 17px);
            line-height: 1.65;
            max-width: 720px;
            margin: 0 auto 36px;
        }

        /* Outer Double-Bezel Search Tray */
        .fpt-hero-search-tray {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 26px;
            padding: 8px;
            box-shadow: 0 24px 60px -12px rgba(15, 23, 42, 0.09), 0 2px 6px rgba(0, 0, 0, 0.02);
            max-width: 1040px;
            margin: 0 auto 20px;
        }

        .fpt-hero-search-inner {
            display: grid;
            grid-template-columns: 2fr 1.3fr 1.3fr auto;
            gap: 8px;
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--fpt-line-subtle);
            border-radius: 20px;
            padding: 6px;
        }

        @media (max-width: 991.98px) {
            .fpt-hero-search-inner {
                grid-template-columns: 1fr 1fr;
            }
            .fpt-hero-search-inner .fpt-search-submit-wrap {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 575.98px) {
            .fpt-hero-search-inner {
                grid-template-columns: 1fr;
            }
        }

        .fpt-hero-search-field {
            position: relative;
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 14px;
            padding: 0 16px;
            height: 52px;
            transition: all 0.2s var(--fpt-ease);
        }

        .fpt-hero-search-field:focus-within {
            border-color: var(--fpt-primary);
            box-shadow: 0 0 0 3.5px rgba(243, 112, 33, 0.12);
        }

        .fpt-hero-search-field i {
            color: #94a3b8;
            font-size: 15px;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .fpt-hero-search-field input,
        .fpt-hero-search-field select {
            width: 100%;
            border: none;
            background: transparent;
            font-size: 14px;
            font-weight: 600;
            color: var(--fpt-ink);
            outline: none;
            padding: 0;
        }

        .fpt-hero-search-field select {
            cursor: pointer;
        }

        .fpt-hero-search-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            height: 52px;
            padding: 0 28px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            border: none;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 8px 20px -4px rgba(243, 112, 33, 0.4);
            transition: all 0.25s var(--fpt-ease);
            white-space: nowrap;
            width: 100%;
        }

        .fpt-hero-search-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -4px rgba(243, 112, 33, 0.5);
        }

        /* Quick Suggestions */
        .fpt-hero-chips {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            max-width: 900px;
            margin: 0 auto 36px;
        }

        .fpt-hero-chips__label {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--fpt-muted);
            margin-right: 4px;
        }

        .fpt-hero-chip {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 999px;
            padding: 5px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-decoration: none !important;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .fpt-hero-chip:hover {
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            border-color: rgba(243, 112, 33, 0.3);
            transform: translateY(-1px);
        }

        /* Live Metrics Strip */
        .fpt-metrics-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            max-width: 960px;
            margin: 0 auto;
        }

        .fpt-metric-card {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 18px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
            transition: all 0.25s ease;
        }

        .fpt-metric-card:hover {
            transform: translateY(-2px);
            border-color: rgba(243, 112, 33, 0.3);
            box-shadow: 0 10px 24px -6px rgba(243, 112, 33, 0.08);
        }

        .fpt-metric-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .fpt-metric-num {
            font-size: 20px;
            font-weight: 900;
            color: var(--fpt-ink);
            line-height: 1.1;
            margin-bottom: 2px;
        }

        .fpt-metric-desc {
            font-size: 12px;
            color: var(--fpt-muted);
            font-weight: 600;
            margin: 0;
        }

        /* ============================================================
           2. AI JOB MATCHING BANNER (Haptic Double-Bezel)
           ============================================================ */
        .fpt-ai-banner-section {
            padding: 20px 0 50px;
        }

        .fpt-ai-banner-shell {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 28px;
            padding: 8px;
            box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.05);
        }

        .fpt-ai-banner-core {
            background: linear-gradient(135deg, #fffaf5 0%, #ffffff 60%, #f8fafc 100%);
            border: 1px solid rgba(243, 112, 33, 0.14);
            border-radius: 22px;
            padding: 32px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 24px;
            position: relative;
            overflow: hidden;
        }

        .fpt-ai-banner-core::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(243, 112, 33, 0.08) 0%, rgba(243, 112, 33, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .fpt-ai-banner-info {
            max-width: 620px;
            position: relative;
            z-index: 2;
        }

        .fpt-ai-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            color: #ea580c;
            background: #fff7ed;
            padding: 4px 12px;
            border-radius: 999px;
            border: 1px solid #ffedd5;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .fpt-ai-banner-title {
            font-size: clamp(22px, 2.5vw, 28px);
            font-weight: 900;
            color: var(--fpt-ink);
            margin: 0 0 8px;
            line-height: 1.25;
        }

        .fpt-ai-banner-desc {
            font-size: 14.5px;
            color: var(--fpt-muted);
            margin: 0;
            line-height: 1.6;
        }

        .fpt-ai-banner-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .fpt-btn-ai-scan {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 13px 28px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 800;
            border: none;
            box-shadow: 0 10px 24px -4px rgba(243, 112, 33, 0.4);
            transition: all 0.25s var(--fpt-ease);
            text-decoration: none !important;
            cursor: pointer;
        }

        .fpt-btn-ai-scan:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px -4px rgba(243, 112, 33, 0.5);
        }

        .fpt-btn-ai-profile {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            color: var(--fpt-ink) !important;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }

        .fpt-btn-ai-profile:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        /* ============================================================
           3. FEATURED JOBS SECTION (Double-Bezel Grid)
           ============================================================ */
        .fpt-jobs-section {
            padding: 60px 0;
        }

        .fpt-job-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 22px;
            margin-bottom: 36px;
        }

        @media (max-width: 575.98px) {
            .fpt-job-cards-grid {
                grid-template-columns: 1fr;
            }
        }

        .fpt-job-shell {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 20px;
            padding: 6px;
            transition: all 0.3s var(--fpt-ease);
            box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.04);
            display: flex;
            flex-direction: column;
        }

        .fpt-job-shell:hover {
            transform: translateY(-4px);
            border-color: rgba(243, 112, 33, 0.35);
            box-shadow: 0 20px 40px -10px rgba(243, 112, 33, 0.12), 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        .fpt-job-core {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .fpt-job-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }

        .fpt-job-logo {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            flex-shrink: 0;
            overflow: hidden;
            transition: transform 0.2s ease;
        }

        .fpt-job-shell:hover .fpt-job-logo {
            transform: scale(1.04);
        }

        .fpt-job-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .fpt-job-title {
            font-size: 16.5px;
            font-weight: 800;
            line-height: 1.35;
            margin: 0 0 8px;
        }

        .fpt-job-title a {
            color: var(--fpt-ink) !important;
            text-decoration: none !important;
            transition: color 0.2s ease;
        }

        .fpt-job-title a:hover {
            color: var(--fpt-primary) !important;
        }

        .fpt-job-company {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 14px;
        }

        .fpt-job-meta-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        .fpt-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 8px;
            background: #f8fafc;
            color: #475569;
            border: 1px solid var(--fpt-line-subtle);
        }

        .fpt-pill i {
            color: var(--fpt-primary);
            font-size: 11px;
        }

        .fpt-pill.salary {
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            border-color: rgba(243, 112, 33, 0.16);
            font-weight: 700;
        }

        .fpt-job-actions {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid var(--fpt-line-subtle);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .fpt-btn-detail {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 14px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            border-radius: 10px;
            color: #334155 !important;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }

        .fpt-btn-detail:hover {
            background: #f1f5f9;
            color: var(--fpt-ink) !important;
            border-color: #cbd5e1;
        }

        .fpt-btn-apply {
            flex: 1.4;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 16px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            border: none;
            border-radius: 10px;
            color: #ffffff !important;
            font-size: 12.5px;
            font-weight: 800;
            text-decoration: none !important;
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.25);
            transition: all 0.2s var(--fpt-ease);
        }

        .fpt-btn-apply:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(243, 112, 33, 0.35);
        }

        /* ============================================================
           4. CATEGORIES SECTION (Soft Structuralism)
           ============================================================ */
        .fpt-cats-section {
            padding: 60px 0;
            background: #ffffff;
            border-top: 1px solid var(--fpt-line);
            border-bottom: 1px solid var(--fpt-line);
        }

        .fpt-cat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 36px;
        }

        .fpt-cat-card {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 20px;
            padding: 22px;
            text-decoration: none !important;
            transition: all 0.25s var(--fpt-ease);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .fpt-cat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(243, 112, 33, 0.35);
            box-shadow: 0 16px 36px -8px rgba(243, 112, 33, 0.12);
        }

        .fpt-cat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 16px;
            transition: transform 0.2s ease;
        }

        .fpt-cat-card:hover .fpt-cat-icon {
            transform: scale(1.08);
        }

        .fpt-cat-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--fpt-ink);
            margin: 0 0 6px;
        }

        .fpt-cat-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            color: var(--fpt-muted);
            font-weight: 600;
            margin-top: auto;
            padding-top: 10px;
        }

        .fpt-cat-arrow {
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            transition: all 0.2s ease;
        }

        .fpt-cat-card:hover .fpt-cat-arrow {
            background: var(--fpt-primary);
            color: #ffffff;
            transform: translateX(3px);
        }

        /* ============================================================
           5. 3-STEP RECRUITMENT PIPELINE (Interactive Workflow)
           ============================================================ */
        .fpt-pipeline-section {
            padding: 70px 0;
            background: var(--fpt-bg);
        }

        .fpt-pipeline-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            position: relative;
        }

        @media (max-width: 991.98px) {
            .fpt-pipeline-grid {
                grid-template-columns: 1fr;
            }
        }

        .fpt-pipeline-card {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 22px;
            padding: 32px 28px;
            position: relative;
            transition: all 0.3s var(--fpt-ease);
            box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.03);
        }

        .fpt-pipeline-card:hover {
            transform: translateY(-4px);
            border-color: rgba(243, 112, 33, 0.3);
            box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.08);
        }

        .fpt-pipeline-step-badge {
            position: absolute;
            top: 24px;
            right: 24px;
            font-size: 32px;
            font-weight: 900;
            color: #f1f5f9;
            line-height: 1;
            font-family: inherit;
        }

        .fpt-pipeline-card:hover .fpt-pipeline-step-badge {
            color: rgba(243, 112, 33, 0.15);
        }

        .fpt-pipeline-icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 20px;
        }

        .fpt-pipeline-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--fpt-ink);
            margin: 0 0 10px;
        }

        .fpt-pipeline-desc {
            font-size: 14px;
            color: var(--fpt-muted);
            line-height: 1.6;
            margin: 0;
        }

        /* ============================================================
           6. CAMPUSES & TABS SECTION (Refined Campus Hub)
           ============================================================ */
        .fpt-campus-section {
            padding: 70px 0;
            background: #ffffff;
            border-top: 1px solid var(--fpt-line);
        }

        .fpt-tab-nav {
            display: inline-flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 14px;
            border: 1px solid var(--fpt-line);
            gap: 4px;
            margin-bottom: 32px;
        }

        .fpt-tab-btn {
            border: none;
            background: transparent;
            padding: 10px 22px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--fpt-muted);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fpt-tab-btn.active {
            background: #ffffff;
            color: var(--fpt-primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .fpt-branch-card {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
            transition: all 0.25s ease;
        }

        .fpt-branch-card:hover {
            border-color: rgba(243, 112, 33, 0.3);
            box-shadow: 0 12px 30px -8px rgba(15, 23, 42, 0.06);
        }

        .fpt-branch-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 18px;
        }

        .fpt-branch-identity {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .fpt-branch-logo-wrap {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .fpt-branch-logo-wrap img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .fpt-branch-name {
            font-size: 18px;
            font-weight: 800;
            color: var(--fpt-ink);
            margin: 0 0 4px;
        }

        .fpt-branch-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: var(--fpt-muted);
            font-weight: 600;
        }

        .fpt-branch-meta i {
            color: var(--fpt-primary);
        }

        .fpt-branch-jobs-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            border-top: 1px solid var(--fpt-line-subtle);
            padding-top: 16px;
        }

        .fpt-branch-job-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line-subtle);
            border-radius: 12px;
            gap: 14px;
            transition: all 0.2s ease;
        }

        .fpt-branch-job-item:hover {
            background: #ffffff;
            border-color: rgba(243, 112, 33, 0.25);
            transform: translateX(3px);
        }

        .fpt-branch-job-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--fpt-ink);
            text-decoration: none !important;
            flex: 1;
        }

        .fpt-branch-job-title:hover {
            color: var(--fpt-primary);
        }

        /* ============================================================
           7. CAREER TIPS & BLOG (Editorial Magazine Style)
           ============================================================ */
        .fpt-blog-section {
            padding: 70px 0;
            background: var(--fpt-bg);
        }

        .fpt-blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 36px;
        }

        .fpt-blog-card {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 20px;
            overflow: hidden;
            text-decoration: none !important;
            transition: all 0.3s var(--fpt-ease);
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
        }

        .fpt-blog-card:hover {
            transform: translateY(-4px);
            border-color: rgba(243, 112, 33, 0.3);
            box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.08);
        }

        .fpt-blog-img-wrap {
            height: 190px;
            width: 100%;
            overflow: hidden;
            background: #f1f5f9;
        }

        .fpt-blog-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s var(--fpt-ease);
        }

        .fpt-blog-card:hover .fpt-blog-img-wrap img {
            transform: scale(1.05);
        }

        .fpt-blog-body {
            padding: 22px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .fpt-blog-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: var(--fpt-muted);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .fpt-blog-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--fpt-ink);
            line-height: 1.4;
            margin: 0 0 12px;
            transition: color 0.2s ease;
        }

        .fpt-blog-card:hover .fpt-blog-title {
            color: var(--fpt-primary);
        }

        .fpt-blog-read-more {
            margin-top: auto;
            font-size: 13px;
            font-weight: 700;
            color: var(--fpt-primary);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* ============================================================
           8. NEWSLETTER / PARTNERSHIP CTA
           ============================================================ */
        .fpt-newsletter-section {
            padding: 20px 0 60px;
        }

        .fpt-newsletter-card {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 26px;
            padding: 44px 48px;
            color: var(--fpt-ink);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.08), 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .fpt-newsletter-card::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -5%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(243, 112, 33, 0.12) 0%, rgba(243, 112, 33, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .fpt-newsletter-info {
            max-width: 580px;
            position: relative;
            z-index: 2;
        }

        .fpt-newsletter-title {
            font-size: clamp(22px, 2.5vw, 28px);
            font-weight: 850;
            color: #0f172a !important;
            -webkit-text-fill-color: #0f172a !important;
            margin: 0 0 10px;
            line-height: 1.3;
            letter-spacing: -0.02em;
            text-shadow: none !important;
        }

        .fpt-newsletter-desc {
            font-size: 14.5px;
            color: #475569 !important;
            -webkit-text-fill-color: #475569 !important;
            margin: 0;
            line-height: 1.65;
        }

        .fpt-btn-newsletter {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 15px 34px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-size: 14.5px;
            font-weight: 800;
            text-decoration: none !important;
            box-shadow: 0 8px 24px rgba(243, 112, 33, 0.35);
            transition: all 0.25s var(--fpt-ease);
            position: relative;
            z-index: 2;
        }

        .fpt-btn-newsletter:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(243, 112, 33, 0.5);
        }
    </style>

    {{-- ============================================================
       1. HERO SECTION & DOUBLE-BEZEL SEARCH HUB
       ============================================================ --}}
    <section class="fpt-hero-section">
        <div class="container text-center">
            <div>
                <span class="fpt-eyebrow">
                    <i class="fa fa-sparkles"></i> Tuyển dụng FPT Education
                </span>
            </div>

            <h1 class="fpt-hero-headline">
                Kiến tạo sự nghiệp giáo dục, <br />
                <span class="highlight">Vươn tầm cùng FPT</span>
            </h1>

            <p class="fpt-hero-subtext">
                Gia nhập hệ sinh thái giáo dục hàng đầu Việt Nam. Hàng trăm vị trí Giảng viên, Nghiên cứu, Cán bộ Tuyển sinh và Chuyên viên Công nghệ đang chờ đón bạn.
            </p>

            {{-- Double-Bezel Search Tray --}}
            <div class="fpt-hero-search-tray">
                <form class="fpt-hero-search-inner" wire:submit.prevent="searchJobs">
                    {{-- Keyword Input --}}
                    <div class="fpt-hero-search-field">
                        <i class="fa fa-search"></i>
                        <input
                            type="search"
                            placeholder="Vị trí tuyển dụng, kỹ năng..."
                            wire:model="searchKeyword"
                        >
                    </div>

                    {{-- Location City Dropdown --}}
                    <div class="fpt-hero-search-field">
                        <i class="fa fa-map-marker"></i>
                        <select wire:model="searchCity">
                            <option value="">Tất cả địa điểm</option>
                            @foreach(\App\Enums\VietnamProvince::cases() as $province)
                                <option value="{{ $province->value }}">{{ $province->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department Dropdown --}}
                    <div class="fpt-hero-search-field">
                        <i class="fa fa-sitemap"></i>
                        <select wire:model="searchDepartmentId">
                            <option value="">Tất cả phòng ban</option>
                            @foreach(($departments ?? []) as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Submit Button --}}
                    <div class="fpt-search-submit-wrap">
                        <button type="submit" class="fpt-hero-search-btn">
                            <i class="fa fa-search"></i>
                            <span>Tìm việc ngay</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Quick Filter Chips --}}
            <div class="fpt-hero-chips">
                <span class="fpt-hero-chips__label"><i class="fa fa-bolt text-warning"></i> Gợi ý tìm nhanh:</span>
                <a href="{{ route('candidates.browse_job', ['q' => 'Giảng viên']) }}" class="fpt-hero-chip">Giảng viên Công nghệ</a>
                <a href="{{ route('candidates.browse_job', ['q' => 'Lập trình viên']) }}" class="fpt-hero-chip">Lập trình viên PHP/Laravel</a>
                <a href="{{ route('candidates.browse_job', ['q' => 'Tuyển sinh']) }}" class="fpt-hero-chip">Cán bộ Tuyển sinh</a>
                <a href="{{ route('candidates.browse_job', ['q' => 'Marketing']) }}" class="fpt-hero-chip">Truyền thông & Marketing</a>
                <a href="{{ route('candidates.browse_job', ['city' => 'Hà Nội']) }}" class="fpt-hero-chip">Khu vực Hà Nội</a>
                <a href="{{ route('candidates.browse_job', ['city' => 'Hồ Chí Minh']) }}" class="fpt-hero-chip">Khu vực TP.HCM</a>
            </div>

            {{-- Live Metrics Bar --}}
            <div class="fpt-metrics-bar">
                <div class="fpt-metric-card">
                    <div class="fpt-metric-icon"><i class="fa fa-briefcase"></i></div>
                    <div class="text-start">
                        <div class="fpt-metric-num">{{ number_format($totalJobs) }}+</div>
                        <p class="fpt-metric-desc">Vị trí đang tuyển</p>
                    </div>
                </div>
                <div class="fpt-metric-card">
                    <div class="fpt-metric-icon"><i class="fa fa-building-o"></i></div>
                    <div class="text-start">
                        <div class="fpt-metric-num">{{ number_format($totalBranches) }}</div>
                        <p class="fpt-metric-desc">Cơ sở & Đơn vị</p>
                    </div>
                </div>
                <div class="fpt-metric-card">
                    <div class="fpt-metric-icon"><i class="fa fa-users"></i></div>
                    <div class="text-start">
                        <div class="fpt-metric-num">{{ number_format($totalCandidates) }}+</div>
                        <p class="fpt-metric-desc">Ứng viên tin dùng</p>
                    </div>
                </div>
                <div class="fpt-metric-card">
                    <div class="fpt-metric-icon"><i class="fa fa-check-circle-o"></i></div>
                    <div class="text-start">
                        <div class="fpt-metric-num">{{ number_format($totalApplications) }}+</div>
                        <p class="fpt-metric-desc">Lượt nộp hồ sơ</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
       2. AI JOB MATCHING QUICK ACCESS BANNER
       ============================================================ --}}
    <section class="fpt-ai-banner-section">
        <div class="container">
            <div class="fpt-ai-banner-shell">
                <div class="fpt-ai-banner-core">
                    <div class="fpt-ai-banner-info">
                        <span class="fpt-ai-badge">
                            <i class="fa fa-magic"></i> AI Job Matching
                        </span>
                        <h2 class="fpt-ai-banner-title">Quét việc làm phù hợp tự động từ CV của bạn</h2>
                        <p class="fpt-ai-banner-desc">
                            Hệ thống AI tự động phân tích hồ sơ và chấm điểm độ tương thích với tất cả các vị trí đang tuyển tại FPT Education trên toàn quốc.
                        </p>
                    </div>

                    <div class="fpt-ai-banner-actions">
                        @if ($hasCandidateAccess)
                            <button
                                type="button"
                                class="fpt-btn-ai-scan"
                                wire:click="openJobMatching"
                                wire:loading.attr="disabled"
                                wire:target="openJobMatching"
                            >
                                <span wire:loading.remove wire:target="openJobMatching">
                                    <i class="fa fa-bolt"></i> Quét việc ngay
                                </span>
                                <span wire:loading wire:target="openJobMatching">
                                    <i class="fa fa-circle-o-notch fa-spin"></i> Đang mở đối chiếu...
                                </span>
                            </button>
                            <a href="{{ route('candidates.candidate_profile') }}" class="fpt-btn-ai-profile">
                                <i class="fa fa-user-circle-o"></i> Cập nhật hồ sơ
                            </a>
                        @else
                            <a href="{{ route('candidates.login') }}" class="fpt-btn-ai-scan">
                                <i class="fa fa-sign-in"></i> Đăng nhập để quét việc AI
                            </a>
                            <a href="{{ route('candidates.register') }}" class="fpt-btn-ai-profile">
                                <i class="fa fa-user-plus"></i> Tạo tài khoản
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
       3. FEATURED SPOTLIGHT JOBS
       ============================================================ --}}
    <section class="fpt-jobs-section">
        <div class="container">
            <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 fpt-section-header">
                <div>
                    <span class="fpt-eyebrow"><i class="fa fa-fire text-danger"></i> Đang tuyển gấp</span>
                    <h2 class="fpt-section-title">Cơ hội việc làm <span>nổi bật</span></h2>
                    <p class="fpt-section-subtitle">Các vị trí có đãi ngộ cạnh tranh, môi trường làm việc chuẩn quốc tế tại FPT Education.</p>
                </div>

                <a href="{{ route('candidates.browse_job') }}" class="fpt-btn-detail" style="flex: none; padding: 10px 22px; border-radius: 12px; font-weight: 800;">
                    Xem tất cả {{ number_format($totalJobs) }} việc làm <i class="fa fa-arrow-right ms-1 text-primary"></i>
                </a>
            </div>

            <div class="fpt-job-cards-grid">
                @php
                    $homeFeaturedJobs = (isset($featuredJobs) && $featuredJobs->isNotEmpty()) ? $featuredJobs : $jobs->take(6);
                @endphp

                @forelse ($homeFeaturedJobs as $spotlight)
                    @php
                        $detailUrl = route('jobs.public', ['slug' => $spotlight->slug]);
                        $applyUrl = route('candidates.apply_job', ['job' => $spotlight->id]);
                        $logoSrc = $spotlight->branch?->image
                            ? '/storage/' . ltrim($spotlight->branch->image, '/')
                            : asset('assets/img/company-logo-1.png');
                        $branchName = trim((string) ($spotlight->branch?->name ?? ''));
                        $cityText = \App\Enums\VietnamProvince::tryFrom($spotlight->branch?->city ?? '')?->label()
                            ?? ($spotlight->branch?->city ?? 'Chưa cập nhật');
                        $deadlineText = $spotlight->deadline?->format('d/m/Y') ?? 'Tuyển liên tục';
                    @endphp

                    <div class="fpt-job-shell">
                        <div class="fpt-job-core">
                            <div class="fpt-job-top">
                                <a href="{{ $detailUrl }}" class="fpt-job-logo" aria-label="{{ $spotlight->title }}">
                                    <img src="{{ $logoSrc }}" alt="{{ $branchName !== '' ? $branchName : 'Logo' }}">
                                </a>

                                <span class="fpt-pill" style="font-size: 11.5px; color: #94a3b8;">
                                    <i class="fa fa-clock-o"></i> Hạn: {{ $deadlineText }}
                                </span>
                            </div>

                            <div class="flex-grow-1 mb-3">
                                <h3 class="fpt-job-title">
                                    <a href="{{ $detailUrl }}">{{ $spotlight->title }}</a>
                                </h3>

                                <div class="fpt-job-company">
                                    <i class="fa fa-building-o"></i>
                                    <span>{{ $branchName !== '' ? $branchName : 'FPT Education' }}</span>
                                </div>

                                <div class="fpt-job-meta-row">
                                    <span class="fpt-pill" title="Địa điểm làm việc">
                                        <i class="fa fa-map-marker"></i> {{ $cityText }}
                                    </span>

                                    <span class="fpt-pill salary" title="Mức thu nhập">
                                        <i class="fa fa-tag"></i> {{ $spotlight->formatted_salary }}
                                    </span>

                                    @if($spotlight->department?->name)
                                        <span class="fpt-pill" title="Phòng ban">
                                            <i class="fa fa-folder-open-o"></i> {{ $spotlight->department->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="fpt-job-actions">
                                <a href="{{ $detailUrl }}" class="fpt-btn-detail">Xem chi tiết</a>
                                <a href="{{ $applyUrl }}" class="fpt-btn-apply">
                                    <span>Ứng tuyển</span>
                                    <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12" style="grid-column: 1 / -1;">
                        <div class="alert alert-light border text-center py-4">Chưa có việc làm nổi bật. Vui lòng quay lại sau!</div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ============================================================
       4. EXPLORE CATEGORIES (Soft Structuralism)
       ============================================================ --}}
    <section class="fpt-cats-section">
        <div class="container">
            <div class="text-center fpt-section-header">
                <span class="fpt-eyebrow"><i class="fa fa-th-large"></i> Danh mục ngành nghề</span>
                <h2 class="fpt-section-title">Khám phá các lĩnh vực <span>chuyên môn</span></h2>
                <p class="fpt-section-subtitle mx-auto">Chọn ngành nghề phù hợp với năng lực và mục tiêu phát triển sự nghiệp của bạn tại FPT.</p>
            </div>

            <div class="fpt-cat-grid">
                @forelse($categories as $category)
                    <a href="{{ route('candidates.browse_job', ['category_id' => $category->id]) }}" class="fpt-cat-card">
                        <div class="fpt-cat-icon">
                            @php
                                $icon = trim((string) ($category->icon ?? ''));
                            @endphp
                            <i class="{{ $icon !== '' ? (\Illuminate\Support\Str::startsWith($icon, 'fa') ? $icon : 'fa fa-' . $icon) : 'fa fa-briefcase' }}"></i>
                        </div>

                        <h3 class="fpt-cat-title">{{ $category->name }}</h3>

                        <div class="fpt-cat-meta">
                            <span>{{ $category->recruitment_jobs_count ?? 0 }} vị trí mở</span>
                            <span class="fpt-cat-arrow"><i class="fa fa-arrow-right"></i></span>
                        </div>
                    </a>
                @empty
                    <div class="col-12 text-center py-4">Không có danh mục nào.</div>
                @endforelse
            </div>

            <div class="text-center">
                <a href="{{ route('candidates.browse_categories') }}" class="fpt-btn-detail" style="display: inline-flex; padding: 11px 28px; border-radius: 12px; font-weight: 800;">
                    Khám phá tất cả ngành nghề <i class="fa fa-angle-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================================
       5. 3-STEP RECRUITMENT PIPELINE
       ============================================================ --}}
    <section class="fpt-pipeline-section">
        <div class="container">
            <div class="text-center fpt-section-header">
                <span class="fpt-eyebrow"><i class="fa fa-road"></i> Lộ trình gia nhập</span>
                <h2 class="fpt-section-title">Quy trình ứng tuyển <span>3 bước</span></h2>
                <p class="fpt-section-subtitle mx-auto">Trải nghiệm ứng tuyển nhanh chóng, minh bạch và chuyên nghiệp cùng FPT Education.</p>
            </div>

            <div class="fpt-pipeline-grid">
                {{-- Step 1 --}}
                <div class="fpt-pipeline-card">
                    <span class="fpt-pipeline-step-badge">01</span>
                    <div class="fpt-pipeline-icon">
                        <i class="fa fa-user-circle-o"></i>
                    </div>
                    <h3 class="fpt-pipeline-title">Khởi tạo Hồ sơ Số</h3>
                    <p class="fpt-pipeline-desc">
                        Tạo tài khoản ứng viên, tải lên CV hoặc sử dụng công cụ thiết kế CV Online chuẩn FPT để hệ thống tự động trích xuất kỹ năng.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="fpt-pipeline-card">
                    <span class="fpt-pipeline-step-badge">02</span>
                    <div class="fpt-pipeline-icon">
                        <i class="fa fa-search"></i>
                    </div>
                    <h3 class="fpt-pipeline-title">Tìm kiếm & Khớp lệnh AI</h3>
                    <p class="fpt-pipeline-desc">
                        Khám phá vị trí theo cơ sở, chuyên môn và sử dụng tính năng AI Job Matching để tìm cơ hội có độ tương thích cao nhất.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="fpt-pipeline-card">
                    <span class="fpt-pipeline-step-badge">03</span>
                    <div class="fpt-pipeline-icon">
                        <i class="fa fa-handshake-o"></i>
                    </div>
                    <h3 class="fpt-pipeline-title">Phỏng vấn & Đồng hành</h3>
                    <p class="fpt-pipeline-desc">
                        Nhận lịch hẹn phỏng vấn trực tiếp từ hội đồng tuyển dụng FPT, theo dõi trạng thái hồ sơ tức thời và nhận offer công việc.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
       6. CAMPUSES & LATEST JOBS (Refined Tabs)
       ============================================================ --}}
    <section class="fpt-campus-section">
        <div class="container">
            <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 fpt-section-header">
                <div>
                    <span class="fpt-eyebrow"><i class="fa fa-sitemap"></i> Cơ sở đào tạo</span>
                    <h2 class="fpt-section-title">Hệ thống đơn vị & <span>việc làm mới</span></h2>
                    <p class="fpt-section-subtitle">Xem các đơn vị thành viên FPT Education trên toàn quốc và danh sách việc làm mới cập nhật.</p>
                </div>
            </div>

            <div class="fpt-tab-nav" role="tablist">
                <button class="fpt-tab-btn active" id="pills-campuses-tab" data-bs-toggle="pill" data-bs-target="#pills-campuses" type="button" role="tab">
                    <i class="fa fa-building-o me-1"></i> Cơ sở hàng đầu ({{ $branches->count() }})
                </button>
                <button class="fpt-tab-btn" id="pills-recent-tab" data-bs-toggle="pill" data-bs-target="#pills-recent" type="button" role="tab">
                    <i class="fa fa-clock-o me-1"></i> Việc làm mới cập nhật
                </button>
            </div>

            <div class="tab-content" id="fpt-campus-tabContent">
                {{-- Campuses Tab --}}
                <div class="tab-pane fade show active" id="pills-campuses" role="tabpanel">
                    @forelse($branches as $branch)
                        @continue(((int) ($branch->published_jobs_count ?? 0)) < 1)
                        @php
                            $cityLabel = \App\Enums\VietnamProvince::tryFrom($branch->city ?? '')?->label() ?? ($branch->city ?? 'Chưa cập nhật');
                        @endphp

                        <div class="fpt-branch-card">
                            <div class="fpt-branch-header">
                                <div class="fpt-branch-identity">
                                    <div class="fpt-branch-logo-wrap">
                                        <img src="{{ $branch->image ? asset('storage/' . ltrim($branch->image, '/')) : asset('assets/img/company-logo-1.png') }}" alt="{{ $branch->name }}">
                                    </div>
                                    <div>
                                        <h3 class="fpt-branch-name">{{ $branch->name }}</h3>
                                        <div class="fpt-branch-meta">
                                            <span><i class="fa fa-map-marker"></i> {{ $cityLabel }}</span>
                                            @if($branch->address)
                                                <span><i class="fa fa-location-arrow"></i> {{ $branch->address }}</span>
                                            @endif
                                            <span><i class="fa fa-briefcase"></i> {{ (int) ($branch->published_jobs_count ?? 0) }} vị trí đang tuyển</span>
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('candidates.browse_job', ['city' => $branch->city]) }}" class="fpt-btn-detail" style="flex: none; padding: 8px 18px;">
                                    Xem tất cả tin tuyển <i class="fa fa-arrow-right ms-1"></i>
                                </a>
                            </div>

                            @if(($branch->recruitmentJobs ?? collect())->isNotEmpty())
                                <div class="fpt-branch-jobs-list">
                                    @foreach(($branch->recruitmentJobs ?? collect())->take(3) as $job)
                                        <div class="fpt-branch-job-item">
                                            <a href="{{ route('jobs.public', ['slug' => $job->slug]) }}" class="fpt-branch-job-title">
                                                <i class="fa fa-angle-right text-primary me-2"></i>{{ $job->title }}
                                            </a>
                                            <span class="fpt-pill salary" style="font-size: 11.5px;">
                                                <i class="fa fa-tag"></i> {{ $job->formatted_salary }}
                                            </span>
                                            <span style="font-size: 12px; color: #94a3b8;">
                                                <i class="fa fa-clock-o"></i> Hạn: {{ $job->deadline?->format('d/m/Y') ?? 'Liên tục' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="alert alert-light border text-center py-4">Không có chi nhánh nào đang mở tuyển dụng.</div>
                    @endforelse
                </div>

                {{-- Recent Jobs Tab --}}
                <div class="tab-pane fade" id="pills-recent" role="tabpanel">
                    <div class="fpt-job-cards-grid">
                        @forelse($jobs->take(6) as $job)
                            @php
                                $detailUrl = route('jobs.public', ['slug' => $job->slug]);
                                $applyUrl = route('candidates.apply_job', ['job' => $job->id]);
                                $logoSrc = $job->branch?->image
                                    ? '/storage/' . ltrim($job->branch->image, '/')
                                    : asset('assets/img/company-logo-1.png');
                                $branchName = trim((string) ($job->branch?->name ?? ''));
                                $cityText = \App\Enums\VietnamProvince::tryFrom($job->branch?->city ?? '')?->label()
                                    ?? ($job->branch?->city ?? 'Chưa cập nhật');
                            @endphp

                            <div class="fpt-job-shell">
                                <div class="fpt-job-core">
                                    <div class="fpt-job-top">
                                        <a href="{{ $detailUrl }}" class="fpt-job-logo" aria-label="{{ $job->title }}">
                                            <img src="{{ $logoSrc }}" alt="{{ $branchName !== '' ? $branchName : 'Logo' }}">
                                        </a>

                                        <span class="fpt-pill" style="font-size: 11.5px; color: #94a3b8;">
                                            <i class="fa fa-clock-o"></i> {{ $job->created_at?->diffForHumans() }}
                                        </span>
                                    </div>

                                    <div class="flex-grow-1 mb-3">
                                        <h3 class="fpt-job-title">
                                            <a href="{{ $detailUrl }}">{{ $job->title }}</a>
                                        </h3>

                                        <div class="fpt-job-company">
                                            <i class="fa fa-building-o"></i>
                                            <span>{{ $branchName !== '' ? $branchName : 'FPT Education' }}</span>
                                        </div>

                                        <div class="fpt-job-meta-row">
                                            <span class="fpt-pill" title="Địa điểm làm việc">
                                                <i class="fa fa-map-marker"></i> {{ $cityText }}
                                            </span>

                                            <span class="fpt-pill salary" title="Mức thu nhập">
                                                <i class="fa fa-tag"></i> {{ $job->formatted_salary }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="fpt-job-actions">
                                        <a href="{{ $detailUrl }}" class="fpt-btn-detail">Xem chi tiết</a>
                                        <a href="{{ $applyUrl }}" class="fpt-btn-apply">
                                            <span>Ứng tuyển</span>
                                            <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12" style="grid-column: 1 / -1;">
                                <div class="alert alert-light border text-center py-4">Chưa có việc làm mới.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
       7. CAREER INSIGHTS & BLOG
       ============================================================ --}}
    @if($posts->isNotEmpty())
        <section class="fpt-blog-section">
            <div class="container">
                <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 fpt-section-header">
                    <div>
                        <span class="fpt-eyebrow"><i class="fa fa-newspaper-o"></i> Góc nghề nghiệp</span>
                        <h2 class="fpt-section-title">Tin tức & <span>Tips phỏng vấn</span></h2>
                        <p class="fpt-section-subtitle">Bí quyết phát triển sự nghiệp, văn hóa làm việc và kinh nghiệm phỏng vấn tại FPT.</p>
                    </div>

                    <a href="{{ route('pages.blog') }}" class="fpt-btn-detail" style="flex: none; padding: 10px 22px; border-radius: 12px; font-weight: 800;">
                        Xem tất cả bài viết <i class="fa fa-arrow-right ms-1 text-primary"></i>
                    </a>
                </div>

                <div class="fpt-blog-grid">
                    @foreach($posts->take(3) as $post)
                        <a href="{{ route('pages.blog') }}" class="fpt-blog-card">
                            <div class="fpt-blog-img-wrap">
                                <img src="{{ asset($post->image) }}" alt="{{ $post->title }}" loading="lazy">
                            </div>
                            <div class="fpt-blog-body">
                                <div class="fpt-blog-meta">
                                    <span><i class="fa fa-calendar me-1"></i> {{ $post->created_at?->format('d/m/Y') }}</span>
                                    <span>•</span>
                                    <span><i class="fa fa-clock-o me-1"></i> 3 phút đọc</span>
                                </div>
                                <h3 class="fpt-blog-title">{{ $post->title }}</h3>
                                <span class="fpt-blog-read-more">
                                    Đọc bài viết <i class="fa fa-arrow-right"></i>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================================================
       8. PARTNERSHIP & NEWSLETTER CTA
       ============================================================ --}}
    <section class="fpt-newsletter-section">
        <div class="container">
            <div class="fpt-newsletter-card">
                <div class="fpt-newsletter-info">
                    <h3 class="fpt-newsletter-title">Sẵn sàng đồng hành cùng FPT Education?</h3>
                    <p class="fpt-newsletter-desc">
                        Khám phá môi trường làm việc năng động, sáng tạo và nhiều cơ hội thăng tiến. Liên hệ ngay với bộ phận tuyển dụng để được tư vấn lộ trình sự nghiệp phù hợp.
                    </p>
                </div>

                <a href="{{ route('pages.contact') }}" class="fpt-btn-newsletter">
                    <span>Liên hệ ứng tuyển ngay</span>
                    <i class="fa fa-paper-plane"></i>
                </a>
            </div>
        </div>
    </section>
</div>

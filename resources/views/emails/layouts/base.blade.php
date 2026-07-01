@php
    $brandName = config('mail.from.name', config('app.name', 'FPT Career'));
    $pageTitle = $title ?? $brandName;
    $eyebrowText = $eyebrow ?? 'FPT Career';
    $previewText = $preview ?? 'Thông báo từ hệ thống tuyển dụng.';
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f7f3ec;
            color: #283044;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            line-height: 1.6;
        }

        a {
            color: #f37021;
        }

        .mail-wrapper {
            width: 100%;
            padding: 36px 18px 42px;
            background: #f7f3ec;
        }

        .mail-preheader {
            display: none;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            color: transparent;
        }

        .brand-pill {
            display: table;
            margin: 0 auto 24px;
            padding: 8px 16px 8px 8px;
            border: 1px solid #f0e5d8;
            border-radius: 999px;
            background: #ffffff;
            color: #111827;
            text-decoration: none;
        }

        .brand-mark {
            display: inline-block;
            width: 34px;
            height: 34px;
            margin-right: 10px;
            border-radius: 999px;
            background: #f37021;
            color: #ffffff;
            font-size: 15px;
            font-weight: 900;
            line-height: 34px;
            text-align: center;
            vertical-align: middle;
        }

        .brand-copy {
            display: inline-block;
            vertical-align: middle;
            line-height: 1.1;
        }

        .brand-copy strong {
            display: block;
            color: #111827;
            font-size: 14px;
            font-weight: 800;
        }

        .brand-copy small {
            display: block;
            margin-top: 3px;
            color: #8a7160;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .mail-shell {
            max-width: 640px;
            margin: 0 auto;
            padding: 8px;
            border-radius: 30px;
            background: #efe7dc;
        }

        .mail-card {
            overflow: hidden;
            border: 1px solid #f4eadf;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 22px 70px rgba(81, 52, 28, .12);
        }

        .mail-hero {
            padding: 38px 44px 20px;
        }

        .mail-eyebrow {
            display: inline-block;
            margin-bottom: 16px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .mail-title {
            margin: 0;
            color: #111827;
            font-size: 25px;
            font-weight: 850;
            letter-spacing: -.02em;
            line-height: 1.22;
        }

        .mail-body {
            padding: 0 44px 42px;
        }

        .mail-body p {
            margin: 0 0 18px;
            color: #475569;
            font-size: 15px;
            line-height: 1.72;
        }

        .mail-body strong {
            color: #111827;
            font-weight: 850;
        }

        .mail-body ul {
            margin: 20px 0;
            padding-left: 20px;
            color: #475569;
        }

        .mail-body li {
            margin-bottom: 9px;
            color: #475569;
            font-size: 15px;
            line-height: 1.62;
        }

        .mail-panel,
        .job-card,
        .info-card {
            margin: 24px 0;
            padding: 20px;
            border: 1px solid #fed7aa;
            border-radius: 18px;
            background: #fff7ed;
        }

        .mail-panel p:last-child,
        .job-card p:last-child,
        .info-card p:last-child {
            margin-bottom: 0;
        }

        .info-item,
        .job-detail {
            display: block;
            margin-bottom: 8px;
            color: #7c6b5d;
            font-size: 14px;
            line-height: 1.5;
        }

        .info-value {
            color: #111827;
            font-weight: 850;
        }

        .mail-actions {
            margin: 30px 0 8px;
            text-align: center;
        }

        .mail-button {
            display: inline-block;
            padding: 6px 7px 6px 22px;
            border-radius: 999px;
            background: #111827;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 900;
            text-decoration: none;
        }

        .mail-button--orange {
            background: #f37021;
        }

        .mail-button--green {
            background: #166534;
        }

        .mail-button--red {
            background: #b91c1c;
        }

        .mail-button span {
            display: inline-block;
            padding-right: 14px;
            line-height: 34px;
            vertical-align: middle;
        }

        .mail-button i {
            display: inline-block;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .16);
            color: #ffffff;
            font-style: normal;
            font-size: 17px;
            line-height: 34px;
            text-align: center;
            vertical-align: middle;
        }

        .mail-muted-link {
            color: #7c6b5d;
            font-size: 13px;
        }

        .mail-footer {
            max-width: 600px;
            margin: 22px auto 0;
            color: #9b8b7d;
            font-size: 12px;
            line-height: 1.6;
            text-align: center;
        }

        @media only screen and (max-width: 620px) {
            .mail-wrapper {
                padding: 26px 12px 34px;
            }

            .mail-shell {
                padding: 5px;
                border-radius: 24px;
            }

            .mail-card {
                border-radius: 20px;
            }

            .mail-hero {
                padding: 30px 24px 16px;
            }

            .mail-body {
                padding: 0 24px 32px;
            }

            .mail-title {
                font-size: 22px;
            }

            .mail-button {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="mail-preheader">{{ $previewText }}</div>
    <div class="mail-wrapper">
        <a href="{{ config('app.url') }}" class="brand-pill" target="_blank" rel="noopener">
            <span class="brand-mark">F</span>
            <span class="brand-copy">
                <strong>{{ $brandName }}</strong>
                <small>Careers Operations</small>
            </span>
        </a>

        <div class="mail-shell">
            <div class="mail-card">
                <div class="mail-hero">
                    <div class="mail-eyebrow">{{ $eyebrowText }}</div>
                    <h1 class="mail-title">{{ $pageTitle }}</h1>
                </div>

                <div class="mail-body">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <div class="mail-footer">
            © {{ date('Y') }} {{ $brandName }}. Email này được gửi tự động từ hệ thống tuyển dụng.
        </div>
    </div>
</body>
</html>

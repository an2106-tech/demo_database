@php
    $brandName = config('mail.from.name', config('app.name', 'FPT Career'));
    $pageTitle = $title ?? $brandName;
    $eyebrowText = $eyebrow ?? null;
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
            background-color: #ffffff;
            color: #171717;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a {
            color: #171717;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .mail-wrapper {
            width: 100%;
            background-color: #ffffff;
            padding: 40px 16px;
            box-sizing: border-box;
        }

        .mail-preheader {
            display: none !important;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            color: transparent;
            mso-hide: all;
        }

        .mail-container {
            max-width: 560px;
            margin: 0 auto;
        }

        .mail-header {
            padding-bottom: 20px;
            border-bottom: 1px solid #ebebeb;
            margin-bottom: 28px;
        }

        .brand-logo {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #171717;
            text-decoration: none;
            display: inline-block;
        }

        .mail-eyebrow {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #737373;
            margin-bottom: 8px;
        }

        .mail-title {
            margin: 0 0 20px 0;
            color: #111111;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -0.02em;
            line-height: 1.35;
        }

        .mail-body {
            color: #333333;
            font-size: 14px;
            line-height: 1.7;
        }

        .mail-body p {
            margin: 0 0 16px 0;
            color: #333333;
            font-size: 14px;
            line-height: 1.7;
        }

        .mail-body strong {
            color: #111111;
            font-weight: 600;
        }

        .mail-body ul {
            margin: 16px 0;
            padding-left: 20px;
            color: #333333;
        }

        .mail-body li {
            margin-bottom: 8px;
            color: #333333;
            font-size: 14px;
            line-height: 1.65;
        }

        .mail-panel,
        .job-card,
        .info-card {
            margin: 24px 0;
            padding: 18px 20px;
            border: 1px solid #ebebeb;
            border-radius: 8px;
            background-color: #fafafa;
        }

        .mail-panel p:last-child,
        .job-card p:last-child,
        .info-card p:last-child {
            margin-bottom: 0;
        }

        .info-item,
        .job-detail {
            display: block;
            padding: 6px 0;
            border-bottom: 1px solid #f0f0f0;
            color: #737373;
            font-size: 13px;
            line-height: 1.5;
        }

        .info-item:first-child,
        .job-detail:first-child {
            padding-top: 0;
        }

        .info-item:last-child,
        .job-detail:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-value {
            color: #111111;
            font-weight: 600;
            float: right;
        }

        .mail-actions {
            margin: 28px 0 12px;
        }

        .mail-button {
            display: inline-block;
            padding: 11px 24px;
            border-radius: 6px;
            background-color: #111111;
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.01em;
            text-decoration: none;
            text-align: center;
        }

        .mail-button span {
            display: inline-block;
            vertical-align: middle;
        }

        .mail-button i {
            display: none;
        }

        .mail-button--orange,
        .mail-button--green,
        .mail-button--red {
            background-color: #111111;
            color: #ffffff !important;
        }

        .mail-footer {
            margin-top: 36px;
            padding-top: 20px;
            border-top: 1px solid #ebebeb;
            color: #8c8c8c;
            font-size: 12px;
            line-height: 1.6;
        }

        @media only screen and (max-width: 600px) {
            .mail-wrapper {
                padding: 24px 16px;
            }

            .info-value {
                float: none;
                display: block;
                margin-top: 2px;
            }

            .mail-button {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="mail-preheader">{{ $previewText }}</div>
    <div class="mail-wrapper">
        <div class="mail-container">
            <div class="mail-header">
                <a href="{{ config('app.url') }}" class="brand-logo" target="_blank" rel="noopener">
                    {{ strtoupper($brandName) }}
                </a>
            </div>

            @if($eyebrowText)
                <div class="mail-eyebrow">{{ $eyebrowText }}</div>
            @endif

            <h1 class="mail-title">{{ $pageTitle }}</h1>

            <div class="mail-body">
                {{ $slot }}
            </div>

            <div class="mail-footer">
                © {{ date('Y') }} {{ $brandName }}. Đây là email tự động từ hệ thống tuyển dụng.
            </div>
        </div>
    </div>
</body>
</html>

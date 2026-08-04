<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 42px 46px 40px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.55; }
        .header-table, .terms-table, .signature-table { width: 100%; border-collapse: collapse; }
        .brand { color: #d95d08; font-size: 19px; font-weight: bold; margin: 0; }
        .brand-subtitle { color: #64748b; font-size: 9px; margin-top: 2px; }
        .document-meta { text-align: right; font-size: 9px; color: #64748b; line-height: 1.7; }
        .document-meta strong { color: #334155; }
        .rule { border: 0; border-top: 2px solid #f97316; margin: 14px 0 18px; }
        h1 { color: #172033; font-size: 18px; letter-spacing: .4px; text-align: center; margin: 0 0 6px; }
        .document-caption { color: #64748b; font-size: 9px; text-align: center; margin: 0 0 18px; }
        .intro { margin: 0 0 14px; }
        .section-title { color: #172033; font-size: 12px; font-weight: bold; margin: 18px 0 7px; }
        .terms-table { table-layout: fixed; margin-bottom: 16px; }
        .terms-table td { border: 1px solid #dbe3ed; padding: 7px 9px; vertical-align: top; }
        .terms-label { width: 22%; background: #f8fafc; color: #64748b; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .terms-value { width: 28%; color: #172033; font-weight: bold; }
        .letter-body { margin-top: 8px; }
        .letter-body p { margin: 0 0 9px; }
        .letter-body ul { margin: 5px 0 10px 18px; padding: 0; }
        .response-note { background: #fff7ed; border-left: 3px solid #f97316; color: #4b5563; margin-top: 16px; padding: 9px 11px; }
        .signature-table { margin-top: 30px; table-layout: fixed; }
        .signature-table td { width: 50%; vertical-align: top; padding: 0 24px 0 0; }
        .signature-table td:last-child { padding: 0 0 0 24px; text-align: right; }
        .signature-role { color: #475569; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .signature-space { height: 52px; }
        .signature-name { border-top: 1px solid #475569; color: #172033; font-weight: bold; padding-top: 6px; }
        .signature-caption { color: #64748b; font-size: 8.5px; margin-top: 3px; }
        .footer { border-top: 1px solid #dbe3ed; color: #64748b; font-size: 8.5px; margin-top: 22px; padding-top: 8px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <p class="brand">FPT Career</p>
                <div class="brand-subtitle">Hệ thống tuyển dụng FPT Education</div>
            </td>
            <td class="document-meta">
                <div>Số: <strong>{{ $offerReference }}</strong></div>
                <div>Ngày phát hành: <strong>{{ $issuedAt->format('d/m/Y') }}</strong></div>
            </td>
        </tr>
    </table>

    <hr class="rule">

    <h1>THƯ MỜI NHẬN VIỆC</h1>
    <p class="document-caption">Đề nghị tuyển dụng được phê duyệt trên hệ thống</p>

    <p class="intro">Kính gửi <strong>{{ $candidateName ?: 'Ứng viên' }}</strong>,</p>
    <p class="intro">FPT Career trân trọng gửi đến bạn thư mời nhận việc với các điều khoản được tóm tắt dưới đây.</p>

    <div class="section-title">Thông tin đề nghị</div>
    <table class="terms-table">
        <tr>
            <td class="terms-label">Ứng viên</td>
            <td class="terms-value">{{ $candidateName ?: '-' }}</td>
            <td class="terms-label">Vị trí</td>
            <td class="terms-value">{{ $offer->application?->job?->title ?? '-' }}</td>
        </tr>
        <tr>
            <td class="terms-label">Đơn vị tuyển dụng</td>
            <td class="terms-value">{{ $offer->application?->job?->branch?->name ?? '-' }}</td>
            <td class="terms-label">Mức lương đề nghị</td>
            <td class="terms-value">{{ number_format((float) $offer->salary_offered, 0, ',', '.') }} VND</td>
        </tr>
        <tr>
            <td class="terms-label">Ngày nhận việc dự kiến</td>
            <td class="terms-value">{{ $offer->start_date?->format('d/m/Y') ?? '-' }}</td>
            <td class="terms-label">Thời gian thử việc</td>
            <td class="terms-value">{{ (int) $offer->probation_months === 0 ? 'Không áp dụng' : $offer->probation_months.' tháng' }}</td>
        </tr>
        <tr>
            <td class="terms-label">Hạn phản hồi</td>
            <td class="terms-value" colspan="3">{{ $responseDeadline?->format('H:i, d/m/Y') ?? '-' }}</td>
        </tr>
    </table>

    <div class="letter-body">{!! $letterInnerHtml !!}</div>

    <div class="response-note">
        Vui lòng phản hồi thư mời trước thời hạn nêu trên bằng liên kết trong email. Bộ phận tuyển dụng sẽ liên hệ để hướng dẫn thủ tục nhận việc sau khi bạn xác nhận.
    </div>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-role">Ứng viên</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $candidateName ?: 'Ứng viên' }}</div>
                <div class="signature-caption">Ký xác nhận khi được yêu cầu lưu bản ký</div>
            </td>
            <td>
                <div class="signature-role">Đại diện đơn vị tuyển dụng</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $approverName }}</div>
                <div class="signature-caption">{{ $approverTitle }} · Đã phê duyệt trên hệ thống</div>
            </td>
        </tr>
    </table>

    <p class="footer">Thư mời này không thay thế hợp đồng lao động chính thức.</p>
</body>
</html>

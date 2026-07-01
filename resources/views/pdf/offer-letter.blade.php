<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
            line-height: 1.45;
        }
        .brand {
            border-bottom: 2px solid #f97316;
            margin-bottom: 18px;
            padding-bottom: 10px;
        }
        .brand-name {
            color: #f97316;
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        .brand-meta {
            color: #555;
            font-size: 10px;
            margin-top: 4px;
        }
        h1 {
            font-size: 17px;
            text-align: center;
            margin: 0 0 18px;
            letter-spacing: 0.5px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 18px;
        }
        .summary-table td {
            border: 1px solid #ddd;
            padding: 7px 8px;
            vertical-align: top;
        }
        .summary-label {
            width: 30%;
            color: #555;
            background: #f8fafc;
            font-weight: bold;
        }
        .letter-body {
            text-align: justify;
        }
        .letter-body p {
            margin: 0 0 10px;
        }
        .footer-note {
            font-size: 9px;
            color: #555;
            margin-top: 20px;
        }
        .sig-table {
            width: 100%;
            margin-top: 36px;
            border-collapse: collapse;
        }
        .sig-table td {
            width: 50%;
            vertical-align: top;
            padding: 10px 8px 0 0;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin-top: 56px;
            padding-top: 6px;
        }
        .stamp {
            border: 2px solid #b91c1c;
            color: #b91c1c;
            display: inline-block;
            padding: 10px 18px;
            font-weight: bold;
            font-size: 11px;
            margin: 8px 0 16px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
<div class="brand">
    <p class="brand-name">FPT Career</p>
    <div class="brand-meta">Hệ thống quản lý tuyển dụng và kết nối ứng viên</div>
</div>

<h1>ĐỀ NGHỊ TUYỂN DỤNG</h1>

<table class="summary-table">
    <tr>
        <td class="summary-label">Mã đề nghị</td>
        <td>#{{ $offer->id }}</td>
        <td class="summary-label">Ngày phát hành</td>
        <td>{{ $offer->created_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td class="summary-label">Ứng viên</td>
        <td>{{ $candidateName ?: '-' }}</td>
        <td class="summary-label">Vị trí</td>
        <td>{{ $offer->application?->job?->title ?? '-' }}</td>
    </tr>
    <tr>
        <td class="summary-label">Chi nhánh/đơn vị</td>
        <td>{{ $offer->application?->job?->branch?->name ?? '-' }}</td>
        <td class="summary-label">Hạn phản hồi</td>
        <td>{{ $offer->expires_at?->format('d/m/Y H:i') ?? '-' }}</td>
    </tr>
</table>

<div class="letter-body">{!! $letterInnerHtml !!}</div>

<table class="sig-table">
    <tr>
        <td>
            <div class="sig-line">
                Ứng viên<br>
                <strong>{{ $candidateName }}</strong><br>
                <span class="footer-note">Ký và ghi rõ họ tên</span>
            </div>
        </td>
        <td style="text-align: right; padding-right: 0; padding-left: 8px;">
            <div class="stamp">XÁC NHẬN HỆ THỐNG</div>
            <div class="sig-line" style="text-align: right;">
                Đại diện tuyển dụng<br>
                <strong>FPT Career</strong><br>
                <span class="footer-note">Xác nhận trên hệ thống</span>
            </div>
        </td>
    </tr>
</table>
<p class="footer-note">
    Văn bản này là đề nghị tuyển dụng được tạo từ hệ thống FPT Career và không thay thế hợp đồng lao động chính thức.
</p>
</body>
</html>

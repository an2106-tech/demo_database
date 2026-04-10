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
        h1 {
            font-size: 17px;
            text-align: center;
            margin: 0 0 18px;
            letter-spacing: 0.5px;
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
<h1>THƯ MỜI NHẬN VIỆC</h1>
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
            <div class="stamp">CHỮ KÝ MINH HỌA</div>
            <div class="sig-line" style="text-align: right;">
                Đại diện công ty<br>
                <span class="footer-note">Chữ ký ảo / demo — không có giá trị pháp lý</span>
            </div>
        </td>
    </tr>
</table>
<p class="footer-note">
    Văn bản được tạo tự động từ hệ thống. Vui lòng đối chiếu email và hợp đồng chính thức (nếu có).
</p>
</body>
</html>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; background-color: #f8fafc; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding-bottom: 40px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; margin-top: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #F37021 0%, #ff8a1d 100%); color: #ffffff; padding: 40px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px; text-transform: uppercase; }
        .header p { margin: 10px 0 0; opacity: 0.9; font-size: 16px; }
        .content { padding: 40px 30px; }
        .content p { margin-bottom: 20px; font-size: 16px; }
        .content strong { color: #0f172a; }
        .info-card { background-color: #f1f5f9; border-radius: 12px; padding: 25px; margin: 25px 0; border: 1px solid #e2e8f0; }
        .info-item { display: block; margin-bottom: 8px; font-size: 14px; color: #64748b; }
        .info-value { color: #1e293b; font-weight: 700; font-size: 15px; }
        .footer { text-align: center; padding: 30px; color: #94a3b8; font-size: 13px; }
        .footer a { color: #F37021; text-decoration: none; font-weight: 600; }
        .status-badge { display: inline-block; background: #fff7ed; color: #c2410c; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; border: 1px solid #ffedd5; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Xác nhận ứng tuyển</h1>
                <p>Cảm ơn bạn đã quan tâm đến cơ hội nghề nghiệp tại {{ config('app.name') }}</p>
            </div>
            <div class="content">
                <div class="status-badge">Hồ sơ đã được tiếp nhận thành công</div>
                
                {!! $htmlBody !!}

                <p style="margin-top: 30px;">Bạn có thể theo dõi trạng thái hồ sơ của mình tại Dashboard cá nhân.</p>
                
                <div style="text-align: center; margin-top: 40px;">
                    <a href="{{ route('candidates.candidate_dashboard') }}" style="background-color: #F37021; color: #ffffff !important; padding: 14px 30px; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(243, 112, 33, 0.3);">
                        Truy cập Dashboard
                    </a>
                </div>
            </div>
            <div class="footer">
                <p>&copy; {{ date('Y') }} <strong>{{ config('app.name') }}</strong>. Mọi quyền được bảo lưu.</p>
                <p>Hệ thống Tuyển dụng Chuyên nghiệp FPT</p>
                <p><a href="{{ config('app.url') }}">Website của chúng tôi</a></p>
            </div>
        </div>
    </div>
</body>
</html>

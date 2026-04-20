<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả phê duyệt</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { max-width: 500px; width: 90%; background: #ffffff; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .icon { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 32px; }
        .icon.success { background-color: #dcfce7; color: #166534; }
        .icon.error { background-color: #fee2e2; color: #991b1b; }
        h1 { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 16px; }
        p { color: #64748b; font-size: 16px; margin-bottom: 32px; }
        .btn { display: inline-block; background-color: #F37021; color: #ffffff !important; text-decoration: none; padding: 12px 32px; border-radius: 12px; font-weight: 700; transition: all 0.3s; box-shadow: 0 4px 12px rgba(243, 112, 33, 0.2); }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(243, 112, 33, 0.3); }
        .job-title { font-weight: 700; color: #1e293b; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon {{ $success ? 'success' : 'error' }}">
            {!! $success ? '&#10004;' : '&#10006;' !!}
        </div>
        <h1>{{ $success ? 'Thành công!' : 'Thông báo' }}</h1>
        <p>{{ $message }}</p>
        
        @if($job)
            <div style="margin-bottom: 30px; font-size: 14px; color: #94a3b8;">
                Tin đăng: <span class="job-title">{{ $job->title }}</span>
            </div>
        @endif

        <a href="{{ route('home') }}" class="btn">Quay lại Trang chủ</a>
    </div>
</body>
</html>

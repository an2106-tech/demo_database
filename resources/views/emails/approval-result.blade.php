<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả phê duyệt</title>
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            background-color: #ffffff;
            color: #171717;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .card {
            width: min(90vw, 460px);
            padding: 40px 36px;
            border: 1px solid #ebebeb;
            border-radius: 8px;
            background-color: #ffffff;
            text-align: center;
            box-sizing: border-box;
        }

        .icon {
            width: 44px;
            height: 44px;
            margin: 0 auto 20px;
            border-radius: 9999px;
            font-size: 18px;
            font-weight: 700;
            line-height: 44px;
            background: #171717;
            color: #ffffff;
        }

        .icon.error {
            background: #ffffff;
            color: #171717;
            border: 1px solid #171717;
        }

        h1 {
            margin: 0 0 10px;
            color: #111111;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        p {
            margin: 0 0 20px;
            color: #555555;
            font-size: 14px;
            line-height: 1.6;
        }

        .job-title {
            display: inline-block;
            margin-bottom: 24px;
            padding: 6px 14px;
            border-radius: 4px;
            background-color: #f5f5f5;
            color: #171717;
            font-size: 12px;
            font-weight: 500;
        }

        .btn {
            display: inline-block;
            padding: 11px 24px;
            border-radius: 6px;
            background-color: #171717;
            color: #ffffff;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            letter-spacing: 0.01em;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon {{ $success ? 'success' : 'error' }}">
            {!! $success ? '&#10004;' : '&#10006;' !!}
        </div>

        <h1>{{ $success ? 'Thành công' : 'Thông báo' }}</h1>
        <p>{{ $message }}</p>

        @if($job)
            <div class="job-title">{{ $job->title }}</div>
        @endif

        <div>
            <a href="{{ route('home') }}" class="btn">Quay lại trang chủ</a>
        </div>
    </div>
</body>
</html>

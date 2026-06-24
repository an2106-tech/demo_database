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
            background: #f7f3ec;
            color: #283044;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
        }

        .shell {
            width: min(92vw, 540px);
            padding: 8px;
            border-radius: 30px;
            background: #efe7dc;
        }

        .card {
            padding: 42px;
            border: 1px solid #f4eadf;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 22px 70px rgba(81, 52, 28, .12);
            text-align: center;
        }

        .icon {
            width: 74px;
            height: 74px;
            margin: 0 auto 24px;
            border-radius: 999px;
            font-size: 30px;
            font-weight: 900;
            line-height: 74px;
        }

        .icon.success {
            background: #dcfce7;
            color: #166534;
        }

        .icon.error {
            background: #fee2e2;
            color: #991b1b;
        }

        h1 {
            margin: 0 0 14px;
            color: #111827;
            font-size: 26px;
            font-weight: 850;
            letter-spacing: -.02em;
        }

        p {
            margin: 0 0 28px;
            color: #64748b;
            font-size: 16px;
            line-height: 1.7;
        }

        .job-title {
            display: inline-block;
            margin-bottom: 30px;
            padding: 10px 14px;
            border-radius: 999px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 13px;
            font-weight: 800;
        }

        .btn {
            display: inline-block;
            padding: 6px 7px 6px 22px;
            border-radius: 999px;
            background: #111827;
            color: #ffffff;
            font-size: 14px;
            font-weight: 900;
            text-decoration: none;
        }

        .btn span {
            display: inline-block;
            padding-right: 14px;
            line-height: 34px;
            vertical-align: middle;
        }

        .btn i {
            display: inline-block;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .16);
            font-style: normal;
            line-height: 34px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="shell">
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
                <a href="{{ route('home') }}" class="btn"><span>Quay lại trang chủ</span><i>→</i></a>
            </div>
        </div>
    </div>
</body>
</html>

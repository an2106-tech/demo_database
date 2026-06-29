<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            width: 100%;
            max-width: 680px;
            background: #ffffff;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.10);
            border: 1px solid #e2e8f0;
        }

        .badge {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .badge.success { background: #dcfce7; color: #166534; }
        .badge.warning { background: #fef3c7; color: #92400e; }
        .badge.info { background: #dbeafe; color: #1d4ed8; }
        .badge.expired { background: #fee2e2; color: #b91c1c; }

        h1 {
            margin: 0 0 12px;
            font-size: 28px;
        }

        p {
            margin: 0 0 12px;
            line-height: 1.7;
        }

        .meta {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #475569;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="card">
            <span class="badge {{ $status }}">{{ strtoupper($status) }}</span>
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>

            <div class="meta">
                <p><strong>Hồ sơ:</strong> #{{ $application->id }}</p>
                <p><strong>Trạng thái thư mời:</strong> {{ $offer->status }}</p>
                <p><strong>Trạng thái hồ sơ:</strong> {{ $application->status?->getLabel() ?? $application->status }}</p>
            </div>
        </div>
    </div>
</body>
</html>

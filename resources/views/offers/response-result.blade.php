@php
    $offerLabels = [
        'draft' => 'Bản nháp',
        'awaiting_approval' => 'Chờ duyệt',
        'pending' => 'Chờ phản hồi',
        'accepted' => 'Đã đồng ý',
        'declined' => 'Đã từ chối',
        'rejected' => 'Giám đốc từ chối',
        'expired' => 'Đã hết hạn',
    ];

    $applicationStatus = $application->status instanceof \App\Enums\StatusApplicationEnum
        ? $application->status->getLabel()
        : (string) $application->status;

    $jobTitle = $application->job?->title ?? 'Vị trí ứng tuyển';
    $branchName = $application->job?->branch?->name ?? 'Chi nhánh tuyển dụng';
    $badgeLabels = [
        'success' => 'Hoàn tất',
        'warning' => 'Đã ghi nhận',
        'info' => 'Thông tin',
        'expired' => 'Không còn hiệu lực',
    ];
@endphp

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
            max-width: 720px;
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
            line-height: 1.25;
        }

        p {
            margin: 0 0 12px;
            line-height: 1.7;
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .item {
            padding: 14px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .item span {
            display: block;
            color: #64748b;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .item strong {
            display: block;
            color: #0f172a;
            font-size: 15px;
        }

        @media (max-width: 640px) {
            .card { padding: 24px; }
            .meta { grid-template-columns: 1fr; }
            h1 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="card">
            <span class="badge {{ $status }}">{{ $badgeLabels[$status] ?? 'Thông tin' }}</span>
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>

            <div class="meta">
                <div class="item">
                    <span>Vị trí</span>
                    <strong>{{ $jobTitle }}</strong>
                </div>
                <div class="item">
                    <span>Chi nhánh</span>
                    <strong>{{ $branchName }}</strong>
                </div>
                <div class="item">
                    <span>Trạng thái thư mời</span>
                    <strong>{{ $offerLabels[$offer->status] ?? $offer->status }}</strong>
                </div>
                <div class="item">
                    <span>Trạng thái hồ sơ</span>
                    <strong>{{ $applicationStatus }}</strong>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

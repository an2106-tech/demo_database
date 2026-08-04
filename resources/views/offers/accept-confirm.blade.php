@php
    $candidateName = $application->candidate?->name ?? 'Ứng viên';
    $jobTitle = $application->job?->title ?? 'vị trí ứng tuyển';
    $branchName = $application->job?->branch?->name ?? 'chi nhánh tuyển dụng';
@endphp

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đồng ý đề nghị tuyển dụng</title>
    <style>
        body { margin: 0; background: #f8fafc; color: #0f172a; font-family: Arial, sans-serif; }
        .shell { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { width: 100%; max-width: 640px; box-sizing: border-box; border: 1px solid #e2e8f0; border-radius: 20px; background: #fff; padding: 32px; box-shadow: 0 18px 50px rgba(15, 23, 42, .10); }
        .eyebrow { display: inline-block; margin-bottom: 16px; border-radius: 999px; background: #dcfce7; padding: 8px 14px; color: #166534; font-size: 13px; font-weight: 700; }
        h1 { margin: 0 0 12px; font-size: 28px; line-height: 1.25; }
        p { margin: 0; color: #475569; font-size: 16px; line-height: 1.7; }
        .summary { margin: 24px 0; border: 1px solid #e2e8f0; border-radius: 14px; background: #f8fafc; padding: 16px; }
        .summary span { display: block; color: #64748b; font-size: 13px; }
        .summary strong { display: block; margin-top: 5px; color: #0f172a; font-size: 16px; line-height: 1.45; }
        .notice { margin-bottom: 24px; color: #64748b; font-size: 14px; line-height: 1.6; }
        .actions { display: flex; justify-content: flex-end; gap: 12px; }
        .button { border: 0; border-radius: 999px; padding: 12px 20px; font-size: 15px; font-weight: 700; cursor: pointer; }
        .button.primary { background: #16a34a; color: #fff; }
        .button.primary[disabled] { cursor: wait; opacity: .7; }
        @media (max-width: 640px) { .card { padding: 24px; } .actions { display: block; } .button { width: 100%; } h1 { font-size: 24px; } }
    </style>
</head>
<body>
    <main class="shell">
        <section class="card">
            <span class="eyebrow">Phản hồi thư mời</span>
            <h1>Xác nhận đồng ý nhận việc</h1>
            <p>Chào {{ $candidateName }}, thao tác này sẽ xác nhận bạn đồng ý đề nghị tuyển dụng.</p>

            <div class="summary">
                <span>Vị trí ứng tuyển</span>
                <strong>{{ $jobTitle }}</strong>
                <span style="margin-top:12px;">Đơn vị tuyển dụng</span>
                <strong>{{ $branchName }}</strong>
            </div>

            <p class="notice">Sau khi xác nhận, bộ phận tuyển dụng sẽ liên hệ để hướng dẫn các thủ tục nhận việc tiếp theo.</p>

            <form id="accept-offer-form" method="POST" action="{{ request()->fullUrl() }}">
                @csrf
                <div class="actions">
                    <button id="accept-offer-button" class="button primary" type="submit">Xác nhận đồng ý</button>
                </div>
            </form>
        </section>
    </main>

    <script>
        document.getElementById('accept-offer-form')?.addEventListener('submit', function () {
            const button = document.getElementById('accept-offer-button');
            button.disabled = true;
            button.textContent = 'Đang xác nhận...';
        });
    </script>
</body>
</html>

@php
    $jobTitle = $application->job?->title ?? 'Vị trí ứng tuyển';
    $branchName = $application->job?->branch?->name ?? 'Chi nhánh tuyển dụng';
@endphp

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận từ chối đề nghị tuyển dụng</title>
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
            max-width: 760px;
            background: #ffffff;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.10);
            border: 1px solid #e2e8f0;
        }

        .eyebrow {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            background: #fff7ed;
            color: #c2410c;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 28px;
            line-height: 1.25;
        }

        p {
            margin: 0 0 12px;
            line-height: 1.7;
            color: #475569;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin: 22px 0;
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

        .form {
            margin-top: 20px;
            padding-top: 22px;
            border-top: 1px solid #e2e8f0;
        }

        .field {
            margin-bottom: 18px;
        }

        .conditional-field {
            display: none;
        }

        .conditional-field.is-visible {
            display: block;
        }

        .field label,
        .legend {
            display: block;
            font-weight: 700;
            margin-bottom: 10px;
            color: #0f172a;
        }

        .reasons {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .reason {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            cursor: pointer;
            background: #ffffff;
        }

        .reason input {
            margin-top: 2px;
        }

        textarea {
            width: 100%;
            box-sizing: border-box;
            min-height: 120px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 12px 14px;
            font: inherit;
            resize: vertical;
        }

        input[type="text"],
        input[type="date"] {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 12px 14px;
            font: inherit;
        }

        textarea:focus,
        input[type="text"]:focus,
        input[type="date"]:focus,
        .reason:has(input:checked) {
            outline: 2px solid #fb923c;
            border-color: #fb923c;
        }

        .error {
            margin-top: 8px;
            color: #dc2626;
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
        }

        .button {
            border: 0;
            border-radius: 999px;
            padding: 12px 20px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
        }

        .button.primary {
            background: #dc2626;
            color: #ffffff;
        }

        .button.secondary {
            background: #f1f5f9;
            color: #334155;
        }

        @media (max-width: 640px) {
            .card { padding: 24px; }
            .summary,
            .reasons { grid-template-columns: 1fr; }
            .actions { flex-direction: column-reverse; }
            .button { text-align: center; }
            h1 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="card">
            <span class="eyebrow">Phản hồi thư mời</span>
            <h1>Xác nhận từ chối đề nghị tuyển dụng</h1>
            <p>Vui lòng cho biết lý do để bộ phận tuyển dụng có thể ghi nhận và hỗ trợ nếu cần.</p>

            <div class="summary">
                <div class="item">
                    <span>Vị trí</span>
                    <strong>{{ $jobTitle }}</strong>
                </div>
                <div class="item">
                    <span>Chi nhánh</span>
                    <strong>{{ $branchName }}</strong>
                </div>
                <div class="item">
                    <span>Mức lương đề nghị</span>
                    <strong>{{ $offer->salary_offered ? number_format((float) $offer->salary_offered, 0, ',', '.').' VND' : '-' }}</strong>
                </div>
                <div class="item">
                    <span>Ngày bắt đầu dự kiến</span>
                    <strong>{{ $offer->start_date?->format('d/m/Y') ?? '-' }}</strong>
                </div>
            </div>

            <form id="decline-offer-form" class="form" method="POST" action="{{ request()->fullUrl() }}">
                @csrf

                <div class="field">
                    <span class="legend">Lý do từ chối</span>
                    <div class="reasons">
                        @foreach ($declineReasons as $value => $label)
                            <label class="reason">
                                <input
                                    type="radio"
                                    name="decline_reason"
                                    value="{{ $value }}"
                                    @checked(old('decline_reason') === $value)
                                >
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('decline_reason')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field conditional-field" data-reason-field="compensation">
                    <label for="expected_compensation">Mức đãi ngộ mong muốn</label>
                    <input
                        id="expected_compensation"
                        type="text"
                        name="expected_compensation"
                        value="{{ old('expected_compensation') }}"
                        placeholder="Ví dụ: khoảng 12.000.000 VND hoặc mong muốn trao đổi thêm"
                    >
                    @error('expected_compensation')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field conditional-field" data-reason-field="start_date">
                    <label for="preferred_start_date">Thời gian có thể bắt đầu phù hợp hơn</label>
                    <input
                        id="preferred_start_date"
                        type="date"
                        name="preferred_start_date"
                        value="{{ old('preferred_start_date') }}"
                        min="{{ now()->toDateString() }}"
                    >
                    @error('preferred_start_date')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="decline_note">Ghi chú thêm</label>
                    <textarea id="decline_note" name="decline_note" placeholder="Bạn có thể chia sẻ thêm nếu muốn bộ phận tuyển dụng nắm rõ hơn.">{{ old('decline_note') }}</textarea>
                    @error('decline_note')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="actions">
                    <a class="button secondary" href="{{ config('app.url') }}">Quay lại</a>
                    <button id="decline-offer-button" class="button primary" type="submit">Xác nhận từ chối</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const reasonInputs = document.querySelectorAll('input[name="decline_reason"]');
        const conditionalFields = document.querySelectorAll('[data-reason-field]');
        const noteInput = document.getElementById('decline_note');

        function syncReasonFields() {
            const selectedReason = document.querySelector('input[name="decline_reason"]:checked')?.value;

            conditionalFields.forEach((field) => {
                field.classList.toggle('is-visible', field.dataset.reasonField === selectedReason);
            });

            noteInput.placeholder = selectedReason === 'other'
                ? 'Vui lòng chia sẻ lý do để bộ phận tuyển dụng ghi nhận chính xác hơn.'
                : 'Bạn có thể chia sẻ thêm nếu muốn bộ phận tuyển dụng nắm rõ hơn.';
        }

        reasonInputs.forEach((input) => input.addEventListener('change', syncReasonFields));
        syncReasonFields();

        document.getElementById('decline-offer-form')?.addEventListener('submit', function () {
            const button = document.getElementById('decline-offer-button');
            button.disabled = true;
            button.textContent = 'Đang gửi phản hồi...';
        });
    </script>
</body>
</html>

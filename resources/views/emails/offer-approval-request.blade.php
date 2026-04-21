@php
    $appName = config('app.name');
@endphp

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="background: #f8f9fa; padding: 24px; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h1 style="margin: 0 0 16px; font-size: 22px; color: #111827;">Yeu cau duyet offer</h1>

        <p style="font-size: 15px; margin: 0 0 16px;">
            Chao <strong>{{ $recipientName }}</strong>,
        </p>

        <p style="margin: 0 0 20px; line-height: 1.6; color: #4b5563;">
            HR da tao mot offer moi va can ban xem chi tiet truoc khi duyet.
        </p>

        <div style="background: #ffffff; padding: 16px; border-radius: 6px; border: 1px solid #e5e7eb; margin: 0 0 24px;">
            <p style="margin: 0 0 8px;"><strong>Ung vien:</strong> {{ $candidateName }}</p>
            <p style="margin: 0 0 8px;"><strong>Vi tri:</strong> {{ $jobTitle }}</p>
            <p style="margin: 0;"><strong>Muc luong:</strong> {{ $salaryOffered }}</p>
        </div>

        <div style="text-align: center; margin: 0 0 24px;">
            <a href="{{ $approvalUrl }}" style="display: inline-block; padding: 12px 24px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">
                Xem chi tiet
            </a>
        </div>

        <p style="margin: 0; color: #6b7280; font-size: 12px;">
            Email tu dong tu he thong {{ $appName }}.
        </p>
    </div>
</div>

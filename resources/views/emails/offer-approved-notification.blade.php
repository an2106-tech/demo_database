@php
    $app_name = config('app.name');
@endphp

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center;">
        <h1 style="margin: 0; font-size: 24px;">✅ Offer đã được duyệt</h1>
    </div>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; border: 1px solid #e9ecef; border-top: none;">
        <p style="font-size: 16px; margin-bottom: 20px;">Chào <strong>{{ $recipientName }}</strong>,</p>

        <p>Offer cho ứng viên dưới đây đã được {{ $showSalary ? 'giám đốc chi nhánh' : '' }} duyệt và sẽ được gửi cho ứng viên.</p>

        <div style="background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #16a34a; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>📌 Ứng viên:</strong> {{ $candidateName }}</p>
            <p style="margin: 5px 0;"><strong>📧 Email:</strong> {{ $candidateEmail }}</p>
            <p style="margin: 5px 0;"><strong>💼 Vị trí:</strong> {{ $jobTitle }}</p>
            @if($showSalary)
            <p style="margin: 5px 0;"><strong>💰 Mức lương:</strong> {{ $salaryOffered }}</p>
            @endif
            <p style="margin: 5px 0;"><strong>📅 Ngày bắt đầu:</strong> {{ $startDate }}</p>
            <p style="margin: 5px 0;"><strong>⏳ Thời gian thử việc:</strong> {{ $probationMonths }} tháng</p>
        </div>

        @if(!$showSalary)
        <div style="background: #cfe2ff; padding: 12px; border-radius: 6px; border-left: 4px solid #0d6efd; margin: 20px 0;">
            <p style="margin: 0; font-size: 13px;">
                <strong>ℹ️ Lưu ý:</strong> Thông tin mức lương được bảo mật và chỉ hiển thị cho HR và giám đốc chi nhánh.
            </p>
        </div>
        @endif

        <div style="background: #e7f3ff; padding: 15px; border-radius: 6px; border-left: 4px solid #0066cc; margin: 20px 0;">
            <p style="margin: 0 0 10px; font-weight: bold;">📬 Hành động đã thực hiện:</p>
            <ul style="margin: 5px 0; padding-left: 20px;">
                <li>Offer letter (PDF) đã được gửi tới ứng viên</li>
                <li>Ứng viên có {{ $offer->expires_at?->diffInDays(now()) ?? '3' }} ngày để phản hồi</li>
                <li>HR và giám đốc chi nhánh đã được thông báo</li>
            </ul>
        </div>

        <hr style="border: none; border-top: 1px solid #e9ecef; margin: 20px 0;">

        <p style="color: #999; font-size: 12px; text-align: center;">
            Đây là email tự động từ hệ thống {{ $app_name }}. Vui lòng không trả lời email này.
        </p>
    </div>
</div>

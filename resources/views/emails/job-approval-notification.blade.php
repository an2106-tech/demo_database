<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background: #F37021; color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px; }
        .content { padding: 40px 30px; background: #fff; }
        .content p { margin-bottom: 20px; font-size: 16px; color: #4b5563; }
        .job-card { background: #f8fafc; border: 1px solid #edf2f7; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .job-card h2 { margin: 0 0 10px; color: #1e293b; font-size: 18px; }
        .job-detail { font-size: 14px; color: #64748b; margin-bottom: 5px; }
        .cta-button { display: inline-block; background: #F37021; color: #fff !important; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 16px; margin-top: 10px; transition: background 0.3s; }
        .footer { background: #f1f5f9; color: #94a3b8; padding: 20px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>YÊU CẦU PHÊ DUYỆT TIN</h1>
        </div>
        <div class="content">
            <p>Xin chào <strong>{{ $directorName }}</strong>,</p>
            <p>Bạn nhận được yêu cầu phê duyệt một tin tuyển dụng mới vừa được đăng tải bởi <strong>{{ $hrName }}</strong>.</p>
            
            <div class="job-card">
                <h2>{{ $jobTitle }}</h2>
                <div class="job-detail"><strong>Vị trí:</strong> {{ $jobTitle }}</div>
                <div class="job-detail"><strong>Chi nhánh:</strong> {{ $branchName }}</div>
                <div class="job-detail"><strong>Phòng ban:</strong> {{ $departmentName }}</div>
                <div class="job-detail"><strong>Người đăng:</strong> {{ $hrName }}</div>
            </div>

            <p>Vui lòng xem qua nội dung và bạn có thể phê duyệt nhanh bằng các nút bấm bên dưới để tin tuyển dụng được hiển thị công khai trên hệ thống.</p>
            
            <div style="text-align: center; margin-top: 30px;">
                <div style="display: inline-block; margin: 10px;">
                    <a href="{{ $approveUrl }}" class="cta-button" style="background: #F37021; box-shadow: 0 4px 12px rgba(243, 112, 33, 0.3);">Phê Duyệt Ngay</a>
                </div>
                <div style="display: inline-block; margin: 10px;">
                    <a href="{{ $rejectUrl }}" style="display: inline-block; background: #fff; color: #ef4444 !important; padding: 12px 26px; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 16px; border: 1px solid #ef4444; transition: all 0.3s;">Từ Chối Tin</a>
                </div>
            </div>

            <p style="text-align: center; margin-top: 25px;">
                <a href="{{ route('director.approve_jobs') }}" style="color: #64748b; text-decoration: underline; font-size: 13px;">Xem chi tiết trên Dashboad</a>
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Mọi quyền được bảo lưu.
        </div>
    </div>
</body>
</html>

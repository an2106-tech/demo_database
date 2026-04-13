<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CV - {{ $candidate->name }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5; /* Grey background for preview */
        }
        .cv-container {
            width: 210mm; /* A4 Width */
            min-height: 297mm; /* A4 Height */
            margin: 20px auto;
            background-color: #ffffff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            display: table;
            table-layout: fixed;
        }
        @media print {
            body { background: none; }
            .cv-container { margin: 0; box-shadow: none; width: 100%; }
        }
        
        /* Layout */
        .sidebar {
            width: 32%;
            background-color: #2c3e50;
            color: #ffffff;
            display: table-cell;
            vertical-align: top;
            padding: 40px 25px;
        }
        .main-content {
            width: 68%;
            background-color: #ffffff;
            display: table-cell;
            vertical-align: top;
            padding: 40px 40px;
        }

        /* Sidebar Elements */
        .avatar-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #f37021;
            object-fit: cover;
        }
        .sidebar-section {
            margin-bottom: 35px;
        }
        .sidebar-title {
            color: #f37021;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #f37021;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .contact-item {
            margin-bottom: 12px;
            font-size: 13px;
        }
        .contact-item i {
            display: block;
            color: #bdc3c7;
            font-style: normal;
            font-size: 11px;
            margin-bottom: 2px;
        }
        
        /* Main Content Elements */
        .header {
            margin-bottom: 40px;
        }
        .candidate-name {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
            text-transform: uppercase;
        }
        .candidate-title {
            font-size: 18px;
            color: #f37021;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            border-left: 5px solid #f37021;
            padding-left: 15px;
            margin: 30px 0 20px 0;
            text-transform: uppercase;
            background-color: #f8f9fa;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .experience-item, .education-item {
            margin-bottom: 25px;
        }
        .item-header {
            overflow: hidden;
            margin-bottom: 8px;
        }
        .item-title {
            font-weight: bold;
            font-size: 16px;
            color: #333;
        }
        .item-subtitle {
            font-style: italic;
            color: #f37021;
            font-weight: bold;
        }
        .item-date {
            float: right;
            color: #7f8c8d;
            font-size: 13px;
        }
        .item-description {
            color: #555;
            font-size: 13.5px;
            text-align: justify;
        }

        .skill-item {
            margin-bottom: 10px;
        }
        .skill-name {
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .skill-bar-container {
            background-color: #3e4f5f;
            height: 6px;
            border-radius: 3px;
            width: 100%;
        }
        .skill-bar-fill {
            background-color: #f37021;
            height: 6px;
            border-radius: 3px;
        }

        .summary-text {
            font-size: 14px;
            color: #444;
            text-align: justify;
        }

    </style>
</head>
<body>

<div class="cv-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="avatar-container">
            @php
                $avatar = $candidate->user?->avatar;
                $defaultAvatar = public_path('assets/img/avatar_detail.jpg');
                $avatarBase64 = '';
                $mimeType = 'image/jpeg';

                try {
                    if ($avatar) {
                        if (str_starts_with($avatar, 'http')) {
                            $content = file_get_contents($avatar);
                            $avatarBase64 = base64_encode($content);
                            $mimeType = 'image/png'; 
                        } else {
                            $localPath = storage_path('app/public/' . ltrim($avatar, '/'));
                            if (file_exists($localPath)) {
                                $avatarBase64 = base64_encode(file_get_contents($localPath));
                                $mimeType = mime_content_type($localPath);
                            } else {
                                $avatarBase64 = base64_encode(file_get_contents($defaultAvatar));
                            }
                        }
                    } else {
                        $avatarBase64 = base64_encode(file_get_contents($defaultAvatar));
                    }
                } catch (\Exception $e) {
                    $avatarBase64 = base64_encode(file_get_contents($defaultAvatar));
                }
            @endphp
            <img src="data:{{ $mimeType }};base64,{{ $avatarBase64 }}" class="avatar" alt="Avatar">
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Thông tin liên hệ</div>
            <div class="contact-item">
                <i>Số điện thoại</i>
                {{ $candidate->phone ?? 'Chưa cập nhật' }}
            </div>
            @if(isset($resume->personal_info['date_of_birth']))
            <div class="contact-item">
                <i>Ngày sinh</i>
                {{ \Carbon\Carbon::parse($resume->personal_info['date_of_birth'])->format('d/m/Y') }}
            </div>
            @endif
            @if(isset($resume->personal_info['gender']))
            <div class="contact-item">
                <i>Giới tính</i>
                {{ $resume->personal_info['gender'] }}
            </div>
            @endif
            <div class="contact-item">
                <i>Email</i>
                {{ $candidate->email }}
            </div>
            @if(isset($resume->personal_info['address']) || isset($resume->personal_info['city']))
            <div class="contact-item">
                <i>Địa chỉ</i>
                {{ $resume->personal_info['address'] ?? '' }}{{ isset($resume->personal_info['city']) ? ', ' . $resume->personal_info['city'] : '' }}
            </div>
            @endif
            @if(isset($resume->personal_info['website']))
            <div class="contact-item">
                <i>Website</i>
                {{ $resume->personal_info['website'] }}
            </div>
            @endif
        </div>

        @if(!empty($resume->skills))
        <div class="sidebar-section">
            <div class="sidebar-title">Kỹ năng</div>
            @foreach($resume->skills as $skill)
                <div class="skill-item">
                    <span class="skill-name">{{ $skill['name'] }}</span>
                    <div class="skill-bar-container">
                        @php
                            $levelMap = ['Cơ bản' => 33, 'Khá' => 66, 'Thành thạo' => 100, 'Chuyên gia' => 100];
                            $width = $levelMap[$skill['level'] ?? 'Khá'] ?? 60;
                        @endphp
                        <div class="skill-bar-fill" style="width: {{ $width }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        @if(!empty($resume->languages))
        <div class="sidebar-section">
            <div class="sidebar-title">Ngôn ngữ</div>
            @foreach($resume->languages as $lang)
                <div class="skill-item">
                    <span class="skill-name">{{ $lang['name'] }}</span>
                    <div class="skill-bar-container">
                        @php
                            $levelMap = ['Cơ bản' => 30, 'Giao tiếp' => 60, 'Thành thạo' => 100];
                            $width = $levelMap[$lang['level'] ?? 'Giao tiếp'] ?? 50;
                        @endphp
                        <div class="skill-bar-fill" style="width: {{ $width }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 class="candidate-name">{{ $candidate->name }}</h1>
            <div class="candidate-title">{{ $resume->profile_title ?? 'Chuyên viên' }}</div>
        </div>

        @if($resume->career_objective)
        <div class="section">
            <div class="section-title">Mục tiêu nghề nghiệp</div>
            <div class="summary-text">
                {!! nl2br(e($resume->career_objective)) !!}
            </div>
        </div>
        @endif

        @if(!empty($resume->experiences))
        <div class="section">
            <div class="section-title">Kinh nghiệm làm việc</div>
            @foreach($resume->experiences as $exp)
            <div class="experience-item">
                <div class="item-header">
                    <span class="item-title">{{ $exp['position'] }}</span>
                    <span class="item-date">{{ $exp['from'] }} - {{ $exp['to'] ?? 'Hiện tại' }}</span>
                </div>
                <div class="item-subtitle">{{ $exp['company'] }}</div>
                <div class="item-description">
                    {!! nl2br(e($exp['description'] ?? '')) !!}
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if(!empty($resume->educations))
        <div class="section">
            <div class="section-title">Học vấn</div>
            @foreach($resume->educations as $edu)
            <div class="education-item">
                <div class="item-header">
                    <span class="item-title">{{ $edu['degree'] }}</span>
                    <span class="item-date">{{ $edu['from'] }} - {{ $edu['to'] }}</span>
                </div>
                <div class="item-subtitle">{{ $edu['school'] }}</div>
                <div class="item-description">
                    {!! nl2br(e($edu['description'] ?? '')) !!}
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if(!empty($resume->achievements))
        <div class="section">
            <div class="section-title">Thành tích</div>
            @foreach($resume->achievements as $ach)
            <div class="experience-item">
                <div class="item-header">
                    <span class="item-title">{{ $ach['title'] }}</span>
                    <span class="item-date">{{ $ach['date'] }}</span>
                </div>
                <div class="item-description">
                    {{ $ach['description'] }}
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if(!empty($resume->certifications))
        <div class="section">
            <div class="section-title">Chứng chỉ</div>
            @foreach($resume->certifications as $cert)
            <div class="experience-item">
                <div class="item-header">
                    <span class="item-title">{{ $cert['name'] }}</span>
                    <span class="item-date">{{ $cert['date'] }}</span>
                </div>
                <div class="item-subtitle">{{ $cert['issuer'] }}</div>
                <div class="item-description">
                    {{ $cert['description'] }}
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if(!empty($resume->activities))
        <div class="section">
            <div class="section-title">Hoạt động</div>
            @foreach($resume->activities as $act)
            <div class="experience-item">
                <div class="item-header">
                    <span class="item-title">{{ $act['title'] }}</span>
                    <span class="item-date">{{ $act['from'] }} - {{ $act['to'] }}</span>
                </div>
                <div class="item-description">
                    {!! nl2br(e($act['description'] ?? '')) !!}
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if(!empty($resume->references))
        <div class="section">
            <div class="section-title">Người tham chiếu</div>
            @foreach($resume->references as $ref)
            <div class="experience-item">
                <div class="item-header">
                    <span class="item-title">{{ $ref['name'] }}</span>
                </div>
                <div class="item-subtitle">{{ $ref['position'] }} - {{ $ref['company'] }}</div>
                <div class="item-description">
                    SĐT: {{ $ref['phone'] }} | Email: {{ $ref['email'] }}
                    @if($ref['note']) <br> {{ $ref['note'] }} @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if(isset($resume->extra['text']) && $resume->extra['text'])
        <div class="section">
            <div class="section-title">Thông tin bổ sung</div>
            <div class="summary-text">
                {!! nl2br(e($resume->extra['text'])) !!}
            </div>
        </div>
        @endif

    </div>
</div>

</body>
</html>

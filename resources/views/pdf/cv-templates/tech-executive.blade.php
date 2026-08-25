<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CV - {{ $candidate->name ?? 'Ứng viên' }} (Tech Executive)</title>
    <style>
        @page {
            margin: 0;
            size: A4 portrait;
        }
        * {
            box-sizing: border-box;
            font-family: 'DejaVu Sans', sans-serif !important;
        }
        html, body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-family: 'DejaVu Sans', sans-serif !important;
            font-size: 10px;
            line-height: 1.38;
            color: #1e293b;
        }
        .hero {
            background-color: #0f172a !important;
            color: #ffffff !important;
            padding: 16px 24px;
            display: table;
            width: 100%;
        }
        .hero-left {
            display: table-cell;
            vertical-align: middle;
            width: 75%;
        }
        .hero-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 25%;
        }
        .hero-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.2px;
            margin: 0 0 2px 0;
            color: #ffffff !important;
        }
        .hero-title {
            font-size: 11.5px;
            font-weight: 600;
            color: #10b981 !important; /* Emerald accent */
            margin: 0 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .hero-meta {
            font-size: 9.5px;
            color: #cbd5e1 !important;
        }
        .hero-meta span {
            margin-right: 12px;
            display: inline-block;
            color: #cbd5e1 !important;
        }
        .hero-avatar {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            border: 3px solid #10b981;
            object-fit: cover;
        }

        .body-layout-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
            border: 0;
        }
        .col-main {
            width: 65%;
            vertical-align: top;
            padding: 14px 16px 14px 22px;
        }
        .col-side {
            width: 35%;
            vertical-align: top;
            padding: 14px 20px 14px 14px;
            border-left: 1.2px solid #e2e8f0;
            background-color: #f8fafc;
        }

        .sec {
            margin-bottom: 9px;
        }
        .sec-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1.5px solid #10b981;
            padding-bottom: 2px;
            margin: 0 0 6px 0;
        }
        .obj-box {
            background-color: #f8fafc;
            border-left: 3px solid #10b981;
            padding: 6px 10px;
            font-size: 9.8px;
            color: #334155;
            line-height: 1.4;
            margin-bottom: 8px;
            text-align: left;
            border-radius: 0 4px 4px 0;
        }
        .exp-item {
            margin-bottom: 6px;
        }
        .exp-header {
            margin-bottom: 1px;
        }
        .exp-role {
            font-weight: bold;
            font-size: 10.5px;
            color: #0f172a;
        }
        .exp-time {
            float: right;
            font-size: 9px;
            color: #065f46;
            font-weight: 600;
            background: #ecfdf5;
            padding: 1px 5px;
            border-radius: 3px;
        }
        .exp-org {
            font-weight: 600;
            font-size: 10px;
            color: #10b981;
            margin: 1px 0 2px 0;
        }
        .exp-detail {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.35;
        }

        /* Sidebar Elements */
        .skill-badge {
            display: inline-block;
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 8.5px;
            font-weight: 600;
            margin: 0 2px 3px 0;
            word-break: break-word;
            max-width: 100%;
        }
        .side-item {
            margin-bottom: 6px;
        }
        .side-item-title {
            font-weight: bold;
            font-size: 10px;
            color: #0f172a;
        }
        .side-item-sub {
            font-size: 9.2px;
            color: #64748b;
        }
        }
        .side-item {
            margin-bottom: 12px;
        }
        .side-item-title {
            font-weight: bold;
            font-size: 12px;
            color: #0f172a;
        }
        .side-item-sub {
            font-size: 11.5px;
            color: #64748b;
        }
    </style>
</head>
<body>

@php
    $resumeData = is_array($resume) ? $resume : ($resume?->toArray() ?? []);
    
    $personalInfo = $resumeData['personal_info'] ?? ($candidate->metadata['personal_info'] ?? []);
    if (is_string($personalInfo)) $personalInfo = json_decode($personalInfo, true) ?: [];
    
    $skills = $resumeData['skills'] ?? [];
    if (is_string($skills)) $skills = json_decode($skills, true) ?: [];
    
    $languages = $resumeData['languages'] ?? [];
    if (is_string($languages)) $languages = json_decode($languages, true) ?: [];
    
    $experiences = $resumeData['experiences'] ?? [];
    if (is_string($experiences)) $experiences = json_decode($experiences, true) ?: [];
    
    $educations = $resumeData['educations'] ?? [];
    if (is_string($educations)) $educations = json_decode($educations, true) ?: [];
    
    $certifications = $resumeData['certifications'] ?? [];
    if (is_string($certifications)) $certifications = json_decode($certifications, true) ?: [];
    
    $achievements = $resumeData['achievements'] ?? [];
    if (is_string($achievements)) $achievements = json_decode($achievements, true) ?: [];
    
    $activities = $resumeData['activities'] ?? [];
    if (is_string($activities)) $activities = json_decode($activities, true) ?: [];
    
    $objective = $resumeData['career_objective'] ?? ($resume->career_objective ?? null);
    $profileTitle = $resumeData['profile_title'] ?? ($resume->profile_title ?? ($candidate->profile_title ?? 'Kỹ sư Công nghệ'));
@endphp

<div class="hero">
    <div class="hero-left">
        <h1 class="hero-name" style="color: #ffffff !important;">{{ $candidate->name }}</h1>
        <div class="hero-title" style="color: #10b981 !important;">{{ $profileTitle }}</div>
        <div class="hero-meta" style="color: #cbd5e1 !important;">
            <span style="color: #cbd5e1 !important;">Email: {{ $candidate->email }}</span>
            <span style="color: #cbd5e1 !important;">SĐT: {{ $candidate->phone ?? 'N/A' }}</span>
            @if(!empty($personalInfo['city']))
                <span style="color: #cbd5e1 !important;">Khu vực: {{ $personalInfo['city'] }}</span>
            @endif
            @if(!empty($personalInfo['linkedin']))
                <span style="color: #cbd5e1 !important;">LinkedIn: {{ $personalInfo['linkedin'] }}</span>
            @endif
        </div>
    </div>
    <div class="hero-right">
        @php
            $avatarRaw = $resumeData['avatar'] ?? ($candidate->user?->avatar ?? null);
            $defaultAvatar = public_path('assets/img/avatar_detail.jpg');
            $avatarPath = null;
            if ($avatarRaw) {
                if (file_exists($avatarRaw)) {
                    $avatarPath = $avatarRaw;
                } elseif (!str_starts_with($avatarRaw, 'http') && file_exists(storage_path('app/public/' . ltrim($avatarRaw, '/')))) {
                    $avatarPath = storage_path('app/public/' . ltrim($avatarRaw, '/'));
                }
            }
            if (!$avatarPath && file_exists($defaultAvatar)) {
                $avatarPath = $defaultAvatar;
            }

            $avatarBase64 = \App\Support\CvAvatarProcessor::process($avatarPath, '1:1');
        @endphp
        @if($avatarBase64)
            <img src="data:image/jpeg;base64,{{ $avatarBase64 }}" class="hero-avatar" alt="Avatar">
        @endif
    </div>
</div>

<table class="body-layout-table" cellpadding="0" cellspacing="0" border="0">
<tr>
    <!-- Main Left Column -->
    <td class="col-main">
        @if(!empty($objective))
        <div class="sec">
            <div class="sec-title">Tổng quan nghề nghiệp</div>
            <div class="obj-box">{!! nl2br(e($objective)) !!}</div>
        </div>
        @endif

        @if(!empty($experiences))
        <div class="sec">
            <div class="sec-title">Kinh nghiệm chuyên môn</div>
            @foreach($experiences as $exp)
                @if(!empty($exp['company']) || !empty($exp['position']))
                <div class="exp-item">
                    <div class="exp-header">
                        <span class="exp-role">{{ $exp['position'] ?? 'Vị trí công tác' }}</span>
                        <span class="exp-time">{{ $exp['from'] ?? '' }} - {{ $exp['to'] ?? 'Hiện tại' }}</span>
                    </div>
                    <div class="exp-org">{{ $exp['company'] ?? '' }}</div>
                    @if(!empty($exp['description']))
                    <div class="exp-detail">{!! nl2br(e($exp['description'])) !!}</div>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
        @endif

        @if(!empty($activities))
        <div class="sec">
            <div class="sec-title">Dự án & Hoạt động tiêu biểu</div>
            @foreach($activities as $act)
                @if(!empty($act['title']))
                <div class="exp-item">
                    <div class="exp-header">
                        <span class="exp-role">{{ $act['title'] }}</span>
                        <span class="exp-time">{{ $act['from'] ?? '' }} - {{ $act['to'] ?? '' }}</span>
                    </div>
                    @if(!empty($act['description']))
                    <div class="exp-detail">{!! nl2br(e($act['description'])) !!}</div>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
        @endif
    </td>

    <!-- Right Sidebar Column -->
    <td class="col-side">
        @if(!empty($skills))
        <div class="sec">
            <div class="sec-title">Kỹ năng công nghệ</div>
            <div>
                @foreach($skills as $skill)
                    @php
                        $sName = is_array($skill) ? ($skill['name'] ?? '') : (string)$skill;
                    @endphp
                    @if(trim($sName) !== '')
                        <span class="skill-badge">{{ $sName }}</span>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($educations))
        <div class="sec">
            <div class="sec-title">Học vấn</div>
            @foreach($educations as $edu)
                @if(!empty($edu['school']) || !empty($edu['degree']))
                <div class="side-item">
                    <div class="side-item-title">{{ $edu['degree'] ?? 'Chuyên ngành' }}</div>
                    <div class="side-item-sub">{{ $edu['school'] ?? '' }} ({{ $edu['from'] ?? '' }} - {{ $edu['to'] ?? '' }})</div>
                </div>
                @endif
            @endforeach
        </div>
        @endif

        @if(!empty($certifications))
        <div class="sec">
            <div class="sec-title">Chứng chỉ</div>
            @foreach($certifications as $cert)
                @if(!empty($cert['name']))
                <div class="side-item">
                    <div class="side-item-title">{{ $cert['name'] }}</div>
                    <div class="side-item-sub">{{ $cert['issuer'] ?? '' }} ({{ $cert['date'] ?? '' }})</div>
                </div>
                @endif
            @endforeach
        </div>
        @endif

        @if(!empty($languages))
        <div class="sec">
            <div class="sec-title">Ngoại ngữ</div>
            @foreach($languages as $lang)
                @php
                    $lName = is_array($lang) ? ($lang['name'] ?? '') : (string)$lang;
                    $lLevel = is_array($lang) ? ($lang['level'] ?? '') : '';
                @endphp
                @if(trim($lName) !== '')
                <div class="side-item">
                    <div class="side-item-title">{{ $lName }}</div>
                    <div class="side-item-sub">{{ $lLevel ?: 'Thành thạo' }}</div>
                </div>
                @endif
            @endforeach
        </div>
        @endif

        @if(!empty($achievements))
        <div class="sec">
            <div class="sec-title">Thành tích</div>
            @foreach($achievements as $ach)
                @if(!empty($ach['title']))
                <div class="side-item">
                    <div class="side-item-title">{{ $ach['title'] }}</div>
                    <div class="side-item-sub">{{ $ach['date'] ?? '' }}</div>
                </div>
                @endif
            @endforeach
        </div>
        @endif
    </td>
</tr>
</table>

</body>
</html>

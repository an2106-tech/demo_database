<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CV - {{ $candidate->name ?? 'Ứng viên' }} (ATS Classic)</title>
    <style>
        @page {
            margin: 10mm 12mm;
            size: A4 portrait;
        }
        * {
            box-sizing: border-box;
            font-family: 'DejaVu Sans', sans-serif !important;
        }
        html, body {
            font-family: 'DejaVu Sans', sans-serif !important;
            font-size: 10px;
            line-height: 1.38;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 10px 0;
            padding: 0;
            border: 0;
        }
        .header-avatar-col {
            width: 105px;
            vertical-align: top;
            padding-right: 16px;
        }
        .header-avatar {
            width: 95px;
            height: 122px;
            border-radius: 4px;
            border: 1.5px solid #94a3b8;
            object-fit: cover;
            display: block;
        }
        .header-info-col {
            vertical-align: top;
        }
        .cand-name {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 2px 0;
            line-height: 1.2;
        }
        .cand-title {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            margin: 0 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Meta Table in Header */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
            border: 0;
        }
        .meta-table td {
            font-size: 9.5px;
            padding: 1.5px 0;
            vertical-align: top;
            line-height: 1.35;
        }
        .meta-lbl {
            font-weight: 700;
            color: #0f172a;
            display: inline-block;
            min-width: 70px;
        }
        .meta-val {
            color: #334155;
        }

        /* Section Styling */
        .section {
            margin-bottom: 9px;
        }
        .section-title {
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #0f172a;
            border-bottom: 1.2px solid #0f172a;
            padding-bottom: 2px;
            margin: 0 0 6px 0;
        }
        .objective-text {
            font-size: 9.8px;
            color: #334155;
            line-height: 1.4;
            margin: 0;
            text-align: justify;
        }

        /* 2-Column Timeline Table */
        .timeline-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .timeline-table:last-child {
            margin-bottom: 0;
        }
        .time-col {
            width: 115px;
            vertical-align: top;
            font-size: 9.5px;
            color: #475569;
            font-weight: 600;
            padding-right: 12px;
            padding-top: 1px;
            white-space: nowrap;
        }
        .content-col {
            vertical-align: top;
        }
        .org-name {
            font-size: 10.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1px;
        }
        .role-name {
            font-size: 10px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .desc-text {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.35;
            text-align: justify;
        }
        .desc-text ul {
            margin: 2px 0;
            padding-left: 14px;
        }
        .desc-text li {
            margin-bottom: 1px;
        }

        /* Skills table */
        .skills-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .skill-cat-col {
            width: 115px;
            vertical-align: top;
            font-size: 9.5px;
            font-weight: 700;
            color: #0f172a;
            padding-right: 12px;
        }
        .skill-list-col {
            vertical-align: top;
            font-size: 9.5px;
            color: #334155;
            line-height: 1.38;
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
    
    $references = $resumeData['references'] ?? [];
    if (is_string($references)) $references = json_decode($references, true) ?: [];
    
    $objective = $resumeData['career_objective'] ?? ($resume->career_objective ?? null);
    $profileTitle = $resumeData['profile_title'] ?? ($resume->profile_title ?? ($candidate->profile_title ?? 'Chuyên viên'));
@endphp

<!-- Header -->
<table class="header-table">
    <tr>
        <td class="header-avatar-col">
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

                $avatarBase64 = \App\Support\CvAvatarProcessor::process($avatarPath, '3:4');
            @endphp
            @if($avatarBase64)
                <img src="data:image/jpeg;base64,{{ $avatarBase64 }}" class="header-avatar" alt="Avatar">
            @endif
        </td>
        <td class="header-info-col">
            <h1 class="cand-name">{{ $candidate->name }}</h1>
            <div class="cand-title">{{ $profileTitle }}</div>
            
            <table class="meta-table">
                <tr>
                    <td style="width: 50%;">
                        <span class="meta-lbl">Ngày sinh:</span>
                        <span class="meta-val">{{ $personalInfo['date_of_birth'] ?? 'N/A' }}</span>
                    </td>
                    <td style="width: 50%;">
                        <span class="meta-lbl">Giới tính:</span>
                        <span class="meta-val">{{ $personalInfo['gender'] ?? 'N/A' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="meta-lbl">Số điện thoại:</span>
                        <span class="meta-val">{{ $candidate->phone ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="meta-lbl">Email:</span>
                        <span class="meta-val">{{ $candidate->email }}</span>
                    </td>
                </tr>
                @if(!empty($personalInfo['website']) || !empty($personalInfo['linkedin']))
                <tr>
                    <td colspan="2">
                        <span class="meta-lbl">Website:</span>
                        <span class="meta-val">{{ $personalInfo['website'] ?? $personalInfo['linkedin'] }}</span>
                    </td>
                </tr>
                @endif
                @if(!empty($personalInfo['address']) || !empty($personalInfo['city']))
                <tr>
                    <td colspan="2">
                        <span class="meta-lbl">Địa chỉ:</span>
                        <span class="meta-val">{{ trim(($personalInfo['address'] ?? '') . ', ' . ($personalInfo['city'] ?? ''), ', ') }}</span>
                    </td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<!-- Mục tiêu nghề nghiệp -->
@if(!empty($objective))
<div class="section">
    <div class="section-title">Mục tiêu nghề nghiệp</div>
    <div class="objective-text">{!! nl2br(e($objective)) !!}</div>
</div>
@endif

<!-- Học vấn -->
@if(!empty($educations))
<div class="section">
    <div class="section-title">Học vấn</div>
    @foreach($educations as $edu)
        @if(!empty($edu['school']) || !empty($edu['degree']))
        <table class="timeline-table">
            <tr>
                <td class="time-col">{{ $edu['from'] ?? '' }} - {{ $edu['to'] ?? '' }}</td>
                <td class="content-col">
                    <div class="org-name">{{ $edu['school'] ?? '' }}</div>
                    <div class="role-name">Chuyên ngành: {{ $edu['degree'] ?? '' }}</div>
                    @if(!empty($edu['description']))
                    <div class="desc-text">{!! nl2br(e($edu['description'])) !!}</div>
                    @endif
                </td>
            </tr>
        </table>
        @endif
    @endforeach
</div>
@endif

<!-- Kinh nghiệm làm việc -->
@if(!empty($experiences))
<div class="section">
    <div class="section-title">Kinh nghiệm làm việc</div>
    @foreach($experiences as $exp)
        @if(!empty($exp['company']) || !empty($exp['position']))
        <table class="timeline-table">
            <tr>
                <td class="time-col">{{ $exp['from'] ?? '' }} - {{ $exp['to'] ?? 'Hiện tại' }}</td>
                <td class="content-col">
                    <div class="org-name">{{ $exp['company'] ?? '' }}</div>
                    <div class="role-name">{{ $exp['position'] ?? '' }}</div>
                    @if(!empty($exp['description']))
                    <div class="desc-text">{!! nl2br(e($exp['description'])) !!}</div>
                    @endif
                </td>
            </tr>
        </table>
        @endif
    @endforeach
</div>
@endif

<!-- Kỹ năng & Ngoại ngữ -->
@if(!empty($skills) || !empty($languages))
<div class="section">
    <div class="section-title">Kỹ năng & Ngoại ngữ</div>
    @if(!empty($skills))
    <table class="skills-table">
        <tr>
            <td class="skill-cat-col">Kỹ năng chuyên môn:</td>
            <td class="skill-list-col">
                @php
                    $skillNames = array_map(function($s) {
                        return is_array($s) ? ($s['name'] . (!empty($s['level']) ? ' ('.$s['level'].')' : '')) : $s;
                    }, $skills);
                @endphp
                {{ implode(' • ', array_filter($skillNames)) }}
            </td>
        </tr>
    </table>
    @endif

    @if(!empty($languages))
    <table class="skills-table">
        <tr>
            <td class="skill-cat-col">Ngoại ngữ:</td>
            <td class="skill-list-col">
                @php
                    $langNames = array_map(function($l) {
                        return is_array($l) ? ($l['name'] . (!empty($l['level']) ? ' ('.$l['level'].')' : '')) : $l;
                    }, $languages);
                @endphp
                {{ implode(' • ', array_filter($langNames)) }}
            </td>
        </tr>
    </table>
    @endif
</div>
@endif

<!-- Chứng chỉ -->
@if(!empty($certifications))
<div class="section">
    <div class="section-title">Chứng chỉ & Khóa đào tạo</div>
    @foreach($certifications as $cert)
        @if(!empty($cert['name']))
        <table class="timeline-table">
            <tr>
                <td class="time-col">{{ $cert['date'] ?? '' }}</td>
                <td class="content-col">
                    <div class="org-name">{{ $cert['name'] }}</div>
                    @if(!empty($cert['issuer']))
                    <div class="role-name">{{ $cert['issuer'] }}</div>
                    @endif
                    @if(!empty($cert['description']))
                    <div class="desc-text">{{ $cert['description'] }}</div>
                    @endif
                </td>
            </tr>
        </table>
        @endif
    @endforeach
</div>
@endif

<!-- Thành tích & Giải thưởng -->
@if(!empty($achievements))
<div class="section">
    <div class="section-title">Thành tích & Giải thưởng</div>
    @foreach($achievements as $ach)
        @if(!empty($ach['title']))
        <table class="timeline-table">
            <tr>
                <td class="time-col">{{ $ach['date'] ?? '' }}</td>
                <td class="content-col">
                    <div class="org-name">{{ $ach['title'] }}</div>
                    @if(!empty($ach['description']))
                    <div class="desc-text">{{ $ach['description'] }}</div>
                    @endif
                </td>
            </tr>
        </table>
        @endif
    @endforeach
</div>
@endif

<!-- Hoạt động & Dự án nổi bật -->
@if(!empty($activities))
<div class="section">
    <div class="section-title">Hoạt động & Dự án nổi bật</div>
    @foreach($activities as $act)
        @if(!empty($act['title']))
        <table class="timeline-table">
            <tr>
                <td class="time-col">{{ $act['from'] ?? '' }} - {{ $act['to'] ?? '' }}</td>
                <td class="content-col">
                    <div class="org-name">{{ $act['title'] }}</div>
                    @if(!empty($act['description']))
                    <div class="desc-text">{!! nl2br(e($act['description'])) !!}</div>
                    @endif
                </td>
            </tr>
        </table>
        @endif
    @endforeach
</div>
@endif

</body>
</html>

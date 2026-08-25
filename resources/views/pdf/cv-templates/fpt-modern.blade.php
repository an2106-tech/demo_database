<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CV - {{ $candidate->name ?? 'Ứng viên' }} (FPT Modern)</title>
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
        .cv-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
            border: 0;
        }
        .sidebar {
            width: 32%;
            background-color: #0f172a;
            color: #f8fafc;
            vertical-align: top;
            padding: 14px 12px;
        }
        .main {
            width: 68%;
            background-color: #ffffff;
            vertical-align: top;
            padding: 14px 18px;
        }

        .avatar-wrap {
            text-align: center;
            margin-bottom: 12px;
            padding-top: 2px;
        }
        .avatar {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            border: 3px solid #f37021;
            object-fit: cover;
            display: inline-block;
        }
        .sidebar-section {
            margin-bottom: 10px;
        }
        .sidebar-title {
            color: #f37021;
            font-size: 10.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1.2px solid rgba(243, 112, 33, 0.6);
            padding-bottom: 2px;
            margin-bottom: 5px;
        }
        .contact-item {
            margin-bottom: 4px;
            font-size: 9.5px;
            word-break: break-word;
        }
        .contact-label {
            color: #94a3b8;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: block;
            margin-bottom: 1px;
        }
        .contact-value {
            color: #f8fafc;
            font-weight: 500;
            font-size: 9.5px;
            line-height: 1.3;
        }

        /* Skill Pill Badges */
        .skill-pill {
            display: inline-block;
            background: #1e293b;
            border: 1px solid rgba(243, 112, 33, 0.3);
            color: #f8fafc;
            font-size: 8.5px;
            padding: 2px 5px;
            border-radius: 3px;
            margin: 0 2px 3px 0;
            line-height: 1.25;
            word-break: break-word;
            max-width: 100%;
        }

        /* Main Header */
        .main-header {
            margin-bottom: 8px;
            border-bottom: 1.5px solid #f1f5f9;
            padding-bottom: 5px;
        }
        .cand-name {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 2px 0;
            letter-spacing: -0.2px;
        }
        .cand-title {
            font-size: 11px;
            font-weight: 600;
            color: #f37021;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* Sections */
        .section {
            margin-bottom: 8px;
        }
        .section-heading {
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-left: 3px solid #f37021;
            padding: 2px 6px;
            margin: 0 0 5px 0;
            background-color: #f8fafc;
            border-radius: 0 3px 3px 0;
        }
        .objective-text {
            font-size: 9.8px;
            color: #334155;
            line-height: 1.4;
            margin: 0;
            text-align: justify;
        }
        .timeline-item {
            margin-bottom: 6px;
        }
        .item-head {
            margin-bottom: 1px;
        }
        .item-title {
            font-weight: bold;
            font-size: 10.5px;
            color: #0f172a;
        }
        .item-period {
            float: right;
            font-size: 9px;
            color: #475569;
            font-weight: 500;
            background: #f1f5f9;
            padding: 1px 5px;
            border-radius: 3px;
        }
        .item-subtitle {
            font-weight: 600;
            font-size: 10px;
            color: #f37021;
            margin: 1px 0 2px 0;
        }
        .item-desc {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.35;
            text-align: justify;
        }
        .item-desc ul {
            margin: 2px 0;
            padding-left: 14px;
        }
        .item-desc li {
            margin-bottom: 1px;
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

<table class="cv-table" cellpadding="0" cellspacing="0" border="0">
<tr>
    <!-- Sidebar Left -->
    <td class="sidebar">
        <div class="avatar-wrap">
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
                <img src="data:image/jpeg;base64,{{ $avatarBase64 }}" class="avatar" alt="Avatar">
            @endif
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Thông tin liên hệ</div>
            <div class="contact-item">
                <span class="contact-label">Số điện thoại</span>
                <span class="contact-value">{{ $candidate->phone ?? 'Chưa cập nhật' }}</span>
            </div>
            <div class="contact-item">
                <span class="contact-label">Email</span>
                <span class="contact-value">{{ $candidate->email }}</span>
            </div>
            @if(!empty($personalInfo['date_of_birth']))
            <div class="contact-item">
                <span class="contact-label">Ngày sinh</span>
                <span class="contact-value">{{ date('d/m/Y', strtotime($personalInfo['date_of_birth'])) }}</span>
            </div>
            @endif
            @if(!empty($personalInfo['gender']))
            <div class="contact-item">
                <span class="contact-label">Giới tính</span>
                <span class="contact-value">{{ $personalInfo['gender'] }}</span>
            </div>
            @endif
            @if(!empty($personalInfo['address']) || !empty($personalInfo['city']))
            <div class="contact-item">
                <span class="contact-label">Địa chỉ</span>
                <span class="contact-value">{{ $personalInfo['address'] ?? '' }}{{ !empty($personalInfo['city']) ? (empty($personalInfo['address']) ? '' : ', ') . $personalInfo['city'] : '' }}</span>
            </div>
            @endif
            @if(!empty($personalInfo['website']))
            <div class="contact-item">
                <span class="contact-label">Website / Portfolio</span>
                <span class="contact-value">{{ $personalInfo['website'] }}</span>
            </div>
            @endif
            @if(!empty($personalInfo['linkedin']))
            <div class="contact-item">
                <span class="contact-label">LinkedIn</span>
                <span class="contact-value">{{ $personalInfo['linkedin'] }}</span>
            </div>
            @endif
        </div>

        @if(!empty($skills))
        <div class="sidebar-section">
            <div class="sidebar-title">Kỹ năng chuyên môn</div>
            <div>
                @foreach($skills as $skill)
                    @php
                        $skillName = is_array($skill) ? ($skill['name'] ?? '') : (string)$skill;
                        $skillLevel = is_array($skill) ? ($skill['level'] ?? null) : null;
                    @endphp
                    @if(trim($skillName) !== '')
                        <span class="skill-pill">{{ $skillName }}{{ $skillLevel ? ' ('.$skillLevel.')' : '' }}</span>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($languages))
        <div class="sidebar-section">
            <div class="sidebar-title">Ngoại ngữ</div>
            <div>
                @foreach($languages as $lang)
                    @php
                        $langName = is_array($lang) ? ($lang['name'] ?? '') : (string)$lang;
                        $langLevel = is_array($lang) ? ($lang['level'] ?? null) : null;
                    @endphp
                    @if(trim($langName) !== '')
                        <span class="skill-pill">{{ $langName }}{{ $langLevel ? ' ('.$langLevel.')' : '' }}</span>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($references))
        <div class="sidebar-section">
            <div class="sidebar-title">Người tham chiếu</div>
            @foreach($references as $ref)
                @if(!empty($ref['name']))
                <div class="contact-item">
                    <span class="contact-value" style="color: #f37021; font-weight: 700;">{{ $ref['name'] }}</span>
                    @if(!empty($ref['title']))
                        <div style="font-size: 9.5px; color: #94a3b8;">{{ $ref['title'] }}</div>
                    @endif
                    @if(!empty($ref['email']))
                        <div style="font-size: 9.5px; color: #cbd5e1;">{{ $ref['email'] }}</div>
                    @endif
                    @if(!empty($ref['phone']))
                        <div style="font-size: 9.5px; color: #cbd5e1;">{{ $ref['phone'] }}</div>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
        @endif
    </td>
    <!-- Main Right Content -->
    <td class="main">
        <div class="main-header">
            <h1 class="cand-name">{{ $candidate->name }}</h1>
            <div class="cand-title">{{ $profileTitle }}</div>
        </div>

        @if(!empty($objective))
        <div class="section">
            <div class="section-heading">Mục tiêu nghề nghiệp</div>
            <p class="objective-text">{!! nl2br(e($objective)) !!}</p>
        </div>
        @endif

        @if(!empty($experiences))
        <div class="section">
            <div class="section-heading">Kinh nghiệm làm việc & Dự án</div>
            @foreach($experiences as $exp)
                @if(!empty($exp['company']) || !empty($exp['position']))
                <div class="timeline-item">
                    <div class="item-head">
                        <span class="item-title">{{ $exp['position'] ?? 'Vị trí công việc' }}</span>
                        <span class="item-period">{{ $exp['from'] ?? '' }} - {{ $exp['to'] ?? 'Hiện tại' }}</span>
                    </div>
                    <div class="item-subtitle">{{ $exp['company'] ?? '' }}</div>
                    @if(!empty($exp['description']))
                    <div class="item-desc">{!! nl2br(e($exp['description'])) !!}</div>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
        @endif

        @if(!empty($educations))
        <div class="section">
            <div class="section-heading">Học vấn & Bằng cấp</div>
            @foreach($educations as $edu)
                @if(!empty($edu['school']) || !empty($edu['degree']))
                <div class="timeline-item">
                    <div class="item-head">
                        <span class="item-title">{{ $edu['degree'] ?? 'Ngành học' }}</span>
                        <span class="item-period">{{ $edu['from'] ?? '' }} - {{ $edu['to'] ?? '' }}</span>
                    </div>
                    <div class="item-subtitle">{{ $edu['school'] ?? '' }}</div>
                    @if(!empty($edu['description']))
                    <div class="item-desc">{!! nl2br(e($edu['description'])) !!}</div>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
        @endif

        @if(!empty($certifications))
        <div class="section">
            <div class="section-heading">Chứng chỉ chuyên môn</div>
            @foreach($certifications as $cert)
                @if(!empty($cert['name']))
                <div class="timeline-item">
                    <div class="item-head">
                        <span class="item-title">{{ $cert['name'] }}</span>
                        <span class="item-period">{{ $cert['date'] ?? '' }}</span>
                    </div>
                    @if(!empty($cert['issuer']))
                    <div class="item-subtitle">{{ $cert['issuer'] }}</div>
                    @endif
                    @if(!empty($cert['description']))
                    <div class="item-desc">{{ $cert['description'] }}</div>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
        @endif

        @if(!empty($achievements))
        <div class="section">
            <div class="section-heading">Thành tích & Giải thưởng</div>
            @foreach($achievements as $ach)
                @if(!empty($ach['title']))
                <div class="timeline-item">
                    <div class="item-head">
                        <span class="item-title">{{ $ach['title'] }}</span>
                        <span class="item-period">{{ $ach['date'] ?? '' }}</span>
                    </div>
                    @if(!empty($ach['description']))
                    <div class="item-desc">{{ $ach['description'] }}</div>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
        @endif

        @if(!empty($activities))
        <div class="section">
            <div class="section-heading">Hoạt động ngoại khóa & Dự án</div>
            @foreach($activities as $act)
                @if(!empty($act['title']))
                <div class="timeline-item">
                    <div class="item-head">
                        <span class="item-title">{{ $act['title'] }}</span>
                        <span class="item-period">{{ $act['from'] ?? '' }} - {{ $act['to'] ?? '' }}</span>
                    </div>
                    @if(!empty($act['description']))
                    <div class="item-desc">{!! nl2br(e($act['description'])) !!}</div>
                    @endif
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

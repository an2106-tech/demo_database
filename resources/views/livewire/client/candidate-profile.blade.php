@php
    $sections = [
        ['id' => 'profile-title', 'label' => 'Tiêu đề hồ sơ'],
        ['id' => 'personal-info', 'label' => 'Thông tin cá nhân'],
        ['id' => 'career-objective', 'label' => 'Mục tiêu nghề nghiệp'],
        ['id' => 'desired-job', 'label' => 'Công việc mong muốn'],
        ['id' => 'experiences', 'label' => 'Kinh nghiệm'],
        ['id' => 'educations', 'label' => 'Học vấn'],
        ['id' => 'certifications', 'label' => 'Chứng chỉ'],
        ['id' => 'languages', 'label' => 'Ngôn ngữ'],
        ['id' => 'skills', 'label' => 'Kỹ năng'],
        ['id' => 'achievements', 'label' => 'Thành tích'],
        ['id' => 'activities', 'label' => 'Hoạt động'],
        ['id' => 'references', 'label' => 'Người tham khảo'],
    ];

    $completedBlocks = collect([
        filled($profile_title),
        filled($phone) || filled($experience_years) || collect($personal_info)->filter(fn ($value) => filled($value))->isNotEmpty(),
        filled($career_objective),
        collect($desired_job)->filter(fn ($value) => filled($value))->isNotEmpty(),
        collect($experiences)->isNotEmpty(),
        collect($educations)->isNotEmpty(),
        collect($skills)->isNotEmpty(),
    ])->filter()->count();
@endphp

<div>
    <style>
        .candidate-profile-page {
            --cp-primary: #f37021;
            --cp-primary-dark: #d95f16;
            --cp-accent: #f37021;
            --cp-text: #2f2f2f;
            --cp-muted: #6e6e6e;
            --cp-border: #f1dfd2;
            --cp-surface: #ffffff;
            --cp-surface-soft: #fff8f3;
            --cp-shadow: 0 14px 35px rgba(25, 25, 25, 0.07);
            color: var(--cp-text);
        }

        .candidate-profile-page .dashboard-right {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .candidate-profile-page .candidate-profile {
            background: transparent;
            border-radius: 0;
            padding: 0;
        }

        .cp-hero {
            background: linear-gradient(135deg, #fff7f1 0%, #fff1e6 55%, #ffffff 55%, #ffffff 100%);
            border-radius: 24px;
            box-shadow: var(--cp-shadow);
            overflow: hidden;
            position: relative;
        }

        .cp-hero::after {
            background: radial-gradient(circle at top right, rgba(243, 112, 33, 0.12), transparent 45%);
            content: "";
            inset: 0;
            pointer-events: none;
            position: absolute;
        }

        .cp-hero__inner {
            align-items: stretch;
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(0, 1.65fr) minmax(280px, 0.95fr);
            position: relative;
            z-index: 1;
        }

        .cp-hero__main {
            color: var(--cp-text);
            padding: 32px;
        }

        .cp-hero__profile {
            align-items: center;
            display: flex;
            gap: 18px;
            margin-bottom: 18px;
        }

        .cp-avatar {
            background: #fff;
            border: 4px solid rgba(255, 255, 255, 0.92);
            border-radius: 28px;
            box-shadow: 0 18px 34px rgba(243, 112, 33, 0.16);
            flex: 0 0 108px;
            height: 108px;
            overflow: hidden;
            width: 108px;
        }

        .cp-avatar img {
            display: block;
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .cp-avatar__content strong {
            color: #272727;
            display: block;
            font-size: 17px;
            margin-bottom: 6px;
        }

        .cp-avatar__content span {
            color: var(--cp-muted);
            display: block;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .cp-avatar__action {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .cp-avatar__upload {
            background: linear-gradient(135deg, #f37021 0%, #ff8a1d 100%);
            border-radius: 999px;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 16px;
            transition: transform .18s ease, box-shadow .18s ease;
            box-shadow: 0 12px 22px rgba(243, 112, 33, 0.22);
        }

        .cp-avatar__upload:hover {
            transform: translateY(-1px);
        }

        .cp-avatar__hint {
            color: var(--cp-muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .cp-eyebrow {
            letter-spacing: 0.08em;
            margin-bottom: 10px;
            opacity: 0.82;
            text-transform: uppercase;
        }

        .cp-hero__title {
            color: #222;
            font-size: 34px;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 10px;
        }

        .cp-hero__subtitle {
            color: #666;
            font-size: 15px;
            line-height: 1.7;
            margin: 0;
            max-width: 720px;
        }

        .cp-hero__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            list-style: none;
            margin: 22px 0 0;
            padding: 0;
        }

        .cp-hero__meta li {
            background: #fff;
            border: 1px solid #f3dfd0;
            border-radius: 999px;
            color: #4b4b4b;
            padding: 10px 14px;
        }

        .cp-hero__side {
            background: linear-gradient(180deg, #fffaf6 0%, #ffffff 100%);
            display: flex;
            flex-direction: column;
            gap: 16px;
            justify-content: center;
            padding: 28px;
        }

        .cp-stat-grid {
            display: grid;
            gap: 12px;
        }

        .cp-stat-card {
            background: var(--cp-surface);
            border: 1px solid #f3dfd0;
            border-radius: 18px;
            padding: 16px 18px;
        }

        .cp-stat-card strong {
            color: var(--cp-primary-dark);
            display: block;
            font-size: 26px;
            line-height: 1;
            margin-bottom: 6px;
        }

        .cp-stat-card span {
            color: var(--cp-muted);
            display: block;
            font-size: 13px;
            line-height: 1.5;
        }

        .cp-form {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .cp-layout {
            display: grid;
            gap: 24px;
            grid-template-columns: minmax(0, 240px) minmax(0, 1fr);
        }

        .cp-toc {
            align-self: start;
            background: var(--cp-surface);
            border: 1px solid var(--cp-border);
            border-radius: 22px;
            box-shadow: var(--cp-shadow);
            padding: 22px;
            position: sticky;
            top: 20px;
        }

        .cp-toc h3 {
            color: var(--cp-primary-dark);
            font-size: 18px;
            margin: 0 0 8px;
        }

        .cp-toc p {
            color: var(--cp-muted);
            font-size: 13px;
            line-height: 1.6;
            margin: 0 0 16px;
        }

        .cp-toc ul {
            display: flex;
            flex-direction: column;
            gap: 10px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .cp-toc a {
            background: var(--cp-surface-soft);
            border: 1px solid transparent;
            border-radius: 14px;
            color: var(--cp-text);
            display: block;
            font-size: 13px;
            font-weight: 500;
            padding: 11px 13px;
            transition: all 0.2s ease;
        }

        .cp-toc a:hover,
        .cp-toc a:focus {
            border-color: rgba(243, 112, 33, 0.25);
            color: var(--cp-primary);
            text-decoration: none;
            transform: translateX(2px);
        }

        .cp-panel,
        .cp-repeat-card,
        .cp-toc,
        .cp-upload,
        .cp-field,
        .cp-stat-card {
            scroll-margin-top: 110px;
        }

        .cp-content {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .cp-panel {
            background: var(--cp-surface);
            border: 1px solid var(--cp-border);
            border-radius: 22px;
            box-shadow: var(--cp-shadow);
            overflow: hidden;
        }

        .cp-panel__header {
            align-items: center;
            background: linear-gradient(180deg, #fffaf6 0%, #fff4eb 100%);
            border-bottom: 1px solid var(--cp-border);
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 22px 24px;
        }

        .cp-panel__title {
            margin: 0;
        }

        .cp-panel__title h3 {
            color: var(--cp-primary-dark);
            font-size: 21px;
            margin: 0 0 6px;
        }

        .cp-panel__title p {
            color: var(--cp-muted);
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        .cp-panel__body {
            padding: 24px;
        }

        .cp-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .cp-grid--3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .cp-stack {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .cp-field {
            background: var(--cp-surface-soft);
            border: 1px solid transparent;
            border-radius: 18px;
            padding: 16px;
        }

        .cp-field .single-input {
            margin: 0;
        }

        .cp-field label,
        .cp-upload label {
            color: var(--cp-primary-dark);
            display: block;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.01em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .cp-field input,
        .cp-field textarea,
        .cp-upload input[type="file"] {
            background: #fff;
            border: 1px solid #ead8ca;
            border-radius: 14px;
            box-shadow: none;
            min-height: 52px;
            width: 100%;
        }

        .cp-field textarea {
            min-height: 140px;
            padding-top: 14px;
            resize: vertical;
        }

        .cp-field input[disabled] {
            background: #f7f2ed;
            color: #7a7068;
        }

        .cp-error {
            color: #d64545;
            font-size: 13px;
            margin-top: 8px;
        }

        .cp-inline-note {
            color: var(--cp-muted);
            font-size: 13px;
            line-height: 1.6;
            margin: 0;
        }

        .cp-repeat-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .cp-repeat-card {
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfd 100%);
            border: 1px solid var(--cp-border);
            border-radius: 20px;
            padding: 18px;
        }

        .cp-repeat-card__head {
            align-items: center;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .cp-repeat-card__head h4 {
            color: var(--cp-text);
            font-size: 17px;
            margin: 0;
        }

        .cp-btn {
            align-items: center;
            background: var(--cp-primary);
            border: none;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            gap: 8px;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            transition: all 0.2s ease;
        }

        .cp-btn:hover,
        .cp-btn:focus {
            background: var(--cp-primary-dark);
            color: #fff;
            text-decoration: none;
        }

        .cp-btn--danger {
            background: #fff1f0;
            color: #c24741;
        }

        .cp-btn--danger:hover,
        .cp-btn--danger:focus {
            background: #ffdeda;
            color: #a9342d;
        }

        .cp-upload {
            background: linear-gradient(180deg, #fffaf7 0%, #fff4ec 100%);
            border: 1px dashed #e8c8b2;
            border-radius: 20px;
            padding: 20px;
        }

        .cp-upload__actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 14px;
        }

        .cp-upload__link {
            color: var(--cp-primary);
            font-weight: 700;
        }

        .cp-submit {
            display: flex;
            justify-content: flex-end;
        }

        .cp-submit button {
            background: linear-gradient(135deg, var(--cp-accent), #ff934f);
            border: none;
            border-radius: 999px;
            box-shadow: 0 15px 35px rgba(243, 112, 33, 0.24);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            min-height: 56px;
            min-width: 220px;
            padding: 0 28px;
        }

        .cp-alert {
            background: #ebfff4;
            border: 1px solid #bfe6cc;
            border-radius: 18px;
            color: #226942;
            margin: 0 0 24px;
            padding: 16px 20px;
        }

        @media (max-width: 1199px) {
            .cp-layout,
            .cp-hero__inner {
                grid-template-columns: 1fr;
            }

            .cp-toc {
                position: static;
            }
        }

        @media (max-width: 767px) {
            .candidate-profile-page .dashboard-right {
                gap: 18px;
            }

            .cp-hero__main,
            .cp-hero__side,
            .cp-panel__header,
            .cp-panel__body,
            .cp-toc {
                padding: 18px;
            }

            .cp-hero__title {
                font-size: 28px;
            }

            .cp-grid,
            .cp-grid--3 {
                grid-template-columns: 1fr;
            }

            .cp-repeat-card__head,
            .cp-panel__header {
                align-items: flex-start;
                flex-direction: column;
            }

            .cp-submit {
                justify-content: stretch;
            }

            .cp-submit button {
                width: 100%;
            }
        }
    </style>

    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Thông tin cá nhân</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="candidate-dashboard-area section_70 candidate-profile-page">
        <div class="container">
            @if (session('status'))
                <p class="cp-alert">{{ session('status') }}</p>
            @endif

            <div class="row">
                <div class="col-lg-3 col-md-4 dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8">
                    <div class="dashboard-right">
                        <div class="cp-hero">
                            <div class="cp-hero__inner">
                                <div class="cp-hero__main">
                                    <div class="cp-hero__profile">
                                        <div class="cp-avatar">
                                            <img src="{{ $avatar ? $avatar->temporaryUrl() : $this->currentAvatarUrl }}" alt="Ảnh đại diện">
                                        </div>
                                        <div class="cp-avatar__content">
                                            <strong>Ảnh đại diện</strong>
                                            <span>Tải ảnh chân dung rõ mặt để hồ sơ trông chuyên nghiệp và dễ nhận diện hơn.</span>
                                            <div class="cp-avatar__action">
                                                <label class="cp-avatar__upload" for="avatar">Chọn ảnh mới</label>
                                                <span class="cp-avatar__hint">Hỗ trợ JPG, PNG, WEBP. Tối đa 5MB.</span>
                                            </div>
                                            @error('avatar')
                                                <div class="cp-error" style="margin-top: 10px;">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="cp-eyebrow">Hồ sơ ứng viên</div>
                                    <h1 class="cp-hero__title">{{ $profile_title ?: 'Hoàn thiện hồ sơ để tăng cơ hội được liên hệ' }}</h1>
                                    <p class="cp-hero__subtitle">
                                        Cập nhật đầy đủ thông tin cá nhân, kinh nghiệm, học vấn và CV để nhà tuyển dụng dễ dàng đánh giá năng lực của bạn.
                                    </p>

                                    <ul class="cp-hero__meta">
                                        <li>{{ $name }}</li>
                                        <li>{{ $email }}</li>
                                        <li>{{ $experience_years !== null ? $experience_years . ' năm kinh nghiệm' : 'Chưa cập nhật kinh nghiệm' }}</li>
                                    </ul>
                                </div>

                                <div class="cp-hero__side">
                                    <div class="cp-stat-grid">
                                        <div class="cp-stat-card">
                                            <strong>{{ $completedBlocks }}/7</strong>
                                            <span>Mục thông tin chính đã được điền</span>
                                        </div>
                                        <div class="cp-stat-card">
                                            <strong>{{ count($experiences) + count($educations) + count($skills) }}</strong>
                                            <span>Mục nội dung đã thêm vào hồ sơ</span>
                                        </div>
                                        <div class="cp-stat-card">
                                            <strong>{{ $this->currentCvUrl ? 'Sẵn sàng' : 'Chưa có' }}</strong>
                                            <span>Trạng thái file CV để nhà tuyển dụng tải xuống</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="candidate-profile">
                            <form class="cp-form" wire:submit.prevent="save" method="POST" enctype="multipart/form-data">
                                <input id="avatar" type="file" wire:model="avatar" accept="image/png,image/jpeg,image/webp" hidden>
                                <div class="cp-layout">
                                    <aside class="cp-toc">
                                        <h3>Điều hướng nhanh</h3>
                                        <p>Chuyển đến từng nhóm thông tin để cập nhật nhanh hơn.</p>
                                        <ul>
                                            @foreach ($sections as $section)
                                                <li><a href="{{ url()->current() }}#{{ $section['id'] }}">{{ $section['label'] }}</a></li>
                                            @endforeach
                                        </ul>
                                    </aside>

                                    <div class="cp-content">
                                        <section id="profile-title" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Thông tin tổng quan</h3>
                                                    <p>Đặt một tiêu đề ngắn gọn, rõ vai trò mục tiêu và cấp độ kinh nghiệm của bạn.</p>
                                                </div>
                                            </div>

                                            <div class="cp-panel__body cp-stack">
                                                <div class="cp-grid">
                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="profile_title">Tiêu đề hồ sơ</label>
                                                            <input id="profile_title" type="text" wire:model.defer="profile_title" placeholder="Ví dụ: Backend Developer (PHP/Laravel)">
                                                            @error('profile_title')
                                                                <div class="cp-error">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="experience_years">Số năm kinh nghiệm</label>
                                                            <input id="experience_years" type="number" min="0" max="60" wire:model.defer="experience_years" placeholder="Ví dụ: 3">
                                                            @error('experience_years')
                                                                <div class="cp-error">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="cp-grid">
                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label>Họ và tên</label>
                                                            <input type="text" value="{{ $name }}" disabled>
                                                        </div>
                                                    </div>

                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label>Email</label>
                                                            <input type="text" value="{{ $email }}" disabled>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <section id="personal-info" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Thông tin cá nhân</h3>
                                                    <p>Bổ sung thông tin liên hệ và các kênh hồ sơ trực tuyến để nhà tuyển dụng dễ dàng tiếp cận.</p>
                                                </div>
                                            </div>

                                            <div class="cp-panel__body cp-stack">
                                                <div class="cp-grid">
                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="phone">Số điện thoại</label>
                                                            <input id="phone" type="text" wire:model.defer="phone" placeholder="Nhập số điện thoại">
                                                            @error('phone')
                                                                <div class="cp-error">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="date_of_birth">Ngày sinh</label>
                                                            <input id="date_of_birth" type="date" wire:model.defer="personal_info.date_of_birth">
                                                            @error('personal_info.date_of_birth')
                                                                <div class="cp-error">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="cp-grid">
                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="gender">Giới tính</label>
                                                            <input id="gender" type="text" wire:model.defer="personal_info.gender" placeholder="Nam/Nữ/Khác">
                                                            @error('personal_info.gender')
                                                                <div class="cp-error">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="country">Quốc gia</label>
                                                            <input id="country" type="text" wire:model.defer="personal_info.country" placeholder="Ví dụ: Việt Nam">
                                                            @error('personal_info.country')
                                                                <div class="cp-error">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="cp-grid">
                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="city">Thành phố</label>
                                                            <input id="city" type="text" wire:model.defer="personal_info.city" placeholder="Ví dụ: Hà Nội">
                                                            @error('personal_info.city')
                                                                <div class="cp-error">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="address">Địa chỉ</label>
                                                            <input id="address" type="text" wire:model.defer="personal_info.address" placeholder="Số nhà, đường, quận/huyện">
                                                            @error('personal_info.address')
                                                                <div class="cp-error">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="cp-grid">
                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="website">Website / Portfolio</label>
                                                            <input id="website" type="text" wire:model.defer="personal_info.website" placeholder="https://...">
                                                            @error('personal_info.website')
                                                                <div class="cp-error">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="linkedin">LinkedIn</label>
                                                            <input id="linkedin" type="text" wire:model.defer="personal_info.linkedin" placeholder="https://linkedin.com/in/...">
                                                            @error('personal_info.linkedin')
                                                                <div class="cp-error">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <section id="career-objective" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Mục tiêu nghề nghiệp</h3>
                                                    <p>Tóm tắt định hướng phát triển, giá trị bạn có thể đóng góp và kỳ vọng trong vai trò tiếp theo.</p>
                                                </div>
                                            </div>

                                            <div class="cp-panel__body">
                                                <div class="cp-field">
                                                    <div class="single-input">
                                                        <label for="career_objective_input">Nội dung</label>
                                                        <textarea id="career_objective_input" rows="5" wire:model.defer="career_objective" placeholder="Mô tả ngắn gọn mục tiêu nghề nghiệp của bạn..."></textarea>
                                                        @error('career_objective')
                                                            <div class="cp-error">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <section id="desired-job" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Công việc mong muốn</h3>
                                                    <p>Cho nhà tuyển dụng biết vai trò, cấp bậc, địa điểm và mức đãi ngộ bạn đang hướng tới.</p>
                                                </div>
                                            </div>

                                            <div class="cp-panel__body cp-stack">
                                                <div class="cp-grid">
                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="desired_position">Vị trí</label>
                                                            <input id="desired_position" type="text" wire:model.defer="desired_job.position" placeholder="Ví dụ: Backend Developer">
                                                        </div>
                                                    </div>

                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="desired_level">Cấp bậc</label>
                                                            <input id="desired_level" type="text" wire:model.defer="desired_job.level" placeholder="Junior / Middle / Senior">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="cp-grid">
                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="desired_workplace">Hình thức làm việc</label>
                                                            <input id="desired_workplace" type="text" wire:model.defer="desired_job.workplace" placeholder="Onsite / Remote / Hybrid">
                                                        </div>
                                                    </div>

                                                    <div class="cp-field">
                                                        <div class="single-input">
                                                            <label for="desired_salary">Mức lương kỳ vọng</label>
                                                            <input id="desired_salary" type="text" wire:model.defer="desired_job.expected_salary" placeholder="VD: 20-30 triệu">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="cp-field">
                                                    <div class="single-input">
                                                        <label for="desired_location">Địa điểm mong muốn</label>
                                                        <input id="desired_location" type="text" wire:model.defer="desired_job.location" placeholder="Ví dụ: Hà Nội, TP HCM, Remote">
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <section id="experiences" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Kinh nghiệm làm việc</h3>
                                                    <p>Tập trung vào vai trò, kết quả nổi bật và tác động bạn đã tạo ra ở mỗi công việc.</p>
                                                </div>

                                                <button type="button" class="cp-btn" wire:click="addExperience">+ Thêm kinh nghiệm</button>
                                            </div>

                                            <div class="cp-panel__body">
                                                <div class="cp-repeat-list">
                                                    @forelse ($experiences as $i => $exp)
                                                        <div class="cp-repeat-card">
                                                            <div class="cp-repeat-card__head">
                                                                <h4>Kinh nghiệm #{{ $i + 1 }}</h4>
                                                                <button type="button" class="cp-btn cp-btn--danger" wire:click="removeExperience({{ $i }})">Xóa mục này</button>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Công ty</label>
                                                                        <input type="text" wire:model.defer="experiences.{{ $i }}.company">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Vị trí</label>
                                                                        <input type="text" wire:model.defer="experiences.{{ $i }}.position">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Từ thời điểm</label>
                                                                        <input type="text" wire:model.defer="experiences.{{ $i }}.from" placeholder="MM/YYYY">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Đến thời điểm</label>
                                                                        <input type="text" wire:model.defer="experiences.{{ $i }}.to" placeholder="MM/YYYY hoặc Hiện tại">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="cp-field">
                                                                <div class="single-input">
                                                                    <label>Mô tả công việc</label>
                                                                    <textarea rows="4" wire:model.defer="experiences.{{ $i }}.description"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="cp-inline-note">Bạn chưa thêm kinh nghiệm nào. Hãy thêm ít nhất một vai trò gần đây để hồ sơ thuyết phục hơn.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </section>

                                        <section id="educations" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Học vấn</h3>
                                                    <p>Thêm trường học, chuyên ngành và những nội dung học tập liên quan đến vị trí ứng tuyển.</p>
                                                </div>

                                                <button type="button" class="cp-btn" wire:click="addEducation">+ Thêm học vấn</button>
                                            </div>

                                            <div class="cp-panel__body">
                                                <div class="cp-repeat-list">
                                                    @forelse ($educations as $i => $edu)
                                                        <div class="cp-repeat-card">
                                                            <div class="cp-repeat-card__head">
                                                                <h4>Học vấn #{{ $i + 1 }}</h4>
                                                                <button type="button" class="cp-btn cp-btn--danger" wire:click="removeEducation({{ $i }})">Xóa mục này</button>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Trường học</label>
                                                                        <input type="text" wire:model.defer="educations.{{ $i }}.school">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Bằng cấp / Chuyên ngành</label>
                                                                        <input type="text" wire:model.defer="educations.{{ $i }}.degree">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Bắt đầu</label>
                                                                        <input type="text" wire:model.defer="educations.{{ $i }}.from" placeholder="MM/YYYY">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Kết thúc</label>
                                                                        <input type="text" wire:model.defer="educations.{{ $i }}.to" placeholder="MM/YYYY">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="cp-field">
                                                                <div class="single-input">
                                                                    <label>Mô tả thêm</label>
                                                                    <textarea rows="4" wire:model.defer="educations.{{ $i }}.description"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="cp-inline-note">Bạn chưa thêm thông tin học vấn.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </section>

                                        <section id="certifications" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Chứng chỉ khác</h3>
                                                    <p>Nếu có các chứng chỉ chuyên môn, đây là nơi rất tốt để bổ sung điểm tin cậy cho hồ sơ.</p>
                                                </div>

                                                <button type="button" class="cp-btn" wire:click="addCertification">+ Thêm chứng chỉ</button>
                                            </div>

                                            <div class="cp-panel__body">
                                                <div class="cp-repeat-list">
                                                    @forelse ($certifications as $i => $cert)
                                                        <div class="cp-repeat-card">
                                                            <div class="cp-repeat-card__head">
                                                                <h4>Chứng chỉ #{{ $i + 1 }}</h4>
                                                                <button type="button" class="cp-btn cp-btn--danger" wire:click="removeCertification({{ $i }})">Xóa mục này</button>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Tên chứng chỉ</label>
                                                                        <input type="text" wire:model.defer="certifications.{{ $i }}.name">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Tổ chức cấp</label>
                                                                        <input type="text" wire:model.defer="certifications.{{ $i }}.issuer">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Thời gian</label>
                                                                        <input type="text" wire:model.defer="certifications.{{ $i }}.date" placeholder="MM/YYYY">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Mô tả</label>
                                                                        <input type="text" wire:model.defer="certifications.{{ $i }}.description">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="cp-inline-note">Không có chứng chỉ nào được thêm.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </section>

                                        <section id="languages" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Ngôn ngữ</h3>
                                                    <p>Liệt kê các ngôn ngữ và cấp độ sử dụng để tạo thêm lợi thế trong hồ sơ.</p>
                                                </div>

                                                <button type="button" class="cp-btn" wire:click="addLanguage">+ Thêm ngôn ngữ</button>
                                            </div>

                                            <div class="cp-panel__body">
                                                <div class="cp-repeat-list">
                                                    @forelse ($languages as $i => $lang)
                                                        <div class="cp-repeat-card">
                                                            <div class="cp-repeat-card__head">
                                                                <h4>Ngôn ngữ #{{ $i + 1 }}</h4>
                                                                <button type="button" class="cp-btn cp-btn--danger" wire:click="removeLanguage({{ $i }})">Xóa mục này</button>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Ngôn ngữ</label>
                                                                        <input type="text" wire:model.defer="languages.{{ $i }}.name">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Trình độ</label>
                                                                        <input type="text" wire:model.defer="languages.{{ $i }}.level" placeholder="Cơ bản / Giao tiếp / Thành thạo">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="cp-inline-note">Bạn chưa thêm ngôn ngữ nào.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </section>

                                        <section id="skills" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Kỹ năng chuyên môn</h3>
                                                    <p>Chọn những kỹ năng phù hợp nhất với vị trí mục tiêu và cho biết mức độ thành thạo.</p>
                                                </div>

                                                <button type="button" class="cp-btn" wire:click="addSkill">+ Thêm kỹ năng</button>
                                            </div>

                                            <div class="cp-panel__body">
                                                <div class="cp-repeat-list">
                                                    @forelse ($skills as $i => $skill)
                                                        <div class="cp-repeat-card">
                                                            <div class="cp-repeat-card__head">
                                                                <h4>Kỹ năng #{{ $i + 1 }}</h4>
                                                                <button type="button" class="cp-btn cp-btn--danger" wire:click="removeSkill({{ $i }})">Xóa mục này</button>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Kỹ năng</label>
                                                                        <input type="text" wire:model.defer="skills.{{ $i }}.name">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Trình độ</label>
                                                                        <input type="text" wire:model.defer="skills.{{ $i }}.level" placeholder="Cơ bản / Khá / Thành thạo">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="cp-inline-note">Bạn chưa thêm kỹ năng chuyên môn.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </section>

                                        <section id="achievements" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Thành tích nổi bật</h3>
                                                    <p>Đây là nơi để thêm giải thưởng, thành tích dự án hoặc cột mốc quan trọng trong sự nghiệp.</p>
                                                </div>

                                                <button type="button" class="cp-btn" wire:click="addAchievement">+ Thêm thành tích</button>
                                            </div>

                                            <div class="cp-panel__body">
                                                <div class="cp-repeat-list">
                                                    @forelse ($achievements as $i => $ach)
                                                        <div class="cp-repeat-card">
                                                            <div class="cp-repeat-card__head">
                                                                <h4>Thành tích #{{ $i + 1 }}</h4>
                                                                <button type="button" class="cp-btn cp-btn--danger" wire:click="removeAchievement({{ $i }})">Xóa mục này</button>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Tiêu đề</label>
                                                                        <input type="text" wire:model.defer="achievements.{{ $i }}.title">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Thời gian</label>
                                                                        <input type="text" wire:model.defer="achievements.{{ $i }}.date" placeholder="MM/YYYY">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="cp-field">
                                                                <div class="single-input">
                                                                    <label>Mô tả</label>
                                                                    <textarea rows="4" wire:model.defer="achievements.{{ $i }}.description"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="cp-inline-note">Chưa có thành tích nào được cập nhật.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </section>

                                        <section id="activities" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Hoạt động khác</h3>
                                                    <p>Bổ sung các hoạt động cộng đồng, CLB, dự án cá nhân hoặc vai trò tình nguyện nếu phù hợp.</p>
                                                </div>

                                                <button type="button" class="cp-btn" wire:click="addActivity">+ Thêm hoạt động</button>
                                            </div>

                                            <div class="cp-panel__body">
                                                <div class="cp-repeat-list">
                                                    @forelse ($activities as $i => $act)
                                                        <div class="cp-repeat-card">
                                                            <div class="cp-repeat-card__head">
                                                                <h4>Hoạt động #{{ $i + 1 }}</h4>
                                                                <button type="button" class="cp-btn cp-btn--danger" wire:click="removeActivity({{ $i }})">Xóa mục này</button>
                                                            </div>

                                                            <div class="cp-grid cp-grid--3">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Tiêu đề</label>
                                                                        <input type="text" wire:model.defer="activities.{{ $i }}.title">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Bắt đầu</label>
                                                                        <input type="text" wire:model.defer="activities.{{ $i }}.from" placeholder="MM/YYYY">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Kết thúc</label>
                                                                        <input type="text" wire:model.defer="activities.{{ $i }}.to" placeholder="MM/YYYY">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="cp-field">
                                                                <div class="single-input">
                                                                    <label>Mô tả</label>
                                                                    <textarea rows="4" wire:model.defer="activities.{{ $i }}.description"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="cp-inline-note">Không có hoạt động bổ sung nào.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </section>

                                        <section id="references" class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Người tham khảo</h3>
                                                    <p>Chỉ thêm thông tin này khi bạn đã được sự đồng ý của người tham khảo.</p>
                                                </div>

                                                <button type="button" class="cp-btn" wire:click="addReference">+ Thêm người tham khảo</button>
                                            </div>

                                            <div class="cp-panel__body">
                                                <div class="cp-repeat-list">
                                                    @forelse ($references as $i => $ref)
                                                        <div class="cp-repeat-card">
                                                            <div class="cp-repeat-card__head">
                                                                <h4>Người tham khảo #{{ $i + 1 }}</h4>
                                                                <button type="button" class="cp-btn cp-btn--danger" wire:click="removeReference({{ $i }})">Xóa mục này</button>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Họ tên</label>
                                                                        <input type="text" wire:model.defer="references.{{ $i }}.name">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Công ty</label>
                                                                        <input type="text" wire:model.defer="references.{{ $i }}.company">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Chức danh</label>
                                                                        <input type="text" wire:model.defer="references.{{ $i }}.position">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Số điện thoại</label>
                                                                        <input type="text" wire:model.defer="references.{{ $i }}.phone">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="cp-grid">
                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Email</label>
                                                                        <input type="text" wire:model.defer="references.{{ $i }}.email">
                                                                    </div>
                                                                </div>

                                                                <div class="cp-field">
                                                                    <div class="single-input">
                                                                        <label>Ghi chú</label>
                                                                        <input type="text" wire:model.defer="references.{{ $i }}.note">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="cp-inline-note">Bạn có thể bỏ qua mục này nếu chưa sẵn sàng chia sẻ người tham khảo.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </section>

                                        <section class="cp-panel">
                                            <div class="cp-panel__header">
                                                <div class="cp-panel__title">
                                                    <h3>Thông tin bổ sung và CV</h3>
                                                    <p>Cập nhật thêm điểm nhấn khác và tải lên CV phiên bản mới nhất của bạn.</p>
                                                </div>
                                            </div>

                                            <div class="cp-panel__body cp-stack">
                                                <div class="cp-field">
                                                    <div class="single-input">
                                                        <label for="extra_info">Thông tin bổ sung</label>
                                                        <textarea id="extra_info" rows="4" wire:model.defer="extra" placeholder="Các hoạt động, giải thưởng, ghi chú hoặc thông tin bổ sung khác..."></textarea>
                                                        @error('extra')
                                                            <div class="cp-error">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="cp-upload">
                                                    <label for="cv">CV (PDF/DOC/DOCX, tối đa 10MB)</label>
                                                    <input
                                                        id="cv"
                                                        type="file"
                                                        wire:model="cv"
                                                        accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                                    >

                                                    @error('cv')
                                                        <div class="cp-error">{{ $message }}</div>
                                                    @enderror

                                                    <div class="cp-upload__actions">
                                                        <span class="cp-inline-note" wire:loading wire:target="cv">Đang tải lên file CV...</span>

                                                        @if ($this->currentCvUrl)
                                                            <a class="cp-upload__link" href="{{ $this->currentCvUrl }}" target="_blank" rel="noopener">Tải CV hiện tại</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </div>

                                <div class="cp-submit">
                                    <button type="submit" wire:loading.attr="disabled">Cập nhật hồ sơ</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

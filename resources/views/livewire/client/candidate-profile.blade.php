@php
    $sections = [
        ['id' => 'personal-info', 'label' => 'Thông tin cá nhân', 'icon' => 'fa-user'],
        ['id' => 'career-objective', 'label' => 'Mục tiêu nghề nghiệp', 'icon' => 'fa-bullseye'],
        ['id' => 'desired-job', 'label' => 'Công việc mong muốn', 'icon' => 'fa-crosshairs'],
        ['id' => 'experiences', 'label' => 'Kinh nghiệm', 'icon' => 'fa-briefcase'],
        ['id' => 'educations', 'label' => 'Học vấn', 'icon' => 'fa-graduation-cap'],
        ['id' => 'skills', 'label' => 'Kỹ năng', 'icon' => 'fa-wrench'],
        ['id' => 'languages', 'label' => 'Ngôn ngữ', 'icon' => 'fa-language'],
        ['id' => 'certifications', 'label' => 'Chứng chỉ', 'icon' => 'fa-certificate'],
        ['id' => 'extra-info', 'label' => 'Đính kèm & Khác', 'icon' => 'fa-paperclip'],
    ];

    $filledCount = collect([
        filled($profile_title),
        filled($phone),
        collect($personal_info)->filter(fn ($value) => filled($value))->isNotEmpty(),
        filled($career_objective),
        collect($desired_job)->filter(fn ($value) => filled($value))->isNotEmpty(),
        collect($experiences)->isNotEmpty(),
        collect($educations)->isNotEmpty(),
        collect($skills)->isNotEmpty(),
        collect($languages)->isNotEmpty(),
    ])->filter()->count();
    
    $completeness = round(($filledCount / 9) * 100);
    $completedBlocks = $filledCount;
@endphp

<div x-data="{ activeSection: $wire.entangle('activeSection') }">
    <style>
        .candidate-profile-page {
            --cv-orange: #f37021;
            --cv-navy: #2c3e50;
            --cv-grey: #64748b;
            --cv-bg: #f4f7f9;
            --cv-white: #ffffff;
            --cv-border: #e2e8f0;
            --cv-radius: 20px;
            --cv-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            background-color: var(--cv-bg);
            font-family: 'Inter', sans-serif;
            color: #334155;
        }

        /* Hero Header */
        .cp-hero {
            background: linear-gradient(135deg, #f37021 0%, #e65c00 100%) !important;
            padding: 40px 32px !important;
            color: white !important;
            border-radius: var(--cv-radius) !important;
            margin-bottom: 30px !important;
            border: none !important;
            box-shadow: 0 15px 40px rgba(243, 112, 33, 0.25) !important;
        }

        .cp-hero__title {
            color: white !important;
            font-size: 32px !important;
            font-weight: 800 !important;
        }

        .cp-hero__subtitle {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .cp-avatar {
            border-radius: 25px !important;
            border: 4px solid rgba(255, 255, 255, 0.3) !important;
            background: rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        }

        /* Sidebar Progress */
        .cp-stat-card {
            background: linear-gradient(135deg, var(--cv-orange) 0%, #e65c00 100%) !important;
            padding: 30px 20px !important;
            border-radius: 24px !important;
            text-align: center !important;
            color: white !important;
            box-shadow: 0 15px 35px rgba(243, 112, 33, 0.25) !important;
            margin-bottom: 25px !important;
            border: none !important;
        }

        .cp-progress-circle {
            width: 84px;
            height: 84px;
            margin: 0 auto 16px;
            background: conic-gradient(rgba(255, 255, 255, 0.9) {{ $completeness }}%, rgba(255, 255, 255, 0.2) 0);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .cp-progress-circle::after {
            content: "";
            position: absolute;
            width: 70px;
            height: 70px;
            background: #f37021;
            border-radius: 50%;
        }

        .cp-progress-circle span {
            position: relative;
            z-index: 2;
            font-size: 18px;
            font-weight: 800;
            color: white;
        }

        /* Layout */
        .cp-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
        }

        @media (max-width: 1024px) {
            .cp-layout { grid-template-columns: 1fr; }
        }

        .cp-panel {
            background: var(--cv-white) !important;
            border-radius: var(--cv-radius) !important;
            border: 1px solid var(--cv-border) !important;
            box-shadow: var(--cv-shadow) !important;
            margin-bottom: 16px !important;
            padding: 0 !important;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .cp-panel__header {
            background: #fff !important;
            border-bottom: 1.5px solid transparent !important;
            padding: 20px 24px !important;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }
        
        .cp-panel__header:hover {
            background: #fcfdfe !important;
        }

        .cp-panel.active {
            border-color: var(--cv-orange) !important;
            box-shadow: 0 10px 40px rgba(243, 112, 33, 0.08) !important;
        }

        .cp-panel.active .cp-panel__header {
            border-bottom-color: #f8fafc !important;
            background: #fffbf9 !important;
        }

        .cp-panel__title h3 {
            color: var(--cv-navy) !important;
            font-size: 17px !important;
            font-weight: 800 !important;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
        }

        .cp-panel-icon {
            width: 34px;
            height: 34px;
            background: #f1f5f9;
            color: var(--cv-navy);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .cp-panel.active .cp-panel-icon {
            background: var(--cv-orange);
            color: white;
        }

        .cp-panel__body {
            padding: 24px 24px 32px !important;
        }

        .cp-panel__chevron {
            font-size: 14px;
            color: #cbd5e1;
            transition: transform 0.3s;
        }

        .cp-panel.active .cp-panel__chevron {
            transform: rotate(180deg);
            color: var(--cv-orange);
        }

        /* Form Fields */
        .cp-field {
            background: #fff !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 12px 18px !important;
            transition: all 0.2s ease !important;
        }

        .cp-field:focus-within {
            border-color: var(--cv-orange) !important;
            box-shadow: 0 0 0 4px rgba(243, 112, 33, 0.08) !important;
        }

        .cp-field label {
            color: #64748b !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            font-size: 11px !important;
            letter-spacing: 0.05em !important;
            margin-bottom: 4px;
        }
        
        .cp-field input, .cp-field textarea {
            border: none;
            outline: none;
            width: 100%;
            background: transparent;
            font-size: 15px;
            padding: 0;
        }

        /* Sidebar Nav */
        .cp-toc {
            background: white !important;
            border-radius: 20px !important;
            padding: 15px !important;
            box-shadow: var(--cv-shadow) !important;
            border: 1px solid var(--cv-border) !important;
            position: sticky;
            top: 100px; /* Adjusted for fixed header height (approx 80px) + 20px gap */
        }

        .cp-toc-link {
            padding: 10px 14px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            color: var(--cv-navy) !important;
            background: transparent !important;
            display: flex !important;
            align-items: center;
            text-decoration: none !important;
            transition: all 0.2s !important;
            margin-bottom: 2px;
            font-size: 14px;
        }

        .cp-toc-link:hover, .cp-toc-link.active {
            background: #fff5ee !important;
            color: var(--cv-orange) !important;
        }
        
        .cp-sidebar-group {
            border-top: 1px solid #f1f5f9;
            margin-top: 15px;
            padding-top: 15px;
        }

        /* Better Buttons */
        .cp-btn {
            border-radius: 12px !important;
            padding: 12px 24px !important;
            font-weight: 700 !important;
            transition: all 0.3s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            border: none !important;
            cursor: pointer !important;
            font-size: 14px !important;
        }

        .cp-btn-primary {
            background: linear-gradient(135deg, #f37021 0%, #e65c00 100%) !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(243, 112, 33, 0.25) !important;
        }

        .cp-btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(243, 112, 33, 0.35) !important;
            filter: brightness(1.05) !important;
        }

        .cp-btn-primary:active {
            transform: translateY(0) !important;
        }

        /* Section Saving */
        .cp-section-save {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px dashed #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
    </style>

    <div style="height: 120px; width: 100%;"></div>
    <section class="candidate-dashboard-area section_70 candidate-profile-page" style="padding-top: 0; padding-bottom: 80px;">
        <div class="container">
            <div class="cp-layout">
                <!-- Sidebar Left -->
                <aside class="dashboard-left">
                    <div class="cp-toc">
                        <div class="cp-stat-card">
                            <div class="cp-progress-circle">
                                <span>{{ $completeness }}%</span>
                            </div>
                            <p style="font-weight: 800; color: white; margin-bottom: 4px; font-size: 15px;">Hoàn thiện hồ sơ</p>
                            <p style="font-size: 12px; color: rgba(255,255,255,0.8); margin: 0;">Hãy đạt 100% để nổi bật nhất!</p>
                        </div>

                        <p style="font-weight: 800; color: var(--cv-orange); text-transform: uppercase; font-size: 11px; letter-spacing: 0.1em; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 15px; padding-left: 10px;">Danh mục hồ sơ</p>
                        
                        <nav style="display: flex; flex-direction: column; gap: 2px;">
                            @foreach ($sections as $section)
                                <button type="button" 
                                        @click="activeSection = '{{ $section['id'] }}'" 
                                        :class="{ 'active': activeSection === '{{ $section['id'] }}' }"
                                        class="cp-toc-link"
                                        style="border: none; text-align: left; width: 100%;">
                                    <i class="fa {{ $section['icon'] }}" style="width: 20px; margin-right: 12px; opacity: 0.7;"></i>
                                    {{ $section['label'] }}
                                </button>
                            @endforeach
                        </nav>

                        <div class="cp-sidebar-group">
                            <a href="{{ route('candidates.candidate_dashboard') }}" class="cp-toc-link">
                                <i class="fa fa-tachometer" style="width: 20px; margin-right: 12px; opacity: 0.7;"></i>
                                Bảng điều khiển
                            </a>
                            <div style="margin-top: 10px; padding: 0 10px;">
                                <livewire:client.logout-button />
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Main Content Right -->
                <div class="cp-content">
                    <div class="cp-hero">
                        <div class="cp-hero__inner" style="display: flex; align-items: center; gap: 32px;">
                            <div class="cp-hero__avatar-box">
                                <div class="cp-avatar" style="width: 110px; height: 110px; flex-shrink: 0; overflow: hidden;">
                                    <img src="{{ $avatar ? $avatar->temporaryUrl() : $this->currentAvatarUrl }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            </div>
                            
                            <div class="cp-hero__info">
                                <h1 class="cp-hero__title" style="margin-bottom: 10px;">{{ $name }}</h1>
                                <p class="cp-hero__subtitle" style="margin-bottom: 20px; font-size: 16px; opacity: 0.9;">
                                    {{ $profile_title ?: 'Chưa cập nhật tiêu đề hồ sơ' }}
                                </p>
                                
                                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                    <input type="file" id="avatar" wire:model="avatar" style="display: none;">
                                    <label for="avatar" class="cp-btn" style="background: white; color: var(--cv-orange); cursor: pointer; padding: 12px 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none;">
                                        <i class="fa fa-camera"></i> Cập nhật ảnh
                                    </label>
                                    
                                    <button type="button" class="cp-btn" data-bs-toggle="modal" data-bs-target="#cvPreviewModal" style="background: rgba(255, 255, 255, 0.2); color: white; border: 1px solid rgba(255,255,255,0.4); padding: 12px 24px; backdrop-filter: blur(8px);">
                                        <i class="fa fa-eye"></i> Xem trước CV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cp-form-sections">
                        <form wire:submit.prevent="save">
                            <div class="cp-stack">
                                <!-- Sections will follow -->

                                <!-- Profile Title -->
                                <section id="personal-info" wire:key="sec-personal-info" class="cp-panel" :class="{ 'active': activeSection === 'personal-info' }">
                                    <div class="cp-panel__header" @click="activeSection = activeSection === 'personal-info' ? null : 'personal-info'">
                                        <div class="cp-panel__title">
                                            <h3>
                                                <span class="cp-panel-icon"><i class="fa fa-user"></i></span>
                                                Thông tin cá nhân & Tổng quan
                                            </h3>
                                        </div>
                                        <i class="fa fa-chevron-down cp-panel__chevron"></i>
                                    </div>

                                    <div x-show="activeSection === 'personal-info'" x-transition:enter="fade-enter" style="display: none;" class="cp-panel__body cp-stack">
                                        <!-- Sub-section: Overview -->
                                        <div style="margin-bottom: 30px;">
                                            <h4 style="font-size: 14px; font-weight: 800; color: var(--cv-navy); margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.05em;">Thông tin tổng quan</h4>
                                            <div class="cp-grid">
                                                <div class="cp-field">
                                                    <label for="profile_title">Tiêu đề hồ sơ</label>
                                                    <input id="profile_title" type="text" wire:model.defer="profile_title" placeholder="Ví dụ: Backend Developer (PHP/Laravel)">
                                                    @error('profile_title') <div class="cp-error">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="cp-field">
                                                    <label for="experience_years">Số năm kinh nghiệm</label>
                                                    <input id="experience_years" type="number" min="0" max="60" wire:model.defer="experience_years" placeholder="Ví dụ: 3">
                                                    @error('experience_years') <div class="cp-error">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sub-section: Personal Details -->
                                        <div>
                                            <h4 style="font-size: 14px; font-weight: 800; color: var(--cv-navy); margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.05em;">Thông tin liên hệ</h4>
                                            <div class="cp-grid">
                                                <div class="cp-field primary">
                                                    <label for="phone">Số điện thoại</label>
                                                    <input id="phone" type="text" wire:model.defer="phone" placeholder="Nhập số điện thoại">
                                                    @error('phone') <div class="cp-error">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="cp-field">
                                                    <label for="date_of_birth">Ngày sinh</label>
                                                    <input id="date_of_birth" type="date" wire:model.defer="personal_info.date_of_birth">
                                                </div>
                                            </div>

                                            <div class="cp-grid" style="margin-top: 20px;">
                                                <div class="cp-field">
                                                    <label for="gender">Giới tính</label>
                                                    <input id="gender" type="text" wire:model.defer="personal_info.gender" placeholder="Nam/Nữ/Khác">
                                                </div>
                                                <div class="cp-field">
                                                    <label for="city">Thành phố</label>
                                                    <input id="city" type="text" wire:model.defer="personal_info.city" placeholder="Ví dụ: Hà Nội">
                                                </div>
                                            </div>

                                            <div class="cp-field" style="margin-top: 20px;">
                                                <label for="address">Địa chỉ</label>
                                                <input id="address" type="text" wire:model.defer="personal_info.address" placeholder="Số nhà, đường, quận/huyện">
                                            </div>
                                        </div>

                                        <div class="cp-section-save">
                                            <button type="button" wire:click="saveSection('career-objective')" class="cp-btn cp-btn-primary">
                                                Lưu & Tiếp tục <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                <section id="career-objective" wire:key="sec-career-objective" class="cp-panel" :class="{ 'active': activeSection === 'career-objective' }">
                                    <div class="cp-panel__header" @click="activeSection = activeSection === 'career-objective' ? null : 'career-objective'">
                                        <div class="cp-panel__title">
                                            <h3>
                                                <span class="cp-panel-icon"><i class="fa fa-bullseye"></i></span>
                                                Mục tiêu nghề nghiệp
                                            </h3>
                                        </div>
                                        <i class="fa fa-chevron-down cp-panel__chevron"></i>
                                    </div>

                                    <div x-show="activeSection === 'career-objective'" x-transition style="display: none;" class="cp-panel__body">
                                        <div class="cp-field">
                                            <label for="career_objective_input">Mô tả ngắn gọn giá trị bạn đóng góp và kỳ vọng của bạn</label>
                                            <textarea id="career_objective_input" rows="5" wire:model.defer="career_objective" placeholder="Ví dụ: Mong muốn áp dụng kiến thức về Laravel để phát chính các hệ thống quy mô lớn..."></textarea>
                                            @error('career_objective') <div class="cp-error">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="cp-section-save">
                                            <button type="button" wire:click="saveSection('desired-job')" class="cp-btn cp-btn-primary">
                                                Lưu & Tiếp tục <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                <section id="desired-job" wire:key="sec-desired-job" class="cp-panel" :class="{ 'active': activeSection === 'desired-job' }">
                                    <div class="cp-panel__header" @click="activeSection = activeSection === 'desired-job' ? null : 'desired-job'">
                                        <div class="cp-panel__title">
                                            <h3>
                                                <span class="cp-panel-icon"><i class="fa fa-crosshairs"></i></span>
                                                Công việc mong muốn
                                            </h3>
                                        </div>
                                        <i class="fa fa-chevron-down cp-panel__chevron"></i>
                                    </div>

                                    <div x-show="activeSection === 'desired-job'" x-transition style="display: none;" class="cp-panel__body cp-stack">
                                        <div class="cp-grid">
                                            <div class="cp-field">
                                                <label for="desired_position">Vị trí mong muốn</label>
                                                <input id="desired_position" type="text" wire:model.defer="desired_job.position" placeholder="Ví dụ: Senior Backend Developer">
                                            </div>
                                            <div class="cp-field">
                                                <label for="desired_salary">Mức lương kỳ vọng</label>
                                                <input id="desired_salary" type="text" wire:model.defer="desired_job.expected_salary" placeholder="VD: 25 - 35 triệu">
                                            </div>
                                        </div>

                                        <div class="cp-grid" style="margin-top: 20px;">
                                            <div class="cp-field">
                                                <label for="desired_level">Cấp bậc</label>
                                                <input id="desired_level" type="text" wire:model.defer="desired_job.level" placeholder="Nhân viên / Trưởng nhóm">
                                            </div>
                                            <div class="cp-field">
                                                <label for="desired_workplace">Hình thức làm việc</label>
                                                <input id="desired_workplace" type="text" wire:model.defer="desired_job.workplace" placeholder="Onsite / Remote / Hybrid">
                                            </div>
                                        </div>

                                        <div class="cp-field" style="margin-top: 20px;">
                                            <label for="desired_location">Địa điểm làm việc</label>
                                            <input id="desired_location" type="text" wire:model.defer="desired_job.location" placeholder="Hồ Chí Minh, Hà Nội, Toàn quốc...">
                                        </div>

                                        <div class="cp-section-save">
                                            <button type="button" wire:click="saveSection('experiences')" class="cp-btn cp-btn-primary">
                                                Lưu & Tiếp tục <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                <section id="experiences" wire:key="sec-experiences" class="cp-panel" :class="{ 'active': activeSection === 'experiences' }">
                                    <div class="cp-panel__header" @click="activeSection = activeSection === 'experiences' ? null : 'experiences'">
                                        <div class="cp-panel__title">
                                            <h3>
                                                <span class="cp-panel-icon"><i class="fa fa-briefcase"></i></span>
                                                Kinh nghiệm làm việc
                                            </h3>
                                        </div>
                                        <i class="fa fa-chevron-down cp-panel__chevron"></i>
                                    </div>

                                    <div x-show="activeSection === 'experiences'" x-transition style="display: none;" class="cp-panel__body">
                                        <div class="cp-repeat-list">
                                            @forelse ($experiences as $i => $exp)
                                                <div class="cp-repeat-card">
                                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                                        <h4 style="font-weight: 800; color: var(--cv-navy); margin: 0;">Kinh nghiệm #{{ $i + 1 }}</h4>
                                                        <button type="button" wire:click="removeExperience({{ $i }})" style="color: #ef4444; background: none; border: none; font-size: 13px; font-weight: 700;">Gỡ bỏ</button>
                                                    </div>

                                                    <div class="cp-grid">
                                                        <div class="cp-field">
                                                            <label>Tên công ty</label>
                                                            <input type="text" wire:model.defer="experiences.{{ $i }}.company" placeholder="VD: Google">
                                                        </div>
                                                        <div class="cp-field">
                                                            <label>Chức danh</label>
                                                            <input type="text" wire:model.defer="experiences.{{ $i }}.position" placeholder="VD: Senior Dev">
                                                        </div>
                                                    </div>

                                                    <div class="cp-grid" style="margin-top: 20px;">
                                                        <div class="cp-field">
                                                            <label>Bắt đầu</label>
                                                            <input type="text" wire:model.defer="experiences.{{ $i }}.from" placeholder="MM/YYYY">
                                                        </div>
                                                        <div class="cp-field">
                                                            <label>Kết thúc</label>
                                                            <input type="text" wire:model.defer="experiences.{{ $i }}.to" placeholder="MM/YYYY hoặc Hiện tại">
                                                        </div>
                                                    </div>

                                                    <div class="cp-field" style="margin-top: 20px;">
                                                        <label>Mô tả chi tiết</label>
                                                        <textarea rows="4" wire:model.defer="experiences.{{ $i }}.description" placeholder="Mô tả công việc và thành tựu..."></textarea>
                                                    </div>
                                                </div>
                                            @empty
                                                <p style="color: #94a3b8; text-align: center; font-style: italic; padding: 20px;">Hãy thêm ít nhất một kinh nghiệm để hồ sơ nổi bật hơn.</p>
                                            @endforelse
                                        </div>

                                        <button type="button" wire:click="addExperience" class="cp-btn-add" style="width: 100%; padding: 15px; margin-top: 10px;">
                                            <i class="fa fa-plus-circle"></i> Thêm kinh nghiệm mới
                                        </button>

                                        <div class="cp-section-save">
                                            <button type="button" wire:click="saveSection('educations')" class="cp-btn cp-btn-primary">
                                                Lưu & Tiếp tục <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                <section id="educations" wire:key="sec-educations" class="cp-panel" :class="{ 'active': activeSection === 'educations' }">
                                    <div class="cp-panel__header" @click="activeSection = activeSection === 'educations' ? null : 'educations'">
                                        <div class="cp-panel__title">
                                            <h3>
                                                <span class="cp-panel-icon"><i class="fa fa-graduation-cap"></i></span>
                                                Học vấn & Bằng cấp
                                            </h3>
                                        </div>
                                        <i class="fa fa-chevron-down cp-panel__chevron"></i>
                                    </div>

                                    <div x-show="activeSection === 'educations'" x-transition style="display: none;" class="cp-panel__body">
                                        <div class="cp-repeat-list">
                                            @forelse ($educations as $i => $edu)
                                                <div class="cp-repeat-card">
                                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                                        <h4 style="font-weight: 800; color: var(--cv-navy); margin: 0;">Học vấn #{{ $i+1 }}</h4>
                                                        <button type="button" wire:click="removeEducation({{ $i }})" style="color: #ef4444; border: none; background: none; font-size: 13px;">Gỡ bỏ</button>
                                                    </div>
                                                    <div class="cp-grid">
                                                        <div class="cp-field">
                                                            <label>Trường / Khóa học</label>
                                                            <input type="text" wire:model.defer="educations.{{ $i }}.school" placeholder="VD: ĐH Kinh tế Quốc dân">
                                                        </div>
                                                        <div class="cp-field">
                                                            <label>Chuyên ngành / Bằng cấp</label>
                                                            <input type="text" wire:model.defer="educations.{{ $i }}.degree" placeholder="VD: Cử nhân Marketing">
                                                        </div>
                                                    </div>
                                                    <div class="cp-field" style="margin-top: 20px;">
                                                        <label>Thời gian & Mô tả</label>
                                                        <input type="text" wire:model.defer="educations.{{ $i }}.from" placeholder="Thời gian (VD: 2018 - 2022)">
                                                    </div>
                                                </div>
                                            @empty
                                                <p style="text-align: center; color: #94a3b8; padding: 20px;">Chưa có thông tin học vấn.</p>
                                            @endforelse
                                        </div>
                                        <button type="button" wire:click="addEducation" class="cp-btn-add" style="width: 100%; padding: 15px; margin-top: 10px;">+ Thêm học vấn</button>
                                        <div class="cp-section-save">
                                            <button type="button" wire:click="saveSection('skills')" class="cp-btn cp-btn-primary">
                                                Lưu & Tiếp tục <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                <section id="skills" wire:key="sec-skills" class="cp-panel" :class="{ 'active': activeSection === 'skills' }">
                                    <div class="cp-panel__header" @click="activeSection = activeSection === 'skills' ? null : 'skills'">
                                        <div class="cp-panel__title">
                                            <h3>
                                                <span class="cp-panel-icon"><i class="fa fa-wrench"></i></span>
                                                Kỹ năng
                                            </h3>
                                        </div>
                                        <i class="fa fa-chevron-down cp-panel__chevron"></i>
                                    </div>

                                    <div x-show="activeSection === 'skills'" x-transition style="display: none;" class="cp-panel__body">
                                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
                                            @forelse ($skills as $i => $skill)
                                                <div style="background: #f8fafc; padding: 15px; border-radius: 12px; display: flex; align-items: center; gap: 10px;">
                                                    <div style="flex: 1;">
                                                        <input type="text" wire:model.defer="skills.{{ $i }}.name" placeholder="Tên kỹ năng" style="width: 100%; border: none; background: transparent; font-weight: 700;">
                                                    </div>
                                                    <button type="button" wire:click="removeSkill({{ $i }})" style="color: #ef4444; border: none; background: none;"><i class="fa fa-trash"></i></button>
                                                </div>
                                            @empty
                                                <p style="text-align: center; color: #94a3b8; padding: 20px; grid-column: 1/-1;">Chưa có kỹ năng nào.</p>
                                            @endforelse
                                        </div>
                                        <button type="button" wire:click="addSkill" class="cp-btn-add" style="width: 100%; padding: 15px; margin-top: 20px;">+ Thêm kỹ năng mới</button>
                                        <div class="cp-section-save">
                                            <button type="button" wire:click="saveSection('languages')" class="cp-btn cp-btn-primary">
                                                Lưu & Tiếp tục <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                <section id="languages" wire:key="sec-languages" class="cp-panel" :class="{ 'active': activeSection === 'languages' }">
                                    <div class="cp-panel__header" @click="activeSection = activeSection === 'languages' ? null : 'languages'">
                                        <div class="cp-panel__title">
                                            <h3>
                                                <span class="cp-panel-icon"><i class="fa fa-language"></i></span>
                                                Ngôn ngữ
                                            </h3>
                                        </div>
                                        <i class="fa fa-chevron-down cp-panel__chevron"></i>
                                    </div>

                                    <div x-show="activeSection === 'languages'" x-transition style="display: none;" class="cp-panel__body">
                                        <div class="cp-repeat-list">
                                            @forelse ($languages as $i => $lang)
                                                <div class="cp-repeat-card" style="padding: 15px; background: #fff; border: 1.5px solid #f1f5f9; margin-bottom: 10px;">
                                                    <div style="display: flex; gap: 15px; align-items: center;">
                                                        <div class="cp-field" style="flex: 2;">
                                                            <label>Ngôn ngữ</label>
                                                            <input type="text" wire:model.defer="languages.{{ $i }}.name" placeholder="VD: Tiếng Anh">
                                                        </div>
                                                        <div class="cp-field" style="flex: 1;">
                                                            <label>Trình độ</label>
                                                            <input type="text" wire:model.defer="languages.{{ $i }}.level" placeholder="IELTS 7.5">
                                                        </div>
                                                        <button type="button" wire:click="removeLanguage({{ $i }})" style="color: #ef4444; background: none; border: none;"><i class="fa fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            @empty
                                                <p style="color: #94a3b8; text-align: center; font-style: italic; padding: 20px;">Ngoại ngữ là một điểm cộng lớn.</p>
                                            @endforelse
                                        </div>
                                        <button type="button" wire:click="addLanguage" class="cp-btn-add" style="width: 100%; padding: 12px; margin-top: 10px;">+ Thêm ngôn ngữ</button>
                                        <div class="cp-section-save">
                                            <button type="button" wire:click="saveSection('certifications')" class="cp-btn cp-btn-primary">
                                                Lưu & Tiếp tục <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                <section id="certifications" wire:key="sec-certifications" class="cp-panel" :class="{ 'active': activeSection === 'certifications' }">
                                    <div class="cp-panel__header" @click="activeSection = activeSection === 'certifications' ? null : 'certifications'">
                                        <div class="cp-panel__title">
                                            <h3>
                                                <span class="cp-panel-icon"><i class="fa fa-certificate"></i></span>
                                                Chứng chỉ
                                            </h3>
                                        </div>
                                        <i class="fa fa-chevron-down cp-panel__chevron"></i>
                                    </div>

                                    <div x-show="activeSection === 'certifications'" x-transition style="display: none;" class="cp-panel__body">
                                        @forelse ($certifications as $i => $cert)
                                            <div style="margin-bottom: 20px; background: #fffbf9; padding: 20px; border-radius: 15px; border: 1px solid #ffedd5;">
                                                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                                    <input type="text" wire:model.defer="certifications.{{ $i }}.name" placeholder="Tên chứng chỉ" style="font-weight: 800; color: var(--cv-navy); font-size: 16px; border: none; background: transparent; width: 80%;">
                                                    <button type="button" wire:click="removeCertification({{ $i }})" style="color: #ef4444; border: none; background: none;"><i class="fa fa-times-circle"></i></button>
                                                </div>
                                                <div class="cp-grid">
                                                    <div class="cp-field"><label>Tổ chức cấp</label><input type="text" wire:model.defer="certifications.{{ $i }}.issuer"></div>
                                                    <div class="cp-field"><label>Thời gian</label><input type="text" wire:model.defer="certifications.{{ $i }}.date"></div>
                                                </div>
                                            </div>
                                        @empty
                                            <p style="text-align: center; color: #94a3b8; padding: 20px;">Bạn chưa thêm chứng chỉ nào.</p>
                                        @endforelse
                                        <button type="button" wire:click="addCertification" class="cp-btn-add" style="width: 100%; padding: 15px;">+ Thêm chứng chỉ</button>
                                        <div class="cp-section-save">
                                            <button type="button" wire:click="saveSection('extra-info')" class="cp-btn cp-btn-primary">
                                                Lưu & Tiếp tục <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                <section id="extra-info" wire:key="sec-extra-info" class="cp-panel" :class="{ 'active': activeSection === 'extra-info' }">
                                    <div class="cp-panel__header" @click="activeSection = activeSection === 'extra-info' ? null : 'extra-info'">
                                        <div class="cp-panel__title">
                                            <h3>
                                                <span class="cp-panel-icon"><i class="fa fa-paperclip"></i></span>
                                                Đính kèm & Khác
                                            </h3>
                                        </div>
                                        <i class="fa fa-chevron-down cp-panel__chevron"></i>
                                    </div>

                                    <div x-show="activeSection === 'extra-info'" x-transition style="display: none;" class="cp-panel__body cp-stack">
                                        <div class="cp-field">
                                            <label for="extra_info">Thông tin bổ sung (Giải thưởng, Sở thích...)</label>
                                            <textarea id="extra_info" rows="5" wire:model.defer="extra" placeholder="Những điểm nhấn khác bạn muốn nhà tuyển dụng biết..."></textarea>
                                        </div>

                                        <div class="cp-upload" style="margin-top: 30px; background: #f8fafc; padding: 25px; border-radius: 16px; border: 2px dashed #e2e8f0;">
                                            <label style="font-weight: 800; color: var(--cv-navy); display: block; margin-bottom: 10px;">Đính kèm file CV cá nhân (PDF/DOCX)</label>
                                            <div style="display: flex; align-items: center; gap: 20px;">
                                                <input id="cv" type="file" wire:model="cv" style="font-size: 13px;">
                                                <div wire:loading wire:target="cv" style="font-size: 13px; color: var(--cv-orange);">Đang tải lên...</div>
                                            </div>
                                            
                                            @if ($this->currentCvUrl)
                                                <div style="margin-top: 15px;">
                                                    <a href="{{ $this->currentCvUrl }}" target="_blank" style="color: var(--cv-orange); font-size: 14px; font-weight: 700; text-decoration: underline;">
                                                        <i class="fa fa-file-pdf-o"></i> Xem CV hiện tại của bạn
                                                    </a>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="cp-section-save">
                                            <button type="submit" class="cp-btn" style="background: var(--cv-navy); color: white;">
                                                Hoàn tất & Cập nhật tất cả <i class="fa fa-check-double" style="margin-left: 8px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- CV Preview Modal -->
    <div wire:ignore.self class="modal fade" id="cvPreviewModal" tabindex="-1" aria-labelledby="cvPreviewModalLabel" aria-hidden="true" style="z-index: 99999;">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 24px; border: none; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: #f37021; color: #fff; padding: 20px 30px; border: none;">
                    <h5 class="modal-title" id="cvPreviewModalLabel" style="color: #fff; font-weight: 700; font-size: 20px;">Xem trước mẫu CV chuyên nghiệp</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1); opacity: 0.8;"></button>
                </div>
                <div class="modal-body" style="background: #f0f2f5; padding: 0;">
                    <iframe src="{{ route('candidates.cv.download', ['preview' => 1]) }}" style="width: 100%; height: 75vh; border: none;"></iframe>
                </div>
                <div class="modal-footer" style="padding: 15px 30px; background: #fff; border-top: 1px solid #eee;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 12px; padding: 10px 20px; font-weight: 600;">Đóng</button>
                    <a href="{{ route('candidates.cv.download') }}" class="btn btn-primary" style="background: #f37021; border: none; border-radius: 12px; padding: 10px 25px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa fa-download"></i> Tải về hồ sơ PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

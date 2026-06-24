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
@endphp

<div class="premium-dashboard-container" x-data="{ activeSection: $wire.entangle('activeSection') }">
    <style>
        .profile-hero {
            background: linear-gradient(135deg, var(--fpt-orange) 0%, #ff4b1f 100%);
            border-radius: 24px;
            padding: 40px;
            color: white;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(243, 112, 33, 0.2);
        }

        .profile-hero::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .profile-avatar-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 30px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .profile-avatar-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn-update-avatar {
            bottom: 0;
            right: 0;
            background: white;
            color: var(--fpt-orange);
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-update-avatar:hover {
            transform: scale(1.1);
            background: #fff5ee;
        }

        .cp-toc {
            background: white;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid #f1f5f9;
            position: sticky;
            top: 20px;
        }

        .cp-toc-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 14px;
            color: #64748b;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 4px;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
        }

        .cp-toc-item:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .cp-toc-item.active {
            background: #fff5ee;
            color: var(--fpt-orange);
        }

        .cp-toc-item i {
            width: 20px;
            font-size: 14px;
            opacity: 0.8;
        }

        /* Form styling */
        .premium-field-group {
            margin-bottom: 20px;
        }

        .premium-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .premium-input, .premium-textarea {
            width: 100%;
            background: #ffffff;
            border: 2px solid #f1f5f9;
            border-radius: 14px;
            padding: 14px 18px;
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            transition: all 0.2s;
            outline: none;
        }

        .premium-input:focus, .premium-textarea:focus {
            border-color: var(--fpt-orange);
            box-shadow: 0 0 0 4px var(--fpt-orange-glow);
            background: #fff;
        }

        .repeatable-card {
            background: #f8fafc;
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid #f1f5f9;
            position: relative;
        }

        .btn-remove-item {
            position: absolute;
            top: 24px;
            right: 24px;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-remove-item:hover {
            background: #ef4444;
            color: white;
        }

        .btn-add-item {
            width: 100%;
            padding: 16px;
            border: 2px dashed #e2e8f0;
            background: transparent;
            border-radius: 16px;
            color: #64748b;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-add-item:hover {
            border-color: var(--fpt-orange);
            color: var(--fpt-orange);
            background: #fff5ee;
        }
    </style>

    <div class="candidate-dashboard-area section_70" style="padding-top: 0; padding-bottom: 80px;">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    <div class="cp-toc">
                        <div style="background: var(--fpt-orange); border-radius: 18px; padding: 20px; color: white; margin-bottom: 24px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 800; margin-bottom: 4px;">{{ $completeness }}%</div>
                            <div style="font-size: 12px; font-weight: 600; opacity: 0.9;">TỈ LỆ HOÀN THIỆN HỒ SƠ</div>
                            <div style="height: 6px; background: rgba(255,255,255,0.2); border-radius: 3px; margin-top: 12px; overflow: hidden;">
                                <div style="width: {{ $completeness }}%; height: 100%; background: white;"></div>
                            </div>
                        </div>

                        <div style="margin-bottom: 12px; padding-left: 10px; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em;">Danh mục nội dung</div>
                        <nav>
                            @foreach ($sections as $section)
                                <button type="button" 
                                        @click="activeSection = '{{ $section['id'] }}'" 
                                        :class="{ 'active': activeSection === '{{ $section['id'] }}' }"
                                        class="cp-toc-item">
                                    <i class="fa {{ $section['icon'] }}"></i>
                                    {{ $section['label'] }}
                                </button>
                            @endforeach
                        </nav>
                        
                        <div style="margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                            <a href="{{ route('candidates.candidate_dashboard') }}" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: #64748b; font-weight: 600; text-decoration: none;">
                                <i class="fa fa-arrow-left" style="font-size: 12px;"></i>
                                Quay lại Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9 col-md-8">
                    @if (session('status'))
                        <div style="background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; border-radius: 16px; padding: 14px 18px; margin-bottom: 20px; font-weight: 700;">
                            {{ session('status') }}
                            @if (session('profile_incomplete'))
                                <span style="font-weight: 600;">Còn thiếu: {{ implode(', ', session('profile_incomplete')) }}.</span>
                            @endif
                        </div>
                    @endif

                    <!-- Hero Info -->
                    <div class="profile-hero">
                        <div class="d-flex align-items-center gap-4 flex-wrap flex-md-nowrap">
                            <div class="position-relative">
                                <div class="profile-avatar-wrapper">
                                    <img src="{{ $avatar ? $avatar->temporaryUrl() : $this->currentAvatarUrl }}" alt="Avatar">
                                </div>
                                <input type="file" id="avatar_upload" wire:model="avatar" class="d-none">
                                <label for="avatar_upload" class="btn-update-avatar position-absolute">
                                    <i class="fa fa-camera"></i>
                                </label>
                            </div>

                            <div class="flex-grow-1">
                                <h2 style="font-weight: 800; color: white; margin-bottom: 8px;">{{ $name }}</h2>
                                <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin-bottom: 20px;">
                                    {{ $profile_title ?: 'Vui lòng cập nhật tiêu đề hồ sơ' }}
                                </p>
                                <div class="d-flex gap-2">
                                    <span style="background: rgba(255,255,255,0.2); padding: 6px 16px; border-radius: 999px; font-size: 13px; font-weight: 600;">
                                        <i class="fa fa-envelope-o mr-2"></i> {{ $email }}
                                    </span>
                                    <span style="background: rgba(255,255,255,0.2); padding: 6px 16px; border-radius: 999px; font-size: 13px; font-weight: 600;">
                                        <i class="fa fa-phone mr-2"></i> {{ $phone ?: 'Chưa cập nhật SĐT' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form wire:submit.prevent="save">
                        <!-- Personal Info -->
                        <div x-show="activeSection === 'personal-info'" x-transition:enter.duration.400ms class="premium-panel">
                            <div class="panel-header">
                                <h4><i class="fa fa-user mr-2 text-warning"></i> Thông tin cá nhân</h4>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="premium-field-group">
                                        <label class="premium-label">Tiêu đề hồ sơ</label>
                                        <input type="text" wire:model.defer="profile_title" class="premium-input" placeholder="VD: Backend Developer (Senior/Junior)">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="premium-field-group">
                                        <label class="premium-label">Kinh nghiệm (năm)</label>
                                        <input type="number" wire:model.defer="experience_years" class="premium-input">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="premium-field-group">
                                        <label class="premium-label">Họ tên</label>
                                        <input type="text" wire:model.defer="name" class="premium-input">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="premium-field-group">
                                        <label class="premium-label">Email liên hệ</label>
                                        <input type="email" wire:model.defer="email" class="premium-input">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="premium-field-group">
                                        <label class="premium-label">Số điện thoại</label>
                                        <input type="text" wire:model.defer="phone" class="premium-input">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="premium-field-group">
                                        <label class="premium-label">Giới tính</label>
                                        <input type="text" wire:model.defer="personal_info.gender" class="premium-input" placeholder="Nam / Nữ / Khác">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="premium-field-group">
                                        <label class="premium-label">Địa chỉ</label>
                                        <input type="text" wire:model.defer="personal_info.address" class="premium-input">
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-4">
                                <button type="button" @click="activeSection = 'career-objective'" class="btn" style="background: var(--fpt-orange); color: white; padding: 12px 32px; border-radius: 12px; font-weight: 700;">Lưu & Tiếp theo</button>
                            </div>
                        </div>

                        <!-- Career Objective -->
                        <div x-show="activeSection === 'career-objective'" x-transition:enter.duration.400ms class="premium-panel">
                            <div class="panel-header">
                                <h4><i class="fa fa-bullseye mr-2 text-danger"></i> Mục tiêu nghề nghiệp</h4>
                            </div>
                            <div class="premium-field-group">
                                <label class="premium-label">Giới thiệu bản thân và mục tiêu ứng tuyển</label>
                                <textarea wire:model.defer="career_objective" class="premium-textarea" rows="6" placeholder="Mời bạn nhập mục tiêu ngắn hạn và dài hạn của mình..."></textarea>
                            </div>
                            <div class="text-end mt-4">
                                <button type="button" @click="activeSection = 'desired-job'" class="btn" style="background: var(--fpt-orange); color: white; padding: 12px 32px; border-radius: 12px; font-weight: 700;">Lưu & Tiếp theo</button>
                            </div>
                        </div>

                        <!-- Desired Job -->
                        <div x-show="activeSection === 'desired-job'" x-transition:enter.duration.400ms class="premium-panel">
                            <div class="panel-header">
                                <h4><i class="fa fa-crosshairs mr-2 text-info"></i> Công việc mong muốn</h4>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="premium-field-group">
                                        <label class="premium-label">Vị trí mong muốn</label>
                                        <input type="text" wire:model.defer="desired_job.position" class="premium-input">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="premium-field-group">
                                        <label class="premium-label">Mức lương kỳ vọng</label>
                                        <input type="text" wire:model.defer="desired_job.expected_salary" class="premium-input">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="premium-field-group">
                                        <label class="premium-label">Cấp bậc</label>
                                        <input type="text" wire:model.defer="desired_job.level" class="premium-input">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="premium-field-group">
                                        <label class="premium-label">Địa điểm làm việc</label>
                                        <input type="text" wire:model.defer="desired_job.location" class="premium-input">
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-4">
                                <button type="button" @click="activeSection = 'experiences'" class="btn" style="background: var(--fpt-orange); color: white; padding: 12px 32px; border-radius: 12px; font-weight: 700;">Lưu & Tiếp theo</button>
                            </div>
                        </div>

                        <!-- Experiences -->
                        <div x-show="activeSection === 'experiences'" x-transition:enter.duration.400ms class="premium-panel">
                            <div class="panel-header">
                                <h4><i class="fa fa-briefcase mr-2 text-primary"></i> Kinh nghiệm làm việc</h4>
                                <button type="button" wire:click="addExperience" class="btn btn-sm" style="background: #eff6ff; color: #2563eb; font-weight: 700; border-radius: 10px;">+ Thêm mới</button>
                            </div>
                            
                            @foreach($experiences as $index => $exp)
                            <div class="repeatable-card">
                                <button type="button" wire:click="removeExperience({{ $index }})" class="btn-remove-item">Xóa</button>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="premium-field-group">
                                            <label class="premium-label">Tên công ty</label>
                                            <input type="text" wire:model.defer="experiences.{{ $index }}.company" class="premium-input">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="premium-field-group">
                                            <label class="premium-label">Chức danh</label>
                                            <input type="text" wire:model.defer="experiences.{{ $index }}.position" class="premium-input">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="premium-field-group">
                                            <label class="premium-label">Bắt đầu - Kết thúc</label>
                                            <input type="text" wire:model.defer="experiences.{{ $index }}.from" class="premium-input" placeholder="VD: 01/2020 - 05/2023">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="premium-field-group">
                                            <label class="premium-label">Mô tả công việc</label>
                                            <textarea wire:model.defer="experiences.{{ $index }}.description" class="premium-textarea" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            @if(count($experiences) == 0)
                            <button type="button" wire:click="addExperience" class="btn-add-item">
                                <i class="fa fa-plus"></i> Thêm kinh nghiệm của bạn
                            </button>
                            @endif

                            <div class="text-end mt-4">
                                <button type="button" @click="activeSection = 'educations'" class="btn" style="background: var(--fpt-orange); color: white; padding: 12px 32px; border-radius: 12px; font-weight: 700;">Lưu & Tiếp theo</button>
                            </div>
                        </div>

                        <!-- Educations -->
                        <div x-show="activeSection === 'educations'" x-transition:enter.duration.400ms class="premium-panel">
                            <div class="panel-header">
                                <h4><i class="fa fa-graduation-cap mr-2 text-success"></i> Học vấn</h4>
                                <button type="button" wire:click="addEducation" class="btn btn-sm" style="background: #f0fdf4; color: #16a34a; font-weight: 700; border-radius: 10px;">+ Thêm mới</button>
                            </div>

                            @foreach($educations as $index => $edu)
                            <div class="repeatable-card">
                                <button type="button" wire:click="removeEducation({{ $index }})" class="btn-remove-item">Xóa</button>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="premium-field-group">
                                            <label class="premium-label">Trường học</label>
                                            <input type="text" wire:model.defer="educations.{{ $index }}.school" class="premium-input">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="premium-field-group">
                                            <label class="premium-label">Thời gian</label>
                                            <input type="text" wire:model.defer="educations.{{ $index }}.from" class="premium-input">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="premium-field-group">
                                            <label class="premium-label">Chuyên ngành / Bằng cấp</label>
                                            <input type="text" wire:model.defer="educations.{{ $index }}.degree" class="premium-input">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            <div class="text-end mt-4">
                                <button type="button" @click="activeSection = 'skills'" class="btn" style="background: var(--fpt-orange); color: white; padding: 12px 32px; border-radius: 12px; font-weight: 700;">Lưu & Tiếp theo</button>
                            </div>
                        </div>

                        <!-- Skills -->
                        <div x-show="activeSection === 'skills'" x-transition:enter.duration.400ms class="premium-panel">
                            <div class="panel-header">
                                <h4><i class="fa fa-wrench mr-2 text-secondary"></i> Kỹ năng chuyên môn</h4>
                            </div>
                            <div class="row">
                                @foreach($skills as $index => $skill)
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex gap-2">
                                        <input type="text" wire:model.defer="skills.{{ $index }}.name" class="premium-input" placeholder="Tên kỹ năng">
                                        <button type="button" wire:click="removeSkill({{ $index }})" class="btn" style="color: #ef4444;"><i class="fa fa-trash"></i></button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" wire:click="addSkill" class="btn-add-item mt-2">
                                <i class="fa fa-plus"></i> Thêm kỹ năng mới
                            </button>
                            <div class="text-end mt-4">
                                <button type="button" @click="activeSection = 'languages'" class="btn" style="background: var(--fpt-orange); color: white; padding: 12px 32px; border-radius: 12px; font-weight: 700;">Lưu & Tiếp theo</button>
                            </div>
                        </div>

                        <!-- Languages -->
                        <div x-show="activeSection === 'languages'" x-transition:enter.duration.400ms class="premium-panel">
                            <div class="panel-header">
                                <h4><i class="fa fa-language mr-2 text-info"></i> Ngôn ngữ</h4>
                            </div>
                            @foreach($languages as $index => $lang)
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <input type="text" wire:model.defer="languages.{{ $index }}.name" class="premium-input" placeholder="VD: Tiếng Anh">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" wire:model.defer="languages.{{ $index }}.level" class="premium-input" placeholder="VD: IELTS 7.0">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" wire:click="removeLanguage({{ $index }})" class="btn w-100" style="color: #ef4444; height: 50px;"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                            @endforeach
                            <button type="button" wire:click="addLanguage" class="btn-add-item">
                                <i class="fa fa-plus"></i> Thêm ngôn ngữ
                            </button>
                            <div class="text-end mt-4">
                                <button type="button" @click="activeSection = 'certifications'" class="btn" style="background: var(--fpt-orange); color: white; padding: 12px 32px; border-radius: 12px; font-weight: 700;">Lưu & Tiếp theo</button>
                            </div>
                        </div>

                         <!-- Certifications -->
                         <div x-show="activeSection === 'certifications'" x-transition:enter.duration.400ms class="premium-panel">
                            <div class="panel-header">
                                <h4><i class="fa fa-certificate mr-2 text-warning"></i> Chứng chỉ</h4>
                            </div>
                            @foreach($certifications as $index => $cert)
                            <div class="repeatable-card">
                                <button type="button" wire:click="removeCertification({{ $index }})" class="btn-remove-item">Xóa</button>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="premium-field-group">
                                            <label class="premium-label">Tên chứng chỉ</label>
                                            <input type="text" wire:model.defer="certifications.{{ $index }}.name" class="premium-input">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="premium-field-group">
                                            <label class="premium-label">Tổ chức cấp / Năm</label>
                                            <input type="text" wire:model.defer="certifications.{{ $index }}.issuer" class="premium-input">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <button type="button" wire:click="addCertification" class="btn-add-item">
                                <i class="fa fa-plus"></i> Thêm chứng chỉ mới
                            </button>
                            <div class="text-end mt-4">
                                <button type="button" @click="activeSection = 'extra-info'" class="btn" style="background: var(--fpt-orange); color: white; padding: 12px 32px; border-radius: 12px; font-weight: 700;">Lưu & Tiếp theo</button>
                            </div>
                        </div>

                        <!-- Extra Info & CV Upload -->
                        <div x-show="activeSection === 'extra-info'" x-transition:enter.duration.400ms class="premium-panel">
                            <div class="panel-header">
                                <h4><i class="fa fa-paperclip mr-2 text-muted"></i> Đính kèm file CV cá nhân</h4>
                            </div>
                            
                            <div style="background: #fff5ee; border: 2px dashed var(--fpt-orange); border-radius: 20px; padding: 40px; text-align: center; margin-bottom: 30px;">
                                <div style="width: 64px; height: 64px; background: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: var(--fpt-orange); font-size: 24px; box-shadow: 0 4px 15px rgba(243, 112, 33, 0.1);">
                                    <i class="fa fa-cloud-upload"></i>
                                </div>
                                <h5 style="font-weight: 800; color: #1e293b; margin-bottom: 8px;">Tải lên CV bản cứng của bạn</h5>
                                <p style="color: #64748b; font-size: 14px; margin-bottom: 24px;">Hệ thống hỗ trợ các định dạng PDF, DOCX (Tối đa 10MB)</p>
                                
                                <input type="file" id="cv_upload" wire:model="cv" class="d-none">
                                <label for="cv_upload" class="btn" style="background: var(--fpt-orange); color: white; padding: 12px 40px; border-radius: 12px; font-weight: 700; cursor: pointer;">
                                    Chọn file từ máy tính
                                </label>
                                
                                <div wire:loading wire:target="cv" class="mt-3 text-orange">
                                    <i class="fa fa-spinner fa-spin mr-2"></i> Đang tải lên...
                                </div>

                                @if($this->currentCvUrl)
                                <div class="mt-4 pt-3 border-top" style="border-top-color: rgba(243, 112, 33, 0.1) !important;">
                                    <a href="{{ $this->currentCvUrl }}" target="_blank" style="color: #1e293b; text-decoration: underline; font-weight: 700;">
                                        <i class="fa fa-file-pdf-o mr-2 text-danger"></i> Xem CV hiện tại của bạn
                                    </a>
                                </div>
                                @endif
                            </div>

                            <div class="premium-field-group">
                                <label class="premium-label">Thông tin bổ sung khác</label>
                                <textarea wire:model.defer="extra" class="premium-textarea" rows="4" placeholder="Giải thưởng, hoạt động ngoại khóa hoặc các ghi chú khác..."></textarea>
                            </div>

                            <div class="text-end mt-4 d-flex justify-content-between align-items-center">
                                <span class="text-muted small italic">* Nhấn Lưu bên dưới để cập nhật toàn bộ hồ sơ</span>
                                <button type="submit" class="btn" style="background: #0f172a; color: white; padding: 14px 48px; border-radius: 12px; font-weight: 800; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                                    <i class="fa fa-check-circle mr-2"></i> LƯU TẤT CẢ THAY ĐỔI
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

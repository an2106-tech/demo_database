@php
    $branch = $job->branch;
    $department = $job->department;
    $workplace = $job->workplace;
    $salaryLabel = $job->salary_range ? (is_array($job->salary_range) ? ($job->salary_range['min'] ? number_format($job->salary_range['min'], 0, ',', '.') . ' - ' . number_format($job->salary_range['max'], 0, ',', '.') : 'Thỏa thuận') : $job->salary_range) : 'Thỏa thuận';
    $branchCityDisplay = \App\Enums\VietnamProvince::tryFrom((string)($branch?->city ?? ''))?->label() ?? $branch?->city;
@endphp

<div class="apply-premium-container">
    <style>
        .apply-premium-container {
            --fpt-orange: #f37021;
            --fpt-orange-dark: #d65a12;
            --fpt-blue: #1e2d7d;
            --soft-gray: #f8fafc;
            --border-light: #e2e8f0;
            --text-dark: #0f172a;
            --text-light: #64748b;
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            min-height: 100vh;
            padding: 160px 0 60px; /* Increased top padding to 160px for maximum clearance */
            font-family: 'Lexend', 'Inter', sans-serif;
        }

        .apply-shell {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 40px;
            padding: 0 20px;
        }

        /* Sidebar Design - Premium Glassmorphism */
        .job-card-sidebar {
            background: #fff;
            border-radius: 32px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: sticky;
            top: 40px;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .job-card-header {
            background: linear-gradient(135deg, var(--fpt-orange) 0%, var(--fpt-orange-dark) 100%);
            padding: 40px 30px;
            text-align: center;
            color: #fff;
        }

        .company-logo-wrapper {
            width: 100px;
            height: 100px;
            background: #fff;
            border-radius: 24px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .company-logo-wrapper img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .sidebar-job-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            line-height: 1.3;
            color: #ffffff !important;
        }

        .sidebar-company-name {
            font-size: 0.95rem;
            font-weight: 500;
            opacity: 0.9;
            color: #ffffff;
        }

        .job-meta-list {
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .meta-entry {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px dashed rgba(15, 23, 42, 0.1);
            background: transparent;
            transition: all 0.3s ease;
        }

        .meta-entry:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .meta-entry:hover {
            transform: translateX(5px);
        }

        .meta-icon-box {
            width: 42px;
            height: 42px;
            background: rgba(243, 112, 33, 0.08);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--fpt-orange);
            font-size: 1.1rem;
        }

        .meta-content label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-light);
            margin-bottom: 2px;
        }

        .meta-content span {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        /* Form Design - Clean & High-End */
        .apply-form-container {
            background: #fff;
            border-radius: 32px;
            padding: 50px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            border: 1px solid #f1f5f9;
        }

        .form-headline {
            margin-bottom: 40px;
        }

        .form-headline h2 {
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .form-headline h2 span {
            color: var(--fpt-orange);
        }

        .form-headline p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .section-separator {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 35px 0 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-separator::after {
            content: "";
            height: 2px;
            flex-grow: 1;
            background: linear-gradient(90deg, #f1f5f9, transparent);
        }

        .grid-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .premium-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .premium-field label {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-dark);
            margin-left: 5px;
        }

        .premium-input {
            height: 56px;
            border: 2px solid #f1f5f9;
            border-radius: 16px;
            padding: 0 20px;
            font-size: 1rem;
            font-weight: 500;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .premium-input:focus {
            border-color: var(--fpt-orange);
            background: #fff;
            box-shadow: 0 10px 20px rgba(243, 112, 33, 0.08);
            outline: none;
        }

        textarea.premium-input {
            height: auto;
            min-height: 140px;
            padding: 18px 20px;
        }

        /* Modern File Upload */
        .upload-wrapper {
            border: 2px dashed #cbd5e1;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-wrapper:hover {
            border-color: var(--fpt-orange);
            background: #fff5ef;
        }

        .upload-icon-anim {
            width: 64px;
            height: 64px;
            background: var(--fpt-orange);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.6rem;
            box-shadow: 0 8px 16px rgba(243, 112, 33, 0.2);
        }

        .upload-hint h4 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .upload-hint p {
            font-size: 0.9rem;
            color: var(--text-light);
            margin: 0;
        }

        .existing-cv-pill {
            margin-top: 20px;
            padding: 15px 20px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: fadeIn 0.5s ease;
        }

        .existing-cv-pill span {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .existing-cv-pill a {
            color: var(--fpt-orange);
            font-weight: 700;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .selected-cv-pill {
            margin-top: 18px;
            padding: 12px 16px;
            border: 1px solid #bbf7d0;
            border-radius: 14px;
            background: #f0fdf4;
            color: #166534;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            max-width: 100%;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .selected-cv-pill strong {
            color: #0f172a;
            overflow-wrap: anywhere;
        }

        .apply-sync-options {
            margin-top: 18px;
            padding: 18px 20px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .apply-sync-options .form-check {
            margin: 0;
            padding-left: 1.7rem;
        }

        .apply-sync-options .form-check + .form-check {
            margin-top: 12px;
        }

        .apply-sync-options .form-check-input {
            margin-top: 0.25rem;
        }

        .apply-sync-options .form-check-label {
            font-weight: 700;
            color: var(--text-dark);
        }

        .apply-sync-options__hint {
            margin-top: 10px;
            font-size: 0.88rem;
            color: var(--text-light);
            line-height: 1.6;
        }

        /* Submit Button */
        .submit-trigger {
            width: 100%;
            height: 64px;
            background: linear-gradient(135deg, var(--fpt-orange), var(--fpt-orange-dark));
            border: none;
            border-radius: 18px;
            color: #fff;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            margin-top: 40px;
            box-shadow: 0 12px 24px rgba(243, 112, 33, 0.25);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .submit-trigger:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(243, 112, 33, 0.35);
        }

        .submit-trigger:active {
            transform: translateY(0);
        }

        /* Success Animation */
        .celebration-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(10px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .celebration-card {
            background: #fff;
            padding: 60px 40px;
            border-radius: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
        }

        .celebration-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 25px;
            animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .celebration-card h3 {
            font-size: 1.8rem;
            font-weight: 900;
            margin-bottom: 15px;
        }

        @keyframes bounceIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @media (max-width: 991px) {
            .apply-shell { grid-template-columns: 1fr; }
            .job-card-sidebar { position: static; }
        }

        @media (max-width: 767px) {
            .grid-row { grid-template-columns: 1fr; }
            .apply-form-container { padding: 30px; }
            .form-headline h2 { font-size: 1.8rem; }
        }
    </style>

    @if ($showSuccessModal)
    <div class="celebration-overlay" wire:click="closeSuccessModal">
        <div class="celebration-card" wire:click.stop>
            <div class="celebration-icon">
                <i class="fa fa-check-circle"></i>
            </div>
            <h3>Khởi động hành trình mới!</h3>
            <p class="text-muted">Hồ sơ của bạn đã được gửi thành công đến đội ngũ tuyển dụng <strong>{{ $branch?->name }}</strong>. Chúng tôi sẽ phản hồi trong thời gian sớm nhất.</p>
            <button class="submit-trigger" wire:click="closeSuccessModal" style="margin-top: 25px;">Đã rõ, xin cảm ơn!</button>
        </div>
    </div>
    @endif

    <div class="apply-shell">
        <!-- Sidebar -->
        <aside class="job-card-sidebar">
            <div class="job-card-header">
                <div class="company-logo-wrapper">
                    <img src="{{ $branch?->image ? asset('storage/' . $branch->image) : asset('assets/img/company-logo-1.png') }}" alt="{{ $branch?->name }}">
                </div>
                <h1 class="sidebar-job-title text-white" style="color: #ffffff !important;">{{ $job->title }}</h1>
                <p class="sidebar-company-name text-white" style="color: rgba(255, 255, 255, 0.9) !important;">{{ $branch?->name }}</p>
            </div>

            <div class="job-meta-list">
                <div class="meta-entry">
                    <div class="meta-icon-box"><i class="fa fa-money"></i></div>
                    <div class="meta-content">
                        <label>Mức lương</label>
                        <span>{{ $salaryLabel }}</span>
                    </div>
                </div>
                <div class="meta-entry">
                    <div class="meta-icon-box"><i class="fa fa-map-marker"></i></div>
                    <div class="meta-content">
                        <label>Địa điểm</label>
                        <span>{{ $branchCityDisplay ?? 'Toàn quốc' }}</span>
                    </div>
                </div>
                <div class="meta-entry">
                    <div class="meta-icon-box"><i class="fa fa-calendar"></i></div>
                    <div class="meta-content">
                        <label>Hạn nộp hồ sơ</label>
                        <span>{{ $job->deadline?->format('d/m/Y') ?? 'Tuyển liên tục' }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Form -->
        <main class="apply-form-container">
            <div class="form-headline">
                <h2>Khởi đầu <span>hành trình mới</span></h2>
                <p>Gửi hồ sơ ngay để bắt đầu hành trình sự nghiệp đầy khát vọng cùng chúng tôi.</p>
            </div>

            @if($this->requiresCandidateActivation)
                <div class="alert alert-warning border-0 mb-4" style="border-radius: 18px; background: #fff7ed; color: #9a3412; padding: 20px 24px;">
                    <div style="font-weight: 800; margin-bottom: 6px;">
                        Tài khoản hiện tại chưa có hồ sơ ứng viên
                    </div>
                    <div style="line-height: 1.6;">
                        Vui lòng kích hoạt hồ sơ ứng viên trước khi ứng tuyển. Việc này giúp hệ thống tách rõ vai trò nhà tuyển dụng và ứng viên trên cùng một tài khoản.
                    </div>
                    <a href="{{ $this->candidateActivationUrl }}" class="submit-trigger" style="margin-top: 18px; text-decoration: none;">
                        Kích hoạt hồ sơ ứng viên
                    </a>
                </div>
            @else
            <form wire:submit.prevent="submit">
                @error('application')
                    <div class="alert alert-info border-0 mb-4" style="border-radius: 18px; background: #eff6ff; color: #1d4ed8; padding: 18px 22px; font-weight: 700;">
                        {{ $message }}
                    </div>
                @enderror

                <div class="section-separator">Thông tin của bạn</div>

                <div class="grid-row">
                    <div class="premium-field">
                        <label>Họ và tên *</label>
                        <input type="text" wire:model="name" class="premium-input" placeholder="Nguyễn Văn A">
                        @error('name') <span class="text-danger small fw-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="premium-field">
                        <label>Địa chỉ Email *</label>
                        <input type="email" wire:model="email" class="premium-input" placeholder="example@email.com">
                        @error('email') <span class="text-danger small fw-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid-row">
                    <div class="premium-field">
                        <label>Số điện thoại</label>
                        <input type="text" wire:model="phone" class="premium-input" placeholder="09xx xxx xxx">
                        @error('phone') <span class="text-danger small fw-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="premium-field">
                        <label>Số năm kinh nghiệm</label>
                        <input type="number" wire:model="experience_years" class="premium-input" placeholder="Ví dụ: 2">
                        @error('experience_years') <span class="text-danger small fw-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="section-separator">Chi tiết chuyên môn</div>

                <div class="premium-field mb-4">
                    <label>Vị trí hiện tại / Tiêu đề hồ sơ</label>
                    <input type="text" wire:model="profile_title" class="premium-input" placeholder="VD: Senior Web Developer">
                    @error('profile_title') <span class="text-danger small fw-bold mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="premium-field mb-4">
                    <label class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-weight: 800; font-size: 14px; color: #0f172a;">
                            <i class="fa fa-file-text-o text-warning me-1"></i> Chọn Bản CV Dùng Để Ứng Tuyển *
                        </span>
                        @if(Auth::check())
                            <a href="{{ route('candidates.manage_cv') }}" target="_blank" class="text-primary small" style="font-weight: 600; text-decoration: none;">
                                <i class="fa fa-cog me-1"></i> Quản lý CV của tôi
                            </a>
                        @endif
                    </label>

                    <div class="cv-selector-container" style="display: grid; gap: 10px; margin-bottom: 14px;">
                        <!-- Online CV Templates -->
                        @foreach($availableTemplates as $tpl)
                            @php
                                $val = 'online_' . $tpl['id'];
                                $isPrimary = (data_get($primaryCv, 'type') === 'online' && data_get($primaryCv, 'template', 'fpt-modern') === $tpl['id']);
                            @endphp
                            <label class="cv-option-card {{ $selectedCvOption === $val ? 'is-selected' : '' }}" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border: 1.5px solid {{ $selectedCvOption === $val ? '#f37021' : '#e2e8f0' }}; background: {{ $selectedCvOption === $val ? '#fffaf5' : '#ffffff' }}; border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <input type="radio" wire:model.live="selectedCvOption" value="{{ $val }}" style="accent-color: #f37021; width: 18px; height: 18px;">
                                    <div>
                                        <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;">
                                            <i class="fa fa-desktop text-primary me-1"></i> {{ $tpl['name'] }} (CV Trực tuyến)
                                            @if($isPrimary)
                                                <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">⭐ CV CHÍNH</span>
                                            @endif
                                        </div>
                                        <div style="font-size: 11.5px; color: #64748b;">Hệ thống tự động xuất PDF chuẩn in ấn A4</div>
                                    </div>
                                </div>
                                <a href="{{ route('candidates.cv.download', ['template' => $tpl['id'], 'mode' => 'stream', 'action' => 'view']) }}" target="_blank" class="btn btn-sm btn-light" style="font-size: 11px; border-radius: 6px; padding: 3px 8px;" onclick="event.stopPropagation();">
                                    <i class="fa fa-eye"></i> Xem trước
                                </a>
                            </label>
                        @endforeach

                        <!-- Existing Uploaded Attachments -->
                        @if(isset($attachments) && $attachments->isNotEmpty())
                            @foreach($attachments as $att)
                                @php
                                    $val = 'attachment_' . $att->id;
                                    $isPrimary = (data_get($primaryCv, 'type') === 'attachment' && (int)data_get($primaryCv, 'attachment_id') === $att->id);
                                @endphp
                                <label class="cv-option-card {{ $selectedCvOption === $val ? 'is-selected' : '' }}" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border: 1.5px solid {{ $selectedCvOption === $val ? '#f37021' : '#e2e8f0' }}; background: {{ $selectedCvOption === $val ? '#fffaf5' : '#ffffff' }}; border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <input type="radio" wire:model.live="selectedCvOption" value="{{ $val }}" style="accent-color: #f37021; width: 18px; height: 18px;">
                                        <div>
                                            <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;">
                                                <i class="fa fa-file-pdf-o text-danger me-1"></i> {{ $att->original_filename }}
                                                @if($isPrimary)
                                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">⭐ CV CHÍNH</span>
                                                @endif
                                            </div>
                                            <div style="font-size: 11.5px; color: #64748b;">File đính kèm ({{ round($att->size_bytes / 1024) }} KB)</div>
                                        </div>
                                    </div>
                                    <a href="{{ Storage::disk('public')->url($att->path) }}" target="_blank" class="btn btn-sm btn-light" style="font-size: 11px; border-radius: 6px; padding: 3px 8px;" onclick="event.stopPropagation();">
                                        <i class="fa fa-eye"></i> Xem file
                                    </a>
                                </label>
                            @endforeach
                        @endif

                        <!-- Option: Upload Brand New CV -->
                        <label class="cv-option-card {{ $selectedCvOption === 'new_upload' ? 'is-selected' : '' }}" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1.5px solid {{ $selectedCvOption === 'new_upload' ? '#f37021' : '#e2e8f0' }}; background: {{ $selectedCvOption === 'new_upload' ? '#fffaf5' : '#ffffff' }}; border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                            <input type="radio" wire:model.live="selectedCvOption" value="new_upload" style="accent-color: #f37021; width: 18px; height: 18px;">
                            <div>
                                <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;">
                                    <i class="fa fa-cloud-upload text-info me-1"></i> Tải lên file CV mới từ máy tính
                                </div>
                                <div style="font-size: 11.5px; color: #64748b;">Chọn file PDF/DOC/DOCX khác cho riêng đợt ứng tuyển này</div>
                            </div>
                        </label>
                    </div>

                    <!-- Dropzone appears when new_upload is selected -->
                    <div x-show="$wire.selectedCvOption === 'new_upload'" x-transition class="mt-3">
                        <div
                            class="upload-wrapper"
                            onclick="document.getElementById('cv-file').click()"
                            x-data="{ selectedCvName: '' }"
                        >
                            <input
                                type="file"
                                id="cv-file"
                                wire:model="cv"
                                hidden
                                accept="{{ \App\Support\CvUpload::acceptAttribute() }}"
                                x-on:change="selectedCvName = $event.target.files?.[0]?.name || ''"
                            >
                            <div class="upload-icon-anim"><i class="fa fa-cloud-upload"></i></div>
                            <div class="upload-hint">
                                <h4>Nhấp để chọn file hồ sơ mới</h4>
                                <p>Định dạng hỗ trợ: PDF, DOC, DOCX (Tối đa 10MB)</p>
                            </div>
                            <div
                                x-show="selectedCvName"
                                x-cloak
                                class="selected-cv-pill"
                                role="status"
                                aria-live="polite"
                            >
                                <i class="fa fa-check-circle"></i>
                                <span>Đã chọn: <strong x-text="selectedCvName"></strong></span>
                            </div>
                            @if($cv && method_exists($cv, 'getClientOriginalName'))
                                <div class="selected-cv-pill" role="status" aria-live="polite">
                                    <i class="fa fa-check-circle"></i>
                                    <span>Đã tải lên tạm thời: <strong>{{ $cv->getClientOriginalName() }}</strong></span>
                                </div>
                            @endif
                        </div>
                        @error('cv') <span class="text-danger small fw-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if(Auth::check() && ! $this->requiresCandidateActivation)
                    <div class="apply-sync-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sync-profile-to-candidate" wire:model="sync_profile_to_candidate">
                            <label class="form-check-label" for="sync-profile-to-candidate">
                                Cập nhật thông tin này vào hồ sơ ứng viên của tôi
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="use-cv-as-primary" wire:model="use_cv_as_primary">
                            <label class="form-check-label" for="use-cv-as-primary">
                                Dùng CV này làm CV chính
                            </label>
                        </div>
                        <div class="apply-sync-options__hint">
                            Nếu không chọn, thông tin chỉ lưu cho lần ứng tuyển này và không ghi đè hồ sơ chính.
                        </div>
                    </div>
                @endif

                <div class="premium-field">
                    <label>Giới thiệu ngắn gọn hoặc mục tiêu nghề nghiệp</label>
                    <textarea wire:model="career_objective" class="premium-input" placeholder="Hãy viết vài dòng chân thành về định hướng của bạn..."></textarea>
                    @error('career_objective') <span class="text-danger small fw-bold mt-1">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="submit-trigger" wire:loading.attr="disabled">
                    <span wire:loading.remove>Xác nhận và gửi đơn ứng tuyển</span>
                    <span wire:loading><i class="fa fa-spinner fa-spin"></i> Đang xử lý hồ sơ...</span>
                </button>
            </form>
            @endif
        </main>
    </div>
</div>

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
            color: #ffffff;
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
            gap: 20px;
            padding: 15px;
            border-radius: 16px;
            background: var(--soft-gray);
            transition: all 0.3s ease;
        }

        .meta-entry:hover {
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transform: translateX(5px);
        }

        .meta-icon-box {
            width: 45px;
            height: 45px;
            background: #fff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--fpt-orange);
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
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
                <h1 class="sidebar-job-title">{{ $job->title }}</h1>
                <p class="sidebar-company-name">{{ $branch?->name }}</p>
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

            <form wire:submit.prevent="submit">
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
                    <label>Tải lên Hồ sơ (CV) *</label>
                    <div class="upload-wrapper" onclick="document.getElementById('cv-file').click()">
                        <input type="file" id="cv-file" wire:model="cv" hidden>
                        <div class="upload-icon-anim"><i class="fa fa-cloud-upload"></i></div>
                        <div class="upload-hint">
                            <h4>Nhấp để chọn file hồ sơ</h4>
                            <p>Định dạng hỗ trợ: PDF, DOC, DOCX (Tối đa 10MB)</p>
                        </div>
                    </div>
                    @error('cv') <span class="text-danger small fw-bold mt-1">{{ $message }}</span> @enderror

                    @if ($this->existingCvName)
                    <div class="existing-cv-pill">
                        <div>
                            <i class="fa fa-file-pdf-o text-primary me-2"></i>
                            <span>{{ $this->existingCvName }}</span>
                        </div>
                        <a href="{{ $this->existingCvUrl }}" target="_blank">Xem ngay</a>
                    </div>
                    @endif
                </div>

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
        </main>
    </div>
</div>

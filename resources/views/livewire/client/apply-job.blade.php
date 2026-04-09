@php
    $branch = $job->branch;
    $department = $job->department;
    $workplace = $job->workplace;
    $salary = is_array($job->salary_range) ? $job->salary_range : [];
    $salaryMin = $salary['min'] ?? null;
    $salaryMax = $salary['max'] ?? null;
    $salaryText = match (true) {
        $salaryMin && $salaryMax => number_format((float) $salaryMin, 0, ',', '.') . ' - ' . number_format((float) $salaryMax, 0, ',', '.') . ' VND',
        $salaryMin => 'Từ ' . number_format((float) $salaryMin, 0, ',', '.') . ' VND',
        $salaryMax => 'Đến ' . number_format((float) $salaryMax, 0, ',', '.') . ' VND',
        default => 'Thỏa thuận',
    };
@endphp

<div>
    <style>
        .apply-job-page {
            --apply-primary: #b85d1f;
            --apply-primary-dark: #7a3810;
            --apply-secondary: #e39b5d;
            --apply-text: #1f1712;
            --apply-muted: #62554d;
            --apply-border: #dcc8b8;
            --apply-surface: #fff;
            --apply-surface-soft: #faf4ee;
            --apply-shadow: 0 16px 42px rgba(41, 29, 20, 0.1);
            position: relative;
        }

        .apply-job-page::before,
        .apply-job-page::after {
            border-radius: 999px;
            content: "";
            filter: blur(14px);
            position: absolute;
            z-index: 0;
        }

        .apply-job-page::before {
            background: rgba(184, 93, 31, 0.09);
            height: 180px;
            left: -30px;
            top: 40px;
            width: 180px;
        }

        .apply-job-page::after {
            background: rgba(227, 155, 93, 0.14);
            height: 220px;
            right: -40px;
            top: 240px;
            width: 220px;
        }

        .apply-shell {
            align-items: start;
            display: grid;
            gap: 24px;
            grid-template-columns: minmax(280px, 0.92fr) minmax(0, 1.35fr);
            position: relative;
            z-index: 1;
        }

        .apply-sidebar,
        .apply-form-card {
            background: var(--apply-surface);
            border: 1px solid var(--apply-border);
            border-radius: 24px;
            box-shadow: var(--apply-shadow);
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .apply-sidebar:hover,
        .apply-form-card:hover {
            box-shadow: 0 20px 52px rgba(41, 29, 20, 0.14);
            transform: translateY(-2px);
        }

        .apply-sidebar {
            position: sticky;
            top: 24px;
        }

        .apply-hero {
            background: linear-gradient(135deg, #fbf1e8 0%, #f0dfcf 100%);
            border-bottom: 1px solid var(--apply-border);
            overflow: hidden;
            padding: 26px 24px;
            position: relative;
        }

        .apply-hero::before {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.68), rgba(255, 255, 255, 0));
            content: "";
            height: 120px;
            position: absolute;
            right: -10px;
            top: -28px;
            transform: rotate(18deg);
            width: 160px;
        }

        .apply-eyebrow {
            color: var(--apply-primary-dark);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .apply-title {
            color: #1c140f;
            font-size: 28px;
            font-weight: 800;
            line-height: 1.25;
            margin: 0 0 10px;
        }

        .apply-subtitle {
            color: #534841;
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
        }

        .apply-highlight-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .apply-chip {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(122, 56, 16, 0.16);
            border-radius: 999px;
            color: #593f31;
            font-size: 12px;
            font-weight: 700;
            padding: 9px 12px;
        }

        .apply-summary,
        .apply-section {
            padding: 22px 24px;
        }

        .apply-section + .apply-section,
        .apply-summary + .apply-section {
            border-top: 1px solid #f3e7dd;
        }

        .apply-summary h3,
        .apply-section h3 {
            color: #1d1511;
            font-size: 18px;
            margin: 0 0 14px;
        }

        .apply-list {
            display: grid;
            gap: 12px;
        }

        .apply-item {
            background: var(--apply-surface-soft);
            border: 1px solid #e2d3c6;
            border-radius: 16px;
            padding: 14px 16px 14px 20px;
            position: relative;
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .apply-item::before {
            background: linear-gradient(180deg, var(--apply-primary), #ff9b57);
            border-radius: 999px;
            content: "";
            height: calc(100% - 18px);
            left: 10px;
            opacity: 0;
            position: absolute;
            top: 9px;
            transition: opacity 0.2s ease;
            width: 4px;
        }

        .apply-item:hover {
            background: #fffdfb;
            border-color: #cfae94;
            transform: translateX(4px);
        }

        .apply-item:hover::before {
            opacity: 1;
        }

        .apply-item span {
            color: var(--apply-muted);
            display: block;
            font-size: 12px;
            letter-spacing: 0.04em;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .apply-item strong,
        .apply-item div {
            color: var(--apply-text);
            font-size: 15px;
            line-height: 1.6;
        }

        .apply-note {
            background: #f3e7dc;
            border: 1px dashed #cda98b;
            border-radius: 18px;
            color: #5c473a;
            line-height: 1.7;
            padding: 16px;
        }

        .apply-form-head {
            background: linear-gradient(135deg, #ffffff 0%, #f7ede4 100%);
            border-bottom: 1px solid var(--apply-border);
            padding: 28px 28px 22px;
            position: relative;
        }

        .apply-form-head::after {
            background: linear-gradient(90deg, var(--apply-primary), var(--apply-secondary));
            border-radius: 999px;
            bottom: 0;
            content: "";
            height: 4px;
            left: 28px;
            position: absolute;
            width: 150px;
        }

        .apply-form-head h3 {
            color: #1d1511;
            font-size: 26px;
            margin: 0 0 8px;
        }

        .apply-form-head p {
            color: var(--apply-muted);
            font-size: 15px;
            line-height: 1.7;
            margin: 0;
        }

        .apply-form-body {
            padding: 28px;
        }

        .apply-steps {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 0 0 22px;
        }

        .apply-step {
            align-items: center;
            background: #f8eee6;
            border: 1px solid #ddc9b8;
            border-radius: 999px;
            color: #5c4638;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            gap: 8px;
            padding: 8px 12px;
        }

        .apply-step b {
            align-items: center;
            background: linear-gradient(135deg, var(--apply-primary), #ff984d);
            border-radius: 50%;
            color: #fff;
            display: inline-flex;
            font-size: 11px;
            height: 20px;
            justify-content: center;
            width: 20px;
        }

        .apply-form-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .apply-form-grid .full {
            grid-column: 1 / -1;
        }

        .apply-field {
            background: var(--apply-surface-soft);
            border: 1px solid #e3d5c8;
            border-radius: 18px;
            padding: 16px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .apply-field:hover {
            border-color: #cba990;
            box-shadow: 0 10px 24px rgba(122, 56, 16, 0.08);
            transform: translateY(-1px);
        }

        .apply-field .single-input {
            margin: 0;
        }

        .apply-field label {
            color: #2e221c;
            display: block;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .apply-field input,
        .apply-field textarea {
            background: #fff;
            border: 1px solid #d5c6b9;
            border-radius: 14px;
            box-shadow: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
        }

        .apply-field input {
            min-height: 52px;
        }

        .apply-field textarea {
            min-height: 150px;
            padding-top: 12px;
            resize: vertical;
        }

        .apply-field input:focus,
        .apply-field textarea:focus {
            border-color: rgba(184, 93, 31, 0.58);
            box-shadow: 0 0 0 4px rgba(184, 93, 31, 0.14);
        }

        .apply-checkbox {
            align-items: flex-start;
            color: #493a31;
            display: flex;
            font-size: 14px;
            font-weight: 500;
            gap: 10px;
            line-height: 1.6;
            margin-bottom: 12px;
            text-transform: none;
        }

        .apply-checkbox input {
            appearance: auto;
            -webkit-appearance: checkbox;
            border: none;
            border-radius: 0;
            box-shadow: none;
            flex: 0 0 auto;
            height: 16px;
            margin-top: 3px;
            min-height: 16px;
            padding: 0;
            width: 16px;
        }

        .apply-checkbox span {
            color: #493a31;
            display: inline;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
            text-transform: none;
        }

        .apply-existing-file {
            background: linear-gradient(135deg, #fff 0%, #fffaf6 100%);
            border: 1px solid #d8c6b6;
            border-radius: 14px;
            color: #43352d;
            margin-top: 12px;
            padding: 12px 14px;
        }

        .apply-existing-file strong {
            color: #2f2f2f;
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .apply-existing-file a {
            color: var(--apply-primary-dark);
            font-size: 13px;
            font-weight: 700;
        }

        .apply-upload-hint {
            color: var(--apply-muted);
            font-size: 13px;
            margin-top: 10px;
        }

        .apply-error {
            color: #cf3c3c;
            font-size: 13px;
            margin-top: 8px;
        }

        .apply-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 22px;
        }

        .apply-actions button {
            background: linear-gradient(135deg, var(--apply-primary), #d69a67);
            border: none;
            border-radius: 999px;
            box-shadow: 0 14px 34px rgba(122, 56, 16, 0.26);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            min-height: 56px;
            min-width: 220px;
            overflow: hidden;
            padding: 0 28px;
            position: relative;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .apply-actions button:hover {
            box-shadow: 0 18px 38px rgba(122, 56, 16, 0.32);
            transform: translateY(-2px);
        }

        .apply-actions button::after {
            animation: applyShine 3.2s linear infinite;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.45), transparent);
            content: "";
            inset: 0 auto 0 -60%;
            position: absolute;
            transform: skewX(-22deg);
            width: 44%;
        }

        .apply-alert {
            background: #ebfff3;
            border: 1px solid #bfe4c9;
            border-radius: 18px;
            color: #206842;
            margin-bottom: 20px;
            padding: 16px 18px;
        }

        @keyframes applyShine {
            0% { left: -60%; }
            100% { left: 140%; }
        }

        @media (max-width: 991px) {
            .apply-shell {
                grid-template-columns: 1fr;
            }

            .apply-sidebar {
                position: static;
            }
        }

        @media (max-width: 767px) {
            .apply-form-grid {
                grid-template-columns: 1fr;
            }

            .apply-form-head,
            .apply-form-body,
            .apply-hero,
            .apply-summary,
            .apply-section {
                padding: 18px;
            }

            .apply-title {
                font-size: 24px;
            }

            .apply-actions button {
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
                            <h3>&#7912;ng tuy&#7875;n</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="candidate-dashboard-area section_70 apply-job-page">
        <div class="container">
            @if (session('status'))
                <div class="apply-alert">{{ session('status') }}</div>
            @endif

            <div class="apply-shell">
                <aside class="apply-sidebar">
                    <div class="apply-hero">
                        <div class="apply-eyebrow">Đơn ứng tuyển</div>
                        <h1 class="apply-title">{{ $job->title }}</h1>
                        <p class="apply-subtitle">
                            Hoàn thiện thông tin ứng tuyển để đội ngũ tuyển dụng xem xét hồ sơ của bạn nhanh hơn.
                        </p>
                        <div class="apply-highlight-row">
                            <div class="apply-chip">Phản hồi nhanh</div>
                            <div class="apply-chip">Nộp hồ sơ trong 1 phút</div>
                            @if ($branch?->city)
                                <div class="apply-chip">{{ $branch->city }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="apply-summary">
                        <h3>Tóm tắt vị trí</h3>
                        <div class="apply-list">
                            <div class="apply-item">
                                <span>Mức lương</span>
                                <strong>{{ $salaryText }}</strong>
                            </div>
                            <div class="apply-item">
                                <span>Chi nhánh</span>
                                <div>{{ $branch?->name ?: 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="apply-item">
                                <span>Phòng ban</span>
                                <div>{{ $department?->name ?: 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="apply-item">
                                <span>Hình thức làm việc</span>
                                <div>{{ $workplace?->name ?: 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="apply-item">
                                <span>Hạn nộp hồ sơ</span>
                                <div>{{ $job->deadline?->format('d/m/Y') ?: 'Không giới hạn' }}</div>
                            </div>
                            <div class="apply-item">
                                <span>Số lượng tuyển</span>
                                <div>{{ $job->positions_count ?: 'Chưa cập nhật' }}</div>
                            </div>
                        </div>
                    </div>

                    @guest
                        <div class="apply-section">
                            <h3>Thông tin chi nhánh</h3>
                            <div class="apply-list">
                                <div class="apply-item">
                                    <span>Tên chi nhánh</span>
                                    <div>{{ $branch?->name ?: 'Chưa cập nhật' }}</div>
                                </div>
                                <div class="apply-item">
                                    <span>Địa chỉ</span>
                                    <div>
                                        {{ $branch?->address ?: 'Chưa cập nhật địa chỉ' }}
                                        @if ($branch?->city)
                                            <br>{{ $branch->city }}
                                        @endif
                                    </div>
                                </div>
                                <div class="apply-item">
                                    <span>Liên hệ</span>
                                    <div>
                                        {{ $branch?->phone ?: 'Chưa cập nhật số điện thoại' }}
                                        @if ($branch?->email_contact)
                                            <br>{{ $branch->email_contact }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="apply-note" style="margin-top: 16px;">
                                Bạn vẫn có thể ứng tuyển mà không cần đăng nhập. Chỉ cần điền thông tin cơ bản và tải CV lên.
                            </div>
                        </div>
                    @else
                        <div class="apply-section">
                            <h3>Lưu ý</h3>
                            <div class="apply-note">
                                Hệ thống sẽ tự điền một phần thông tin từ hồ sơ ứng viên của bạn. Hãy kiểm tra lại trước khi gửi để hồ sơ được đầy đủ nhất.
                            </div>
                        </div>
                    @endguest
                </aside>

                <div class="apply-form-card">
                    <div class="apply-form-head">
                        <h3>Nộp hồ sơ cho vị trí này</h3>
                        <p>
                            {{ Auth::check() ? 'Bạn có thể dùng lại CV hiện có hoặc tải lên phiên bản mới.' : 'Nhập thông tin ứng tuyển và tải CV để hoàn tất hồ sơ.' }}
                        </p>
                    </div>

                    <div class="apply-form-body">
                        <form wire:submit.prevent="submit" enctype="multipart/form-data">
                            <div class="apply-steps">
                                <div class="apply-step"><b>1</b> Điền thông tin</div>
                                <div class="apply-step"><b>2</b> Chọn CV phù hợp</div>
                                <div class="apply-step"><b>3</b> Gửi ứng tuyển</div>
                            </div>

                            <div class="apply-form-grid">
                                <div class="apply-field">
                                    <div class="single-input">
                                        <label>H&#7885; v&#224; t&#234;n</label>
                                        <input type="text" wire:model.defer="name" placeholder="Nhập họ và tên">
                                        @error('name')<div class="apply-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="apply-field">
                                    <div class="single-input">
                                        <label>Email</label>
                                        <input type="email" wire:model.defer="email" placeholder="Nhập email">
                                        @error('email')<div class="apply-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="apply-field">
                                    <div class="single-input">
                                        <label>Số điện thoại</label>
                                        <input type="text" wire:model.defer="phone" placeholder="Nhập số điện thoại">
                                        @error('phone')<div class="apply-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="apply-field">
                                    <div class="single-input">
                                        <label>Số năm kinh nghiệm</label>
                                        <input type="number" min="0" wire:model.defer="experience_years" placeholder="Ví dụ: 2">
                                        @error('experience_years')<div class="apply-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="apply-field full">
                                    <div class="single-input">
                                        <label>Tiêu đề hồ sơ</label>
                                        <input type="text" wire:model.defer="profile_title" placeholder="Ví dụ: Backend Developer">
                                        @error('profile_title')<div class="apply-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="apply-field full">
                                    <div class="single-input">
                                        <label>Mục tiêu nghề nghiệp</label>
                                        <textarea wire:model.defer="career_objective" rows="5" placeholder="Giới thiệu ngắn gọn về định hướng công việc và thế mạnh của bạn"></textarea>
                                        @error('career_objective')<div class="apply-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="apply-field full">
                                    <div class="single-input">
                                        <label>CV</label>

                                        @if ($hasExistingCv)
                                            <label class="apply-checkbox">
                                                <input type="checkbox" wire:model="use_existing_cv">
                                                <span>Dùng lại CV đã có trong hồ sơ ứng viên của tôi</span>
                                            </label>

                                            @if ($use_existing_cv && $existingCvName)
                                                <div class="apply-existing-file">
                                                    <strong>CV đang được sử dụng</strong>
                                                    <div>{{ $existingCvName }}</div>
                                                    @if ($existingCvUrl)
                                                        <a href="{{ $existingCvUrl }}" target="_blank" rel="noopener">Xem CV hiện tại</a>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif

                                        <input type="file" wire:model="cv" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                        <div wire:loading wire:target="cv" class="apply-upload-hint">Đang tải lên...</div>
                                        <div class="apply-upload-hint">Hỗ trợ PDF, DOC, DOCX. Dung lượng tối đa 10MB.</div>
                                        @error('cv')<div class="apply-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="apply-actions">
                                <button type="submit" wire:loading.attr="disabled">Nộp ứng tuyển</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

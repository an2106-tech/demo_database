<div class="cv-builder-page">
    <style>
        .cv-builder-page {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-top: 110px;
            padding-bottom: 60px;
        }

        .cv-builder-page .fa,
        .cv-builder-page i.fa,
        .cv-builder-page .fa:before {
            font-family: 'FontAwesome', FontAwesome !important;
            font-style: normal;
            font-weight: normal;
        }

        /* Top Bar */
        .cv-header-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .cv-title-group h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px 0;
            letter-spacing: -0.2px;
        }
        .cv-title-group p {
            font-size: 13.5px;
            color: #64748b;
            margin: 0;
        }

        /* Template Switcher Pills */
        .cv-template-pills {
            display: inline-flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            gap: 4px;
        }
        .cv-template-pill-btn {
            background: transparent;
            color: #64748b;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .cv-template-pill-btn:hover {
            color: #0f172a;
        }
        .cv-template-pill-btn.active {
            background: #ffffff;
            color: #f37021;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        /* Action Buttons */
        .btn-cv-ai {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            color: #ffffff !important;
            border: none;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 3px 12px rgba(124, 58, 237, 0.25);
            transition: all 0.2s ease;
        }
        .btn-cv-ai:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }
        .btn-cv-save {
            background: #0f172a;
            color: #ffffff !important;
            border: none;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-cv-save:hover {
            background: #1e293b;
            transform: translateY(-1px);
        }
        .btn-cv-download {
            background: #f37021;
            color: #ffffff !important;
            border: none;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 3px 12px rgba(243, 112, 33, 0.25);
            transition: all 0.2s ease;
        }
        .btn-cv-download:hover {
            background: #e05f12;
            transform: translateY(-1px);
        }

        /* 2 Columns Workspace */
        .cv-main-grid {
            display: grid;
            grid-template-columns: minmax(420px, 1fr) minmax(620px, 1.25fr);
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 1350px) {
            .cv-main-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 1080px) {
            .cv-main-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Editor Card */
        .cv-editor-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Nav Tabs */
        .cv-nav-tabs {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .cv-nav-tab {
            background: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .cv-nav-tab:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .cv-nav-tab.active {
            background: rgba(243, 112, 33, 0.1);
            color: #f37021;
            border-color: rgba(243, 112, 33, 0.4);
        }

        /* Form Inputs */
        .form-group-clean {
            margin-bottom: 18px;
        }
        .form-group-clean label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-control-clean {
            width: 100%;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13.5px;
            color: #0f172a;
            transition: all 0.2s ease;
        }
        .form-control-clean:focus {
            outline: none;
            border-color: #f37021;
            box-shadow: 0 0 0 3px rgba(243, 112, 33, 0.15);
        }
        textarea.form-control-clean {
            min-height: 90px;
            line-height: 1.5;
            resize: vertical;
        }

        /* Dynamic Item Cards */
        .cv-dynamic-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 16px;
            position: relative;
        }
        .cv-dynamic-card:hover {
            border-color: #cbd5e1;
        }
        .btn-card-remove {
            position: absolute;
            top: 14px;
            right: 14px;
            background: #fee2e2;
            color: #ef4444;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-card-remove:hover {
            background: #ef4444;
            color: #ffffff;
        }

        /* AI Prompt Pill */
        .ai-pilot-badge {
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            color: #7c3aed;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .ai-pilot-badge:hover {
            background: #ede9fe;
            color: #6d28d9;
            transform: translateY(-1px);
        }

        /* Right Column Preview Sheet */
        .cv-preview-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 110px;
        }
        .cv-preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .cv-preview-viewport {
            background: #e2e8f0;
            background: radial-gradient(circle at center, #f1f5f9 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 24px 18px;
            max-height: calc(100vh - 220px);
            overflow-y: auto;
            overflow-x: auto;
            display: flex;
            justify-content: center;
        }
        .cv-a4-sheet {
            background: #ffffff;
            width: 100%;
            max-width: 794px;
            min-height: 1050px;
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.16);
            border-radius: 4px;
            overflow: hidden;
            flex-shrink: 0;
            position: relative !important;
        }

        /* Scoped browser preview override so sidebar-bg stays inside A4 sheet */
        .cv-builder-page .sidebar-bg {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            width: 32% !important;
            height: 100% !important;
            min-height: 100% !important;
            background-color: #0f172a !important;
            z-index: 0 !important;
            pointer-events: none;
        }
        .cv-builder-page .cv-table {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            position: relative !important;
        }
        .cv-builder-page td.sidebar,
        .cv-builder-page .sidebar {
            position: relative !important;
            width: 32% !important;
            background-color: #0f172a !important;
            color: #f8fafc !important;
            vertical-align: top !important;
        }
        .cv-builder-page td.main,
        .cv-builder-page .main {
            position: relative !important;
            width: 68% !important;
            background-color: #ffffff !important;
            vertical-align: top !important;
        }
    </style>

    <div class="container-fluid px-lg-5" style="padding-top: 24px;">
        <!-- Top Toolbar -->
        <div class="cv-header-panel">
            <div class="cv-title-group">
                <h2>Tạo & Thiết kế CV Online</h2>
                <p>Chọn mẫu giao diện chuyên nghiệp, chỉnh sửa trực quan và xuất PDF chuẩn in ấn A4.</p>
            </div>

            <!-- Template Selectors -->
            <div class="cv-template-pills">
                <button type="button" wire:click="setTemplate('fpt-modern')" class="cv-template-pill-btn {{ $selectedTemplate === 'fpt-modern' ? 'active' : '' }}">
                    <i class="fa fa-th-large"></i> FPT Modern
                </button>
                <button type="button" wire:click="setTemplate('ats-classic')" class="cv-template-pill-btn {{ $selectedTemplate === 'ats-classic' ? 'active' : '' }}">
                    <i class="fa fa-file-text-o"></i> ATS Classic
                </button>
                <button type="button" wire:click="setTemplate('tech-executive')" class="cv-template-pill-btn {{ $selectedTemplate === 'tech-executive' ? 'active' : '' }}">
                    <i class="fa fa-code"></i> Tech Executive
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <label for="cv_upload_ai_clean" class="btn-cv-ai mb-0" title="Tải file PDF/DOCX cũ để AI tự điền">
                    <i class="fa fa-magic"></i> AI Auto-Fill từ CV
                    <input type="file" id="cv_upload_ai_clean" wire:model="uploadedCvFile" class="d-none" accept=".pdf,.docx">
                </label>

                <button type="button" wire:click="runAiAudit" class="btn-cv-ai" style="background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); box-shadow: 0 3px 12px rgba(2, 132, 199, 0.25);" wire:loading.attr="disabled">
                    <i class="fa fa-bar-chart"></i> AI Chấm điểm ATS
                </button>

                <button type="button" wire:click="save" class="btn-cv-save" wire:loading.attr="disabled">
                    <i class="fa fa-save"></i> Lưu hồ sơ
                </button>

                <button type="button" wire:click="downloadPdf" class="btn-cv-download" wire:loading.attr="disabled" wire:target="downloadPdf">
                    <span wire:loading.remove wire:target="downloadPdf"><i class="fa fa-download"></i> Tải CV PDF</span>
                    <span wire:loading wire:target="downloadPdf"><i class="fa fa-spinner fa-spin"></i> Đang tải...</span>
                </button>
            </div>
        </div>

        <!-- AI Upload Notice -->
        @if($uploadedCvFile)
            <div class="p-3 mb-4" style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; gap: 16px;">
                <div>
                    <strong style="color: #6d28d9;"><i class="fa fa-file-pdf-o"></i> Đã chọn file:</strong> {{ $uploadedCvFile->getClientOriginalName() }}
                    <div style="font-size: 12.5px; color: #64748b; margin-top: 2px;">Nhấn "Bắt đầu bóc tách" để AI tự động quét kinh nghiệm, học vấn và điền vào form.</div>
                </div>
                <button type="button" wire:click="importCvWithAi" class="btn-cv-ai" style="padding: 8px 16px;">
                    <span wire:loading.remove wire:target="importCvWithAi">Bắt đầu bóc tách AI</span>
                    <span wire:loading wire:target="importCvWithAi"><i class="fa fa-spinner fa-spin"></i> Đang đọc file...</span>
                </button>
            </div>
        @endif

        <!-- AI Audit Report Card -->
        @if($aiScore !== null)
            <div class="p-4 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: #f37021; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; box-shadow: 0 2px 10px rgba(243, 112, 33, 0.3);">
                            {{ $aiScore }}
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a;">Báo cáo phân tích chất lượng CV từ AI</h4>
                            <p style="margin: 2px 0 0; font-size: 13px; color: #64748b;">{{ $aiSummary }}</p>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    @if(!empty($aiStrengths))
                        <div class="col-md-6">
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 16px; height: 100%;">
                                <strong style="color: #15803d; font-size: 13px;"><i class="fa fa-check-circle me-1"></i> Điểm mạnh nổi bật:</strong>
                                <ul style="margin: 6px 0 0; padding-left: 18px; font-size: 13px; color: #166534;">
                                    @foreach($aiStrengths as $st)
                                        <li style="margin-bottom: 3px;">{{ $st }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if(!empty($aiWeaknesses))
                        <div class="col-md-6">
                            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 12px 16px; height: 100%;">
                                <strong style="color: #b91c1c; font-size: 13px;"><i class="fa fa-exclamation-circle me-1"></i> Điểm cần cải thiện:</strong>
                                <ul style="margin: 6px 0 0; padding-left: 18px; font-size: 13px; color: #991b1b;">
                                    @foreach($aiWeaknesses as $wk)
                                        <li style="margin-bottom: 3px;">{{ $wk }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                @if(!empty($aiAtsKeywords) || !empty($aiMissingKeywords))
                    <div class="mt-3 pt-3" style="border-top: 1px solid #f1f5f9; display: flex; gap: 24px; flex-wrap: wrap;">
                        @if(!empty($aiAtsKeywords))
                            <div>
                                <span style="font-size: 12px; color: #475569; font-weight: 700;">Từ khóa ATS đã có:</span>
                                <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 4px;">
                                    @foreach($aiAtsKeywords as $kw)
                                        <span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">{{ $kw }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if(!empty($aiMissingKeywords))
                            <div>
                                <span style="font-size: 12px; color: #b91c1c; font-weight: 700;">Từ khóa nên bổ sung:</span>
                                <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 4px;">
                                    @foreach($aiMissingKeywords as $kw)
                                        <span style="background: #fee2e2; color: #b91c1c; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">{{ $kw }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- Main 2-Column Grid -->
        <div class="cv-main-grid">
            <!-- Left Column: Form Editor -->
            <div class="cv-editor-card">
                <div class="cv-nav-tabs">
                    <button type="button" wire:click="setTab('personal')" class="cv-nav-tab {{ $activeTab === 'personal' ? 'active' : '' }}">
                        <i class="fa fa-user me-1"></i> Thông tin chung
                    </button>
                    <button type="button" wire:click="setTab('objective')" class="cv-nav-tab {{ $activeTab === 'objective' ? 'active' : '' }}">
                        <i class="fa fa-bullseye me-1"></i> Mục tiêu nghề nghiệp
                    </button>
                    <button type="button" wire:click="setTab('experience')" class="cv-nav-tab {{ $activeTab === 'experience' ? 'active' : '' }}">
                        <i class="fa fa-briefcase me-1"></i> Kinh nghiệm ({{ count($experiences) }})
                    </button>
                    <button type="button" wire:click="setTab('education')" class="cv-nav-tab {{ $activeTab === 'education' ? 'active' : '' }}">
                        <i class="fa fa-graduation-cap me-1"></i> Học vấn ({{ count($educations) }})
                    </button>
                    <button type="button" wire:click="setTab('skills')" class="cv-nav-tab {{ $activeTab === 'skills' ? 'active' : '' }}">
                        <i class="fa fa-star me-1"></i> Kỹ năng & Ngoại ngữ
                    </button>
                    <button type="button" wire:click="setTab('certifications')" class="cv-nav-tab {{ $activeTab === 'certifications' ? 'active' : '' }}">
                        <i class="fa fa-certificate me-1"></i> Chứng chỉ & Khác
                    </button>
                </div>

                <!-- Tab 1: Personal Info -->
                @if($activeTab === 'personal')
                    <!-- Avatar Upload Card -->
                    <div class="p-3 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                        <div style="position: relative; width: 92px; height: 92px; flex-shrink: 0;">
                            @php
                                $displayAvatarUrl = asset('assets/img/avatar_detail.jpg');
                                if ($avatar) {
                                    try {
                                        $displayAvatarUrl = $avatar->temporaryUrl();
                                    } catch (\Throwable $e) {}
                                } elseif ($currentAvatar) {
                                    if (str_starts_with($currentAvatar, 'http')) {
                                        $displayAvatarUrl = $currentAvatar;
                                    } else {
                                        $displayAvatarUrl = asset('storage/' . ltrim($currentAvatar, '/'));
                                    }
                                }
                            @endphp
                            <img src="{{ $displayAvatarUrl }}" alt="Avatar" style="width: 92px; height: 92px; border-radius: 50%; object-fit: cover; border: 3px solid #f37021; box-shadow: 0 4px 12px rgba(243, 112, 33, 0.15);">
                            
                            <div wire:loading wire:target="avatar" style="position: absolute; inset: 0; background: rgba(0,0,0,0.5); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fa fa-spinner fa-spin"></i>
                            </div>
                        </div>

                        <div style="flex: 1; min-width: 240px;">
                            <h6 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 700; color: #0f172a;">Ảnh chân dung CV</h6>
                            <p style="margin: 0 0 10px 0; font-size: 12.5px; color: #64748b;">Hỗ trợ JPG, PNG, WEBP (Tối đa 5MB). Ảnh rõ nét, nghiêm túc giúp CV tăng cơ hội trúng tuyển.</p>
                            
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <label for="cv_avatar_upload_input" class="btn btn-sm" style="background: #f37021; color: white; font-weight: 700; border-radius: 8px; padding: 6px 14px; cursor: pointer; margin: 0; font-size: 12.5px;">
                                    <i class="fa fa-camera me-1"></i> Tải ảnh đại diện lên
                                    <input type="file" id="cv_avatar_upload_input" wire:model="avatar" accept="image/png,image/jpeg,image/jpg,image/webp" class="d-none">
                                </label>

                                @if($avatar || $currentAvatar)
                                    <button type="button" wire:click="removeAvatar" class="btn btn-sm btn-outline-danger" style="border-radius: 8px; font-weight: 600; padding: 6px 12px; font-size: 12.5px;">
                                        <i class="fa fa-trash me-1"></i> Xóa ảnh
                                    </button>
                                @endif
                            </div>
                            @error('avatar') <span class="text-danger d-block mt-1" style="font-size: 12px;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group-clean">
                            <label>Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" wire:model.live.debounce.300ms="name" class="form-control-clean" placeholder="Nguyễn Văn A">
                        </div>
                        <div class="col-md-6 form-group-clean">
                            <label>Chức danh / Vị trí chuyên môn <span class="text-danger">*</span></label>
                            <input type="text" wire:model.live.debounce.300ms="profile_title" class="form-control-clean" placeholder="Senior Backend Developer">
                        </div>
                        <div class="col-md-6 form-group-clean">
                            <label>Email liên hệ <span class="text-danger">*</span></label>
                            <input type="email" wire:model.live.debounce.300ms="email" class="form-control-clean" placeholder="email@example.com">
                        </div>
                        <div class="col-md-6 form-group-clean">
                            <label>Số điện thoại</label>
                            <input type="text" wire:model.live.debounce.300ms="phone" class="form-control-clean" placeholder="0987654321">
                        </div>
                        <div class="col-md-6 form-group-clean">
                            <label>Ngày sinh</label>
                            <input type="date" wire:model.live="personal_info.date_of_birth" class="form-control-clean">
                        </div>
                        <div class="col-md-6 form-group-clean">
                            <label>Giới tính</label>
                            <select wire:model.live="personal_info.gender" class="form-control-clean">
                                <option value="">Chọn giới tính</option>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group-clean">
                            <label>Tỉnh / Thành phố</label>
                            <input type="text" wire:model.live.debounce.300ms="personal_info.city" class="form-control-clean" placeholder="Hà Nội / TP.HCM / Đà Nẵng">
                        </div>
                        <div class="col-md-6 form-group-clean">
                            <label>Địa chỉ cụ thể</label>
                            <input type="text" wire:model.live.debounce.300ms="personal_info.address" class="form-control-clean" placeholder="Số 10, Đường ABC...">
                        </div>
                        <div class="col-md-6 form-group-clean">
                            <label>Website / Portfolio</label>
                            <input type="text" wire:model.live.debounce.300ms="personal_info.website" class="form-control-clean" placeholder="https://myportfolio.com">
                        </div>
                        <div class="col-md-6 form-group-clean">
                            <label>LinkedIn</label>
                            <input type="text" wire:model.live.debounce.300ms="personal_info.linkedin" class="form-control-clean" placeholder="https://linkedin.com/in/...">
                        </div>
                    </div>
                @endif

                <!-- Tab 2: Career Objective -->
                @if($activeTab === 'objective')
                    <div class="form-group-clean">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0">Mục tiêu nghề nghiệp / Giới thiệu bản thân</label>
                            <button type="button" wire:click="generateObjectiveWithAi" class="ai-pilot-badge" wire:loading.attr="disabled">
                                <i class="fa fa-magic"></i> ✨ AI Gợi ý viết mục tiêu
                            </button>
                        </div>
                        <textarea wire:model.live.debounce.400ms="career_objective" class="form-control-clean" style="min-height: 160px;" placeholder="Tóm tắt ngắn gọn năng lực chuyên môn, kinh nghiệm cốt lõi và định hướng giá trị bạn sẽ đóng góp cho doanh nghiệp..."></textarea>
                    </div>
                @endif

                <!-- Tab 3: Experience -->
                @if($activeTab === 'experience')
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">Quá trình làm việc & Dự án</h5>
                        <button type="button" wire:click="addExperience" class="btn btn-sm" style="background: #f37021; color: white; font-weight: 700; border-radius: 8px; padding: 6px 14px;">
                            <i class="fa fa-plus me-1"></i> Thêm kinh nghiệm
                        </button>
                    </div>

                    @foreach($experiences as $index => $exp)
                        <div class="cv-dynamic-card" wire:key="exp-card-{{ $index }}">
                            <button type="button" wire:click="removeExperience({{ $index }})" class="btn-card-remove" title="Xóa">
                                ×
                            </button>
                            <div class="row">
                                <div class="col-md-6 form-group-clean">
                                    <label>Tên công ty / Tổ chức</label>
                                    <input type="text" wire:model.live.debounce.300ms="experiences.{{ $index }}.company" class="form-control-clean" placeholder="FPT Software">
                                </div>
                                <div class="col-md-6 form-group-clean">
                                    <label>Vị trí đảm nhiệm</label>
                                    <input type="text" wire:model.live.debounce.300ms="experiences.{{ $index }}.position" class="form-control-clean" placeholder="Fullstack Developer">
                                </div>
                                <div class="col-md-6 form-group-clean">
                                    <label>Từ thời gian</label>
                                    <input type="text" wire:model.live.debounce.300ms="experiences.{{ $index }}.from" class="form-control-clean" placeholder="01/2024">
                                </div>
                                <div class="col-md-6 form-group-clean">
                                    <label>Đến thời gian</label>
                                    <input type="text" wire:model.live.debounce.300ms="experiences.{{ $index }}.to" class="form-control-clean" placeholder="Hiện tại">
                                </div>
                                <div class="col-12 form-group-clean mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="mb-0">Mô tả công việc & Kết quả đạt được</label>
                                        <button type="button" wire:click="enhanceExperienceWithAi({{ $index }})" class="ai-pilot-badge mb-0" wire:loading.attr="disabled">
                                            <i class="fa fa-magic"></i> ✨ AI Tối ưu câu mô tả (chuẩn STAR)
                                        </button>
                                    </div>
                                    <textarea wire:model.live.debounce.400ms="experiences.{{ $index }}.description" class="form-control-clean" placeholder="- Chủ trì phát triển module thanh toán giúp tăng 25% tốc độ xử lý...&#10;- Tối ưu kiến trúc cơ sở dữ liệu giảm 40% thời gian truy vấn..."></textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if(empty($experiences))
                        <div class="text-center py-4" style="color: #94a3b8;">
                            <p>Chưa có kinh nghiệm nào. Nhấn <strong>"Thêm kinh nghiệm"</strong> ở trên để bắt đầu.</p>
                        </div>
                    @endif
                @endif

                <!-- Tab 4: Education -->
                @if($activeTab === 'education')
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">Học vấn & Bằng cấp</h5>
                        <button type="button" wire:click="addEducation" class="btn btn-sm" style="background: #f37021; color: white; font-weight: 700; border-radius: 8px; padding: 6px 14px;">
                            <i class="fa fa-plus me-1"></i> Thêm học vấn
                        </button>
                    </div>

                    @foreach($educations as $index => $edu)
                        <div class="cv-dynamic-card" wire:key="edu-card-{{ $index }}">
                            <button type="button" wire:click="removeEducation({{ $index }})" class="btn-card-remove" title="Xóa">
                                ×
                            </button>
                            <div class="row">
                                <div class="col-md-6 form-group-clean">
                                    <label>Trường / Cơ sở đào tạo</label>
                                    <input type="text" wire:model.live.debounce.300ms="educations.{{ $index }}.school" class="form-control-clean" placeholder="Đại học FPT / FPT Polytechnic">
                                </div>
                                <div class="col-md-6 form-group-clean">
                                    <label>Ngành học / Bằng cấp</label>
                                    <input type="text" wire:model.live.debounce.300ms="educations.{{ $index }}.degree" class="form-control-clean" placeholder="Kỹ sư Công nghệ Thông tin">
                                </div>
                                <div class="col-md-6 form-group-clean">
                                    <label>Từ năm</label>
                                    <input type="text" wire:model.live.debounce.300ms="educations.{{ $index }}.from" class="form-control-clean" placeholder="2022">
                                </div>
                                <div class="col-md-6 form-group-clean">
                                    <label>Đến năm</label>
                                    <input type="text" wire:model.live.debounce.300ms="educations.{{ $index }}.to" class="form-control-clean" placeholder="2026">
                                </div>
                                <div class="col-12 form-group-clean mb-0">
                                    <label>Chi tiết bổ sung (GPA, đề tài...)</label>
                                    <textarea wire:model.live.debounce.400ms="educations.{{ $index }}.description" class="form-control-clean" placeholder="GPA: 3.6/4.0, Đề tài tốt nghiệp Xuất sắc..."></textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                <!-- Tab 5: Skills & Languages -->
                @if($activeTab === 'skills')
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">Kỹ năng chuyên môn</h5>
                            <button type="button" wire:click="addSkill" class="btn btn-sm" style="background: #f37021; color: white; font-weight: 700; border-radius: 8px; padding: 6px 14px;">
                                <i class="fa fa-plus me-1"></i> Thêm kỹ năng
                            </button>
                        </div>

                        @foreach($skills as $index => $skill)
                            <div class="d-flex align-items-center gap-2 mb-2" wire:key="skill-item-{{ $index }}">
                                <input type="text" wire:model.live.debounce.300ms="skills.{{ $index }}.name" class="form-control-clean" placeholder="Tên kỹ năng (PHP, Laravel, React, SQL...)" style="flex: 2;">
                                <select wire:model.live="skills.{{ $index }}.level" class="form-control-clean" style="flex: 1;">
                                    <option value="Cơ bản">Cơ bản</option>
                                    <option value="Khá">Khá</option>
                                    <option value="Thành thạo">Thành thạo</option>
                                    <option value="Chuyên gia">Chuyên gia</option>
                                </select>
                                <button type="button" wire:click="removeSkill({{ $index }})" class="btn btn-danger btn-sm" style="height: 42px; border-radius: 8px; width: 42px;">
                                    ×
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3" style="border-top: 1px solid #e2e8f0;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">Ngoại ngữ</h5>
                            <button type="button" wire:click="addLanguage" class="btn btn-sm" style="background: #f37021; color: white; font-weight: 700; border-radius: 8px; padding: 6px 14px;">
                                <i class="fa fa-plus me-1"></i> Thêm ngoại ngữ
                            </button>
                        </div>

                        @foreach($languages as $index => $lang)
                            <div class="d-flex align-items-center gap-2 mb-2" wire:key="lang-item-{{ $index }}">
                                <input type="text" wire:model.live.debounce.300ms="languages.{{ $index }}.name" class="form-control-clean" placeholder="Tiếng Anh, Tiếng Nhật..." style="flex: 2;">
                                <select wire:model.live="languages.{{ $index }}.level" class="form-control-clean" style="flex: 1;">
                                    <option value="Cơ bản">Cơ bản</option>
                                    <option value="Giao tiếp">Giao tiếp</option>
                                    <option value="Thành thạo">Thành thạo</option>
                                </select>
                                <button type="button" wire:click="removeLanguage({{ $index }})" class="btn btn-danger btn-sm" style="height: 42px; border-radius: 8px; width: 42px;">
                                    ×
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Tab 6: Certifications -->
                @if($activeTab === 'certifications')
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">Chứng chỉ & Khóa đào tạo</h5>
                        <button type="button" wire:click="addCertification" class="btn btn-sm" style="background: #f37021; color: white; font-weight: 700; border-radius: 8px; padding: 6px 14px;">
                            <i class="fa fa-plus me-1"></i> Thêm chứng chỉ
                        </button>
                    </div>

                    @foreach($certifications as $index => $cert)
                        <div class="cv-dynamic-card" wire:key="cert-card-{{ $index }}">
                            <button type="button" wire:click="removeCertification({{ $index }})" class="btn-card-remove" title="Xóa">
                                ×
                            </button>
                            <div class="row">
                                <div class="col-md-6 form-group-clean">
                                    <label>Tên chứng chỉ</label>
                                    <input type="text" wire:model.live.debounce.300ms="certifications.{{ $index }}.name" class="form-control-clean" placeholder="AWS Certified Solutions Architect">
                                </div>
                                <div class="col-md-6 form-group-clean">
                                    <label>Tổ chức cấp</label>
                                    <input type="text" wire:model.live.debounce.300ms="certifications.{{ $index }}.issuer" class="form-control-clean" placeholder="Amazon Web Services">
                                </div>
                                <div class="col-md-6 form-group-clean mb-0">
                                    <label>Năm / Thời hạn</label>
                                    <input type="text" wire:model.live.debounce.300ms="certifications.{{ $index }}.date" class="form-control-clean" placeholder="2025">
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Right Column: Live A4 Preview Card -->
            <div class="cv-preview-card">
                <div class="cv-preview-header">
                    <span style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; font-weight: 700; color: #0f172a;">
                        Xem trước trực tiếp ({{ str_replace('-', ' ', ucwords($selectedTemplate, '-')) }})
                    </span>
                    <button type="button" wire:click="openPdf" class="btn btn-sm" style="background: #f1f5f9; color: #0f172a; font-weight: 700; border-radius: 6px; font-size: 12.5px; border: 1px solid #e2e8f0; cursor: pointer; padding: 5px 12px; transition: all 0.2s;" title="Xem toàn bộ CV trên tab mới" wire:loading.attr="disabled" wire:target="openPdf">
                        <span wire:loading.remove wire:target="openPdf"><i class="fa fa-external-link me-1"></i> Mở xem toàn bộ (PDF)</span>
                        <span wire:loading wire:target="openPdf"><i class="fa fa-spinner fa-spin me-1"></i> Đang mở...</span>
                    </button>
                </div>

                <div class="cv-preview-viewport">
                    <div class="cv-a4-sheet">
                        @include('pdf.cv-templates.' . $selectedTemplate, [
                            'candidate' => $candidate,
                            'resume' => $previewResume,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-pdf-window', (event) => {
                const url = event.url || (event[0] && event[0].url);
                if (url) {
                    window.open(url, '_blank');
                }
            });
        });
    </script>
</div>

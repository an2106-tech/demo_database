<div>
    <style>
        .cv-manage-shell { display: grid; gap: 24px; }
        
        /* Modern Clean Hero Banner */
        .cv-hero {
            background: linear-gradient(135deg, #ffffff 0%, #fffaf5 60%, #fff5eb 100%);
            border: 1.5px solid rgba(243, 112, 33, 0.16);
            border-radius: 20px;
            padding: 28px 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        }
        .cv-hero::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(243, 112, 33, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .cv-hero__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 24px;
        }
        .cv-hero__title {
            font-size: 22px;
            font-weight: 900;
            margin-bottom: 8px;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .cv-hero__title-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #f37021, #ff8c42);
            color: #fff;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 10px rgba(243, 112, 33, 0.25);
        }
        .cv-hero__desc {
            color: #64748b;
            font-size: 14px;
            max-width: 680px;
            line-height: 1.6;
            margin: 0;
        }
        .cv-hero__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .btn-hero-primary {
            background: linear-gradient(135deg, #f37021 0%, #ff8c42 100%);
            color: #fff !important;
            border: none;
            border-radius: 12px;
            font-weight: 800;
            font-size: 13.5px;
            padding: 10px 20px;
            box-shadow: 0 4px 14px rgba(243, 112, 33, 0.28);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(243, 112, 33, 0.35);
        }
        .btn-hero-secondary {
            background: #ffffff;
            color: #334155 !important;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            font-weight: 700;
            font-size: 13.5px;
            padding: 10px 18px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-hero-secondary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a !important;
        }

        /* Modern KPI Cards */
        .cv-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }
        .cv-kpi-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s ease;
        }
        .cv-kpi-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }
        .cv-kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .cv-kpi-info { flex: 1; min-width: 0; }
        .cv-kpi-label {
            font-size: 11.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .cv-kpi-val {
            font-size: 17px;
            font-weight: 900;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        .cv-kpi-sub {
            font-size: 11.5px;
            font-weight: 700;
            margin-top: 2px;
        }

        /* Section Styling */
        .cv-section { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; padding: 26px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .cv-section__head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 14px; }
        .cv-section__title { font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; margin: 0; }
        .cv-section__title i { color: #f37021; }

        /* Cards Grid for Online CVs */
        .online-cv-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        .online-cv-card {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            transition: all 0.25s ease;
            position: relative;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .online-cv-card:hover {
            border-color: #f37021;
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(243, 112, 33, 0.12);
        }
        .online-cv-card.is-primary {
            border-color: #f37021;
            background: linear-gradient(180deg, #fffaf5 0%, #ffffff 100%);
            box-shadow: 0 8px 24px rgba(243, 112, 33, 0.12);
        }

        .primary-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            background: linear-gradient(135deg, #f37021, #ff8c42);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 20px;
            box-shadow: 0 4px 10px rgba(243, 112, 33, 0.3);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .badge-fpt { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
        .badge-ats { background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; }
        .badge-tech { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }

        .cv-card-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
        .cv-card-title { font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        .cv-card-desc { font-size: 12.5px; color: #64748b; line-height: 1.5; margin-bottom: 16px; min-height: 38px; }

        .cv-score-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 16px;
        }
        .cv-score-pill strong { color: #0284c7; }

        .cv-card-footer {
            border-top: 1px solid #f1f5f9;
            padding-top: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
        }

        .btn-set-primary {
            background: #fff;
            border: 1.5px solid #f37021;
            color: #f37021;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-set-primary:hover {
            background: #f37021;
            color: #fff;
        }
        .btn-action-icon {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-action-icon:hover {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }

        /* Upload Area */
        .upload-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            background: #f8fafc;
            transition: all 0.2s;
            margin-bottom: 20px;
        }
        .upload-dropzone:hover {
            border-color: #f37021;
            background: #fffaf5;
        }
        .upload-dropzone i { font-size: 36px; color: #f37021; margin-bottom: 8px; }

        /* Uploaded Attachments List */
        .attachment-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }
        .attachment-item:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }
        .attachment-item.is-primary {
            border-color: #f37021;
            background: #fffaf5;
        }
        .attachment-left { display: flex; align-items: center; gap: 14px; }
        .attachment-icon {
            width: 42px;
            height: 42px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .attachment-name { font-weight: 700; color: #0f172a; font-size: 14px; margin-bottom: 2px; }
        .attachment-meta { font-size: 12px; color: #64748b; }
    </style>

    <div class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <!-- Sidebar -->
                <div class="col-lg-3 col-xl-3 dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <!-- Main Content -->
                <div class="col-lg-9 col-xl-9">
                    <div class="cv-manage-shell">
                        
                        <!-- Hero Banner -->
                        <div class="cv-hero">
                            <div class="cv-hero__header">
                                <div>
                                    <div class="cv-hero__title">
                                        <div class="cv-hero__title-icon">
                                            <i class="fa fa-file-text-o"></i>
                                        </div>
                                        <span>Quản Lý Hồ Sơ & CV Ứng Tuyển</span>
                                    </div>
                                    <p class="cv-hero__desc">
                                        Quản lý tập trung các mẫu CV Online thiết kế với AI và các file CV đính kèm. 
                                        Chọn <strong>1 bản làm "CV Chính"</strong> để hệ thống tự động sử dụng khi bạn ứng tuyển nhanh vào các vị trí tuyển dụng.
                                    </p>
                                </div>
                                <div class="cv-hero__actions">
                                    <a href="{{ route('candidates.cv_builder') }}" class="btn-hero-primary">
                                        <i class="fa fa-magic"></i> Mở Trình Thiết Kế CV (AI Pro)
                                    </a>
                                    <a href="{{ route('candidates.candidate_profile') }}" class="btn-hero-secondary">
                                        <i class="fa fa-id-card"></i> Sửa hồ sơ ứng viên
                                    </a>
                                </div>
                            </div>

                            <!-- KPIs Cards -->
                            <div class="cv-kpi-grid">
                                <div class="cv-kpi-card">
                                    <div class="cv-kpi-icon" style="background: #fff7ed; color: #f37021;">
                                        <i class="fa fa-star"></i>
                                    </div>
                                    <div class="cv-kpi-info">
                                        <div class="cv-kpi-label">CV Đang Đặt Làm Chính</div>
                                        <div class="cv-kpi-val" title="{{ $primaryCvType === 'online' ? $this->getTemplateName($primaryCvTemplate) : 'File Đính Kèm' }}">
                                            @if($primaryCvType === 'online')
                                                {{ $this->getTemplateName($primaryCvTemplate) }}
                                            @else
                                                File Đính Kèm
                                            @endif
                                        </div>
                                        <div class="cv-kpi-sub text-warning">
                                            <i class="fa fa-check-circle"></i> Đang ưu tiên nộp
                                        </div>
                                    </div>
                                </div>

                                <div class="cv-kpi-card">
                                    <div class="cv-kpi-icon" style="background: #eff6ff; color: #0284c7;">
                                        <i class="fa fa-line-chart"></i>
                                    </div>
                                    <div class="cv-kpi-info">
                                        <div class="cv-kpi-label">Điểm AI Chấm (ATS)</div>
                                        <div class="cv-kpi-val">
                                            <span>{{ $aiScore > 0 ? $aiScore : '--' }}</span>
                                            <span style="font-size: 13px; color: #94a3b8; font-weight: 600;">/100</span>
                                        </div>
                                        <div class="cv-kpi-sub {{ $aiScore >= 80 ? 'text-success' : ($aiScore >= 50 ? 'text-info' : 'text-muted') }}">
                                            <i class="fa fa-circle" style="font-size: 8px;"></i> {{ $aiScore >= 80 ? 'Rất Tốt' : ($aiScore >= 50 ? 'Khá' : 'Cần chấm lại') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="cv-kpi-card">
                                    <div class="cv-kpi-icon" style="background: #ecfdf5; color: #059669;">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                    <div class="cv-kpi-info">
                                        <div class="cv-kpi-label">Hoàn thiện Hồ sơ</div>
                                        <div class="cv-kpi-val">
                                            {{ $profileCompletion }}%
                                        </div>
                                        <div class="cv-kpi-sub {{ $profileCompletion >= 80 ? 'text-success' : 'text-danger' }}">
                                            <i class="fa fa-shield"></i> {{ $profileCompletion >= 80 ? 'Sẵn sàng ứng tuyển' : 'Cần bổ sung thêm' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 1: CV ONLINE (AI PRO SYSTEM) -->
                        <div class="cv-section">
                            <div class="cv-section__head">
                                <h2 class="cv-section__title">
                                    <i class="fa fa-desktop"></i> CV Trực Tuyến Từ Hệ Thống (Online Resume)
                                </h2>
                                <a href="{{ route('candidates.cv_builder') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                                    <i class="fa fa-edit"></i> Chỉnh sửa nội dung CV
                                </a>
                            </div>

                            <p class="text-muted" style="font-size: 13.5px; margin-bottom: 20px;">
                                Các mẫu CV dưới đây được tự động tạo và đồng bộ từ hồ sơ của bạn. Bạn có thể chọn bất kỳ mẫu nào để đặt làm CV chính thức:
                            </p>

                            <div class="online-cv-grid">
                                @foreach($availableTemplates as $tpl)
                                    @php
                                        $isThisPrimary = ($primaryCvType === 'online' && $primaryCvTemplate === $tpl['id']);
                                    @endphp
                                    <div class="online-cv-card {{ $isThisPrimary ? 'is-primary' : '' }}">
                                        @if($isThisPrimary)
                                            <div class="primary-badge">
                                                <i class="fa fa-star"></i> CV CHÍNH
                                            </div>
                                        @endif

                                        <div>
                                            <div class="cv-card-meta">
                                                <span class="badge {{ $tpl['badge_class'] }}" style="border-radius: 6px; font-weight: 700;">
                                                    {{ $tpl['badge'] }}
                                                </span>
                                                <span class="text-muted" style="font-size: 11px;">A4 Portrait</span>
                                            </div>

                                            <div class="cv-card-title">{{ $tpl['name'] }}</div>
                                            <div class="cv-card-desc">{{ $tpl['desc'] }}</div>

                                            <div class="cv-score-pill">
                                                <i class="fa fa-line-chart text-info"></i> Điểm ATS: <strong>{{ $aiScore > 0 ? $aiScore . '/100' : 'Sẵn sàng' }}</strong>
                                            </div>
                                        </div>

                                        <div class="cv-card-footer">
                                            @if($isThisPrimary)
                                                <span class="text-success font-weight-bold" style="font-size: 12.5px;">
                                                    <i class="fa fa-check-circle"></i> Đang là CV Chính
                                                </span>
                                            @else
                                                <button wire:click="setPrimaryCv('online', '{{ $tpl['id'] }}')" class="btn-set-primary" type="button">
                                                    <i class="fa fa-star-o"></i> Đặt làm CV chính
                                                </button>
                                            @endif

                                            <div class="d-flex gap-1">
                                                <a href="{{ route('candidates.cv.download', ['template' => $tpl['id'], 'mode' => 'stream', 'action' => 'view']) }}" target="_blank" class="btn-action-icon" title="Xem trước PDF">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('candidates.cv.download', ['template' => $tpl['id']]) }}" class="btn-action-icon" title="Tải file PDF về máy">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                                <a href="{{ route('candidates.cv_builder', ['template' => $tpl['id']]) }}" class="btn-action-icon" title="Tùy chỉnh trong CV Builder">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- SECTION 2: FILE CV ĐÍNH KÈM (UPLOADED CVs) -->
                        <div class="cv-section">
                            <div class="cv-section__head">
                                <h2 class="cv-section__title">
                                    <i class="fa fa-cloud-upload"></i> File CV Tải Lên (PDF / DOCX)
                                </h2>
                                <span class="badge bg-light text-dark font-weight-bold" style="font-size: 12px;">
                                    {{ $attachments->count() }} file đính kèm
                                </span>
                            </div>

                            <!-- Upload Box Form -->
                            <form wire:submit.prevent="uploadNewCv" class="mb-4">
                                <div class="upload-dropzone">
                                    <i class="fa fa-file-pdf-o"></i>
                                    <h5 style="font-weight: 800; color: #0f172a; margin-bottom: 6px;">Tải lên bản CV cá nhân mới</h5>
                                    <p class="text-muted" style="font-size: 13px; margin-bottom: 14px;">Hỗ trợ định dạng .PDF, .DOC, .DOCX (Dung lượng tối đa 10MB)</p>
                                    
                                    <div class="row justify-content-center g-2 align-items-center">
                                        <div class="col-md-5">
                                            <input type="text" wire:model="newCvTitle" class="form-control form-control-sm" placeholder="Tên gợi nhớ (Ví dụ: CV_Backend_Senior_2026)" style="border-radius: 8px;">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="file" wire:model="newCvUpload" class="form-control form-control-sm" accept=".pdf,.doc,.docx" style="border-radius: 8px;">
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary btn-sm w-100" style="border-radius: 8px; font-weight: 700; background: #0f172a; border-color: #0f172a;" wire:loading.attr="disabled">
                                                <span wire:loading.remove wire:target="newCvUpload, uploadNewCv"><i class="fa fa-upload me-1"></i> Tải lên</span>
                                                <span wire:loading wire:target="newCvUpload, uploadNewCv"><i class="fa fa-spinner fa-spin"></i> Đang tải...</span>
                                            </button>
                                        </div>
                                    </div>
                                    @error('newCvUpload') <div class="text-danger mt-2" style="font-size: 12px; font-weight: 600;">{{ $message }}</div> @enderror
                                </div>
                            </form>

                            <!-- List of Attachments -->
                            @if($attachments->isNotEmpty())
                                <div class="attachment-list">
                                    @foreach($attachments as $att)
                                        @php
                                            $isThisPrimary = ($primaryCvType === 'attachment' && $primaryCvAttachmentId === $att->id);
                                        @endphp
                                        <div class="attachment-item {{ $isThisPrimary ? 'is-primary' : '' }}">
                                            <div class="attachment-left">
                                                <div class="attachment-icon">
                                                    <i class="fa fa-file-pdf-o"></i>
                                                </div>
                                                <div>
                                                    <div class="attachment-name">
                                                        {{ $att->original_filename }}
                                                        @if($isThisPrimary)
                                                            <span class="badge bg-warning text-dark ms-2" style="font-size: 10.5px;">⭐ CV CHÍNH</span>
                                                        @endif
                                                    </div>
                                                    <div class="attachment-meta">
                                                        <span><i class="fa fa-hdd-o me-1"></i> {{ round($att->size_bytes / 1024) }} KB</span>
                                                        <span class="mx-2">•</span>
                                                        <span><i class="fa fa-clock-o me-1"></i> Tải lên ngày {{ $att->created_at->format('d/m/Y H:i') }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                @if($isThisPrimary)
                                                    <span class="text-success font-weight-bold me-2" style="font-size: 12.5px;">
                                                        <i class="fa fa-check-circle"></i> Đang là CV Chính
                                                    </span>
                                                @else
                                                    <button wire:click="setPrimaryCv('attachment', null, {{ $att->id }})" class="btn-set-primary" type="button">
                                                        <i class="fa fa-star-o"></i> Đặt làm CV chính
                                                    </button>
                                                @endif

                                                <a href="{{ Storage::disk('public')->url($att->path) }}" target="_blank" class="btn-action-icon" title="Xem file">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ Storage::disk('public')->url($att->path) }}" download class="btn-action-icon" title="Tải xuống">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                                <button wire:click="deleteAttachment({{ $att->id }})" wire:confirm="Bạn có chắc chắn muốn xóa file CV này?" class="btn-action-icon text-danger" title="Xóa file">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted" style="background: #f8fafc; border-radius: 12px;">
                                    <i class="fa fa-folder-open-o fa-2x mb-2 text-muted"></i>
                                    <p class="mb-0" style="font-size: 13.5px;">Bạn chưa tải lên file CV đính kèm nào. Hãy kéo thả file PDF vào ô bên trên để tải lên!</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

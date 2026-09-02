@php
    $isProfileReady = empty($missingProfileFields);
    $cityLabel = \App\Enums\VietnamProvince::tryFrom((string) $city)?->label() ?? $city;
@endphp

<div class="premium-dashboard-container">
    <section class="candidate-dashboard-area section_70" style="padding: 28px 0 60px 0; background: #f8fafc; min-height: 85vh;">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <!-- Standard Left Employer Sidebar -->
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <!-- Main Content (Right Column) -->
                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right d-flex flex-column gap-4">
                        
                        <!-- Top Hero & Profile Completion (Double Bezel) -->
                        <div class="p-4 rounded-4 shadow-sm bg-white border">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center gap-3.5">
                                        <div class="position-relative flex-shrink-0">
                                            <img src="{{ $logo ? $logo->temporaryUrl() : ($branch?->image ? asset('storage/' . ltrim($branch->image, '/')) : asset('assets/img/company-logo-1.png')) }}" 
                                                 alt="{{ $name ?: 'Logo chi nhánh' }}" 
                                                 class="rounded-4 object-fit-cover border shadow-sm" 
                                                 style="width: 80px; height: 80px; background: #fff; padding: 4px;">
                                            @if($canEdit)
                                                <label for="company_logo_upload" class="position-absolute bottom-0 end-0 bg-dark text-white rounded-circle d-flex align-items-center justify-content-center cursor-pointer shadow" style="width: 28px; height: 28px; font-size: 11px; cursor: pointer;">
                                                    <i class="fa fa-camera"></i>
                                                </label>
                                                <input type="file" id="company_logo_upload" wire:model="logo" class="d-none" accept="image/jpeg,image/png,image/webp">
                                            @endif
                                        </div>

                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <h2 class="fw-bold mb-0 text-dark" style="font-size: 20px;">{{ $name ?: 'Hồ sơ chi nhánh' }}</h2>
                                                @if($code)
                                                    <span class="badge rounded-pill bg-light text-secondary border px-2.5 py-1" style="font-size: 11px;">Mã: {{ $code }}</span>
                                                @endif
                                                <span class="badge rounded-pill {{ $branch?->is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-2.5 py-1" style="font-size: 11px;">
                                                    {{ $branch?->is_active ? 'Đang hoạt động' : 'Chưa kích hoạt' }}
                                                </span>
                                            </div>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                {{ $address ?: 'Tổ chức Giáo dục FPT - Môi trường làm việc chuẩn quốc tế và đổi mới sáng tạo.' }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    @error('logo') <div class="text-danger mt-2" style="font-size: 12px;">{{ $message }}</div> @enderror
                                    <div wire:loading wire:target="logo" class="text-primary mt-2" style="font-size: 12px;"><i class="fa fa-spinner fa-spin"></i> Đang tải logo lên...</div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="p-3 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5" style="font-size: 12.5px;">
                                            <span class="text-muted fw-bold">Độ hoàn thiện hồ sơ</span>
                                            <strong class="text-dark">{{ $profileCompletion }}%</strong>
                                        </div>
                                        <div class="progress rounded-pill mb-2" style="height: 7px; background: #e2e8f0;">
                                            <div class="progress-bar rounded-pill" role="progressbar" style="width: {{ $profileCompletion }}%; background: linear-gradient(90deg, #f37021, #ea580c);" aria-valuenow="{{ $profileCompletion }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            {{ $isProfileReady ? '✓ Hồ sơ chi nhánh đã đầy đủ thông tin.' : 'Cần bổ sung: ' . implode(', ', array_slice($missingProfileFields, 0, 2)) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Sections -->
                        <form wire:submit.prevent="save" class="d-flex flex-column gap-4">
                            
                            <!-- 01. Core Branch Details -->
                            <div class="p-4 rounded-4 shadow-sm bg-white border">
                                <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                                    <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 12px; background: #f37021 !important;">1</span>
                                    <h4 class="fw-bold mb-0 text-dark" style="font-size: 16px;">Thông tin định danh chi nhánh</h4>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;">Tên chi nhánh / Đơn vị <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="name" class="form-control rounded-3" style="font-size: 13px;" @readonly(! $canEdit)>
                                        @error('name') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;">Mã chi nhánh</label>
                                        <input type="text" wire:model="code" class="form-control rounded-3 bg-light" style="font-size: 13px;" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;">Tỉnh / Thành phố <span class="text-danger">*</span></label>
                                        <select wire:model.live="city" class="form-select rounded-3" style="font-size: 13px;" @disabled(! $canEdit)>
                                            <option value="">-- Chọn tỉnh/thành --</option>
                                            @foreach($provinceOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('city') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;">Mã tỉnh</label>
                                        <input type="text" wire:model="province_code" class="form-control rounded-3 bg-light" style="font-size: 13px;" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;">Quy mô nhân sự</label>
                                        <input type="number" min="0" wire:model="employee_count" placeholder="Ví dụ: 500" class="form-control rounded-3" style="font-size: 13px;" @readonly(! $canEdit)>
                                        @error('employee_count') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;">Giới thiệu tổng quan về chi nhánh</label>
                                        <textarea rows="5" wire:model="description" placeholder="Mô tả văn hóa, lịch sử thành lập và môi trường làm việc..." class="form-control rounded-3" style="font-size: 13px;" @readonly(! $canEdit)></textarea>
                                        @error('description') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- 02. Contact & Headquarters -->
                            <div class="p-4 rounded-4 shadow-sm bg-white border">
                                <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                                    <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 12px; background: #f37021 !important;">2</span>
                                    <h4 class="fw-bold mb-0 text-dark" style="font-size: 16px;">Thông tin liên hệ & Trụ sở chính</h4>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;">Hotline / Số điện thoại tuyển dụng</label>
                                        <input type="text" wire:model="phone" placeholder="024 7300 1866" class="form-control rounded-3" style="font-size: 13px;" @readonly(! $canEdit)>
                                        @error('phone') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;">Email tiếp nhận hồ sơ</label>
                                        <input type="email" wire:model="email_contact" placeholder="tuyendung.fe@fpt.edu.vn" class="form-control rounded-3" style="font-size: 13px;" @readonly(! $canEdit)>
                                        @error('email_contact') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;">Địa chỉ chi nhánh / Trụ sở</label>
                                        <input type="text" wire:model="address" placeholder="Tòa nhà FPT, Phạm Văn Bạch, Cầu Giấy, Hà Nội" class="form-control rounded-3" style="font-size: 13px;" @readonly(! $canEdit)>
                                        @error('address') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;">Trang web chính thức</label>
                                        <input type="url" wire:model="website" placeholder="https://fpt.edu.vn" class="form-control rounded-3" style="font-size: 13px;" @readonly(! $canEdit)>
                                        @error('website') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- 03. Social Channels -->
                            <div class="p-4 rounded-4 shadow-sm bg-white border">
                                <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                                    <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 12px; background: #f37021 !important;">3</span>
                                    <h4 class="fw-bold mb-0 text-dark" style="font-size: 16px;">Kênh truyền thông & Mạng xã hội</h4>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;"><i class="fa fa-facebook-square text-primary me-1"></i> Fanpage Facebook</label>
                                        <input type="url" wire:model="facebook_url" placeholder="https://facebook.com/fptcareers" class="form-control rounded-3" style="font-size: 13px;" @readonly(! $canEdit)>
                                        @error('facebook_url') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;"><i class="fa fa-linkedin-square text-info me-1"></i> Kênh LinkedIn</label>
                                        <input type="url" wire:model="linkedin_url" placeholder="https://linkedin.com/company/fpt-education" class="form-control rounded-3" style="font-size: 13px;" @readonly(! $canEdit)>
                                        @error('linkedin_url') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;"><i class="fa fa-twitter text-primary me-1"></i> Twitter / X</label>
                                        <input type="url" wire:model="twitter_url" placeholder="https://twitter.com/..." class="form-control rounded-3" style="font-size: 13px;" @readonly(! $canEdit)>
                                        @error('twitter_url') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- 04. Workplaces list -->
                            @if($branch && $branch->workplaces->isNotEmpty())
                                <div class="p-4 rounded-4 shadow-sm bg-white border">
                                    <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                                        <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 12px; background: #f37021 !important;">4</span>
                                        <h4 class="fw-bold mb-0 text-dark" style="font-size: 16px;">Cơ sở & Địa điểm làm việc trực thuộc</h4>
                                    </div>

                                    <div class="row g-3">
                                        @foreach($branch->workplaces as $wp)
                                            <div class="col-md-6">
                                                <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                                    <div class="fw-bold text-dark" style="font-size: 13.5px;"><i class="fa fa-building-o text-primary me-1.5"></i> {{ $wp->name }}</div>
                                                    <div class="text-muted mt-1" style="font-size: 12px;"><i class="fa fa-map-marker text-danger me-1"></i> {{ $wp->address }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Save Actions -->
                            <div class="d-flex align-items-center justify-content-between p-4 rounded-4 bg-white shadow-sm border">
                                <div>
                                    @if($canEdit)
                                        <span class="text-success fw-bold" style="font-size: 12.5px;"><i class="fa fa-check-circle me-1"></i> Quyền Giám đốc chi nhánh: Có thể cập nhật toàn bộ thông tin.</span>
                                    @else
                                        <span class="text-muted" style="font-size: 12.5px;"><i class="fa fa-lock me-1"></i> Chế độ xem: Chỉ Giám đốc chi nhánh có quyền lưu thay đổi.</span>
                                    @endif
                                </div>

                                @if($canEdit)
                                    <button type="submit" wire:loading.attr="disabled" class="btn text-white fw-bold px-4 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2" style="background: linear-gradient(135deg, #f37021 0%, #ea580c 100%); border: none; font-size: 13.5px;">
                                        <span wire:loading.remove wire:target="save">Cập nhật hồ sơ chi nhánh</span>
                                        <span wire:loading wire:target="save"><i class="fa fa-spinner fa-spin"></i> Đang lưu...</span>
                                        <i class="fa fa-arrow-right"></i>
                                    </button>
                                @endif
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

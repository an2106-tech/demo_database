<div class="premium-dashboard-container">
    <section class="candidate-dashboard-area section_70" style="padding: 28px 0 60px 0; background: #f8fafc; min-height: 85vh;">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <!-- Standard Employer Sidebar (Left Column) -->
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <!-- Main Content (Right Column) -->
                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right d-flex flex-column gap-4">
                        
                        <!-- Top Header & Filter Bar (Double Bezel Outer Shell) -->
                        <div class="p-4 rounded-4 shadow-sm bg-white border d-flex flex-column gap-3">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                <div>
                                    <div class="d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill mb-2" style="background: rgba(243, 112, 33, 0.1); color: #f37021; font-size: 11.5px; font-weight: 700;">
                                        <i class="fa fa-search"></i> Talent Acquisition
                                    </div>
                                    <h3 class="fw-bold mb-1" style="font-size: 20px; color: #0f172a;">
                                        Tìm kiếm & Săn nhân tài FPT
                                    </h3>
                                    <p class="mb-0 text-muted" style="font-size: 13px;">
                                        Khám phá ứng viên tiềm năng trong toàn bộ hệ thống FPT Education.
                                    </p>
                                </div>

                                <div class="text-muted text-nowrap fw-semibold" style="font-size: 13px;">
                                    Tìm thấy <span class="badge bg-light text-primary border rounded-pill px-2.5 py-1" style="font-size: 13px;">{{ $candidates->total() }}</span> hồ sơ
                                </div>
                            </div>

                            <!-- Integrated Search & Filter Controls -->
                            <div class="row g-2 pt-2 border-top">
                                <div class="col-md-6">
                                    <div class="position-relative">
                                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm theo tên, chuyên môn, kỹ năng..." class="form-control rounded-pill ps-4 pe-4" style="font-size: 13px; height: 40px; border-color: #e2e8f0;">
                                        <i class="fa fa-search position-absolute text-muted" style="left: 14px; top: 13px; font-size: 13px;"></i>
                                        @if(filled($search))
                                            <button type="button" wire:click="$set('search', '')" class="btn btn-link position-absolute p-0 text-muted" style="right: 14px; top: 8px; font-size: 14px;">&times;</button>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select wire:model.live="location" class="form-select rounded-pill" style="font-size: 13px; height: 40px; border-color: #e2e8f0;">
                                        <option value="">Tất cả khu vực</option>
                                        <option value="Hà Nội">Hà Nội</option>
                                        <option value="Hồ Chí Minh">TP. Hồ Chí Minh</option>
                                        <option value="Đà Nẵng">Đà Nẵng</option>
                                        <option value="Cần Thơ">Cần Thơ</option>
                                        <option value="Quy Nhơn">Bình Định (Quy Nhơn)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select wire:model.live="experience" class="form-select rounded-pill" style="font-size: 13px; height: 40px; border-color: #e2e8f0;">
                                        <option value="">Kinh nghiệm làm việc</option>
                                        <option value="0">Dưới 1 năm / Fresher</option>
                                        <option value="1-3">1 - 3 năm</option>
                                        <option value="3-5">3 - 5 năm</option>
                                        <option value="5+">Trên 5 năm (Senior)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Candidates High-End List -->
                        <div class="d-flex flex-column gap-3">
                            @forelse ($candidates as $candidate)
                                @php
                                    $latestApp = $candidate->applications->first();
                                    $jobTitle = $candidate->title ?? $latestApp?->job?->title ?? 'Chuyên viên tài năng';
                                    $branchName = $latestApp?->job?->branch?->name ?? 'FPT Education';
                                @endphp
                                <div class="p-3.5 p-md-4 rounded-4 bg-white shadow-sm border d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3" 
                                     style="transition: all 0.2s cubic-bezier(0.32,0.72,0,1);" 
                                     wire:key="candidate-browse-{{ $candidate->id }}">
                                    
                                    <div class="d-flex align-items-start gap-3.5">
                                        <div class="position-relative flex-shrink-0">
                                            <img src="{{ $candidate->user?->avatar_url ?? asset('assets/img/candidate-default.png') }}" 
                                                 alt="{{ $candidate->name }}" 
                                                 class="rounded-circle object-fit-cover border" 
                                                 style="width: 54px; height: 54px; background: #fff;">
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle"></span>
                                        </div>

                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <a href="{{ route('employers.candidate_detail', $candidate->id) }}" class="fw-bold text-dark text-decoration-none" style="font-size: 15.5px;">
                                                    {{ $candidate->name }}
                                                </a>
                                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                                    <i class="fa fa-briefcase me-1 text-primary"></i> {{ $candidate->experience_years ?? 0 }} năm KN
                                                </span>
                                            </div>

                                            <div class="text-muted fw-semibold" style="font-size: 13px; margin-bottom: 6px;">
                                                {{ $jobTitle }}
                                            </div>

                                            <div class="d-flex align-items-center gap-3 text-muted flex-wrap" style="font-size: 12px;">
                                                <span><i class="fa fa-envelope-o me-1"></i> {{ $candidate->email ?? 'Chưa cập nhật' }}</span>
                                                <span><i class="fa fa-map-marker text-danger me-1"></i> {{ $candidate->address ?: $branchName }}</span>
                                                @if($candidate->phone)
                                                    <span><i class="fa fa-phone me-1"></i> {{ $candidate->phone }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 align-self-end align-self-md-center flex-shrink-0">
                                        <a href="{{ route('employers.message', ['chat' => $candidate->id]) }}" class="btn btn-sm btn-light border px-3 py-2 rounded-pill fw-bold text-secondary" style="font-size: 12.5px;">
                                            <i class="fa fa-comment-o me-1 text-primary"></i> Nhắn tin
                                        </a>
                                        <a href="{{ route('employers.candidate_detail', $candidate->id) }}" class="btn btn-sm text-white fw-bold px-3.5 py-2 rounded-pill d-inline-flex align-items-center gap-1.5 shadow-sm" style="background: linear-gradient(135deg, #f37021 0%, #ea580c 100%); border: none; font-size: 12.5px;">
                                            <span>Xem hồ sơ</span>
                                            <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-5 text-center bg-white rounded-4 border shadow-sm text-muted">
                                    <div style="width: 64px; height: 64px; border-radius: 20px; background: #f1f5f9; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px auto;">
                                        <i class="fa fa-users"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-1" style="font-size: 16px;">Không tìm thấy ứng viên phù hợp</h4>
                                    <p class="m-0" style="font-size: 13px;">Hãy thử điều chỉnh lại bộ lọc hoặc nhập từ khóa tìm kiếm khác.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Pagination -->
                        @if($candidates->hasPages())
                            <div class="d-flex justify-content-end p-2">
                                {{ $candidates->links() }}
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

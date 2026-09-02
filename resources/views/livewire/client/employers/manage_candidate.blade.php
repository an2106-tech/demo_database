<div class="premium-dashboard-container">
    <section class="candidate-dashboard-area section_70" style="padding: 28px 0 60px 0; background: #f8fafc; min-height: 85vh;">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right d-flex flex-column gap-4">
                        <!-- Top Header & Search Outer Double-Bezel Shell -->
                        <div class="p-4 rounded-4 shadow-sm bg-white border d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                            <div>
                                <div class="d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill mb-2" style="background: rgba(243, 112, 33, 0.1); color: #f37021; font-size: 11.5px; font-weight: 700;">
                                    <i class="fa fa-users"></i> Quản lý nhân tài
                                </div>
                                <h3 class="fw-bold mb-1" style="font-size: 20px; color: #0f172a;">
                                    Hồ sơ ứng viên đang theo dõi
                                </h3>
                                <p class="mb-0 text-muted" style="font-size: 13px;">
                                    Xem nhanh trạng thái hồ sơ, lượt ứng tuyển và phân tích độ phù hợp AI.
                                </p>
                            </div>
                            <div class="position-relative" style="min-width: 280px;">
                                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm theo tên, email, kỹ năng..." class="form-control rounded-pill ps-5 pe-4" style="font-size: 13px; height: 42px; border-color: #e2e8f0;">
                                <i class="fa fa-search position-absolute text-muted" style="left: 16px; top: 14px; font-size: 13px;"></i>
                                @if(filled($search))
                                    <button type="button" wire:click="$set('search', '')" class="btn btn-link position-absolute p-0 text-muted" style="right: 14px; top: 9px; font-size: 15px;">&times;</button>
                                @endif
                            </div>
                        </div>

                        <!-- Candidates Bento Grid (Double Bezel Cards) -->
                        <div class="row g-3">
                            @forelse ($candidates as $candidate)
                                @php
                                    $latestApplication = $candidate->applications->sortByDesc('created_at')->first();
                                    $latestSubmission = $candidate->submissions->sortByDesc('created_at')->first();
                                    $jobTitle = $latestApplication?->job?->title
                                        ?? $latestSubmission?->job?->title
                                        ?? $candidate->title
                                        ?? 'Ứng viên tiềm năng';
                                    $appliedAt = $latestApplication?->created_at ?? $latestSubmission?->created_at;
                                    $aiScore = $latestSubmission?->ai_matching_score;
                                    $aiBadgeStyle = match (true) {
                                        $aiScore !== null && $aiScore >= 80 => 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;',
                                        $aiScore !== null && $aiScore >= 50 => 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;',
                                        $aiScore !== null => 'background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;',
                                        default => 'background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;',
                                    };
                                @endphp

                                <div class="col-md-6 col-xl-6" wire:key="candidate-card-{{ $candidate->id }}">
                                    <!-- Double-Bezel Card Outer Shell -->
                                    <div class="p-3.5 p-md-4 rounded-4 bg-white shadow-sm border h-100 d-flex flex-column justify-content-between" style="transition: all 0.25s cubic-bezier(0.32,0.72,0,1);">
                                        <div>
                                            <!-- Card Header -->
                                            <div class="d-flex align-items-start gap-3 mb-3">
                                                <div class="position-relative flex-shrink-0">
                                                    <img src="{{ $candidate->user?->avatar_url ?? asset('assets/img/candidate-default.png') }}" 
                                                         alt="{{ $candidate->name }}" 
                                                         class="rounded-circle object-fit-cover border shadow-sm" 
                                                         style="width: 52px; height: 52px; background: #fff;">
                                                    <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle"></span>
                                                </div>

                                                <div class="min-w-0 flex-grow-1">
                                                    <a href="{{ route('employers.candidate_detail', ['candidate' => $candidate->id]) }}" class="fw-bold text-dark text-decoration-none text-truncate d-block" style="font-size: 15px;">
                                                        {{ $candidate->name }}
                                                    </a>
                                                    <div class="text-muted text-truncate" style="font-size: 12.5px; margin-top: 2px;">
                                                        {{ $jobTitle }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Meta Chips -->
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap mb-3">
                                                @if ($aiScore !== null)
                                                    <span class="badge rounded-pill px-2.5 py-1" style="{{ $aiBadgeStyle }} font-size: 11px; font-weight: 700;">
                                                        <i class="fa fa-bolt me-1"></i> AI Match {{ $aiScore }}%
                                                    </span>
                                                @endif
                                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                                    <i class="fa fa-briefcase me-1 text-primary"></i> {{ ($candidate->experience_years ?? 0) }} năm KN
                                                </span>
                                                @if ($appliedAt)
                                                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                                        <i class="fa fa-clock-o me-1"></i> {{ $appliedAt->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Inner Core Details Box -->
                                            <div class="p-3 rounded-3 mb-3" style="background: #f8fafc; border: 1px solid #eef2f6; font-size: 12.5px;">
                                                <div class="d-flex align-items-center justify-content-between text-muted mb-1.5">
                                                    <span>Email:</span>
                                                    <strong class="text-dark text-truncate" style="max-width: 180px;">{{ $candidate->email ?? 'Chưa cập nhật' }}</strong>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between text-muted">
                                                    <span>Điện thoại:</span>
                                                    <strong class="text-dark">{{ $candidate->phone ?? 'Chưa cập nhật' }}</strong>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Card Footer Actions (Button-in-Button architecture) -->
                                        <div class="d-flex align-items-center gap-2 pt-3 border-top">
                                            <a href="{{ route('employers.message', ['chat' => $candidate->id]) }}" class="btn btn-sm btn-light border px-3 py-2 rounded-pill fw-bold text-secondary" style="font-size: 12px;" title="Nhắn tin trực tiếp">
                                                <i class="fa fa-comment-o text-primary me-1"></i> Nhắn tin
                                            </a>

                                            <a href="{{ route('employers.candidate_detail', ['candidate' => $candidate->id]) }}" class="btn btn-sm btn-light border px-3 py-2 rounded-pill fw-bold flex-grow-1 text-secondary text-center" style="font-size: 12px;">
                                                Xem chi tiết hồ sơ
                                            </a>

                                            @if ($latestSubmission)
                                                <button type="button" 
                                                        wire:click="analyzeWithAi({{ $latestSubmission->id }})" 
                                                        wire:loading.attr="disabled"
                                                        class="btn btn-sm text-white fw-bold px-3 py-2 rounded-pill d-inline-flex align-items-center justify-content-center" 
                                                        style="background: linear-gradient(135deg, #f37021 0%, #ea580c 100%); border: none; font-size: 12px;"
                                                        title="Phân tích lại AI Matching">
                                                    <i class="fa fa-bolt"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="p-5 text-center bg-white rounded-4 border shadow-sm text-muted">
                                        <div style="width: 64px; height: 64px; border-radius: 20px; background: #f1f5f9; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px auto;">
                                            <i class="fa fa-users"></i>
                                        </div>
                                        <h4 class="fw-bold text-dark mb-1" style="font-size: 16px;">Không tìm thấy ứng viên</h4>
                                        <p class="m-0" style="font-size: 13px;">Thử tìm kiếm với từ khóa khác hoặc kiểm tra lại bộ lọc.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>

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

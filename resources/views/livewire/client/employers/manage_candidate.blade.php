<div>

    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li class="active">Quản lý ứng viên</li>
        </ul>
    </div>
    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-md-4 col-lg-3 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>
                <div class="col-md-8 col-lg-9 mx-auto">
                    <div class="dashboard-right">
                        @if (session()->has('message'))
                            <div class="alert alert-success" style="border-radius: 12px; border: none; background: #e7f9ed; color: #198754; margin-bottom: 2rem;">
                                {{ session('message') }}
                            </div>
                        @endif

                        <div class="premium-panel">
                            <div class="manage-jobs-heading">
                                <h3>Quản lý ứng viên</h3>
                                <p style="margin: 10px 0 0; color: #64748b;">
                                    Danh sách ứng viên đã nộp hồ sơ vào các vị trí tuyển dụng của bạn.
                                </p>
                            </div>
                        </div>
                        <div class="candidate-list-page manage-candidates">
                            @forelse ($candidates as $candidate)
                            <div class="single-candidate-list">
                                <div class="main-comment d-flex align-items-start">
                                    <div class="candidate-image">
                                        @if($candidate->user?->avatar && file_exists(public_path('storage/' . $candidate->user->avatar)))
                                        <img src="{{ asset('storage/' . $candidate->user->avatar) }}" alt="{{ $candidate->name }}">
                                        @else
                                        <img src="{{ asset('assets/img/avatar_detail.jpg') }}" alt="{{ $candidate->name }}">
                                        @endif
                                    </div>
                                    <div class="candidate-text">
                                        <div class="candidate-info">
                                            <div class="candidate-title">
                                                <h3><a href="{{ route('candidates.candidate_detail') }}?id={{ $candidate->id }}">{{ $candidate->name }}</a></h3>
                                                <img src="{{ asset('assets/img/vn.png') }}" alt="Vietnam">
                                            </div>
                                            <p class="job-applied">{{ $candidate->applications->first()?->job->title ?? 'Nỗ lực ứng tuyển' }}</p>
                                            
                                            @php
                                                $latestSubmission = \App\Models\CandidateJobSubmission::where('candidate_id', $candidate->id)->latest()->first();
                                            @endphp

                                            <div class="ai-score-box">
                                                @if($latestSubmission && $latestSubmission->ai_matching_score)
                                                    <span class="badge-ai {{ $latestSubmission->ai_matching_score >= 80 ? 'bg-success' : ($latestSubmission->ai_matching_score >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                                        AI Match: {{ $latestSubmission->ai_matching_score }}%
                                                    </span>
                                                    <button wire:click="analyzeWithAi({{ $latestSubmission->id }})" wire:loading.attr="disabled" class="btn btn-sm btn-link" style="color: #f37021; text-decoration: none;">
                                                        <i class="fa fa-refresh"></i> Cập nhật AI
                                                    </button>
                                                @elseif($latestSubmission)
                                                    <button wire:click="analyzeWithAi({{ $latestSubmission->id }})" wire:loading.attr="disabled" class="jobguru-btn-2" style="padding: 8px 18px; font-size: 13px; border-radius: 10px;">
                                                        <span wire:loading.remove wire:target="analyzeWithAi({{ $latestSubmission->id }})">Phân tích AI</span>
                                                        <span wire:loading wire:target="analyzeWithAi({{ $latestSubmission->id }})">Đang xử lý...</span>
                                                    </button>
                                                @else
                                                    <span style="font-size: 13px; color: #999;">Chưa có dữ liệu phân tích</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="candidate-text-bottom">
                                            <div class="candidate-text-box">
                                                <p><i class="fa fa-envelope-o"></i> {{ $candidate->email }}</p>
                                                <p><i class="fa fa-briefcase"></i> {{ $candidate->experience_years ?? 0 }} năm kinh nghiệm</p>
                                            </div>
                                            <div class="candidate-action">
                                                <a href="{{ route('candidates.candidate_detail', ['id' => $candidate->id]) }}" class="jobguru-btn-2" style="border-radius: 10px; padding: 10px 24px;">Chi tiết hồ sơ</a>
                                            </div>
                                        </div>
                                        <div class="remove-icon">
                                            <a href="#" wire:click.prevent="deleteCandidate({{ $candidate->id }})" onclick="return confirm('Bạn có chắc chắn muốn xóa?')"><i class="fa fa-times"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                                <div class="text-center py-5">
                                    <img src="{{ asset('assets/img/empty.svg') }}" alt="Empty" style="max-width: 150px; opacity: 0.5; margin-bottom: 20px;">
                                    <p>Không có ứng viên nào.</p>
                                </div>
                            @endforelse
                            
                            @if($candidates->count() > 0)
                            <div class="pagination-box-row">
                                <p>Hiển thị {{ $candidates->count() }} ứng viên</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

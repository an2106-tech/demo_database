<div>
    <style>
        .manage-candidates .single-candidate-list {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #f1dfd2;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            margin-bottom: 24px;
            padding: 24px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .manage-candidates .single-candidate-list:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(243, 112, 33, 0.08);
            border-color: #f37021;
        }

        .manage-candidates .candidate-image {
            flex: 0 0 100px;
            height: 100px;
            width: 100px;
            border-radius: 20px;
            overflow: hidden;
            border: 3px solid #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .manage-candidates .candidate-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .manage-candidates .candidate-text {
            flex: 1;
            padding-left: 24px;
        }

        .manage-candidates .candidate-title h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .manage-candidates .candidate-title h3 a {
            color: #222;
        }

        .manage-candidates .candidate-title h3 a:hover {
            color: #f37021;
        }

        .manage-candidates .candidate-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 6px;
        }

        .manage-candidates .candidate-title img {
            width: 24px;
            height: auto;
            border-radius: 4px;
        }

        .manage-candidates .job-applied {
            color: #f37021;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .manage-candidates .candidate-text-bottom {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f8f9fa;
        }

        .manage-candidates .candidate-text-box {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .manage-candidates .candidate-text-box p {
            margin: 0;
            font-size: 14px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .manage-candidates .candidate-text-box p i {
            color: #f37021;
            font-size: 16px;
        }

        .manage-candidates .ai-score-box {
            background: #fff8f3;
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #ffe8d7;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .manage-candidates .badge-ai {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            color: #fff;
        }

        .manage-candidates .remove-icon {
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .manage-candidates .remove-icon a {
            color: #ccc;
            font-size: 18px;
            transition: color 0.2s;
        }

        .manage-candidates .remove-icon a:hover {
            color: #dc3545;
        }
    </style>

    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Quản lý ứng viên</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="breadcromb-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box-pagin">
                            <ul>
                                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                                <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.manage_candidates') }}">Quản lý ứng viên</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="candidate-dashboard-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-lg-3 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>
                <div class="col-md-8 col-lg-9 mx-auto">
                    <div class="dashboard-right">
                        @if (session()->has('message'))
                            <div class="alert alert-success" style="border-radius: 12px; border: none; background: #e7f9ed; color: #198754;">
                                {{ session('message') }}
                            </div>
                        @endif

                        <div class="manage-jobs manage-candidates">
                            <div class="manage-jobs-heading">
                                <h3>Quản lý ứng viên</h3>
                            </div>
                        </div>
                        <div class="candidate-list-page manage-candidates">
                            @forelse ($candidates as $candidate)
                            <div class="single-candidate-list">
                                <div class="main-comment d-flex align-items-start">
                                    <div class="candidate-image">
                                        <img src="{{ $candidate->user?->avatar ? asset('storage/' . $candidate->user->avatar) : asset('assets/img/avatar_detail.jpg') }}" alt="{{ $candidate->name }}">
                                    </div>
                                    <div class="candidate-text">
                                        <div class="candidate-info">
                                            <div class="candidate-title">
                                                <h3><a href="{{ route('candidates.candidate_detail') }}?id={{ $candidate->id }}">{{ $candidate->name }}</a></h3>
                                                <img src="{{ asset('assets/img/de.svg') }}" alt="Germany">
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

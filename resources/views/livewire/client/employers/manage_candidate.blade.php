<div>
    @php
        $candidateCount = $candidates->count();
        $submissionCount = $candidates->sum(fn ($candidate) => $candidate->submissions->count());
        $applicationCount = $candidates->sum(fn ($candidate) => $candidate->applications->count());
    @endphp

    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li class="active">Quản lý ứng viên</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <div class="col-lg-3 col-xl-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-lg-9 col-xl-9">
                    <div class="portal-grid">
                        @if (session()->has('message'))
                            <div class="alert alert-success mb-0">{{ session('message') }}</div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger mb-0">{{ session('error') }}</div>
                        @endif

                        <div class="portal-shell portal-shell--subtle p-4 p-lg-5">
                            <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
                                <div>
                                    <span class="portal-eyebrow">Quản lý ứng viên</span>
                                    <h1 class="portal-title">Danh sách hồ sơ đang được theo dõi</h1>
                                    <p class="portal-subtitle">
                                        Xem nhanh trạng thái hồ sơ, lượt ứng tuyển và phân tích AI gần nhất trong một bố cục gọn, rõ và dễ quét.
                                    </p>
                                </div>
                                <div class="portal-stats flex-grow-1" style="max-width: 520px;">
                                    <div class="portal-stat">
                                        <span class="portal-stat__value">{{ $candidateCount }}</span>
                                        <span class="portal-stat__label">Ứng viên</span>
                                    </div>
                                    <div class="portal-stat">
                                        <span class="portal-stat__value">{{ $applicationCount }}</span>
                                        <span class="portal-stat__label">Lượt ứng tuyển</span>
                                    </div>
                                    <div class="portal-stat">
                                        <span class="portal-stat__value">{{ $submissionCount }}</span>
                                        <span class="portal-stat__label">Submission</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="candidate-grid">
                            @forelse ($candidates as $candidate)
                                @php
                                    $latestApplication = $candidate->applications->sortByDesc('created_at')->first();
                                    $latestSubmission = $candidate->submissions->sortByDesc('created_at')->first();
                                    $jobTitle = $latestApplication?->job?->title
                                        ?? $latestSubmission?->job?->title
                                        ?? 'Chưa có vị trí';
                                    $appliedAt = $latestApplication?->created_at ?? $latestSubmission?->created_at;
                                    $aiScore = $latestSubmission?->ai_matching_score;
                                    $aiChip = match (true) {
                                        $aiScore !== null && $aiScore >= 80 => 'chip chip--success',
                                        $aiScore !== null && $aiScore >= 50 => 'chip chip--warning',
                                        $aiScore !== null => 'chip chip--danger',
                                        default => 'chip',
                                    };
                                @endphp

                                <article class="candidate-card">
                                    <div class="candidate-card__head">
                                        <div class="d-flex gap-3 align-items-start flex-grow-1">
                                            <div class="candidate-card__avatar">
                                                @if ($candidate->user?->avatar && file_exists(public_path('storage/' . $candidate->user->avatar)))
                                                    <img src="{{ asset('storage/' . $candidate->user->avatar) }}" alt="{{ $candidate->name }}">
                                                @else
                                                    <img src="{{ asset('assets/img/avatar_detail.jpg') }}" alt="{{ $candidate->name }}">
                                                @endif
                                            </div>

                                            <div class="candidate-card__identity">
                                                <h3>
                                                    <a href="{{ route('employers.candidate_detail', ['candidate' => $candidate->id]) }}">
                                                        {{ $candidate->name }}
                                                    </a>
                                                </h3>
                                                <p>{{ $jobTitle }}</p>
                                            </div>
                                        </div>

                                        <div class="candidate-card__meta">
                                            <span class="chip chip--accent">Việt Nam</span>
                                            @if ($appliedAt)
                                                <span class="chip">Nộp {{ $appliedAt->format('d/m/Y') }}</span>
                                            @endif
                                            @if ($aiScore !== null)
                                                <span class="{{ $aiChip }}">AI Match {{ $aiScore }}%</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="candidate-card__body">
                                        <div class="candidate-card__item">
                                            <span>Email</span>
                                            <strong>{{ $candidate->email ?? 'Chưa có' }}</strong>
                                        </div>
                                        <div class="candidate-card__item">
                                            <span>Kinh nghiệm</span>
                                            <strong>{{ ($candidate->experience_years ?? 0) }} năm</strong>
                                        </div>
                                        <div class="candidate-card__item">
                                            <span>Trạng thái</span>
                                            <strong>{{ $latestSubmission ? 'Có dữ liệu phân tích' : 'Chưa có submission' }}</strong>
                                        </div>
                                        <div class="candidate-card__item">
                                            <span>Hồ sơ</span>
                                            <strong>{{ $latestApplication ? 'Ứng tuyển gần nhất' : 'Chưa ứng tuyển' }}</strong>
                                        </div>
                                    </div>

                                    <div class="candidate-card__footer">
                                        <div class="candidate-card__actions">
                                            <a href="{{ route('employers.candidate_detail', ['candidate' => $candidate->id]) }}" class="jobguru-btn-2">
                                                Chi tiết hồ sơ
                                            </a>

                                            @if ($latestSubmission)
                                                <button
                                                    type="button"
                                                    wire:click="analyzeWithAi({{ $latestSubmission->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="analyzeWithAi({{ $latestSubmission->id }})"
                                                    class="jobguru-btn-2"
                                                >
                                                    <span wire:loading.remove wire:target="analyzeWithAi({{ $latestSubmission->id }})">
                                                        {{ $aiScore !== null ? 'Cập nhật AI' : 'Phân tích AI' }}
                                                    </span>
                                                    <span wire:loading wire:target="analyzeWithAi({{ $latestSubmission->id }})">Đang xử lý...</span>
                                                </button>
                                            @endif
                                        </div>

                                        <button
                                            type="button"
                                            wire:click="deleteCandidate({{ $candidate->id }})"
                                            wire:loading.attr="disabled"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa ứng viên này?')"
                                            class="candidate-card__danger"
                                        >
                                            <i class="fa fa-trash-o"></i>
                                            Xóa hồ sơ
                                        </button>
                                    </div>
                                </article>
                            @empty
                                <div class="portal-shell portal-shell--subtle p-5 text-center">
                                    <img src="{{ asset('assets/img/empty.svg') }}" alt="Empty" style="max-width: 180px; opacity: 0.55; margin-bottom: 18px;">
                                    <h3 class="mb-2">Chưa có ứng viên nào</h3>
                                    <p class="mb-0 text-muted">Khi có hồ sơ ứng tuyển hoặc submission mới, danh sách sẽ xuất hiện ở đây.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

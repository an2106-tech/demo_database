<div class="browse-jobs-page">
    <style>
        .browse-jobs-page .job-browse-action {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .browse-jobs-page .job-browse-action .dropdown .btn-dropdown {
            white-space: nowrap;
            padding-right: 34px;
            flex: 0 0 auto;
        }

        .browse-jobs-page .job-view-toggle {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .browse-jobs-page .job-view-toggle button {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, .35);
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .browse-jobs-page .job-view-toggle button.active {
            border-color: #2f7ff7;
            box-shadow: 0 0 0 3px rgba(47, 127, 247, .15);
        }

        .browse-jobs-page .job-view-toggle button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(47, 127, 247, .15);
        }
    </style>
    <!-- Breadcrumb -->
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Tìm việc làm</h3>
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
                                <li><a href="#">Ứng viên</a></li>
                                <li class="active-breadcromb"><a href="#">Tìm việc làm</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Job List -->
    <section class="jobguru-top-job-area browse-page section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="browse-job-head-option">
                        <div class="job-browse-search">
                            <form>
                                <input type="search" placeholder="Tìm kiếm việc làm tại đây...">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </form>
                        </div>
                        <div class="job-browse-action">
                            <div class="email-alerts">
                                <input type="checkbox" class="styled" id="b_1">
                                <label class="styled" for="b_1">Nhận thông báo qua email cho tìm kiếm này</label>
                            </div>
                            <div class="dropdown">
                                <button class="btn-dropdown dropdown-toggle" type="button" id="dropdowncur"
                                    data-bs-toggle="dropdown" aria-haspopup="true" style="text-transform:none;">Sắp xếp theo</button>
                                <ul class="dropdown-menu" aria-labelledby="dropdowncur">
                                    <li>Mới nhất</li>
                                    <li>Cũ nhất</li>
                                    <li>Ngẫu nhiên</li>
                                </ul>
                            </div>
                            <div class="job-view-toggle">
                                <button type="button"
                                    class="{{ ($display ?? 'grid') === 'grid' ? 'active' : '' }}"
                                    wire:click="setDisplay('grid')" title="Dạng lưới">
                                    <i class="fa fa-th"></i>
                                </button>
                                <button type="button"
                                    class="{{ ($display ?? 'grid') === 'list' ? 'active' : '' }}"
                                    wire:click="setDisplay('list')" title="Dạng danh sách">
                                    <i class="fa fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="available-count">
                        <h4>Có {{ $jobs->count() }} việc làm</h4>
                    </div>
                </div>
            </div>
            <div class="row" style="{{ ($display ?? 'grid') === 'list' ? 'display:none;' : '' }}">
                @forelse ($jobs as $job)
                    <div class="col-md-6 col-lg-4">
                        <div class="sigle-top-job">
                            <div class="top-job-company-image">
                                <div class="company-logo-img">
                                    <a href="{{ route('candidates.job_detail', ['id' => $job->id]) }}">
                                        <img src="{{ $job->branch?->image ? '/storage/' . ltrim($job->branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                            alt="{{ $job->branch?->name ?? 'Chi nhánh' }}"
                                            style="display:block; width:100px; height:80px; margin:0 auto; object-fit:contain;">
                                    </a>
                                </div>
                                    <h3><a href="{{ route('candidates.job_detail', ['id' => $job->id]) }}">{{ $job->title }}</a></h3>
                                    <p class="company-state" style="margin: 8px 0 0;">
                                    <i class="fa fa-building-o"></i> {{ $job->branch?->name ?? 'Chưa cập nhật' }}
                                    </p>
                                </div>
                                <div class="top-job-company-desc">
                                <ul>
                                    <li>
                                        Địa điểm
                                        <span class="company-state">
                                            <i class="fa fa-map-marker"></i>
                                            @php
                                                $cityText = \App\Enums\VietnamProvince::tryFrom($job->branch?->city ?? '')?->label() ?? ($job->branch?->city ?? 'Chưa cập nhật');
                                                $addressText = trim((string) ($job->branch?->address ?? ''));
                                                $normalize = static fn ($value) => function_exists('mb_strtolower')
                                                    ? mb_strtolower(trim((string) $value), 'UTF-8')
                                                    : strtolower(trim((string) $value));
                                                $showAddress = $addressText !== ''
                                                    && $normalize($addressText) !== $normalize($cityText)
                                                    && $normalize($addressText) !== $normalize($job->branch?->city ?? '');
                                            @endphp
                                            {{ $cityText }}
                                        </span>
                                        @if ($showAddress)
                                            <span class="company-state" style="display:block; margin-top:4px;">
                                                <i class="fa fa-location-arrow"></i> {{ $addressText }}
                                            </span>
                                        @endif
                                    </li>
                                    <li>
                                        Mức lương
                                        <span class="open-icon">
                                            <i class="fa fa-credit-card-alt"></i>
                                            @if (is_array($job->salary_range) && isset($job->salary_range['min'], $job->salary_range['max']))
                                                {{ number_format($job->salary_range['min']) }} - {{ number_format($job->salary_range['max']) }} VND
                                            @elseif (is_array($job->salary_range))
                                                {{ implode(' - ', $job->salary_range) }}
                                            @elseif (!empty($job->salary_range))
                                                {{ $job->salary_range }}
                                            @else
                                                Thỏa thuận
                                            @endif
                                        </span>
                                    </li>
                                    <li>
                                        Trạng thái
                                        <span class="varify">
                                            <i class="fa fa-check"></i>
                                            {{ $job->status?->getLabel() ?? '' }}
                                        </span>
                                    </li>
                                </ul>
                                <div class="top-job-company-btn">
                                    <a href="{{ route('candidates.apply_job', ['job' => $job->id]) }}"
                                        class="jobguru-btn-2">
                                        Ứng tuyển ngay
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center mb-0">Không có việc làm nào.</p>
                    </div>
                @endforelse
            </div>

            <div class="row" style="{{ ($display ?? 'grid') === 'grid' ? 'display:none;' : '' }}">
                <div class="col-12">
                    <div class="top-company-tab">
                        <ul>
                            @forelse ($jobs as $job)
                                <li>
                                    <div class="top-company-list">
                                            <div class="company-list-logo">
                                                <a href="{{ route('candidates.job_detail', ['id' => $job->id]) }}">
                                                    <img src="{{ $job->branch?->image ? '/storage/' . ltrim($job->branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                                    alt="{{ $job->branch?->name ?? 'Chi nhánh' }}"
                                                    style="display:block; width:100px; height:80px; margin:0 auto; object-fit:contain;">
                                                </a>
                                            </div>
                                            <div class="company-list-details">
                                                <h3><a href="{{ route('candidates.job_detail', ['id' => $job->id]) }}">{{ $job->title }}</a></h3>
                                            <p class="company-state"><i class="fa fa-building-o"></i> {{ $job->branch?->name ?? 'Chưa cập nhật' }}</p>
                                            @php
                                                $cityText = \App\Enums\VietnamProvince::tryFrom($job->branch?->city ?? '')?->label() ?? ($job->branch?->city ?? 'Chưa cập nhật');
                                                $addressText = trim((string) ($job->branch?->address ?? ''));
                                                $normalize = static fn ($value) => function_exists('mb_strtolower')
                                                    ? mb_strtolower(trim((string) $value), 'UTF-8')
                                                    : strtolower(trim((string) $value));
                                                $showAddress = $addressText !== ''
                                                    && $normalize($addressText) !== $normalize($cityText)
                                                    && $normalize($addressText) !== $normalize($job->branch?->city ?? '');
                                            @endphp
                                            <p class="company-state"><i class="fa fa-map-marker"></i> {{ $cityText }}</p>
                                            @if ($showAddress)
                                                <p class="company-state"><i class="fa fa-location-arrow"></i> {{ $addressText }}</p>
                                            @endif
                                            @if (!empty($job->workplace?->name))
                                                <p class="company-state"><i class="fa fa-thumb-tack"></i> {{ $job->workplace->name }}</p>
                                            @endif
                                            <p class="varify"><i class="fa fa-credit-card-alt"></i>
                                                @if (is_array($job->salary_range) && isset($job->salary_range['min'], $job->salary_range['max']))
                                                    {{ number_format($job->salary_range['min']) }} - {{ number_format($job->salary_range['max']) }} VND
                                                @elseif (is_array($job->salary_range))
                                                    {{ implode(' - ', $job->salary_range) }}
                                                @elseif (!empty($job->salary_range))
                                                    {{ $job->salary_range }}
                                                @else
                                                    Thỏa thuận
                                                @endif
                                            </p>
                                            <p class="open-icon"><i class="fa fa-check"></i> {{ $job->status?->getLabel() ?? '' }}</p>
                                            @if (!empty($job->deadline))
                                                <p class="open-icon"><i class="fa fa-clock-o"></i> Hạn nộp: {{ $job->deadline?->format('d/m/Y') ?? '' }}</p>
                                            @endif
                                        </div>
                                        <div class="company-list-btn">
                                            <a href="{{ route('candidates.apply_job', ['job' => $job->id]) }}"
                                                class="jobguru-btn">Ứng tuyển</a>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li>Không có việc làm nào.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

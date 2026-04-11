<div class="browse-jobs-page">
    @php
        /** @var \Illuminate\Support\Collection<int, \App\Models\Department>|\App\Models\Department[] $departments */
    @endphp
    <style>
        .browse-jobs-page .browse-job-head-option {
            background: #fff;
            padding: 24px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .06);
            border: 1px solid rgba(226, 232, 240, .8);
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .browse-jobs-page .job-browse-search-wrapper {
            display: grid;
            grid-template-columns: 1fr 200px 220px auto;
            gap: 12px;
            width: 100%;
        }

        .browse-jobs-page .form-control, 
        .browse-jobs-page .form-select {
            height: 50px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, .3);
            background: #f8fafc;
            padding: 0 16px;
            font-size: 14px;
            transition: all .2s;
        }

        .browse-jobs-page .form-control:focus, 
        .browse-jobs-page .form-select:focus {
            background: #fff;
            border-color: #2f7ff7;
            box-shadow: 0 0 0 4px rgba(47, 127, 247, .1);
            outline: none;
        }

        .browse-jobs-page .btn-reset {
            height: 50px;
            padding: 0 24px;
            border-radius: 12px;
            background: #ff6b35;
            color: #fff;
            border: none;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            transition: all .2s;
        }

        .browse-jobs-page .btn-reset:hover {
            background: #fa5a1f;
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(255, 107, 53, .2);
        }

        .browse-jobs-page .job-browse-action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            padding-top: 15px;
            border-top: 1px solid rgba(226, 232, 240, .6);
        }

        .browse-jobs-page .email-alerts-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f1f5f9;
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        .browse-jobs-page .job-view-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .browse-jobs-page .view-toggle-btn {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, .2);
            background: #fff;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .2s;
        }

        .browse-jobs-page .view-toggle-btn.active {
            background: #2f7ff7;
            color: #fff;
            border-color: #2f7ff7;
            box-shadow: 0 8px 16px rgba(47, 127, 247, .25);
        }

        .browse-jobs-page .sort-dropdown .btn-sort {
            height: 44px;
            padding: 0 16px;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, .2);
            background: #fff;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 991px) {
            .browse-jobs-page .job-browse-search-wrapper {
                grid-template-columns: 1fr 1fr;
            }
            .browse-jobs-page .btn-reset {
                grid-column: span 2;
            }
        }

        @media (max-width: 575px) {
            .browse-jobs-page .job-browse-search-wrapper {
                grid-template-columns: 1fr;
            }
            .browse-jobs-page .btn-reset {
                grid-column: span 1;
            }
            .browse-jobs-page .email-alerts-box span {
                display: none;
            }
        }

        /* Equal-Height Cards */
        .browse-jobs-page .job-grid-row {
            display: flex;
            flex-wrap: wrap;
        }
        
        .browse-jobs-page .job-item-col {
            display: flex;
            margin-bottom: 24px;
        }

        .browse-jobs-page .sigle-top-job {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
            background: #fff;
            border: 1px solid rgba(226, 232, 240, .8);
            border-radius: 18px;
            padding: 24px;
            transition: all .3s;
        }

        .browse-jobs-page .sigle-top-job:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, .08);
            border-color: rgba(47, 127, 247, .3);
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
                        <div class="job-browse-search-wrapper">
                            <input type="search" class="form-control"
                                placeholder="Từ khóa (Laravel, Kế toán...)"
                                wire:model.live.debounce.400ms="q">
                            
                            <input type="text" class="form-control"
                                placeholder="Khu vực"
                                wire:model.live.debounce.400ms="city">
                            
                            <select class="form-select" wire:model.live="department_id">
                                <option value="">Tất cả phòng ban</option>
                                @foreach(($departments ?? []) as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>

                            <button type="button" class="btn-reset" wire:click="$set('q', ''); $set('city', ''); $set('department_id', null)">
                                <i class="fa fa-refresh"></i> Xóa lọc
                            </button>
                        </div>

                        <div class="job-browse-action-bar">
                            <div class="email-alerts-box">
                                <input type="checkbox" id="email-alert-chk">
                                <label for="email-alert-chk" class="mb-0"><span>Nhận thông báo qua email cho tìm kiếm này</span></label>
                            </div>

                            <div class="job-view-controls">
                                <div class="dropdown sort-dropdown">
                                    <button class="btn-sort dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Sắp xếp theo
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Mới nhất</a></li>
                                        <li><a class="dropdown-item" href="#">Cũ nhất</a></li>
                                        <li><a class="dropdown-item" href="#">Ngẫu nhiên</a></li>
                                    </ul>
                                </div>

                                <div class="job-view-toggle">
                                    <button type="button" class="view-toggle-btn {{ ($display ?? 'grid') === 'grid' ? 'active' : '' }}"
                                        wire:click="setDisplay('grid')" title="Dạng lưới">
                                        <i class="fa fa-th"></i>
                                    </button>
                                    <button type="button" class="view-toggle-btn {{ ($display ?? 'grid') === 'list' ? 'active' : '' }}"
                                        wire:click="setDisplay('list')" title="Dạng danh sách">
                                        <i class="fa fa-list"></i>
                                    </button>
                                </div>
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
            <div class="row g-4" style="{{ ($display ?? 'grid') === 'list' ? 'display:none;' : '' }}">
                @forelse ($jobs as $job)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="sigle-top-job h-100">
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

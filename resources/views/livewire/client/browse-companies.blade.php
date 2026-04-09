<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Địa chỉ việc làm</h3>
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
                                <li class="active-breadcromb"><a href="#">Địa chỉ việc làm</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="jobguru-browse-company-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-tabs" id="companyTabs" role="tablist">
                        @foreach ($letters as $letter)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="company_{{ strtolower($letter) }}_tab" data-bs-toggle="tab"
                                    href="#company_{{ strtolower($letter) }}" role="tab"
                                    aria-controls="company_{{ strtolower($letter) }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ strtolower($letter) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content" id="companyTabContent">
                        @foreach ($letters as $letter)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                id="company_{{ strtolower($letter) }}" role="tabpanel"
                                aria-labelledby="company_{{ strtolower($letter) }}_tab">
                                <div class="row">
                                    @forelse ($branchesByLetter->get($letter, collect()) as $branch)
                                        @continue(((int) ($branch->published_jobs_count ?? 0)) < 1)
                                        <div class="col-lg-4 col-md-6">
                                            <div class="single-browse-company">
                                                <div class="browse-company-logo">
                                                    <a href="#">
                                                        <img src="{{ $branch->image ? '/storage/' . ltrim($branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                                            alt="{{ $branch->name }}"
                                                            style="display:block; width:120px; height:80px; margin:0 auto; object-fit:contain;">
                                                    </a>
                                                </div>

                                                <h3><a href="#">{{ $branch->name }}</a></h3>

                                                <p class="company-state" style="margin: 6px 0 0;">
                                                    <i class="fa fa-map-marker"></i>
                                                    {{ \App\Enums\VietnamProvince::tryFrom($branch->city)?->label() ?? $branch->city }}
                                                </p>
                                                @if (!empty($branch->address))
                                                    <p class="company-state" style="margin: 6px 0 0;">
                                                        <i class="fa fa-location-arrow"></i>
                                                        {{ $branch->address }}
                                                    </p>
                                                @endif
                                                <p class="open-icon" style="margin: 6px 0 0;">
                                                    <i class="fa fa-briefcase"></i>
                                                    1 vị trí đang tuyển
                                                </p>
                                                <p class="varify" style="margin: 6px 0 0;">
                                                    <i class="fa fa-check"></i>
                                                    {{ $branch->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động' }}
                                                </p>

                                                <ul>
                                                    <li><i class="fa fa-star"></i></li>
                                                    <li><i class="fa fa-star"></i></li>
                                                    <li><i class="fa fa-star"></i></li>
                                                    <li><i class="fa fa-star"></i></li>
                                                    <li><i class="fa fa-star-half-o"></i></li>
                                                </ul>

                                                @if ($branch->recruitmentJobs?->isNotEmpty())
                                                    <div style="margin-top: 12px; border-top: 1px solid #eee; padding-top: 10px;">
                                                        <ul class="list-unstyled" style="margin: 0;">
                                                            @foreach ($branch->recruitmentJobs->take(1) as $job)
                                                                @php
                                                                    $salaryText = 'Thỏa thuận';
                                                                    if (is_array($job->salary_range) && isset($job->salary_range['min'], $job->salary_range['max'])) {
                                                                        $salaryText = number_format($job->salary_range['min']) . ' - ' . number_format($job->salary_range['max']) . ' VND';
                                                                    } elseif (is_array($job->salary_range) && count($job->salary_range) > 0) {
                                                                        $salaryText = implode(' - ', $job->salary_range);
                                                                    } elseif (!empty($job->salary_range)) {
                                                                        $salaryText = (string) $job->salary_range;
                                                                    }
                                                                @endphp
                                                                <li style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding: 6px 0; border-bottom: 1px dashed #eee;">
                                                                    <a href="{{ route('candidates.apply_job', ['job' => $job->id]) }}"
                                                                        style="flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                                        {{ $job->title }}
                                                                    </a>
                                                                    <span style="white-space:nowrap; font-size: 12px; color: #666;">
                                                                        {{ $salaryText }}
                                                                    </span>
                                                                    <span style="white-space:nowrap; font-size: 12px; color: #666;">
                                                                        {{ $job->deadline?->format('d/m') ?? '' }}
                                                                    </span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif

                                                <div class="single-browse-company-btn">
                                                    <a href="#" class="jobguru-btn">Xem hồ sơ</a>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center">
                                            <p>Không có công ty bắt đầu bằng chữ {{ $letter }}</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

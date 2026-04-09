<div>
    <section class="jobguru-banner-area home-banner">
        <div class="banner-slider owl-carousel">
            <div class="banner-single-slider slider-item-1">
                <div class="slider-offset">
                    {{-- <img src="{{ asset('assets/img/banner-tuyen-dung-11_1632972849.png') }}" alt=""> --}}
                </div>
            </div>
            <div class="banner-single-slider slider-item-2">
                <div class="slider-offset">
                    {{-- <img src="{{ asset('assets/img/banner2.jpg') }}" alt=""> --}}
                </div>
            </div>
        </div>
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="banner-search" style="max-width:760px; margin:0 auto;">
                            <h2>Thuê các chuyên gia tự do hàng đầu.</h2>
                            <h4>Chúng tôi có 1542 cơ hội việc làm dành cho bạn! </h4>
                            <form>
                                <div class="banner-form-box">
                                    <div class="banner-form-input">
                                        <input type="text" placeholder="Chức danh, Từ khóa, hoặc Cụm từ">
                                    </div>
                                    <div class="banner-form-input">
                                        <input type="text" placeholder="Thành phố, Tỉnh hoặc Mã bưu điện">
                                    </div>
                                    <div class="banner-form-input">
                                        <select class="banner-select">
                                            <option selected>Chọn lĩnh vực</option>
                                            @forelse($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @empty
                                                <option disabled>Không có dữ liệu</option>
                                            @endforelse
                                        </select>
                                    </div>
                                    <div class="banner-form-input">
                                        <button type="submit"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="jobguru-categories-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="site-heading">
                        <h2>Danh mục <span>Phổ biến nhất</span></h2>
                        <p>Một sự nghiệp tốt hơn đang chờ đón bạn. Chúng tôi sẽ giúp bạn tìm thấy nó. Chúng tôi là bước
                            đệm đầu tiên để bạn đạt được mọi ước mơ.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                @forelse($categories as $category)
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <a href="#" class="single-category-holder account_cat">
                        <div class="category-holder-icon">
                            @php($icon = trim((string) ($category->icon ?? '')))
                            <i class="{{ $icon !== '' ? (\Illuminate\Support\Str::startsWith($icon, 'bi') ? $icon : 'bi bi-' . $icon) : 'bi bi-grid' }}"></i>
                        </div>
                            <div class="category-holder-text">
                                <h3>{{ $category->name }}</h3>
                            </div>
                            <img src="{{ asset($category->image) }}" alt="{{ $category->name }}" /></a>
                    </div>
                @empty
                    <div class="col-12">
                        <p>Không có danh mục nào</p>
                    </div>
                @endforelse
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="load-more">
                        <a href="{{ route('candidates.browse_categories') }}" class="jobguru-btn">Xem tất cả ngành nghề</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
            
    <section class="jobguru-inner-hire-area section_100">
        <div class="hire_circle"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="inner-hire-left">
                        <h3>Tuyển dụng nhân sự</h3>

                        <p>Tìm kiếm ứng viên tài năng cho doanh nghiệp của bạn một cách nhanh chóng và hiệu quả. Chúng
                            tôi kết nối bạn với hàng ngàn chuyên gia sẵn sàng làm việc ngay lập tức. Hãy bắt đầu xây
                        </p>

        </div>
    </section>
    <section class="jobguru-job-tab-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="site-heading">
                        <h2>Chi nhánh & <span>Việc làm</span></h2>
                        <p>Thật dễ dàng. Chỉ cần đăng việc bạn cần hoàn thành và nhận báo giá cạnh tranh từ các
                            freelancer trong vài phút.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class=" job-tab">
                        <ul class="nav nav-pills job-tab-switch" id="pills-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pills-companies-tab" data-bs-toggle="pill"
                                    href="#pills-companies" role="tab" aria-controls="pills-companies"
                                    aria-selected="true">Chi nhánh hàng đầu</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-job-tab" data-bs-toggle="pill" href="#pills-job"
                                    role="tab" aria-controls="pills-job" aria-selected="false">Việc làm mới nhất</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-companies" role="tabpanel"
                            aria-labelledby="pills-companies-tab">
                            <div class="top-company-tab">
                                <ul>
                                    {{--
                                    @forelse($branches as $branch)
                                        @continue(((int) ($branch->published_jobs_count ?? 0)) < 1)
                                        <li>
                                            <div class="top-company-list" style="display:block;">
                                                <?php
                                                $cityLabel = \App\Enums\VietnamProvince::tryFrom($branch->city ?? '')?->label()
                                                    ?? ($branch->city ?? null);

                                                $branchTitle = (string) ($branch->name ?? '');
                                                $titleLower = function_exists('mb_strtolower')
                                                    ? mb_strtolower($branchTitle, 'UTF-8')
                                                    : strtolower($branchTitle);
                                                $cityLower = $cityLabel
                                                    ? (function_exists('mb_strtolower') ? mb_strtolower($cityLabel, 'UTF-8') : strtolower($cityLabel))
                                                    : '';

                                                if ($cityLabel && $cityLower !== '' && !str_contains($titleLower, $cityLower)) {
                                                    $branchTitle .= ' - ' . $cityLabel;
                                                }
                                                ?>
                                                <div class="row align-items-center" style="margin: 0;">
                                                    <div class="col-12 col-md-2" style="padding: 18px 10px;">
                                                        <div class="company-list-logo">
                                                            <a href="#">
                                                                <img src="{{ $branch->image ? asset('storage/' . ltrim($branch->image, '/')) : asset('assets/img/company-logo-1.png') }}"
                                                                    alt="{{ $branch->name }}"
                                                                    style="display:block; width:100px; height:80px; margin:0 auto; object-fit:contain;" />
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-8" style="padding: 18px 10px;">
                                                        <div class="company-list-details">
                                                            <h3 style="margin-bottom: 6px;"><a href="#">{{ $branchTitle }}</a></h3>

                                                    <?php if (false): ?>
                                                    <p class="company-state"><i class="fa fa-map-marker"></i>
                                                        {{ \App\Enums\VietnamProvince::tryFrom($branch->city ?? '')?->label() ?? ($branch->city ?? 'Địa điểm chưa xác định') }}
                                                    </p>

                                                    @if($branch->address)
                                                        <p class="company-state"><i class="fa fa-location-arrow"></i>
                                                            {{ $branch->address }}</p>
                                                    @endif
                                                    <p class="open-icon"><i
                                                            class="fa fa-briefcase"></i>{{ (int) ($branch->published_jobs_count ?? 0) }}
                                                        vị trí đang tuyển</p>
                                                    <p class="varify"><i
                                                            class="fa fa-check"></i>{{ $branch->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động' }}
                                                    </p>
                                                    <p class="rating-company">{{ number_format(rand(37, 50) / 10, 1) }}</p>
                                                    <?php endif; ?>

                                                    <div style="display:flex; flex-wrap:wrap; gap:14px; align-items:center; font-size: 13px; color:#6b7280;">
                                                        <span><i class="fa fa-map-marker"></i> {{ $cityLabel ?? 'Chưa cập nhật' }}</span>
                                                        @if($branch->address)
                                                            <span><i class="fa fa-location-arrow"></i> {{ $branch->address }}</span>
                                                        @endif
                                                        <span><i class="fa fa-briefcase"></i> {{ (int) ($branch->published_jobs_count ?? 0) }} vị trí đang tuyển</span>
                                                        <span><i class="fa fa-check"></i> {{ $branch->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động' }}</span>
                                                        <span class="rating-company" style="margin-left: auto;">{{ number_format(rand(37, 50) / 10, 1) }}</span>
                                                    </div>

                                                    <?php if (false): ?>
                                                        <div style="margin-top: 10px;">
                                                            <ul class="list-unstyled" style="margin: 0;">
                                                                <?php foreach (($branch->recruitmentJobs ?? collect())->take(3) as $job): ?>
                                                                    <?php
                                                                        $salaryText = 'Thỏa thuận';
                                                                        if (is_array($job->salary_range) && isset($job->salary_range['min'], $job->salary_range['max'])) {
                                                                            $salaryText = number_format($job->salary_range['min']) . ' - ' . number_format($job->salary_range['max']) . ' VND';
                                                                        } elseif (is_array($job->salary_range) && count($job->salary_range) > 0) {
                                                                            $salaryText = implode(' - ', $job->salary_range);
                                                                        } elseif (!empty($job->salary_range)) {
                                                                            $salaryText = (string) $job->salary_range;
                                                                        }
                                                                    ?>
                                                                    <li style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding: 6px 0; border-top: 1px dashed #eee;">
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
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                </div>
                                                <div class="col-12 col-md-2" style="padding: 18px 10px; text-align:right;">
                                                <div class="company-list-btn">
                                                    <a href="#" class="jobguru-btn">Xem hồ sơ</a>
                                                </div>
                                                </div>
                                                </div>

                                                <?php if (($branch->recruitmentJobs ?? collect())->isNotEmpty()): ?>
                                                    <div class="row" style="margin: 0;">
                                                        <div class="col-12 col-md-2"></div>
                                                        <div class="col-12 col-md-10" style="padding: 0 10px 18px;">
                                                            <ul class="list-unstyled" style="margin: 0;">
                                                                <?php foreach (($branch->recruitmentJobs ?? collect())->take(5) as $job): ?>
                                                                    <?php
                                                                    $salaryText = 'Thỏa thuận';
                                                                    if (is_array($job->salary_range) && isset($job->salary_range['min'], $job->salary_range['max'])) {
                                                                        $salaryText = number_format($job->salary_range['min']) . ' - ' . number_format($job->salary_range['max']) . ' VND';
                                                                    } elseif (is_array($job->salary_range) && count($job->salary_range) > 0) {
                                                                        $salaryText = implode(' - ', $job->salary_range);
                                                                    } elseif (!empty($job->salary_range)) {
                                                                        $salaryText = (string) $job->salary_range;
                                                                    }
                                                                    ?>
                                                                    <li style="display:flex; align-items:center; gap:10px; padding: 10px 0; border-top: 1px solid #f0f0f0;">
                                                                        <div style="flex:1; min-width:0;">
                                                                            <i class="fa fa-heart-o" style="margin-right: 10px; color:#a3a3a3;"></i>
                                                                            <a href="{{ route('candidates.apply_job', ['job' => $job->id]) }}"
                                                                                style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; max-width:100%;">
                                                                                {{ $job->title }}
                                                                            </a>
                                                                        </div>
                                                                        <div style="width: 210px; text-align:right; font-size:12px; color:#6b7280;">
                                                                            <i class="fa fa-money"></i> {{ $salaryText }}
                                                                        </div>
                                                                        <div style="width: 70px; text-align:right; font-size:12px; color:#6b7280;">
                                                                            <i class="fa fa-clock-o"></i> {{ $job->deadline?->format('d/m') ?? '' }}
                                                                        </div>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    @empty
                                        <li>Không có chi nhánh nào.</li>
                                    @endforelse
                                    --}}

                                    <style>
                                        #pills-companies .branch-card{background:#fff;border:1px solid #eef0f3;border-radius:12px;padding:18px;margin:0 0 18px;box-shadow:0 1px 2px rgba(0,0,0,.03)}
                                        #pills-companies .branch-header{display:flex;gap:16px;align-items:flex-start}
                                        #pills-companies .branch-logo{flex:0 0 auto}
                                        #pills-companies .branch-logo img{width:86px;height:64px;object-fit:contain}
                                        #pills-companies .branch-main{flex:1;min-width:0}
                                        #pills-companies .branch-title{font-size:18px;font-weight:600;margin:0 0 6px}
                                        #pills-companies .branch-meta{display:flex;flex-wrap:wrap;gap:14px;font-size:13px;color:#6b7280;align-items:center}
                                        #pills-companies .branch-action{flex:0 0 auto;display:flex;align-items:center;gap:12px;white-space:nowrap}
                                        #pills-companies .branch-rating{background:#f5a623;color:#fff;font-weight:700;font-size:12px;border-radius:6px;padding:2px 6px;line-height:18px}
                                        #pills-companies .branch-jobs{margin-top:14px;border-top:1px solid #eef0f3;padding-top:12px}
                                        #pills-companies .branch-job-row{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid #edf0f3;border-radius:10px;padding:12px 14px;margin-top:10px}
                                        #pills-companies .branch-job-title{flex:1;min-width:0;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}
                                        #pills-companies .branch-job-title a{font-weight:500}
                                        #pills-companies .branch-job-salary{width:220px;text-align:right;font-size:12px;color:#6b7280;white-space:nowrap}
                                        #pills-companies .branch-job-deadline{width:70px;text-align:right;font-size:12px;color:#6b7280;white-space:nowrap}
                                    </style>

                                    @forelse($branches as $branch)
                                        @continue(((int) ($branch->published_jobs_count ?? 0)) < 1)
                                        <?php
                                        $cityLabel = \App\Enums\VietnamProvince::tryFrom($branch->city ?? '')?->label()
                                            ?? ($branch->city ?? null);

                                        $branchTitle = (string) ($branch->name ?? '');
                                        $titleLower = function_exists('mb_strtolower')
                                            ? mb_strtolower($branchTitle, 'UTF-8')
                                            : strtolower($branchTitle);
                                        $cityLower = $cityLabel
                                            ? (function_exists('mb_strtolower') ? mb_strtolower($cityLabel, 'UTF-8') : strtolower($cityLabel))
                                            : '';

                                        if ($cityLabel && $cityLower !== '' && !str_contains($titleLower, $cityLower)) {
                                            $branchTitle .= ' - ' . $cityLabel;
                                        }
                                        ?>
                                        <li>
                                            <div class="branch-card">
                                                <div class="branch-header">
                                                    <div class="branch-logo">
                                                        <a href="#">
                                                            <img src="{{ $branch->image ? asset('storage/' . ltrim($branch->image, '/')) : asset('assets/img/company-logo-1.png') }}"
                                                                alt="{{ $branchTitle }}">
                                                        </a>
                                                    </div>
                                                    <div class="branch-main">
                                                        <div class="branch-title"><a href="#">{{ $branchTitle }}</a></div>
                                                        <div class="branch-meta">
                                                            <span><i class="fa fa-map-marker"></i> {{ $cityLabel ?? 'Chưa cập nhật' }}</span>
                                                            <?php if (!empty($branch->address)): ?>
                                                                <span><i class="fa fa-location-arrow"></i> {{ $branch->address }}</span>
                                                            <?php endif; ?>
                                                            <span><i class="fa fa-briefcase"></i> {{ (int) ($branch->published_jobs_count ?? 0) }} vị trí đang tuyển</span>
                                                            <span><i class="fa fa-check"></i> {{ $branch->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="branch-action">
                                                        <span class="branch-rating">{{ number_format(rand(37, 50) / 10, 1) }}</span>
                                                        <a href="#" class="jobguru-btn">Xem hồ sơ</a>
                                                    </div>
                                                </div>

                                                <?php if (($branch->recruitmentJobs ?? collect())->isNotEmpty()): ?>
                                                    <div class="branch-jobs">
                                                        <?php foreach ((($branch->recruitmentJobs ?? collect())->values()) as $job): ?>
                                                            <?php
                                                            $salaryText = 'Thỏa thuận';
                                                            if (is_array($job->salary_range) && isset($job->salary_range['min'], $job->salary_range['max'])) {
                                                                $salaryText = number_format($job->salary_range['min']) . ' - ' . number_format($job->salary_range['max']) . ' VND';
                                                            } elseif (is_array($job->salary_range) && count($job->salary_range) > 0) {
                                                                $salaryText = implode(' - ', $job->salary_range);
                                                            } elseif (!empty($job->salary_range)) {
                                                                $salaryText = (string) $job->salary_range;
                                                            }
                                                            ?>
                                                            <div class="branch-job-row">
                                                                <i class="fa fa-heart-o" style="color:#a3a3a3;"></i>
                                                                <div class="branch-job-title">
                                                                    <a href="{{ route('candidates.apply_job', ['job' => $job->id]) }}">{{ $job->title }}</a>
                                                                </div>
                                                                <div class="branch-job-salary"><i class="fa fa-money"></i> {{ $salaryText }}</div>
                                                                <div class="branch-job-deadline"><i class="fa fa-clock-o"></i> {{ $job->deadline?->format('d/m') ?? '' }}</div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    @empty
                                        <li>Không có chi nhánh nào.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-job" role="tabpanel" aria-labelledby="pills-job-tab">
                            <div class="top-company-tab">
                                <ul>
                                    @forelse ($jobs as $job)
                                        <li>
                                            <div class="top-company-list">
                                                <div class="company-list-logo">
                                                    <a href="#">
                                                        <img src="{{ $job->branch?->image ? '/storage/' . ltrim($job->branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                                            alt="{{ $job->branch?->name ?? 'Chi nhánh' }}"
                                                            style="display:block; width:100px; height:80px; margin:0 auto; object-fit:contain;">
                                                    </a>
                                                </div>
                                                <div class="company-list-details">
                                                    <h3><a href="#">{{ $job->title }}</a></h3>
                                                    <p class="company-state"><i class="fa fa-map-marker"></i>
                                                        {{ \App\Enums\VietnamProvince::tryFrom($job->branch?->city ?? '')?->label() ?? ($job->branch?->city ?? 'Địa điểm chưa xác định') }}
                                                    </p>
                                                    <p class="open-icon"><i class="fa fa-clock-o"></i>
                                                        {{ $job->created_at?->diffForHumans() }}</p>
                                                    <p class="varify"><i class="fa fa-check"></i>Giá:
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
                                                    <p class="rating-company">{{ number_format(rand(37, 50) / 10, 1) }}</p>
                                                </div>
                                                <div class="company-list-btn">
                                                    <a href="{{ route('candidates.apply_job', ['job' => $job->id]) }}"
                                                        class="jobguru-btn">Xem ứng tuyển</a>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                    <li>Không có công việc nào</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="load-more">
                        <a href="{{ route('candidates.browse_job') }}" class="jobguru-btn">Xem thêm danh sách</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="jobguru-video-area section_100">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="video-container">
                        <h2>Thuê các chuyên gia tự do ngay hôm nay cho <br> bất kỳ công việc nào, vào bất kỳ lúc nào.
                        </h2>
                        <div class="video-btn">
                            <a class="popup-youtube" href="https://www.youtube.com/watch?v=k-R6AFn9-ek">
                                <i class="fa fa-play"></i>
                                Cách thức hoạt động
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="how-works-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="site-heading">
                        <h2>Cách thức <span>Hoạt động</span></h2>
                        <p>Thật dễ dàng. Chỉ cần đăng việc bạn cần hoàn thành và nhận báo giá cạnh tranh từ các
                            freelancer trong vài phút</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="how-works-box box-1">
                        <img src="{{ asset('assets/img/arrow-right-top.png') }}" alt="works" />
                        <div class="works-box-icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="works-box-text">
                            <p>Đăng ký</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="how-works-box box-2">
                        <img src="{{ asset('assets/img/arrow-right-bottom.png') }}" alt="works" />
                        <div class="works-box-icon">
                            <i class="fa fa-gavel"></i>
                        </div>
                        <div class="works-box-text">
                            <p>Đăng tin tuyển dụng</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="how-works-box box-3">
                        <div class="works-box-icon">
                            <i class="fa fa-thumbs-up"></i>
                        </div>
                        <div class="works-box-text">
                            <p>Chọn chuyên gia</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="jobguru-blog-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="site-heading">
                        <h2>Bài viết <span>Mới nhất</span></h2>
                        <p>Tìm hiểu các bí quyết và tin tức mới nhất để phát triển sự nghiệp của bạn một cách nhanh
                            chóng.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-12">
                    <a href="#">
                        <div class="single-blog">
                            <div class="blog-image">

                                <img src="{{ asset('assets/img/content01_2307-01.jpg') }}" alt="blog image" />
                                <p><span> 21</span> Tháng 7</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-12">
                    <a href="#">
                        <div class="single-blog">
                            <div class="blog-image">
                                <img src="{{ asset('assets/img/ipad_12_002.jpg') }}" alt="blog image" />
                                <p><span> 21</span> Tháng 7</p>
                            </div>
                            <div class="blog-text">
                                <h3>Chi tiết về các mẫu iPad Pro mới của Apple</h3>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-12">
                    <a href="#">
                        <div class="single-blog">
                            <div class="blog-image">
                                <img src="{{ asset('assets/img/aptech_aprotrain_5.jpg') }}" alt="blog image" />
                                <p><span> 21</span> Tháng 7</p>
                            </div>
                            <div class="blog-text">
                                <h3>Những bước để trở thành một lập trình viên thành công</h3>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="selectRoleModal" tabindex="-1" aria-labelledby="selectRoleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header pb-0">
                    <div>
                        <h5 class="modal-title" id="selectRoleModalLabel">Chào bạn!</h5>
                        <p style="margin: .4rem 0 0; color:#6b7280;">Chọn nhóm phù hợp để bắt đầu trải nghiệm.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="role-panel">
                        <div class="role-card">
                            <img class="role-card-img" src="{{ asset('assets/img/anh-tuyen-dung-6.webp') }}"
                                alt="Nhà tuyển dụng" />
                            <div class="role-card-body">
                                <h4 class="role-card-title">Tôi là nhà tuyển dụng</h4>
                                <p class="role-card-text">Đăng tin, quản lý ứng viên và mở rộng đội ngũ nhân sự của bạn
                                    nhanh chóng.</p>
                                <a href="{{ route('auth.sign_up', ['role' => 'employer']) }}"
                                    class="btn btn-success role-card-button">Chọn nhà tuyển dụng</a>
                            </div>
                        </div>
                        <div class="role-card">
                            <img class="role-card-img" src="{{ asset('assets/img/uv.webp') }}"
                                alt="Ứng viên tìm việc" width="800" height="600" loading="lazy"
                                decoding="async" />
                            <div class="role-card-body">
                                <h4 class="role-card-title">Tôi là ứng viên tìm việc</h4>
                                <p class="role-card-text">Tìm việc phù hợp, nộp hồ sơ và quản lý thông tin ứng tuyển
                                    của
                                    bạn.</p>
                                <a href="{{ route('auth.sign_up', ['role' => 'candidate']) }}"
                                    class="btn btn-outline-success role-card-button">Chọn ứng viên</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

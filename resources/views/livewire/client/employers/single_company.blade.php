@php
    $cityLabel = \App\Enums\VietnamProvince::tryFrom((string) $branch?->city)?->label() ?? $branch?->city;
    $logoUrl = $branch?->image ? asset('storage/' . ltrim($branch->image, '/')) : asset('assets/img/company-logo-1.png');
    $websiteUrl = $branch?->website && ! str_starts_with($branch->website, 'http')
        ? 'https://' . $branch->website
        : $branch?->website;
@endphp

<div class="company-public-profile">
    <section class="single-candidate-page section_70">
        <div class="container">
            <div class="company-public-hero">
                <div class="company-public-identity">
                    <div class="company-public-logo">
                        <img src="{{ $logoUrl }}" alt="{{ $branch->name }}">
                    </div>
                    <div>
                        <span>{{ $cityLabel ?: 'Nhà tuyển dụng' }}</span>
                        <h1>{{ $branch->name }}</h1>
                        <p>{{ $branch->address ?: 'Địa chỉ đang được cập nhật' }}</p>
                    </div>
                </div>

                <div class="company-public-stats">
                    <div>
                        <strong>{{ $jobs->count() }}</strong>
                        <span>Vị trí đang tuyển</span>
                    </div>
                    <div>
                        <strong>{{ $branch->employee_count ? number_format($branch->employee_count, 0, ',', '.') : '-' }}</strong>
                        <span>Nhân sự</span>
                    </div>
                    <div>
                        <strong>{{ $branch->is_active ? 'Active' : 'Paused' }}</strong>
                        <span>Trạng thái</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="single-candidate-bottom-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-lg-8 mx-auto">
                    <div class="single-candidate-bottom-left">
                        <div class="single-candidate-widget">
                            <h3>Mô tả chi nhánh</h3>
                            <p>{!! nl2br(e($branch->description ?: 'Chi nhánh đang cập nhật phần giới thiệu để ứng viên hiểu rõ hơn về môi trường làm việc, định hướng phát triển và nhu cầu tuyển dụng.')) !!}</p>
                        </div>

                        <div class="single-candidate-widget">
                            <h3>{{ $jobs->count() }} vị trí đang tuyển</h3>

                            @forelse($jobs as $job)
                                @php
                                    $salaryText = 'Thỏa thuận';
                                    if (is_array($job->salary_range) && isset($job->salary_range['min'], $job->salary_range['max'])) {
                                        $salaryText = number_format($job->salary_range['min'], 0, ',', '.') . ' - ' . number_format($job->salary_range['max'], 0, ',', '.') . ' VND';
                                    } elseif (is_array($job->salary_range) && count($job->salary_range) > 0) {
                                        $salaryText = implode(' - ', $job->salary_range);
                                    } elseif (! empty($job->salary_range)) {
                                        $salaryText = (string) $job->salary_range;
                                    }
                                @endphp
                                <div class="single-work-history company-single-page">
                                    <div class="single-candidate-list">
                                        <div class="main-comment">
                                            <div class="candidate-text">
                                                <div class="candidate-info">
                                                    <div class="candidate-title">
                                                        <h3>
                                                            <a href="{{ route('candidates.job_detail', ['id' => $job->id]) }}">
                                                                {{ $job->title }}
                                                            </a>
                                                        </h3>
                                                    </div>
                                                    <p class="company-state">
                                                        <i class="fa fa-map-marker"></i>
                                                        {{ $cityLabel ?: 'Chưa cập nhật' }}
                                                    </p>
                                                    <p class="open-icon">
                                                        <i class="fa fa-clock-o"></i>
                                                        {{ $job->workplace?->name ?: 'Hình thức làm việc linh hoạt' }}
                                                    </p>
                                                    <p class="varify">
                                                        <i class="fa fa-check"></i>
                                                        Mức lương: {{ $salaryText }}
                                                    </p>
                                                </div>
                                                <div class="candidate-text-inner">
                                                    <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) $job->description), 220) ?: 'Chi tiết công việc đang được cập nhật.' }}</p>
                                                    <a href="{{ route('candidates.job_detail', ['id' => $job->id]) }}" class="jobguru-btn-2">Xem tin tuyển dụng</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p>Hiện chưa có vị trí đang tuyển.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-10 col-lg-4 mx-auto">
                    <div class="single-candidate-bottom-right">
                        <div class="single-candidate-widget-2">
                            <ul>
                                <li><i class="fa fa-envelope"></i> {{ $branch->email_contact ?: 'Chưa cập nhật email' }}</li>
                                <li><i class="fa fa-phone"></i> {{ $branch->phone ?: 'Chưa cập nhật số điện thoại' }}</li>
                                <li>
                                    <i class="fa fa-globe"></i>
                                    @if($websiteUrl)
                                        <a href="{{ $websiteUrl }}" target="_blank" rel="noopener">{{ $branch->website }}</a>
                                    @else
                                        Chưa cập nhật website
                                    @endif
                                </li>
                            </ul>
                        </div>

                        <div class="single-candidate-widget-2">
                            <h3>Liên kết mạng xã hội</h3>
                            <ul class="candidate-social">
                                @if($branch->facebook_url)
                                    <li><a href="{{ $branch->facebook_url }}" target="_blank" rel="noopener"><i class="fa fa-facebook"></i></a></li>
                                @endif
                                @if($branch->twitter_url)
                                    <li><a href="{{ $branch->twitter_url }}" target="_blank" rel="noopener"><i class="fa fa-twitter"></i></a></li>
                                @endif
                                @if($branch->linkedin_url)
                                    <li><a href="{{ $branch->linkedin_url }}" target="_blank" rel="noopener"><i class="fa fa-linkedin"></i></a></li>
                                @endif
                                @unless($branch->facebook_url || $branch->twitter_url || $branch->linkedin_url)
                                    <li>Chưa cập nhật liên kết mạng xã hội</li>
                                @endunless
                            </ul>
                        </div>

                        <div class="single-candidate-widget-2">
                            <h3>Thông tin nhanh</h3>
                            <ul>
                                <li><i class="fa fa-map-marker"></i> {{ $cityLabel ?: 'Chưa cập nhật tỉnh/thành' }}</li>
                                <li><i class="fa fa-building"></i> {{ $branch->code ? 'Mã chi nhánh: ' . $branch->code : 'Chưa cập nhật mã chi nhánh' }}</li>
                                <li><i class="fa fa-users"></i> {{ $branch->employee_count ? number_format($branch->employee_count, 0, ',', '.') . ' nhân sự' : 'Chưa cập nhật quy mô' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

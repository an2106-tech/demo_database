<div class="bc2-page">
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

    <section class="bc2-section section_70">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3">
                    <aside class="bc2-sidebar">
                        <div class="bc2-filter-card">
                            <h3 class="bc2-filter-card__title">Ngày đăng</h3>
                            <div class="bc2-radio-list">
                                <label class="bc2-radio">
                                    <input id="last_hour" name="date_filter" type="radio" value="hour" wire:model.live="date_filter">
                                    <span>Giờ qua</span>
                                </label>
                                <label class="bc2-radio">
                                    <input id="last_24" name="date_filter" type="radio" value="24h" wire:model.live="date_filter">
                                    <span>24 giờ qua</span>
                                </label>
                                <label class="bc2-radio">
                                    <input id="last_7d" name="date_filter" type="radio" value="7d" wire:model.live="date_filter">
                                    <span>7 ngày qua</span>
                                </label>
                                <label class="bc2-radio">
                                    <input id="last_14d" name="date_filter" type="radio" value="14d" wire:model.live="date_filter">
                                    <span>14 ngày qua</span>
                                </label>
                                <label class="bc2-radio">
                                    <input id="last_30d" name="date_filter" type="radio" value="30d" wire:model.live="date_filter">
                                    <span>30 ngày qua</span>
                                </label>
                                <label class="bc2-radio">
                                    <input id="last_all" name="date_filter" type="radio" value="all" wire:model.live="date_filter">
                                    <span>Tất cả</span>
                                </label>
                            </div>
                        </div>

                        <div class="bc2-filter-card" wire:ignore>
                            <h3 class="bc2-filter-card__title">Mức lương tối thiểu</h3>
                            <div class="bc2-salary">
                                <input type="text" id="amount" readonly class="bc2-salary__value">
                                <div id="slider-single" class="bc2-salary__slider"></div>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="col-lg-9">
                    <div class="bc2-toolbar">
                        <div class="bc2-toolbar__left">
                            <form class="bc2-search" wire:submit.prevent="">
                                <i class="fa fa-search"></i>
                                <input
                                    type="search"
                                    wire:model.live.debounce.500ms="search"
                                    placeholder="Tìm kiếm công ty, địa điểm..."
                                    class="form-control"
                                >
                            </form>

                            <div class="dropdown bc2-city-dropdown custom-location-dropdown" id="cityDropdown">
                                <button
                                    class="bc2-pill dropdown-toggle"
                                    type="button"
                                    id="cityDropdownBtn"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside"
                                >
                                    <i class="fa fa-map-marker"></i>
                                    {{ count($applied_cities) > 0 ? 'Địa điểm (' . count($applied_cities) . ')' : 'Địa điểm' }}
                                </button>

                                <div class="dropdown-menu p-0 bc2-city-menu" wire:ignore.self>
                                    <div class="bc2-city-menu__head">
                                        <i class="fa fa-search"></i>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.300ms="search_city_keyword"
                                            class="form-control"
                                            placeholder="Nhập Tỉnh/Thành phố"
                                        >
                                    </div>

                                    <div class="bc2-city-menu__list">
                                        @forelse ($provincesList as $value => $label)
                                            <label class="bc2-city-item" for="loc_{{ $value }}">
                                                <span class="bc2-city-item__left">
                                                    <input
                                                        type="checkbox"
                                                        class="bc2-city-item__chk"
                                                        wire:model="selected_cities"
                                                        id="loc_{{ $value }}"
                                                        value="{{ $value }}"
                                                    >
                                                    <span class="bc2-city-item__label">{{ $label }}</span>
                                                </span>
                                            </label>
                                        @empty
                                            <div class="bc2-city-empty">Không tìm thấy địa điểm</div>
                                        @endforelse
                                    </div>

                                    <div class="bc2-city-menu__foot">
                                        <button type="button" wire:click="clearAllCities" class="bc2-link-btn">Bỏ chọn tất cả</button>
                                        <button type="button" wire:click="applyCityFilter" class="bc2-apply-btn" onclick="closeCityDropdown()">Áp dụng</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bc2-toolbar__right">
                            <div class="dropdown">
                                <button class="bc2-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Sắp xếp theo
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Mới nhất</a></li>
                                    <li><a class="dropdown-item" href="#">Cũ nhất</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bc2-list">
                        @forelse ($branches as $branch)
                            @continue(((int) ($branch->published_jobs_count ?? 0)) < 1)
                            @php
                                $branchName = trim((string) ($branch->name ?? ''));
                                $logoSrc = $branch->image ? '/storage/' . ltrim($branch->image, '/') : asset('assets/img/company-logo-1.png');
                                $cityLabel = \App\Enums\VietnamProvince::tryFrom((string) ($branch->city ?? ''))?->label() ?? ($branch->city ?? 'Chưa cập nhật');
                                $address = trim((string) ($branch->address ?? ''));
                            @endphp

                            <article class="bc2-company">
                                <div class="bc2-company__head">
                                    <div class="bc2-company__logo">
                                        <img src="{{ $logoSrc }}" alt="{{ $branchName !== '' ? $branchName : 'Chi nhánh' }}" loading="lazy" decoding="async">
                                    </div>

                                    <div class="bc2-company__meta">
                                        <h3 class="bc2-company__name">{{ $branchName !== '' ? $branchName : 'Doanh nghiệp' }}</h3>
                                        <div class="bc2-company__sub">
                                            <span class="bc2-company__sub-item"><i class="fa fa-map-marker"></i> {{ $cityLabel }}</span>
                                            @if ($address !== '')
                                                <span class="bc2-company__sub-sep">•</span>
                                                <span class="bc2-company__sub-item">{{ $address }}</span>
                                            @endif
                                        </div>
                                        <div class="bc2-company__badges">
                                            <span class="bc2-badge bc2-badge--accent">
                                                <i class="fa fa-briefcase"></i> {{ (int) ($branch->published_jobs_count ?? 0) }} vị trí
                                            </span>
                                            <span class="bc2-badge">
                                                <i class="fa fa-check"></i> {{ $branch->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="bc2-company__actions">
                                        <a class="bc2-primary-btn" href="{{ route('candidates.browse_job', ['city' => $branch->city]) }}">Xem việc</a>
                                    </div>
                                </div>

                                <div class="bc2-company__body">
                                    <div class="bc2-company__contact">
                                        <i class="fa fa-envelope"></i>
                                        <span>{{ $branch->email_contact ?? 'Không có thông tin email' }}</span>
                                    </div>

                                    @if ($branch->recruitmentJobs?->isNotEmpty())
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

                                            <div class="bc2-company__job">
                                                <a class="bc2-company__job-title" href="{{ route('candidates.job_detail', ['id' => $job->id]) }}">
                                                    {{ $job->title }}
                                                </a>
                                                <div class="bc2-company__job-meta">
                                                    <span><i class="fa fa-credit-card-alt"></i> {{ $salaryText }}</span>
                                                    @if (! empty($job->deadline))
                                                        <span><i class="fa fa-clock-o"></i> {{ $job->deadline?->format('d/m/Y') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="bc2-empty">
                                <img src="{{ asset('assets/img/no-results.png') }}" alt="No data" loading="lazy" decoding="async">
                                <p>Không có công ty hoặc địa điểm tương ứng với bộ lọc của bạn.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="bc2-pagination">
                        {{ $branches->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            function closeCityDropdown() {
                const dropdownBtn = document.getElementById('cityDropdownBtn');
                if (!dropdownBtn || typeof bootstrap === 'undefined') return;

                const bsDropdown = bootstrap.Dropdown.getInstance(dropdownBtn);
                if (bsDropdown) bsDropdown.hide();
            }

            window.addEventListener('click', function (e) {
                const dropdown = document.querySelector('.custom-location-dropdown');
                if (!dropdown) return;
                if (dropdown.contains(e.target)) return;

                const btn = document.getElementById('cityDropdownBtn');
                if (!btn || typeof bootstrap === 'undefined') return;

                const instance = bootstrap.Dropdown.getInstance(btn);
                if (instance) instance.hide();
            });

            function initSingleSlider() {
                if (typeof $ === 'undefined' || typeof $.fn.slider === 'undefined') return;

                const $slider = $("#slider-single");
                if (!$slider.length) return;

                $slider.slider({
                    range: "min",
                    min: 0,
                    max: 10000,
                    value: @json($salary_min ?? 0),
                    step: 100,
                    slide: function (event, ui) {
                        $("#amount").val(ui.value.toLocaleString('vi-VN') + " VND");
                    },
                    stop: function (event, ui) {
                        @this.set('salary_min', ui.value);
                    }
                });

                $("#amount").val($slider.slider("value").toLocaleString('vi-VN') + " VND");
            }

            document.addEventListener('livewire:navigated', initSingleSlider);
            document.addEventListener('DOMContentLoaded', initSingleSlider);
        </script>
    @endpush
</div>


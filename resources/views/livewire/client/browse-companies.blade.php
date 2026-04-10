<div>
    <style>
        /* CSS CHO BUTTON (Giống hình bạn gửi) */
        .btn-sort-custom {
            background-color: #fff;
            /* Màu border này sẽ giống nút Sắp Xếp Theo của bạn hơn */
            border: 1px solid #e0e0e0;
            color: #333;
            /* Điều chỉnh padding để kích thước nút cân đối */
            padding: 21px 20px;
            border-radius: 6px;
            font-size: 14px;
            display: inline-flex;
            /* Chuyển thành inline-flex để không bị nhảy hàng */
            align-items: center;
            transition: all 0.2s ease;
            height: 40px;
            /* Đảm bảo cao bằng ô Search bên trái */
        }

        .btn-sort-custom:hover {
            border-color: #bbb;
            background-color: #fcfcfc;
        }

        /* Mũi tên: Chỉnh nhỏ lại một chút cho tinh tế */
        .btn-sort-custom::after {
            display: inline-block;
            margin-left: 8px;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }

        /* Các phần CSS cho Dropdown Menu phía dưới giữ nguyên như cũ của bạn */
        .custom-check-input {
            width: 17px !important;
            height: 17px !important;
            appearance: auto !important;
            -webkit-appearance: checkbox !important;
        }

        .location-item:hover {
            background-color: #f8f9fa;
        }

        .location-item label {
            cursor: pointer;
            font-size: 14px;
        }

        .btn-clear-all {
            background: none;
            border: none;
            color: #888;
            font-size: 13px;
        }

        .btn-apply-filter {
            background-color: #F37021;
            color: white;
            border: none;
            padding: 6px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }
    </style>
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

    <section class="jobguru-top-job-area browse-page section_70">
        <div class="container">
            <div class="row">
                <!-- SIDEBAR LỌC (GIỮ NGUYÊN) -->
                <div class="col-md-10 col-lg-3 mx-auto">
                    <div class="job-grid-sidebar">
                        <div class="single-job-sidebar sidebar-location">
                            <h3>Ngày đăng</h3>
                            <div class="date-post-job job-sidebar-box">
                                <div class="form-group form-radio">
                                    <input id="last_hour" name="date_filter" type="radio" value="hour"
                                        wire:model.live="date_filter">
                                    <label for="last_hour" class="inline control-label">giờ qua</label>
                                </div>
                                <div class="form-group form-radio">
                                    <input id="last_24" name="date_filter" type="radio" value="24h"
                                        wire:model.live="date_filter">
                                    <label for="last_24" class="inline control-label">24 giờ qua</label>
                                </div>
                                <!-- ... làm tương tự cho 7, 14, 30 ngày ... -->
                                <div class="form-group form-radio">
                                    <input id="last_all" name="date_filter" type="radio" value="all"
                                        wire:model.live="date_filter">
                                    <label for="last_all" class="inline control-label">tất cả</label>
                                </div>
                            </div>
                        </div>
                        <!-- Thêm wire:ignore để Slider không bị mất khi Livewire render lại -->
                        <div class="single-job-sidebar sidebar-salary" wire:ignore>
                            <h3>Lọc theo mức lương tối thiểu</h3>
                            <div class="job-sidebar-box">
                                <p>
                                    <!-- Hiển thị giá trị lương đang chọn -->
                                    <input type="text" id="amount" readonly
                                        style="border:0; color:#f6931f; font-weight:bold;">
                                </p>
                                <div id="slider-single"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NỘI DUNG CHÍNH (KẾT HỢP TAB CHỮ CÁI VÀ DANH SÁCH) -->
                <div class="col-md-12 col-lg-9 mx-auto">
                    <div class="job-grid-right">
                        <!-- Thêm d-flex và align-items-center để căn giữa theo chiều dọc -->
                        <div class="browse-job-head-option d-flex align-items-center justify-content-between flex-wrap"
                            style="gap: 15px;">
                            <!-- Nhóm bên trái: Gồm Search và Địa điểm -->
                            <div class="d-flex align-items-center" style="gap: 15px; flex-grow: 1;">

                                <!-- 1. Ô tìm kiếm -->
                                <div class="job-browse-search mb-0">
                                    <form wire:submit.prevent="">
                                        <input type="search" wire:model.live.debounce.500ms="search"
                                            placeholder="Tìm kiếm công ty, địa điểm..." class="form-control">
                                        <button type="button"><i class="fa fa-search"></i></button>
                                    </form>
                                </div>

                                <!-- 2. Dropdown Địa điểm -->
                                <div class="dropdown custom-location-dropdown" id="cityDropdown">
                                    <button class="btn btn-sort-custom dropdown-toggle" type="button"
                                        id="cityDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                        <i class="fa fa-map-marker-alt me-1"></i>
                                        {{ count($applied_cities) > 0 ? 'Địa điểm (' . count($applied_cities) . ')' : 'Địa điểm' }}
                                    </button>

                                    <div class="dropdown-menu p-0 shadow-lg" wire:ignore.self
                                        style="width: 300px; border-radius: 15px; overflow: hidden;">
                                        <!-- Ô tìm kiếm trong Dropdown -->
                                        <div class="p-3 pb-2">
                                            <div class="d-flex align-items-center border-bottom pb-2">
                                                <i class="fa fa-search me-2" style="color: #F37021;"></i>
                                                <input type="text" wire:model.live.debounce.300ms="search_city_keyword"
                                                    class="form-control border-0 p-0 shadow-none"
                                                    placeholder="Nhập Tỉnh/Thành phố">
                                            </div>
                                        </div>

                                        <!-- Danh sách Tỉnh/Thành -->
                                        <div class="location-scroll-area" style="max-height: 250px; overflow-y: auto;">
                                            @forelse ($provincesList as $value => $label)
                                                <div
                                                    class="location-item d-flex align-items-center justify-content-between px-3 py-2">
                                                    <div class="d-flex align-items-center w-100">
                                                        <input type="checkbox" class="custom-check-input"
                                                            wire:model="selected_cities" id="loc_{{ $value }}"
                                                            value="{{ $value }}">
                                                        <label class="ms-3 mb-0 w-100 cursor-pointer"
                                                            for="loc_{{ $value }}">
                                                            {{ $label }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="p-3 text-center text-muted">Không tìm thấy địa điểm</div>
                                            @endforelse
                                        </div>

                                        <!-- Footer Dropdown -->
                                        <div
                                            class="d-flex align-items-center justify-content-between p-3 border-top bg-light">
                                            <button type="button" wire:click="clearAllCities"
                                                class="btn text-secondary btn-sm p-0 border-0">Bỏ chọn tất cả</button>
                                            <button type="button" wire:click="applyCityFilter"
                                                class="btn btn-primary btn-sm px-3" onclick="closeCityDropdown()"
                                                style="background-color: #F37021; border: none; border-radius: 20px;">
                                                Áp dụng
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Nhóm bên phải: Sắp xếp -->
                            <div class="job-browse-action">
                                <div class="dropdown">
                                    <button class="btn-dropdown dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        Sắp xếp theo
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>Mới nhất</li>
                                        <li>Cũ nhất</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Nội dung hiển thị dữ liệu theo Tab -->
                        <div class="tab-content" id="companyTabContent">
                            <div class="candidate-list-page">
                                @forelse ($branches as $branch)
                                    {{-- Chỉ hiện công ty có tin tuyển dụng --}}
                                    @continue(((int) ($branch->published_jobs_count ?? 0)) < 1)
                                    <div class="single-candidate-list mb-4">
                                        <div class="main-comment">
                                            <!-- Logo -->
                                            <div class="candidate-image">
                                                <img src="{{ $branch->image ? '/storage/' . ltrim($branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                                    alt="{{ $branch->name }}">
                                            </div>
                                            <!-- Thông tin chi tiết -->
                                            <div class="candidate-text">
                                                <div class="candidate-info">
                                                    <div class="candidate-title">
                                                        <h3><a href="#">{{ $branch->name }}</a></h3>
                                                    </div>
                                                    <p class="profession">
                                                        <i class="fa fa-briefcase"></i>
                                                        {{ $branch->published_jobs_count }} vị trí đang tuyển
                                                    </p>
                                                </div>
                                                <div class="candidate-text-inner">
                                                    <p>{{ $branch->email_contact ?? 'Không có thông tin email' }}</p>
                                                    @if ($branch->recruitmentJobs?->isNotEmpty())
                                                        <div style="margin-top: 12px;">
                                                            <ul class="list-unstyled" style="margin: 0;">
                                                                @foreach ($branch->recruitmentJobs->take(1) as $job)
                                                                    @php
                                                                        $salaryText = 'Thỏa thuận';
                                                                        if (
                                                                            is_array($job->salary_range) &&
                                                                            isset(
                                                                            $job->salary_range['min'],
                                                                            $job->salary_range['max'],
                                                                        )
                                                                        ) {
                                                                            $salaryText =
                                                                                number_format(
                                                                                    $job->salary_range['min'],
                                                                                ) .
                                                                                ' - ' .
                                                                                number_format(
                                                                                    $job->salary_range['max'],
                                                                                ) .
                                                                                ' VND';
                                                                        } elseif (
                                                                            is_array($job->salary_range) &&
                                                                            count($job->salary_range) > 0
                                                                        ) {
                                                                            $salaryText = implode(
                                                                                ' - ',
                                                                                $job->salary_range,
                                                                            );
                                                                        } elseif (!empty($job->salary_range)) {
                                                                            $salaryText = (string) $job->salary_range;
                                                                        }
                                                                    @endphp
                                                                    <li
                                                                        style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                                                                        <a href="{{ route('candidates.job_detail', ['id' => $job->id]) }}"
                                                                            style="flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                                            {{ $job->title }}
                                                                        </a>
                                                                        <span
                                                                            style="white-space:nowrap; font-size: 12px; color: #666;">
                                                                            {{ $salaryText }}
                                                                        </span>
                                                                        <span
                                                                            style="white-space:nowrap; font-size: 12px; color: #666;">
                                                                            {{ $job->deadline?->format('d/m') ?? '' }}
                                                                        </span>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="candidate-text-bottom">
                                                    <div class="candidate-text-box">
                                                        <p class="open-icon"><i class="fa fa-thumbs-up"></i>
                                                            100% thành công</p>
                                                        <p class="company-state">
                                                            <i class="fa fa-map-marker"></i>
                                                            {{ \App\Enums\VietnamProvince::tryFrom($branch->city)?->label() ?? $branch->city }}
                                                        </p>
                                                        <p class="varify">
                                                            <i class="fa fa-check"></i>
                                                            {{ $branch->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động' }}
                                                        </p>
                                                    </div>
                                                    <div class="candidate-action">
                                                        <a href="#" class="jobguru-btn-2">Xem hồ sơ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5">
                                        <img src="{{ asset('assets/img/no-results.png') }}" style="width: 150px"
                                            alt="No data">
                                        <p class="mt-3">Không có công ty hoặc địa điểm tương ứng với bộ lọc của bạn.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        <!-- 4. Phân trang -->
                        <div class="pagination-box-row mt-5">
                            <p>Trang 1 trên 6</p>
                            <ul class="pagination">
                                <!-- Code pagination của Laravel sẽ đặt ở đây -->
                                <li class="active"><a href="#">1</a></li>
                                <li><a href="#">2</a></li>
                                <li><a href="#"><i class="fa fa-angle-double-right"></i></a></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('livewire:navigated', () => {
                // Code khởi tạo cho Livewire v3
                initFilters();
            });

            function closeCityDropdown() {
                // Tìm cái nút dropdown qua ID
                const dropdownBtn = document.getElementById('cityDropdownBtn');

                // Sử dụng API của Bootstrap để đóng
                const bsDropdown = bootstrap.Dropdown.getInstance(dropdownBtn);
                if (bsDropdown) {
                    bsDropdown.hide();
                }
            }

            // Trường hợp bạn muốn chắc chắn hơn khi nhấn ra ngoài cũng đóng mượt
            window.addEventListener('click', function (e) {
                const dropdown = document.querySelector('.custom-location-dropdown');
                if (!dropdown.contains(e.target)) {
                    const btn = document.getElementById('cityDropdownBtn');
                    const instance = bootstrap.Dropdown.getInstance(btn);
                    if (instance) instance.hide();
                }
            });

            function initFilters() {
                // Slider Lương
                if ($("#slider").length) {
                    $("#slider").slider({
                        range: true, // Cho phép chọn khoảng giá trị
                        min: 0, // thấp nhất là 0 USD
                        max: 100000000, // cao nhất là 100 triệu
                        values: [@this.salary_range[0], @this.salary_range[1]], // Lấy giá trị từ Livewire component
                        // Cập nhật hiển thị giá trị khi kéo slider
                        slide: function (event, ui) {
                            // Hiển thị giá trị đã chọn với định dạng có dấu phẩy và đơn vị USD
                            $("#amount").val(ui.values[0].toLocaleString() + " - " + ui.values[1].toLocaleString() +
                                " USD");
                        },
                        // Gửi dữ liệu về Livewire khi người dùng thả chuột
                        stop: function (event, ui) {
                            // Gửi dữ liệu về Livewire
                            @this.set('salary_range', ui.values);
                        }
                    });
                }
            }

            // Slider Lương tối thiểu (Single)
            $(document).ready(function () {
                // Hàm khởi tạo Slider
                function initSingleSlider() {
                    // Kiểm tra nếu phần tử tồn tại
                    $("#slider-single").slider({
                        range: "min", // Tạo thanh màu từ mốc 0 đến nút kéo
                        min: 0, // 0 VND
                        max: 10000, // 10 nghìn
                        value: @json($salary_min ?? 0), // Lấy giá trị từ Livewire component
                        step: 100, // Mỗi bước nhảy 100
                        // Cập nhật hiển thị giá trị khi kéo slider
                        slide: function (event, ui) {
                            $("#amount").val(ui.value.toLocaleString('vi-VN') + " VND");
                        },
                        // Gửi dữ liệu về Livewire khi người dùng thả chuột
                        stop: function (event, ui) {
                            // Gửi giá trị về Livewire sau khi người dùng thả chuột
                            @this.set('salary_min', ui.value);
                        }
                    });

                    // Hiển thị giá trị mặc định ban đầu
                    $("#amount").val($("#slider-single").slider("value").toLocaleString('vi-VN') + " VND");
                }

                // Chạy lần đầu
                initSingleSlider();

                // Nếu bạn cần chạy lại sau khi chuyển trang (nếu dùng Livewire điều hướng)
                document.addEventListener('livewire:navigated', initSingleSlider);
            });

            // Chạy lần đầu khi load trang
            initFilters();
        </script>
    @endpush
</div>
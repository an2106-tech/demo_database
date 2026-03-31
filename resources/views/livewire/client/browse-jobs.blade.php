<div>
    <!-- Breadcrumb -->
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
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
                                    <label class="styled" for="b_1">Nhận thông báo qua email cho tìm kiếm
                                        này</label>
                                </div>
                                <div class="dropdown">
                                    <button class="btn-dropdown dropdown-toggle" type="button" id="dropdowncur"
                                        data-bs-toggle="dropdown" aria-haspopup="true">Sắp xếp theo</button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdowncur">
                                        <li>Mới nhất</li>
                                        <li>Cũ nhất</li>
                                        <li>Ngẫu nhiên</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="available-count">
                            <h4>Có 3087 việc làm trong danh mục <a href="#">Lập trình & Công nghệ</a></h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="sigle-top-job">
                            <div class="top-job-company-image">
                                <div class="company-logo-img">
                                    <a href="#">
                                        <img src="{{ $job->branch?->image ? '/storage/' . ltrim($job->branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                            alt="{{ $job->branch?->name ?? 'Chi nhánh' }}"
                                            style="display:block; width:100px; height:80px; margin:0 auto; object-fit:contain;">
                                    </a>
                                </div>
                                <h3><a href="#">Lập trình viên C# Senior (.Net)</a></h3>
                            </div>
                            <div class="top-job-company-desc">
                                <ul>

                                    <li>
                                        Địa điểm
                                        <span class="company-state">
                                            <i class="fa fa-map-marker"></i>
                                            {{ \App\Enums\VietnamProvince::tryFrom($job->branch?->city ?? '')?->label() ?? ($job->branch?->city ?? 'Chưa cập nhật') }}
                                        </span>
                                    </li>

                                    <li>
                                        Mức lương
                                        <span class="open-icon">
                                            <i class="fa fa-credit-card-alt"></i>

                                            @if ($job->salary_range)
                                                {{ number_format($job->salary_range['min']) }}
                                                -
                                                {{ number_format($job->salary_range['max']) }} VND
                                            @else
                                                Thỏa thuận
                                            @endif

                                        </span>
                                    </li>

                                    <li>
                                        Trạng thái
                                        <span class="varify">
                                            <i class="fa fa-check"></i>
                                            {{ $job->status }}
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

                </div>
            </div>

    </section>

    <!-- Job List -->
    <section class="jobguru-top-job-area browse-page section_70">
        <div class="container">

            <!-- Search -->
            <div class="row">
                <div class="col-md-12">
                    <div class="browse-job-head-option">

                        <div class="job-browse-search">
                            <form>
                                <input type="search" placeholder="Tìm kiếm việc làm tại đây...">
                                <button type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                            </form>
                        </div>

                        <div class="job-browse-action">
                            <div class="dropdown">
                                <button class="btn-dropdown dropdown-toggle" data-bs-toggle="dropdown">
                                    Sắp xếp theo
                                </button>
                                <ul class="dropdown-menu">
                                    <li>Mới nhất</li>
                                    <li>Cũ nhất</li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Count -->
                <div class="col-md-12">
                    <div class="available-count">
                        <h4>
                            Có {{ $jobs->count() }} việc làm
                        </h4>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

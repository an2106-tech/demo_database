<div>
    <style>
        .jobguru-banner-area {
            background: linear-gradient(180deg, rgba(12, 77, 133, .92), rgba(25, 115, 224, .90));
        }

        .jobguru-banner-area .banner-text h2 {
            color: #fff;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .jobguru-banner-area .banner-text h4 {
            color: rgba(255, 255, 255, .88);
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .jobguru-banner-area .jobguru-btn {
            min-width: 210px;
        }

        #selectRoleModal .modal-dialog {
            max-width: 1120px;
        }

        #selectRoleModal .modal-content {
            border-radius: 32px;
            overflow: hidden;
            border: none;
            background: transparent;
        }

        #selectRoleModal .modal-header {
            border-bottom: none;
            padding: 2rem 2rem 0;
        }

        #selectRoleModal .modal-body {
            padding: 0;
        }

        #selectRoleModal .role-panel {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(12px);
        }

        #selectRoleModal .role-card {
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, .8);
            background: #ffffff;
            box-shadow: 0 28px 70px rgba(15, 23, 42, .12);
            transition: transform .3s ease, box-shadow .3s ease;
        }

        #selectRoleModal .role-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 32px 90px rgba(15, 23, 42, .18);
        }

        #selectRoleModal .role-card-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        #selectRoleModal .role-card-body {
            padding: 1.8rem 1.6rem 2rem;
        }

        #selectRoleModal .role-card-title {
            margin-bottom: .85rem;
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
        }

        #selectRoleModal .role-card-text {
            margin-bottom: 1.4rem;
            color: #475569;
            line-height: 1.75;
        }

        #selectRoleModal .role-card-button {
            width: 100%;
            padding: 1rem 1.2rem;
            border-radius: 999px;
            font-weight: 700;
        }

        #selectRoleModal .modal-body::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, .18), rgba(16, 185, 129, .14));
            pointer-events: none;
        }

        #selectRoleModal .modal-content {
            position: relative;
        }

        @media (max-width: 991px) {
            #selectRoleModal .modal-dialog {
                max-width: 95%;
            }

            #selectRoleModal .role-panel {
                grid-template-columns: 1fr;
            }

            #selectRoleModal .role-card-img {
                height: 220px;
            }
        }

        @media (max-width: 575px) {
            .jobguru-banner-area .banner-text h2 {
                font-size: 2.2rem;
            }

            .jobguru-banner-area .banner-text h4 {
                font-size: 1.05rem;
            }
        }
    </style>
    <section class="jobguru-banner-area">
        <div class="banner-slider owl-carousel">
            <div class="banner-single-slider slider-item-1">
                <div class="slider-offset"></div>
            </div>
            <div class="banner-single-slider slider-item-2">
                <div class="slider-offset"></div>
            </div>
        </div>
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="banner-search" style="max-width:760px; margin:0 auto;">
                            <h2>Thuê các chuyên gia tự do hàng đầu.</h2>
                            <h4>Chúng tôi có 1542 cơ hội việc làm dành cho bạn!</h4>
                            <p
                                style="margin: 20px 0 28px; color: rgba(255,255,255,.85); font-size:1rem; line-height:1.6;">
                                Chọn loại tài khoản của bạn để bắt đầu. Ứng viên hay nhà tuyển dụng sẽ có giao diện khác
                                nhau.</p>
                            <div style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap;">
                                <button type="button" class="jobguru-btn" data-bs-toggle="modal"
                                    data-bs-target="#selectRoleModal">Đăng ký ngay</button>
                                <a href="{{ route('auth.login') }}" class="jobguru-btn-2">Đăng nhập</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Banner Area End -->


    <!-- Categories Area Start -->
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
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <a href="#" class="single-category-holder account_cat">
                        <div class="category-holder-icon">
                            <i class="bi bi-buildings"></i>
                        </div>
                        <div class="category-holder-text">
                            <h3>Accounting & Finance</h3>
                        </div>
                        <img src="{{ asset('assets/img/logo.png') }}" alt="category" />

                    </a>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="category-holder-icon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        <div class="category-holder-text">
                            <h3>Design, Art & Multimedia</h3>
                        </div>
                        <img src="{{ asset('assets/img/design_art.jpg') }}" alt="category" />
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <a href="#" class="single-category-holder restaurant_cat">
                            <div class="category-holder-icon">
                                <i class="fa fa-cutlery"></i>
                            </div>
                            <div class="category-holder-text">
                                <h3>Restaurant / Food Service</h3>
                            </div>
                            <img src="{{ asset('assets/img/restaurent.jpg') }}" alt="category" />
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <a href="#" class="single-category-holder tech_cat">
                            <div class="category-holder-icon">
                                <i class="fa fa-code"></i>
                            </div>
                            <div class="category-holder-text">
                                <h3>Programming & Tech</h3>
                            </div>
                            <img src="{{ asset('assets/img/programing_cat.jpeg') }}" alt="category" />
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <a href="#" class="single-category-holder data_cat">
                            <div class="category-holder-icon">
                                <i class="fa fa-bar-chart"></i>
                            </div>
                            <div class="category-holder-text">
                                <h3>Data Science & Analitycs</h3>
                            </div>
                            <img src="{{ asset('assets/img/data_cat.png') }}" alt="category" />
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <a href="#" class="single-category-holder writing_cat">
                            <div class="category-holder-icon">
                                <i class="bi bi-person-gear"></i>
                            </div>
                            <div class="category-holder-text">
                                <h3>Writing / Translations</h3>
                            </div>
                            <img src="{{ asset('assets/img/writing_cat.jpg') }}" alt="category" />
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <a href="#" class="single-category-holder edu_cat">
                            <div class="category-holder-icon">
                                <i class="fa fa-graduation-cap"></i>
                            </div>
                            <div class="category-holder-text">
                                <h3>Education / Training</h3>
                            </div>
                            <img src="{{ asset('assets/img/edu_cat.jpg') }}" alt="category" />
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <a href="#" class="single-category-holder sale_cat">
                            <div class="category-holder-icon">
                                <i class="fa fa-bullhorn"></i>
                            </div>
                            <div class="category-holder-text">
                                <h3>sales / marketing</h3>
                            </div>
                            <img src="{{ asset('assets/img/sale_cat.png') }}" alt="category" />
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="load-more">
                            <a href="#" class="jobguru-btn">Xem tất cả danh mục</a>
                            <a href="{{ route('candidates.browse_job') }}" class="jobguru-btn">Xem tất cả ngành nghề</a>
                        </div>
                        <div class="category-holder-text">
                            <h3>Tài chính & đầu tư</h3>
                        </div>
                        <img src="{{ asset('assets/img/img-tc.jpg') }}" alt="category" />
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <a href="#" class="single-category-holder data_cat">
                        <div class="category-holder-icon">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div class="category-holder-text">
                            <h3>Ngân hàng</h3>
                        </div>
                        <img src="{{ asset('assets/img/bank.jpg') }}" alt="category" />
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <a href="#" class="single-category-holder writing_cat">
                        <div class="category-holder-icon">
                            <i class="bi bi-person-gear"></i>
                        </div>
                        <div class="category-holder-text">
                            <h3>Quản lý điều hành</h3>
                        </div>
                        <img src="{{ asset('assets/img/qldh.webp') }}" alt="category" />
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <a href="#" class="single-category-holder edu_cat">
                        <div class="category-holder-icon">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
                        <div class="category-holder-text">
                            <h3>Giáo dục / Đào tạo</h3>
                        </div>
                        <img src="{{ asset('assets/img/gddt.jpg') }}" alt="category" />
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <a href="#" class="single-category-holder sale_cat">
                        <div class="category-holder-icon">
                            <i class="fa fa-bullhorn"></i>
                        </div>
                        <div class="category-holder-text">
                            <h3>Bán hàng / Marketing</h3>
                        </div>
                        <img src="{{ asset('assets/img/mkt.webp') }}" alt="category" />
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="load-more">
                        <a href="{{ route('candidates.browse_categories') }}" class="jobguru-btn">Xem tất cả ngành nghề</a>
                    </div>
                </div>
            </div>
    </section>
    <!-- Categories Area End -->


    <!-- Inner Hire Area Start -->
    <section class="jobguru-inner-hire-area section_100">
        <div class="hire_circle"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="inner-hire-left">
                        <h3>Tuyển dụng nhân sự</h3>
                        <p>Tìm kiếm ứng viên tài năng cho doanh nghiệp của bạn một cách nhanh chóng và hiệu quả. Chúng
                            tôi kết nối bạn với hàng ngàn chuyên gia sẵn sàng làm việc ngay lập tức. Hãy bắt đầu xây
                            dựng đội ngũ trong mơ của bạn ngay hôm nay.
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Inner Hire Area End -->


    <!-- Job Tab Area Start -->
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
                                    role="tab" aria-controls="pills-job" aria-selected="false">job openning</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-companies" role="tabpanel"
                            aria-labelledby="pills-companies-tab">
                            <div class="top-company-tab">
                                <ul>
                                    @forelse($branches as $branch)
                                        <li>
                                            <div class="top-company-list">
                                                <div class="company-list-logo">
                                                    <a href="#">
                                                        <img src="{{ asset('storage/' . ltrim($branch->image)) }}"
                                                            alt="{{ $branch->name }}" />
                                                    </a>
                                                </div>
                                                <div class="company-list-details">
                                                    <h3><a href="#">{{ $branch->name }}</a></h3>

                                                    @if ($branch->address)
                                                        <p class="company-state"><i class="fa fa-location-arrow"></i>
                                                            {{ $branch->address }}</p>
                                                    @endif
                                                    <p class="open-icon"><i
                                                            class="fa fa-briefcase"></i>{{ $branch->workplaces_count ?? $branch->workplaces()->count() }}
                                                        vị trí đang tuyển</p>
                                                    <p class="varify"><i
                                                            class="fa fa-check"></i>{{ $branch->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động' }}
                                                    </p>
                                                    <p class="rating-company">
                                                        {{ number_format(rand(37, 50) / 10, 1) }}</p>
                                                </div>
                                                <div class="company-list-btn">
                                                    <a href="#" class="jobguru-btn">Xem hồ sơ</a>
                                                </div>
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
                                    @forelse($jobs as $job)
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
                                                        {{ \App\Enums\VietnamProvince::tryFrom(optional($job->branch)->city ?? '')?->label() ?? (optional($job->branch)->city ?? 'Địa điểm chưa xác định') }}</p>
                                                    <p class="open-icon"><i class="fa fa-clock-o"></i>
                                                        {{ $job->created_at->diffForHumans() }}</p>
                                                    <p class="varify"><i class="fa fa-check"></i>Giá:
                                                        {{ is_array($job->salary_range) ? join(' - ', $job->salary_range) : $job->salary_range ?? 'Thỏa thuận' }}
                                                    </p>
                                                    <p class="rating-company">
                                                        {{ number_format(rand(37, 50) / 10, 1) }}</p>
                                                </div>
                                                <div class="company-list-btn">
                                                    <a href="#" class="jobguru-btn">Xem ứng tuyển</a>
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
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="load-more">
                        <a href="#" class="jobguru-btn">browse more listing</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Job Tab Area End -->


    <!-- Video Area Start -->
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
                                how it works
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Video Area End -->


    <!-- How Works Area Start -->
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
                            <p>sign up</p>
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
                            <p>post job</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="how-works-box box-3">
                        <div class="works-box-icon">
                            <i class="fa fa-thumbs-up"></i>
                        </div>
                        <div class="works-box-text">
                            <p>choose expert</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- How Works Area End -->


    <!-- Blog Area Start -->
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
                            <div class="blog-text">
                                <h3>If you're having trouble coming up with</h3>
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
                                <h3>details about Apple’s new iPad Pro models</h3>
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
                                <h3>what are those Steps to be a Successful developer</h3>
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

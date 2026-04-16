<div>
    <!-- Hero Section -->
    <section class="about-hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-hero-content" data-aos="fade-right">
                        <span class="about-kicker">Câu chuyện của chúng tôi</span>
                        <h1 class="about-headline">Kết nối <span class="text-primary">nhân tài</span> với cơ hội bứt phá.</h1>
                        <p class="about-lead">Chúng tôi không chỉ là một trang web tìm việc. Chúng tôi là cầu nối giúp ứng viên xây dựng lộ trình sự nghiệp và hỗ trợ doanh nghiệp tìm thấy những mảnh ghép văn hóa hoàn hảo nhất.</p>
                        <div class="about-hero-actions">
                            <a href="{{ route('candidates.browse_job') }}" class="jobguru-btn">Tìm việc ngay</a>
                            <a href="{{ route('pages.contact') }}" class="btn-link ms-4">Liên hệ hỗ trợ <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-hero-image" data-aos="zoom-in">
                        <img src="{{ asset('assets/img/about-hero.png') }}" alt="About Us Hero">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="about-stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">10k+</span>
                        <span class="stat-label">Ứng viên</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Doanh nghiệp</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">2.5k+</span>
                        <span class="stat-label">Việc làm mới</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Hài lòng</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="about-values-section">
        <div class="container">
            <div class="row mb-5 justify-content-center">
                <div class="col-lg-7 text-center">
                    <div class="site-heading mb-0">
                        <h2 class="fw-bold">Giá trị <span class="text-primary">cốt lõi</span></h2>
                        <p>Chúng tôi xây dựng nền tảng dựa trên những nguyên tắc bền vững để mang lại giá trị thực cho cộng đồng tuyển dụng.</p>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon"><i class="fa fa-rocket"></i></div>
                        <h3>Tốc độ & Hiệu quả</h3>
                        <p>Quy trình ứng tuyển và tuyển dụng được tối ưu hóa để tiết kiệm thời gian tối đa cho cả hai bên.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon"><i class="fa fa-shield"></i></div>
                        <h3>Minh bạch & Tin cậy</h3>
                        <p>Mọi thông tin việc làm và hồ sơ doanh nghiệp đều được kiểm duyệt kỹ lưỡng, đảm bảo tính xác thực.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon"><i class="fa fa-users"></i></div>
                        <h3>Cùng nhau phát triển</h3>
                        <p>Chúng tôi tin rằng thành công của ứng viên và doanh nghiệp chính là thành công lớn nhất của chúng tôi.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="about-cta-section">
        <div class="container">
            <div class="about-cta-box">
                <h2>Bắt đầu hành trình sự nghiệp mới?</h2>
                <p>Khám phá hàng ngàn cơ hội việc làm từ những công ty hàng đầu. Đừng bỏ lỡ tương lai của chính mình.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('candidates.browse_job') }}" class="jobguru-btn">Khám phá ngay</a>
                    <a href="{{ route('register') }}" class="jobguru-btn">Đăng ký thành viên</a>
                </div>
            </div>
        </div>
    </section>
</div>

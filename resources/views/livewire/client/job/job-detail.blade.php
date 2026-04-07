<div>
    <style>
        /* Container chính */
        .job-container {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        /* Card bao quanh */
        .job-card {
            background: #ffffff;
            max-width: 900px;
            width: 100%;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
        }

        /* Tiêu đề và Nút */
        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .job-title {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            color: #1a1a1a;
        }

        .btn-apply {
            background-color: #0ea5e9;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        /* Grid thông tin */
        .job-info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .label {
            font-size: 13px;
            color: #666;
            margin-bottom: 4px;
        }

        .value {
            font-weight: 500;
            color: #333;
        }

        .apply-link {
            color: #3182ce;
            text-decoration: none;
            font-size: 14px;
        }

        .divider {
            border: 0;
            border-top: 1px solid #eee;
            margin: 25px 0;
        }

        /* Nội dung văn bản */
        .job-description h3 {
            font-size: 18px;
            font-weight: 700;
        }

        .job-description h4 {
            font-size: 15px;
            font-weight: 700;
            margin-top: 20px;
            text-transform: uppercase;
        }

        .job-description p,
        .job-description ul {
            font-size: 15px;
            line-height: 1.6;
            color: #444;
        }

        .job-description ul {
            padding-left: 20px;
            list-style-type: "- ";
        }

        /* Phần đính kèm */
        .attachments-section {
            margin-top: 30px;
        }

        .attachments-section h5 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #222;
        }

        .attachment-list {
            display: flex;
            gap: 30px;
        }

        .attachment-item {
            width: 200px;
        }

        .attachment-item img {
            width: 100%;
            border-radius: 4px;
            height: 120px;
            object-fit: cover;
        }

        .file-placeholder {
            width: 150px;
            height: 120px;
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pdf-icon {
            color: #e53e3e;
            font-weight: bold;
            font-size: 24px;
            border: 2px solid #e53e3e;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .attachment-item p {
            font-size: 12px;
            text-align: center;
            margin-top: 8px;
            color: #555;
        }

        /* Responsive cơ bản */
        @media (max-width: 768px) {
            .job-info-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .job-header {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>

    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Chi tiết tin tuyển dụng</h3>
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
                                <li><a href="#">Nhà tuyển dụng</a></li>
                                <li class="active-breadcromb"><a href="#">Chi tiết tin tuyển dụng</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="job-container">
            <div class="job-card">
                <!-- Header: Tiêu đề và Nút -->
                <div class="job-header">
                    <h2 class="job-title">CHUYÊN VIÊN KINH DOANH CAO CẤP</h2>
                    <button class="btn-apply">ỨNG TUYỂN NGAY</button>
                </div>

                <!-- Grid thông tin chi tiết -->
                <div class="job-info-grid">
                    <div class="info-item">
                        <span class="label">Địa điểm:</span>
                        <span class="value">TP. Hồ Chí Minh</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Khu vực:</span>
                        <span class="value">Quận 1</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Loại hình công việc:</span>
                        <span class="value">Toàn thời gian</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Danh mục:</span>
                        <span class="value">Kinh doanh & Phát triển thị trường</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Liên kết ứng tuyển bên ngoài:</span>
                        <a href="#" class="apply-link">[Link nộp đơn trực tiếp]</a>
                    </div>
                    <div class="info-item">
                        <span class="label"> Mức lương tối thiểu ($):</span>
                        <span class="value">$1,000 / tháng</span>
                    </div>
                    <div class="info-item">
                        <span class="label"> Mức lương tối đa ($):</span>
                        <span class="value">$1,500 / tháng</span>
                    </div>
                </div>

                <hr class="divider">

                <!-- Nội dung mô tả -->
                <div class="job-description">
                    <h3>MÔ TẢ CÔNG VIỆC</h3>
                    <p>Công ty [Tên Công Ty] đang tìm kiếm một Chuyên viên Kinh doanh Cao cấp đầy nhiệt huyết và
                        tài năng để gia nhập đội ngũ tại TP. HCM. Bạn sẽ chịu trách nhiệm phát triển khách hàng
                        mới, duy trì mối quan hệ và đạt được mục tiêu doanh số.</p>

                    <h4>NHIỆM VỤ CHÍNH:</h4>
                    <ul>
                        <li>Tìm kiếm & phát triển khách hàng doanh nghiệp;</li>
                        <li>Đàm phán, chốt hợp đồng & chăm sóc khách hàng;</li>
                        <li>Theo dõi doanh số & báo cáo định kỳ.</li>
                    </ul>

                    <h4>YÊU CẦU:</h4>
                    <ul>
                        <li>Tốt nghiệp Đại học chuyên ngành Kinh doanh;</li>
                        <li>3+ năm kinh nghiệm bán hàng (B2B);</li>
                        <li>Kỹ năng giao tiếp & đàm phán xuất sắc;</li>
                        <li>Tiếng Anh tốt.</li>
                    </ul>
                </div>

                <!-- Tài liệu đính kèm -->
                <div class="attachments-section">
                    <h5>HÌNH ẢNH HOẶC TÀI LIỆU CÓ THỂ HỮU ÍCH TRONG VIỆC MÔ TẢ CÔNG VIỆC CỦA BẠN</h5>
                    <div class="attachment-list">
                        <div class="attachment-item">
                            <div class="img-placeholder">
                                <img src="{{ asset('assets/img/a_logo.png') }}" alt="Office">
                            </div>
                            <p>Văn phòng hiện đại & Đồng nghiệp thân thiện</p>
                        </div>
                        <div class="attachment-item">
                            <div class="file-placeholder">
                                <span class="pdf-icon">PDF</span>
                            </div>
                            <p>Chi tiết JD - Senior Sales Exec.pdf</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
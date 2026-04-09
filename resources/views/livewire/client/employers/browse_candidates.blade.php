<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Tìm kiếm ứng viên</h3>
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
                                <li><a href="{{ route('home') }}">home</a></li>
                                <li><a href="{{ route('employers.dashboard') }}">employers</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.browse') }}">Browse Candidates</a></li>
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
                <div class="col-md-10 col-lg-3 mx-auto">
                    <div class="job-grid-sidebar">
                        <div class="single-job-sidebar sidebar-location">
                            <h3>địa điểm</h3>
                            <div class="job-sidebar-box">
                                <form>
                                    <p>
                                        <input type="search" placeholder="Địa điểm">
                                    </p>
                                    <p class="location-value">
                                        <input type="text" value="50">
                                    </p>
                                    <div class="dropdown">
                                        <button class="btn-dropdown dropdown-toggle" type="button" id="location"
                                            data-bs-toggle="dropdown" aria-haspopup="true">km</button>
                                        <ul class="dropdown-menu" aria-labelledby="location">
                                            <li>km</li>
                                            <li>dặm</li>
                                        </ul>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="single-job-sidebar sidebar-keywords">
                            <h3>Từ khóa</h3>
                            <div class="job-sidebar-box">
                                <form>
                                    <select class="sidebar-category-select" name="states[]" multiple="multiple">
                                        <option value="1">kế toán</option>
                                        <option value="2">tài chính</option>
                                        <option value="3">ô tô</option>
                                        <option value="4">xây dựng</option>
                                        <option value="5">photoshop</option>
                                        <option value="6">đồ họa</option>
                                        <option value="7">After affects</option>
                                        <option value="8">thiết kế poster</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                        <div class="single-job-sidebar sidebar-category">
                            <h3>Danh mục</h3>
                            <div class="job-sidebar-box">
                                <form>
                                    <select class="sidebar-category-select-2" name="states[]">
                                        <option value="1">tất cả danh mục</option>
                                        <option value="2">kế toán/tài chính</option>
                                        <option value="3">việc làm ô tô</option>
                                        <option value="4">xây dựng</option>
                                        <option value="5">thiết kế, nghệ thuật & đa phương tiện</option>
                                        <option value="6">giáo dục đào tạo</option>
                                        <option value="7">nhà hàng/ẩm thực</option>
                                        <option value="7">lập trình/công nghệ</option>
                                        <option value="7">bán hàng/marketing</option>
                                        <option value="7">khoa học dữ liệu/phân tích</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                        <div class="single-job-sidebar sidebar-location">
                            <h3>Ngày đăng</h3>
                            <div class="date-post-job job-sidebar-box">
                                <div class="form-group form-radio">
                                    <input id="last_hour" name="radio" type="radio">
                                    <label for="last_hour" class="inline control-label">giờ qua</label>
                                </div>
                                <div class="form-group form-radio">
                                    <input id="last_24" name="radio" type="radio">
                                    <label for="last_24" class="inline control-label">24 giờ qua</label>
                                </div>
                                <div class="form-group form-radio">
                                    <input id="last_7" name="radio" type="radio">
                                    <label for="last_7" class="inline control-label">7 ngày qua</label>
                                </div>
                                <div class="form-group form-radio">
                                    <input id="last_14" name="radio" type="radio">
                                    <label for="last_14" class="inline control-label">14 ngày qua</label>
                                </div>
                                <div class="form-group form-radio">
                                    <input id="last_30" name="radio" type="radio">
                                    <label for="last_30" class="inline control-label">30 ngày qua</label>
                                </div>
                                <div class="form-group form-radio">
                                    <input id="last_all" name="radio" type="radio">
                                    <label for="last_all" class="inline control-label">tất cả</label>
                                </div>
                            </div>
                        </div>
                        <div class="single-job-sidebar sidebar-type">
                            <h3>loại hình công việc</h3>
                            <div class="job-sidebar-box">
                                <ul>
                                    <li class="checkbox">
                                        <input class="checkbox-spin" type="checkbox" id="Freelance" />
                                        <label for="Freelance"><span></span>Tự do (Freelance)</label>
                                    </li>
                                    <li class="checkbox">
                                        <input class="checkbox-spin" type="checkbox" id="Full_Time" />
                                        <label for="Full_Time"><span></span>Toàn thời gian</label>
                                    </li>
                                    <li class="checkbox">
                                        <input class="checkbox-spin" type="checkbox" id="Internship" />
                                        <label for="Internship"><span></span>Thực tập</label>
                                    </li>
                                    <li class="checkbox">
                                        <input class="checkbox-spin" type="checkbox" id="Part_Time" />
                                        <label for="Part_Time"><span></span>Bán thời gian</label>
                                    </li>
                                    <li class="checkbox">
                                        <input class="checkbox-spin" type="checkbox" id="Temporary" />
                                        <label for="Temporary"><span></span>Tạm thời</label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="single-job-sidebar sidebar-salary">
                            <h3>Lọc theo mức lương</h3>
                            <div class="job-sidebar-box">
                                <p>
                                    <input type="text" id="amount" readonly>
                                </p>
                                <div id="slider"></div>
                            </div>
                        </div>
                        </div>
                </div>
                <div class="col-md-12 col-lg-9  mx-auto">
                    <div class="job-grid-right">
                        <div class="browse-job-head-option">
                            <div class="job-browse-search">
                                <form>
                                    <input type="search" placeholder="Tìm kiếm công việc tại đây...">
                                    <button type="submit"><i class="fa fa-search"></i></button>
                                </form>
                            </div>
                            <div class="job-browse-action">
                                <div class="email-alerts">
                                    <input type="checkbox" class="styled" id="b_1">
                                    <label class="styled" for="b_1">nhận thông báo email cho tìm kiếm này</label>
                                </div>
                                <div class="dropdown">
                                    <button class="btn-dropdown dropdown-toggle" type="button" id="dropdowncur"
                                        data-bs-toggle="dropdown" aria-haspopup="true" style="text-transform:none;">Sắp xếp theo</button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdowncur">
                                        <li>Mới nhất</li>
                                        <li>Cũ nhất</li>
                                        <li>Ngẫu nhiên</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="candidate-list-page">
                            @forelse ($candidates as $candidate)
                            <div class="single-candidate-list">
                                <div class="main-comment">
                                    <div class="candidate-image">
                                        <img src="{{ asset('assets/img/avatar_detail.jpg') }}" alt="tác giả">
                                    </div>
                                    <div class="candidate-text">
                                        <div class="candidate-info">
                                            <div class="candidate-title">
                                                <h3><a href="#">{{ $candidate->name }}</a></h3>
                                                <img src="{{ asset('assets/img/de.svg') }}" alt="vùng" />
                                            </div>
                                            <p>Thiết kế UI/UX</p>
                                        </div>
                                        <div class="candidate-text-inner">
                                            <p>{{ $candidate->email }}
                                            </p>
                                        </div>
                                        <div class="candidate-text-bottom">
                                            <div class="candidate-text-box">
                                                <p class="open-icon"><i class="fa fa-thumbs-up"></i> 100% thành công
                                                </p>
                                                <p class="company-state"><i class="fa fa-map-marker"></i> Berlin</p>
                                                <p class="varify"><i class="fa fa-check"></i> $50 / giờ</p>
                                            </div>
                                            <div class="candidate-action">
                                                <a href="#" class="jobguru-btn-2">xem hồ sơ</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p>Không có ứng viên nào.</p>
                            @endforelse     
                            </div>
                        <div class="pagination-box-row">
                            <p>Trang 1 trên 6</p>
                            <ul class="pagination">
                                <li class="active"><a href="#">1</a></li>
                                <li><a href="#">2</a></li>
                                <li><a href="#">3</a></li>
                                <li>...</li>
                                <li><a href="#">6</a></li>
                                <li><a href="#"><i class="fa fa-angle-double-right"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

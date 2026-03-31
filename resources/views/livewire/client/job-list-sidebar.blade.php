<div>
      <section class="jobguru-breadcromb-area">
         <div class="breadcromb-top section_100">
            <div class="container">
               <div class="row">
                  <div class="col-md-12">
                     <div class="breadcromb-box">
                        <h3>Tìm việc làm</h3>
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
                           <li><a href="#">Trang chủ</a></li>
                           <li><a href="#">Ứng viên</a></li>
                           <li class="active-breadcromb"><a href="#">Tìm việc làm</a></li>
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
                        <h3>Địa điểm</h3>
                        <div class="job-sidebar-box">
                           <form>
                              <p>
                                 <input type="search" placeholder="Nhập địa điểm">
                              </p>
                              <p class="location-value">
                                 <input type="text" value="50">
                              </p>
                              <div class="dropdown">
                                 <button class="btn-dropdown dropdown-toggle" type="button" id="location" data-bs-toggle="dropdown" aria-haspopup="true">km</button>
                                 <ul class="dropdown-menu" aria-labelledby="location">
                                    <li>km</li>
                                    <li>dặm</li>
                                 </ul>
                              </div>
                           </form>
                        </div>

                        <div class="single-job-sidebar sidebar-category">
                            <h3>Danh mục</h3>
                            <div class="job-sidebar-box">
                                <form>
                                    <select class="sidebar-category-select-2" name="category_id">
                                        <option value="">Tất cả danh mục</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                     </div>
                     <div class="single-job-sidebar sidebar-category">
                        <h3>Danh mục</h3>
                        <div class="job-sidebar-box">
                           <form>
                              <select class="sidebar-category-select-2" name="states[]">
                                 <option value="1">Tất cả danh mục</option>
                                 <option value="2">Kế toán / Tài chính</option>
                                 <option value="3">Việc làm Ô tô</option>
                                 <option value="4">Xây dựng</option>
                                 <option value="5">Thiết kế, Nghệ thuật & Đa phương tiện</option>
                                 <option value="6">Giáo dục / Đào tạo</option>
                                 <option value="7">Nhà hàng / Ăn uống</option>
                                 <option value="7">Lập trình / Công nghệ</option>
                                 <option value="7">Bán hàng / Marketing</option>
                                 <option value="7">Khoa học dữ liệu / Phân tích</option>
                              </select>
                           </form>
                        </div>
                     </div>
                     <div class="single-job-sidebar sidebar-location">
                        <h3>Ngày đăng</h3>
                        <div class="date-post-job job-sidebar-box">
                           <div class="form-group form-radio">
                              <input id="last_hour" name="radio" type="radio">
                              <label for="last_hour" class="inline control-label">1 giờ qua</label>
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
                              <label for="last_all" class="inline control-label">Tất cả</label>
                           </div>
                        </div>
                     </div>
                     <div class="single-job-sidebar sidebar-type">
                        <h3>Loại hình công việc</h3>
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
                                 <label for="Temporary"><span></span>Thời vụ</label>
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
               <div class="col-md-10 col-lg-9 mx-auto">
                  <div class="job-grid-right">
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
                              <label class="styled" for="b_1">Nhận thông báo qua email</label>
                           </div>
                           <div class="dropdown">
                              <button class="btn-dropdown dropdown-toggle" type="button" id="dropdowncur" data-bs-toggle="dropdown" aria-haspopup="true">Sắp xếp theo</button>
                              <ul class="dropdown-menu" aria-labelledby="dropdowncur">
                                 <li>Mới nhất</li>
                                 <li>Cũ nhất</li>
                                 <li>Ngẫu nhiên</li>
                              </ul>
                           </div>
                        </div>
                     </div>
                     <div class="job-sidebar-list-single">
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-1.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">Quản lý bán hàng khu vực</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 phút trước</p>
                                 <p class="varify"><i class="fa fa-check"></i>Giá cố định: $1200-$2000</p>
                                 <p class="rating-company">4.1</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">Đấu thầu ngay</a>
                              </div>
                           </div>
                        </div>
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-4.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">Lập trình viên C (Senior) C .Net</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 phút trước</p>
                                 <p class="varify"><i class="fa fa-check"></i>Giá cố định: $800-$1200</p>
                                 <p class="rating-company">3.1</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">Đấu thầu ngay</a>
                              </div>
                           </div>
                        </div>
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-3.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">Trợ giảng</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 phút trước</p>
                                 <p class="varify"><i class="fa fa-check"></i>Giá cố định: $800-$1200</p>
                                 <p class="rating-company">4.2</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">Đấu thầu ngay</a>
                              </div>
                           </div>
                        </div>

                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-2.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">Kỹ sư xây dựng</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 phút trước</p>
                                 <p class="varify"><i class="fa fa-check"></i>Giá cố định: $800-$1200</p>
                                 <p class="rating-company">4.2</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">Đấu thầu ngay</a>
                              </div>
                           </div>
                        </div>

                     </div>
                     <div class="pagination-box-row">
                        <p>Trang 1 / 6</p>
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
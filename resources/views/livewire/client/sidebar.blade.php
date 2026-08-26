<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Tìm kiếm việc làm</h3>
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
                                <li class="active-breadcromb"><a href="#">Tìm kiếm việc làm</a></li>
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
               <div class="col-lg-3 col-md-11 mx-auto">
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
                              <select class="sidebar-category-select" name="skills[]" multiple="multiple">
                                 @foreach ($skills as $skill)
                                    <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                                 @endforeach
                              </select>
                           </form>
                        </div>
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
                      <div class="single-job-sidebar sidebar-location">
                         <h3>Ngày đăng</h3>
                         <div class="date-post-job job-sidebar-box">
                            <div class="form-group form-radio">
                              <input id="last_hour" name="posted_at" type="radio">
                              <label for="last_hour" class="inline control-label">1 giờ qua</label>
                           </div>
                           <div class="form-group form-radio">
                              <input id="last_24" name="posted_at" type="radio">
                              <label for="last_24" class="inline control-label">24 giờ qua</label>
                           </div>
                           <div class="form-group form-radio">
                              <input id="last_7" name="posted_at" type="radio">
                              <label for="last_7" class="inline control-label">7 ngày qua</label>
                           </div>
                           <div class="form-group form-radio">
                              <input id="last_14" name="posted_at" type="radio">
                              <label for="last_14" class="inline control-label">14 ngày qua</label>
                           </div>
                           <div class="form-group form-radio">
                              <input id="last_30" name="posted_at" type="radio">
                              <label for="last_30" class="inline control-label">30 ngày qua</label>
                           </div>
                           <div class="form-group form-radio">
                              <input id="last_all" name="posted_at" type="radio" checked>
                              <label for="last_all" class="inline control-label">Tất cả</label>
                           </div>
                        </div>
                     </div>
                     <div class="single-job-sidebar sidebar-type">
                        <h3>Loại công việc</h3>
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
               <div class="col-lg-9 col-md-11 mx-auto">
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
                              <label class="styled" for="b_1">Nhận thông báo qua email</label>
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

                     <div class="available-count">
                        <h4>Có {{ $jobs->count() }} việc làm</h4>
                     </div>

                     <div class="row">
                        @forelse ($jobs as $job)
                           <div class="col-lg-6 col-md-6">
                              <div class="sigle-top-job">
                                 <div class="top-job-company-image">
                                    <div class="company-logo-img">
                                       <a href="#">
                                          <img
                                             src="{{ $job->branch?->image ? '/storage/' . ltrim($job->branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                             alt="{{ $job->branch?->name ?? 'Chi nhánh' }}"
                                             style="display:block; width:100px; height:80px; margin:0 auto; object-fit:contain;">
                                       </a>
                                    </div>
                                    <h3><a href="#">{{ $job->title }}</a></h3>
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
                                              <i class="fa fa-tag"></i>
                                              {{ $job->formatted_salary }}
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
                                       <a href="{{ route('candidates.apply_job', ['job' => $job->id]) }}" class="jobguru-btn-2">Ứng tuyển ngay</a>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        @empty
                           <div class="col-md-12 text-center">
                              <h4>Không có việc làm nào</h4>
                           </div>
                        @endforelse
                     </div>
                  </div>
               </div>
            </div>
        </div>
    </section>
</div>

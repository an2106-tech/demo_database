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
                           <label class="styled" for="b_1">Nhận thông báo qua email cho tìm kiếm này</label>
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
                              <img
                                 src="{{ $job->branch?->image ? '/storage/' . ltrim($job->branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                 alt="{{ $job->branch?->name ?? 'Chi nhánh' }}"
                                 style="display:block; width:100px; height:80px; margin:0 auto; object-fit:contain;"
                              >
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

                                 @if($job->salary_range)
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
                           <a href="{{ route('candidates.apply_job', ['job' => $job->id]) }}" class="jobguru-btn-2">
                              Ứng tuyển ngay
                           </a>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="col-md-6 col-lg-4">
                  <div class="sigle-top-job">
                     <div class="top-job-company-image">
                        <div class="company-logo-img">
                           <a href="#">
                           <img src="{{ asset('assets/img/company-logo-2.png') }}" alt="hình ảnh công ty" />
                           </a>
                        </div>
                        <h3><a href="#">Kỹ sư xây dựng cấp bằng</a></h3>
                     </div>
                     <div class="top-job-company-desc">
                        <ul>
                           <li>Địa điểm <span class="company-state"><i class="fa fa-map-marker"></i> Brisbane</span></li>
                           <li>Mức lương <span class="open-icon"><i class="fa fa-credit-card-alt"></i>$600-$1200</span></li>
                           <li>Trạng thái <span class="varify"><i class="fa fa-check"></i>Bán thời gian</span></li>
                        </ul>
                        <div class="top-job-company-btn">
                           <a href="#" class="jobguru-btn-2">Ứng tuyển ngay</a>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="col-md-6 col-lg-4">
                  <div class="sigle-top-job">
                     <div class="top-job-company-image">
                        <div class="company-logo-img">
                           <a href="#">
                           <img src="{{ asset('assets/img/company-logo-3.png') }}" alt="hình ảnh công ty" />
                           </a>
                        </div>
                        <h3><a href="#">Trợ lý hành chính</a></h3>
                     </div>
                     <div class="top-job-company-desc">
                        <ul>
                           <li>Địa điểm <span class="company-state"><i class="fa fa-map-marker"></i> Brisbane</span></li>
                           <li>Mức lương <span class="open-icon"><i class="fa fa-credit-card-alt"></i>$600-$1200</span></li>
                           <li>Trạng thái <span class="varify"><i class="fa fa-check"></i>Bán thời gian</span></li>
                        </ul>
                        <div class="top-job-company-btn">
                           <a href="#" class="jobguru-btn-2">Ứng tuyển ngay</a>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="col-md-6 col-lg-4">
                  <div class="sigle-top-job">
                     <div class="top-job-company-image">
                        <div class="company-logo-img">
                           <a href="#">
                           <img src="{{ asset('assets/img/company-logo-1.png') }}" alt="hình ảnh công ty" />
                           </a>
                        </div>
                        <h3><a href="#">Quản lý bán hàng khu vực</a></h3>
                     </div>
                     <div class="top-job-company-desc">
                        <ul>
                           <li>Địa điểm <span class="company-state"><i class="fa fa-map-marker"></i> Brisbane</span></li>
                           <li>Mức lương <span class="open-icon"><i class="fa fa-credit-card-alt"></i>$600-$1200</span></li>
                           <li>Trạng thái <span class="varify"><i class="fa fa-check"></i>Bán thời gian</span></li>
                        </ul>
                        <div class="top-job-company-btn">
                           <a href="#" class="jobguru-btn-2">Ứng tuyển ngay</a>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="col-md-6 col-lg-4">
                  <div class="sigle-top-job">
                     <div class="top-job-company-image">
                        <div class="company-logo-img">
                           <a href="#">
                           <img src="{{ asset('assets/img/company-logo-3.png') }}" alt="hình ảnh công ty" />
                           </a>
                        </div>
                        <h3><a href="#">Tư vấn nguồn nhân lực (HR)</a></h3>
                     </div>
                     <div class="top-job-company-desc">
                        <ul>
                           <li>Địa điểm <span class="company-state"><i class="fa fa-map-marker"></i> Brisbane</span></li>
                           <li>Mức lương <span class="open-icon"><i class="fa fa-credit-card-alt"></i>$600-$1200</span></li>
                           <li>Trạng thái <span class="varify"><i class="fa fa-check"></i>Bán thời gian</span></li>
                        </ul>
                        <div class="top-job-company-btn">
                           <a href="#" class="jobguru-btn-2">Ứng tuyển ngay</a>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="col-md-6 col-lg-4">
                  <div class="sigle-top-job">
                     <div class="top-job-company-image">
                        <div class="company-logo-img">
                           <a href="#">
                           <img src="{{ asset('assets/img/company-logo-4.png') }}" alt="hình ảnh công ty" />
                           </a>
                        </div>
                        <h3><a href="#">Chuyên viên hỗ trợ sự kiện</a></h3>
                     </div>
                     <div class="top-job-company-desc">
                        <ul>
                           <li>Địa điểm <span class="company-state"><i class="fa fa-map-marker"></i> Brisbane</span></li>
                           <li>Mức lương <span class="open-icon"><i class="fa fa-credit-card-alt"></i>$600-$1200</span></li>
                           <li>Trạng thái <span class="varify"><i class="fa fa-check"></i>Bán thời gian</span></li>
                        </ul>
                        <div class="top-job-company-btn">
                           <a href="#" class="jobguru-btn-2">Ứng tuyển ngay</a>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="col-md-6 col-lg-4">
                  <div class="sigle-top-job">
                     <div class="top-job-company-image">
                        <div class="company-logo-img">
                           <a href="#">
                           <img src="{{ asset('assets/img/company-logo-4.png') }}" alt="hình ảnh công ty" />
                           </a>
                        </div>
                        <h3><a href="#">Lập trình viên C# Senior (.Net)</a></h3>
                     </div>
                     <div class="top-job-company-desc">
                        <ul>
                           <li>Địa điểm <span class="company-state"><i class="fa fa-map-marker"></i> Brisbane</span></li>
                           <li>Mức lương <span class="open-icon"><i class="fa fa-credit-card-alt"></i>$600-$1200</span></li>
                           <li>Trạng thái <span class="varify"><i class="fa fa-check"></i>Bán thời gian</span></li>
                        </ul>
                        <div class="top-job-company-btn">
                           <a href="#" class="jobguru-btn-2">Ứng tuyển ngay</a>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="col-md-6 col-lg-4">
                  <div class="sigle-top-job">
                     <div class="top-job-company-image">
                        <div class="company-logo-img">
                           <a href="#">
                           <img src="{{ asset('assets/img/company-logo-2.png') }}" alt="hình ảnh công ty" />
                           </a>
                        </div>
                        <h3><a href="#">Kỹ sư xây dựng cấp bằng</a></h3>
                     </div>
                     <div class="top-job-company-desc">
                        <ul>
                           <li>Địa điểm <span class="company-state"><i class="fa fa-map-marker"></i> Brisbane</span></li>
                           <li>Mức lương <span class="open-icon"><i class="fa fa-credit-card-alt"></i>$600-$1200</span></li>
                           <li>Trạng thái <span class="varify"><i class="fa fa-check"></i>Bán thời gian</span></li>
                        </ul>
                        <div class="top-job-company-btn">
                           <a href="#" class="jobguru-btn-2">Ứng tuyển ngay</a>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="col-md-6 col-lg-4">
                  <div class="sigle-top-job">
                     <div class="top-job-company-image">
                        <div class="company-logo-img">
                           <a href="#">
                           <img src="{{ asset('assets/img/company-logo-3.png') }}" alt="hình ảnh công ty" />
                           </a>
                        </div>
                        <h3><a href="#">Trợ lý hành chính</a></h3>
                     </div>
                     <div class="top-job-company-desc">
                        <ul>
                           <li>Địa điểm <span class="company-state"><i class="fa fa-map-marker"></i> Brisbane</span></li>
                           <li>Mức lương <span class="open-icon"><i class="fa fa-credit-card-alt"></i>$600-$1200</span></li>
                           <li>Trạng thái <span class="varify"><i class="fa fa-check"></i>Bán thời gian</span></li>
                        </ul>
                        <div class="top-job-company-btn">
                           <a href="#" class="jobguru-btn-2">Ứng tuyển ngay</a>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="row">
               <div class="col-md-12">
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

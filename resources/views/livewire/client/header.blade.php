<div>
   <header class="jobguru-header-area stick-top forsticky page-header">
      <div class="menu-animation">
         <div class="container-fluid">
            <div class="row">
               <div class="col-lg-2">
                  <div class="site-logo">
                     <a href="/">
                        <img src="{{ asset('assets/img/fe-logo.png') }}" alt="FPT Polytechnic" />
                     </a>
                  </div>
                  <div class="jobguru-responsive-menu"></div>
               </div>
               <div class="col-lg-6">
                  <div class="header-menu">
                     <nav id="navigation">
                        <ul id="jobguru_navigation">
                           <li class="active has-children">
                              <a href="/">Trang chủ</a>
                           </li>

                           <li class="has-children">
                              <a href="#">Cho Ứng Viên</a>
                              <ul>
                                 <li class="has-inner-child">
                                    <a href="#">Tìm việc làm</a>
                                    <ul>
                                       <li><a href="{{ route('candidates.browse_job') }}">Tất cả việc làm</a></li>
                                       <li><a href="{{ route('candidates.sidebar') }}">Dạng lưới (Sidebar)</a></li>
                                       <li><a href="{{ route('candidates.joblist_sidebar') }}">Dạng danh sách</a></li>
                                    </ul>
                                 </li>
                                 <li><a href="{{ route('candidates.browse_categories') }}">Danh mục ngành nghề</a></li>
                                 <li><a href="{{ route('candidates.browse_companies') }}">Danh sách chi nhánh</a></li>
                                 <li><a href="{{ route('candidates.candidate_detail') }}">Chi tiết ứng viên</a></li>
                                 <li><a href="{{ route('candidates.submit_resume') }}">Nộp hồ sơ (CV)</a></li>
                                 <li class="has-inner-child">
                                    <a href="#">Bảng điều khiển</a>
                                    <ul>
                                       <li><a href="{{ route('candidates.candidate_dashboard') }}">Tổng quan hồ sơ</a></li>
                                       <li><a href="{{ route('candidates.candidate_profile') }}">Thông tin cá nhân</a></li>
                                       <li><a href="{{ route('candidates.messages') }}">Tin nhắn</a></li>
                                       <li><a href="{{ route('candidates.manage_jobs') }}">Việc làm của tôi</a></li>
                                       <li><a href="{{ route('candidates.earnings') }}">Thu nhập</a></li>
                                       <li><a href="{{ route('candidates.change_password') }}">Đổi mật khẩu</a></li>
                                    </ul>
                                 </li>
                              </ul>
                           </li>
                           <li class="has-children">
                              <a href="#">Cho Nhà Tuyển Dụng</a>
                              <ul>
                                 <li><a href="{{ route('employers.browse') }}">Tìm ứng viên</a></li>
                                 <li><a href="{{ route('employers.single_company') }}">Thông tin công ty</a></li>
                                 <li><a href="{{ route('employers.post_job') }}">Đăng tin tuyển dụng</a></li>
                                 <li><a href="{{ route('employers.job_detail') }}">Chi tiết tin tuyển dụng</a></li>
                                 <li class="has-inner-child">
                                    <a href="#">Quản lý tuyển dụng</a>
                                    <ul>
                                       <li><a href="{{ route('employers.dashboard') }}">Bảng điều khiển</a></li>
                                       <li><a href="{{ route('employers.company_profile') }}">Hồ sơ công ty</a></li>
                                       <li><a href="{{ route('employers.message') }}">Tin nhắn</a></li>
                                       <li><a href="{{ route('employers.manage_candidates') }}">Quản lý ứng viên</a></li>
                                       <li><a href="{{ route('employers.transaction') }}">Giao dịch</a></li>
                                       <li><a href="{{ route('employers.change_password') }}">Đổi mật khẩu</a></li>
                                    </ul>
                                 </li>
                              </ul>
                           </li>
                           <li class="has-children">
                              <a href="#">Trang phụ</a>
                              <ul>
                                 <li><a href="{{ route('pages.about') }}">Về chúng tôi</a></li>
                                 <li class="has-inner-child">
                                    <a href="#">Tin tức/Blog</a>
                                    <ul>
                                       <li><a href="{{ route('pages.blog') }}">Danh sách bài viết</a></li>
                                       <li><a href="{{ route('pages.single') }}">Chi tiết bài viết</a></li>
                                    </ul>
                                 </li>
                                 <li><a href="{{ route('pages.job') }}">Trang việc làm</a></li>
                                 @guest
                                    <li><a href="{{ route('auth.login') }}">Đăng nhập</a></li>
                                    <li><a href="{{ route('auth.sign_up') }}">Đăng ký</a></li>
                                 @endguest
                                 <li><a href="{{ route('pages.contact') }}">Liên hệ</a></li>
                              </ul>
                           </li>
                        </ul>
                     </nav>
                  </div>
               </div>

               <div class="col-lg-4">
                  <div class="header-right-menu">
                     <ul>
                        @auth
                           @if($showRoleSwitcher ?? false)
                              <li>
                                 <div class="role-switcher" role="group" aria-label="Chuyển chế độ">
                                    <button type="button" wire:click="switchTo('candidate')"
                                       class="role-switcher__btn is-candidate {{ !$isEmployerHeader ? 'is-active' : '' }}">Ứng
                                       viên</button>
                                    <button type="button" wire:click="switchTo('employer')"
                                       class="role-switcher__btn is-employer {{ $isEmployerHeader ? 'is-active' : '' }}">Nhà
                                       tuyển dụng</button>
                                 </div>
                              </li>
                           @endif
                        @endauth
                        @if($isEmployerHeader)
                           <li><a href="{{route('auth.post_jobs')}}" class="post-jobs">Đăng tin ngay</a></li>
                        @endif
                        @guest
                           <li><a href="#" data-bs-toggle="modal" data-bs-target="#selectRoleModal"><i
                                    class="fa fa-user"></i> Đăng ký</a></li>
                           <li><a href="{{ route('auth.login') }}"><i class="fa fa-lock"></i> Đăng nhập</a></li>
                        @endguest
                        @auth
                           <li>
                              <livewire:client.logout-button />
                           </li>
                        @endauth
                     </ul>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </header>

   <div class="modal fade" id="selectRoleModal" tabindex="-1" aria-labelledby="selectRoleModalLabel" aria-hidden="true">
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
                        <p class="role-card-text">Đăng tin, quản lý ứng viên và mở rộng đội ngũ nhân sự của bạn nhanh
                           chóng.</p>
                        <a href="{{ route('auth.sign_up', ['role' => 'employer']) }}"
                           class="btn btn-success role-card-button">Chọn nhà tuyển dụng</a>
                     </div>
                  </div>
                  <div class="role-card">
                     <img class="role-card-img" src="{{ asset('assets/img/uv.webp') }}" alt="Ứng viên tìm việc"
                        width="800" height="600" loading="lazy" decoding="async" />
                     <div class="role-card-body">
                        <h4 class="role-card-title">Tôi là ứng viên tìm việc</h4>
                        <p class="role-card-text">Tìm việc phù hợp, nộp hồ sơ và quản lý thông tin ứng tuyển của bạn.
                        </p>
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

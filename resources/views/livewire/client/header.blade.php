<div>
   <style>
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

      .jobguru-header-area .header-right-menu ul {
         display: flex;
         justify-content: flex-end;
         align-items: center;
         gap: .75rem;
         flex-wrap: wrap;
      }

      .jobguru-header-area .header-right-menu ul>li {
         margin: 0;
      }

      .jobguru-header-area .role-switcher {
         display: inline-flex;
         align-items: center;
         gap: .25rem;
         padding: .25rem;
         border-radius: 999px;
         border: 1px solid rgba(15, 23, 42, .14);
         background: rgba(255, 255, 255, .76);
         backdrop-filter: blur(10px);
         box-shadow: 0 18px 45px rgba(15, 23, 42, .10);
      }

      .jobguru-header-area .role-switcher__btn {
         appearance: none;
         border: 0;
         background: transparent;
         color: #0f172a;
         padding: .65rem 1rem;
         border-radius: 999px;
         font-weight: 900;
         line-height: 1;
         transition: background-color .15s ease, color .15s ease, transform .15s ease;
      }

      .jobguru-header-area .role-switcher__btn:hover {
         transform: translateY(-1px);
      }

      .jobguru-header-area .role-switcher__btn:active {
         transform: translateY(0);
      }

      .jobguru-header-area .role-switcher__btn.is-active {
         color: #fff;
      }

      .jobguru-header-area .role-switcher__btn.is-active.is-candidate {
         background: #0ea5e9;
      }

      .jobguru-header-area .role-switcher__btn.is-active.is-employer {
         background: #16a34a;
      }
   </style>
   <header class="jobguru-header-area stick-top forsticky page-header">
      <div class="menu-animation">
         <div class="container-fluid">
            <div class="row">
               <div class="col-lg-2">
                  <div class="site-logo">
                     <a href="/">
                        <img src="{{ asset('assets/img/logo-2.png') }}" alt="jobguru" />
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

                           @if(!$isEmployerHeader)
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
                                          <li><a href="{{ route('candidates.candidate_dashboard') }}">Tổng quan hồ sơ</a>
                                          </li>
                                          <li><a href="{{ route('candidates.candidate_profile') }}">Thông tin cá nhân</a>
                                          </li>
                                          <li><a href="{{ route('candidates.messages') }}">Tin nhắn</a></li>
                                          <li><a href="{{ route('candidates.manage_jobs') }}">Việc làm của tôi</a></li>
                                          <li><a href="{{ route('candidates.earnings') }}">Thu nhập</a></li>
                                          <li><a href="{{ route('candidates.change_password') }}">Đổi mật khẩu</a></li>
                                       </ul>
                                    </li>
                                 </ul>
                              </li>
                           @else
                              <li class="has-children">
                                 <a href="#">Cho Nhà Tuyển Dụng</a>
                                 <ul>
                                    <li><a href="{{ route('employers.browse') }}">Tìm ứng viên</a></li>
                                    <li><a href="{{ route('employers.single_company') }}">Thông tin công ty</a></li>
                                    <li><a href="{{ route('employers.post_job') }}">Đăng tin tuyển dụng</a></li>
                                    <li><a href="{{ route('employers.job_detail') }}">Chi tiết tin tuyển dụng</a></li>
                                    <li><a href="{{ route('candidates.submit_resume') }}">Nộp hồ sơ (Ứng viên)</a></li>
                                    <li class="has-inner-child">
                                       <a href="#">Quản lý tuyển dụng</a>
                                       <ul>
                                          <li><a href="{{ route('employers.dashboard') }}">Bảng điều khiển</a></li>
                                          <li><a href="{{ route('employers.company_profile') }}">Hồ sơ công ty</a></li>
                                          <li><a href="{{ route('employers.message') }}">Tin nhắn</a></li>
                                          <li><a href="{{ route('employers.manage_candidates') }}">Quản lý ứng viên</a>
                                          </li>
                                          <li><a href="{{ route('employers.transaction') }}">Giao dịch</a></li>
                                          <li><a href="{{ route('employers.change_password') }}">Đổi mật khẩu</a></li>
                                       </ul>
                                    </li>
                                 </ul>
                              </li>
                           @endif

                           <li class="has-children">
                              <a href="#">Trang phụ</a>
                              <ul>
                                 <li><a href="{{ route('pages.about') }}">About us</a></li>
                                 <li class="has-inner-child">
                                    <a href="#">blog</a>
                                    <ul>
                                       <li><a href="{{ route('pages.blog') }}">blog</a></li>
                                       <li><a href="{{ route('pages.single') }}">single blog</a></li>
                                    </ul>
                                 </li>
                                 <li><a href="{{ route('pages.job') }}">job page</a></li>
                                 <li><a href="{{ route('pages.login') }}">login</a></li>
                                 <li><a href="{{ route('pages.register') }}">register</a></li>
                                 <li><a href="{{ route('pages.contact') }}">contact us</a></li>
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
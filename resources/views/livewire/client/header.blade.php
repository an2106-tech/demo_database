<div x-data="{ active: @entangle('type').live }">
   <style>
      /* Khối Dropdown */
      .userbox-dropdown {
         position: absolute;
         top: calc(100% + 12px);
         left: 15;
         /* Bám lề trái của icon Avatar */
         right: auto;
         width: 250px;
         /* Tăng nhẹ độ rộng để chữ không bị xuống dòng */
         background: #ffffff !important;
         border-radius: 12px;
         box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
         border: 1px solid rgba(0, 0, 0, 0.05);
         z-index: 9999;

         /* Hiệu ứng ẩn hiện */
         opacity: 0;
         visibility: hidden;
         transform: translateY(15px);
         transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
      }

      /* Hover hiện dropdown */
      .userbox-menu:hover .userbox-dropdown {
         opacity: 1;
         visibility: visible;
         transform: translateY(0);
      }

      /* Mũi tên nhọn - Căn lề phải cho khớp với icon */
      .userbox-dropdown::before {
         content: "";
         position: absolute;
         top: -8px;
         left: 15px;
         right: auto;
         /* Điều chỉnh số này để mũi tên nằm ngay giữa icon Avatar của bạn */
         border-left: 8px solid transparent;
         border-right: 8px solid transparent;
         border-bottom: 8px solid #ffffff;
      }

      /* Fix lỗi chữ bị dính vào nhau trong hình của bạn */
      .user-link {
         display: flex !important;
         align-items: center;
         text-align: left !important;
         gap: 12px;
         padding: 12px 20px !important;
         /* Tăng padding để menu thoáng hơn */
         color: #475569 !important;
         white-space: nowrap;
         /* Giữ chữ trên 1 dòng */
      }

      /* Container của danh sách */
      .dropdown-list-wrapper {
         list-style: none;
         padding: 12px 20px !important;
         margin: 0;
         display: flex;
         flex-direction: column;
         text-align: left !important;
         align-items: flex-start;
         /* Căn các phần tử con về bên trái */
      }

      .dropdown-list-wrapper li {
         width: 100%;
         display: flex !important;
         align-items: center;
         text-align: left !important;
         /* Đảm bảo mỗi mục chiếm toàn bộ chiều rộng để dễ click */
      }

      /* Căn trái cho phần Tên và Email */
      .userbox-account-info {
         padding: 12px 20px !important;
         display: flex;
         border-bottom: 1px solid #f1f5f9;
         flex-direction: column;
         text-align: left !important;
         align-items: flex-start;
         /* Căn các phần tử con về bên trái */
         /* Đảm bảo căn trái */
      }

      /* Các hàng trong menu */
      .user-link {
         display: flex !important;
         align-items: center;
         gap: 12px;
         /* Khoảng cách giữa Icon và Chữ */
         padding: 10px 20px !important;
         color: #475569 !important;
         text-decoration: none;
         transition: background 0.2s;
         font-size: 14px;
         white-space: nowrap;
         cursor: pointer;
      }

      .user-link:hover {
         background-color: #f8fafc;
         color: #F37021 !important;
         /* Đổi màu khi hover cho đẹp */
      }

      .user-link i,
      .user-link .icon {
         width: 20px;
         /* Cố định độ rộng icon để chữ luôn thẳng hàng dọc */
         text-align: left !important;
         align-items: flex-start;
         /* Căn icon về bên trái */
         font-size: 16px;
      }

      /* Nhãn "Giao diện" */
      .dropdown-label {
         padding: 12px 20px 5px;
         font-size: 11px;
         font-weight: 700;
         color: #94a3b8;
         text-transform: uppercase;
         letter-spacing: 0.5px;
      }

      /* Phần đăng xuất */
      .logout-section {
         border-top: 1px solid #f1f5f9;
         margin-top: 8px;
         padding: 8px 10px 4px;
      }

      /* Giữ nguyên khối userbox-dropdown của bạn nhưng điều chỉnh nhỏ */
      .userbox-dropdown {
         position: absolute;
         top: calc(100% + 12px);
         right: 0;
         width: 240px;
         background: #ffffff !important;
         border-radius: 12px;
         box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
         border: 1px solid rgba(0, 0, 0, 0.06);
         z-index: 9999;
         overflow: hidden;
         /* Đảm bảo các hàng hover không tràn khỏi bo góc */

         opacity: 0;
         visibility: hidden;
         transform: translateY(15px);
         transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
      }

      /* Đảm bảo các dòng menu luôn căn trái */
      .user-link {
         display: flex !important;
         align-items: center;
         justify-content: flex-start !important;
         /* Ép nội dung bám lề trái */
         gap: 12px;
         padding: 10px 20px !important;
         text-align: left !important;
      }

      /* Đảm bảo icon có độ rộng cố định để chữ thẳng hàng dọc */
      .user-link i,
      .user-link .icon {
         width: 20px;
         display: flex;
         justify-content: center;
         flex-shrink: 0;
         /* Không cho icon bị bóp méo */
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

      .jobguru-header-area .header-right-menu ul {
         display: flex;
         justify-content: flex-end;
         align-items: center;
         gap: .55rem;
         flex-wrap: nowrap;
      }

      .jobguru-header-area .header-right-menu ul>li {
         margin: 0;
         flex: 0 0 auto;
      }

      .jobguru-header-area .role-switcher {
         --switch-gap: .3rem;
         --switch-pad: .32rem;
         position: relative;
         isolation: isolate;
         display: inline-grid;
         grid-template-columns: repeat(2, minmax(0, 1fr));
         align-items: center;
         gap: var(--switch-gap);
         min-width: 228px;
         padding: var(--switch-pad);
         border-radius: 999px;
         border: 1px solid rgba(14, 116, 144, .16);
         background: linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(240, 249, 255, .92));
         backdrop-filter: blur(16px);
         box-shadow: 0 18px 45px rgba(14, 116, 144, .12);
         overflow: hidden;
      }

      .jobguru-header-area .role-switcher::before {
         content: "";
         position: absolute;
         top: var(--switch-pad);
         bottom: var(--switch-pad);
         left: var(--switch-pad);
         width: calc(50% - ((var(--switch-gap) / 2) + var(--switch-pad)));
         border-radius: 999px;
         background: linear-gradient(135deg, #f37021 0%, #ff8a1d 100%);
         box-shadow: 0 10px 24px rgba(243, 112, 33, .24);
         transition: transform .34s cubic-bezier(.22, 1, .36, 1), box-shadow .24s ease;
         z-index: 0;
      }

      .jobguru-header-area .role-switcher[data-active='employer']::before {
         transform: translateX(calc(100% + var(--switch-gap)));
      }

      .jobguru-header-area .role-switcher__btn {
         appearance: none;
         border: 0;
         background: transparent;
         position: relative;
         z-index: 1;
         display: inline-flex;
         align-items: center;
         justify-content: center;
         min-height: 44px;
         color: #28415b;
         padding: .75rem 1.05rem;
         border-radius: 999px;
         font-weight: 800;
         letter-spacing: .01em;
         line-height: 1;
         transition: color .22s ease, transform .18s ease;
      }

      .jobguru-header-area .role-switcher__btn:hover {
         transform: translateY(-1px);
      }

      .jobguru-header-area .role-switcher__btn:active {
         transform: translateY(0);
      }

      .jobguru-header-area .role-switcher__btn:focus-visible {
         outline: none;
      }

      .jobguru-header-area .role-switcher__btn i {
         font-size: .95rem;
         opacity: .76;
         transition: opacity .2s ease, transform .2s ease;
      }

      .jobguru-header-area .role-switcher__btn:hover i {
         transform: scale(1.06);
      }

      .jobguru-header-area .role-switcher__btn.is-active {
         color: #fff;
         text-shadow: 0 1px 1px rgba(15, 23, 42, .12);
      }

      .jobguru-header-area .role-switcher__btn.is-active i {
         opacity: 1;
      }

      .jobguru-header-area .role-switcher__btn[disabled] {
         cursor: wait;
         opacity: .9;
      }

      .jobguru-header-area .role-switcher__btn-label {
         white-space: nowrap;
      }

      .jobguru-header-area .header-right-menu .post-jobs {
         padding: 10px 22px !important;
         white-space: nowrap;
      }

      .jobguru-header-area .header-right-menu .client-logout-btn {
         width: auto;
         min-width: max-content;
         padding-inline: .95rem;
      }

      @media (max-width: 1399px) {
         .jobguru-header-area .role-switcher {
            min-width: 212px;
         }

         .jobguru-header-area .role-switcher__btn {
            padding-inline: .9rem;
         }

         .jobguru-header-area .header-right-menu .post-jobs {
            padding-inline: 18px !important;
         }
      }

      @media (max-width: 1199px) {
         .jobguru-header-area .header-right-menu ul {
            flex-wrap: wrap;
         }
      }

      @media (max-width: 767px) {
         .jobguru-header-area .header-right-menu ul {
            justify-content: stretch;
         }

         .jobguru-header-area .header-right-menu ul>li {
            width: 100%;
         }

         .jobguru-header-area .role-switcher {
            min-width: 100%;
         }

         .jobguru-header-area .header-right-menu .post-jobs,
         .jobguru-header-area .header-right-menu .client-logout-btn {
            width: 100%;
         }
      }
   </style>

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
                           <li class="active">
                              <a href="/">Trang chủ</a>
                           </li>

                           @if($showCandidateMenu ?? auth()->guest())
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
                                    <li><a href="{{ route('candidates.browse_companies') }}">Địa chỉ việc làm</a></li>
                                    <li><a href="{{ route('candidates.candidate_detail') }}">Chi tiết ứng viên</a></li>
                                    <li><a href="{{ route('candidates.submit_resume') }}">Nộp hồ sơ (CV)</a></li>
                                 </ul>
                              </li>
                           @endif
                           @if($showEmployerMenu ?? auth()->guest())
                              <li class="has-children">
                                 <a href="#">Cho Nhà Tuyển Dụng</a>
                                 <ul>
                                    <li><a href="{{ route('employers.browse') }}">Tìm ứng viên</a></li>
                                    <li><a href="{{ route('employers.single_company') }}">Thông tin công ty</a></li>
                                    <li><a href="{{ route('employers.post_job') }}">Đăng tin tuyển dụng</a></li>
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
                                 <li><a href="{{ route('pages.about') }}">Về chúng tôi</a></li>
                                 <li class="has-inner-child">
                                    <a href="#">Tin tức/Blog</a>
                                    <ul>
                                       <li><a href="{{ route('pages.blog') }}">Danh sách bài viết</a></li>
                                       <li><a href="{{ route('pages.single') }}">Chi tiết bài viết</a></li>
                                    </ul>
                                 </li>
                                 <li><a href="{{ route('pages.job') }}">Trang việc làm</a></li>
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


                           @auth
                              @if($showRoleSwitcher ?? false)
                                 <li>
                                    <div class="role-switcher" role="group" x-data="{ active: @entangle('type').live }" :data-active="active" aria-label="Chuyển chế độ">
                                       <button type="button" wire:click="switchTo('candidate')"
                                          wire:loading.attr="disabled" wire:target="switchTo" @click="active = 'candidate'" class="role-switcher__btn is-candidate" :class="{ 'is-active': active === 'candidate' }">
                                          Ứng viên
                                       </button>
                                       <button type="button" wire:click="switchTo('employer')"
                                          wire:loading.attr="disabled" wire:target="switchTo" @click="active = 'employer'" class="role-switcher__btn is-employer" :class="{ 'is-active': active === 'employer' }">
                                          Nhà tuyển dụng
                                       </button>
                                    </div>
                                 </li>
                              @endif
                           @endauth
                           @if($isEmployerHeader)
                              <li>
                                 <a href="{{route('auth.post_jobs')}}" class="post-jobs">Đăng tin ngay</a>
                              </li>
                           @endif
                           @guest
                              <li>
                                 <a href="#" data-bs-toggle="modal" data-bs-target="#selectRoleModal">
                                    <i class="fa fa-user"></i>
                                    Đăng ký
                                 </a>
                              </li>
                              <li>
                                 <a href="{{ route('auth.login') }}">
                                    <i class="fa fa-lock"></i>
                                    Đăng nhập
                                 </a>
                              </li>
                           @endguest






                           @auth
                              <!-- Chỉ can thiệp vào khối User Menu này -->
                              <li class="userbox-menu"
                                 style="position: relative; list-style: none; display: inline-block; margin-left: 10px; vertical-align: middle;">

                                 <!-- Icon đại diện (Avatar hoặc Chữ) -->
                                 <div class="userbox-trigger" style="display: flex; align-items: center; cursor: pointer;">
                                    @if(Auth::user()->avatar)
                                       <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar"
                                          style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                    @else
                                       <div
                                          style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #ff7e3e 0%, #ff5722 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                          {{ substr(Auth::user()->name, 0, 1) }}
                                       </div>
                                    @endif
                                 </div>

                                 <!-- Khối Dropdown Menu -->
                                 <div class="userbox-dropdown">
                                    <div class="userbox-account-info"
                                       style="padding: 12px 20px; border-bottom: 1px solid #f1f5f9;">
                                       <span
                                          style="display: block; font-weight: 700; color: #1e293b; font-size: 14px; line-height: 1.2;">{{ Auth::user()->name }}</span>
                                       <span
                                          style="display: block; color: #64748b; font-size: 11px; margin-top: 2px;">{{ Auth::user()->email }}</span>
                                    </div>

                                    <ul class="dropdown-list-wrapper">
                                       <li>
                                          <a href="{{ route('candidates.candidate_dashboard') }}" class="user-link">
                                             <span>Tổng quan hồ sơ</span>
                                          </a>
                                       </li>
                                       <li>
                                          <a href="{{ route('candidates.candidate_profile') }}" class="user-link">
                                             <span>Thông tin cá nhân</span>
                                          </a>
                                       </li>
                                       <li>
                                          <a href="{{ route('candidates.manage_jobs') }}" class="user-link">
                                             <span>Việc làm của tôi</span>
                                          </a>
                                       </li>
                                       <li>
                                          <a href="{{ route('candidates.messages') }}" class="user-link">
                                             <span>Tin nhắn</span>
                                          </a>
                                       </li>
                                       <li>
                                          <a href="{{ route('candidates.earnings') }}" class="user-link">
                                             <span>Thu nhập</span>
                                          </a>
                                       </li>
                                       <li>
                                          <a href="{{ route('candidates.change_password') }}" class="user-link">
                                             <span>Đổi mật khẩu</span>
                                          </a>
                                       </li>

                                       <!-- Logout Section -->
                                       <div>
                                          <li class="logout-section">
                                             <div class="logout-wrapper">
                                                <livewire:client.logout-button />
                                             </div>
                                          </li>
                                       </div>

                                    </ul>
                                 </div>
                              </li>

                           @endauth
                        @endauth
                        @guest
                           <li><a href="#" data-bs-toggle="modal" data-bs-target="#selectRoleModal"><i
                                    class="fa fa-user"></i> Đăng ký</a></li>
                           <li><a href="{{ route('auth.login') }}"><i class="fa fa-lock"></i> Đăng nhập</a></li>
                        @endguest
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


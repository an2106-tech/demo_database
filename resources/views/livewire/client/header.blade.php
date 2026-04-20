<div x-data="{ active: @entangle('type').live, openUserMenu: false }">
   <style>
      /* Khối Dropdown */
      .userbox-dropdown {
         position: absolute;
         top: calc(100% + 12px);
         left: 15;
         /* Bám lề trái của icon Avatar */
         right: auto;
         width: 250px;
         /* TÄƒng nháº¹ Ä‘á»™ rá»™ng Ä‘á»ƒ chá»¯ khÃ´ng bá»‹ xuá»‘ng dòng */
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

      /* Mở dropdown khi click icon user */
      .userbox-menu.is-open .userbox-dropdown {
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
         /* Äiá»u chá»‰nh sá»‘ nÃ y Ä‘á»ƒ mũi tên nằm ngay giữa icon Avatar của bạn */
         border-left: 8px solid transparent;
         border-right: 8px solid transparent;
         border-bottom: 8px solid #ffffff;
      }

      /* Fix lá»—i chá»¯ bá»‹ dính vào nhau trong hình của bạn */
      .user-link {
         display: flex !important;
         align-items: center;
         text-align: left !important;
         gap: 12px;
         padding: 12px 20px !important;
         /* TÄƒng padding Ä‘á»ƒ menu thoáng hơn */
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
         /* CÄƒn các phần tử con về bên trái */
      }

      .dropdown-list-wrapper li {
         width: 100%;
         display: flex !important;
         align-items: center;
         text-align: left !important;
         /* Đảm bảo mỗi mục chiếm toàn bộ chiều rộng để dễ click */
      }

      /* CÄƒn trái cho phần Tên và Email */
      .userbox-account-info {
         padding: 12px 20px !important;
         display: flex;
         border-bottom: 1px solid #f1f5f9;
         flex-direction: column;
         text-align: left !important;
         align-items: flex-start;
         /* CÄƒn các phần tử con về bên trái */
         /* Äáº£m báº£o cÄƒn trái */
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
         color: var(--fpt-orange) !important;
         /* Đổi màu khi hover cho đẹp */
      }

      .user-link i,
      .user-link .icon {
         width: 20px;
         /* Cá»‘ Ä‘á»‹nh Ä‘á»™ rá»™ng icon Ä‘á»ƒ chữ luôn thẳng hàng dọc */
         text-align: left !important;
         align-items: flex-start;
         /* CÄƒn icon về bên trái */
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

      /* Giữ nguyên khối userbox-dropdown của bạn nhưng điều chỉnh nh? */
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

      /* Äáº£m báº£o cÃ¡c dÃ²ng menu luÃ´n cÄƒn trái */
      .user-link {
         display: flex !important;
         align-items: center;
         justify-content: flex-start !important;
         /* Ã‰p ná»™i dung bám lề trái */
         gap: 12px;
         padding: 10px 20px !important;
         text-align: left !important;
      }

      /* Äáº£m báº£o icon cÃ³ Ä‘á»™ rá»™ng cá»‘ Ä‘á»‹nh Ä‘á»ƒ chữ thẳng hàng dọc */
      .user-link i,
      .user-link .icon {
         width: 20px;
         display: flex;
         justify-content: center;
         flex-shrink: 0;
         /* KhÃ´ng cho icon bá»‹ bóp méo */
      }

      .jobguru-header-area .header-right-menu ul {
         display: flex;
         justify-content: flex-end;
         align-items: center;
         gap: .55rem;
         flex-wrap: nowrap !important;
         white-space: nowrap;
      }

      .jobguru-header-area .header-right-menu ul>li {
         margin: 0;
         flex: 0 0 auto;
         width: auto !important;
      }

      .jobguru-header-area .row {
         display: flex;
         align-items: center;
         flex-wrap: nowrap;
      }

      .jobguru-header-area .col-lg-2 {
         flex: 0 0 auto;
         width: auto;
         max-width: none;
      }

      .jobguru-header-area .col-lg-7 {
         flex: 1 1 auto;
         min-width: 0;
      }

      .jobguru-header-area .col-lg-3 {
         flex: 0 0 auto;
         width: auto;
         max-width: none;
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
         min-width: 196px;
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
         background: linear-gradient(135deg, var(--fpt-orange) 0%, #ff8a1d 100%);
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
         min-height: 38px;
         color: #28415b;
         padding: .58rem .72rem;
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

      .jobguru-header-area .role-switcher-cta {
         all: unset;
         box-sizing: border-box;
         display: inline-flex;
         align-items: center;
         justify-content: center;
         min-height: 44px;
         padding: 12px 24px;
         border-radius: 999px;
         background: #ff8a1d;
         border: none;
         color: #fff !important;
         -webkit-text-fill-color: #fff;
         font-family: 'Inter', sans-serif;
         font-size: 15px;
         font-weight: 700;
         line-height: 1;
         letter-spacing: 0;
         white-space: nowrap;
         text-decoration: none !important;
         cursor: pointer;
         opacity: 1 !important;
         filter: none !important;
         box-shadow: none !important;
         transform: none !important;
         transition: none !important;
      }

      .jobguru-header-area .role-switcher-cta:hover,
      .jobguru-header-area .role-switcher-cta:focus,
      .jobguru-header-area .role-switcher-cta:active,
      .jobguru-header-area .role-switcher-cta:visited {
         background: #ff8a1d;
         color: #fff !important;
         -webkit-text-fill-color: #fff;
         color: #fff !important;
         text-decoration: none !important;
         opacity: 1 !important;
         filter: none !important;
         box-shadow: none !important;
         transform: none !important;
      }

      .jobguru-header-area .header-right-menu .post-jobs {
         padding: 10px 22px !important;
         white-space: nowrap;
      }

      .jobguru-header-area .header-right-menu .client-logout-btn {
         width: auto !important;
         min-width: 0 !important;
         padding: .45rem .6rem !important;
      }

      .jobguru-header-area .header-right-menu .client-logout-btn span {
         white-space: nowrap;
      }

      .jobguru-header-area .header-right-menu {
         overflow-x: auto;
         -webkit-overflow-scrolling: touch;
      }

      .userbox-trigger {
         appearance: none;
         border: 0;
         background: transparent;
         padding: 0;
         display: inline-flex;
         align-items: center;
         cursor: pointer;
      }

      @media (max-width: 1399px) {
         .jobguru-header-area .role-switcher {
            min-width: 178px;
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
            flex-wrap: nowrap;
            gap: .4rem;
         }

         .jobguru-header-area .role-switcher {
            min-width: 190px;
         }

         .jobguru-header-area .role-switcher__btn {
            min-height: 40px;
            padding: .62rem .75rem;
            font-size: .92rem;
         }

         .jobguru-header-area .header-right-menu .client-logout-btn {
            padding: .62rem .72rem;
         }
      }

      @media (max-width: 767px) {
         .jobguru-header-area .header-right-menu ul {
            justify-content: flex-end;
            flex-wrap: nowrap;
            gap: .35rem;
            overflow-x: auto;
            padding-bottom: 2px;
         }

         .jobguru-header-area .header-right-menu ul>li {
            width: auto;
            flex: 0 0 auto;
         }

         .jobguru-header-area .role-switcher {
            min-width: 175px;
         }

         .jobguru-header-area .role-switcher__btn {
            min-height: 38px;
            padding: .52rem .62rem;
            font-size: .88rem;
         }

         .jobguru-header-area .header-right-menu .client-logout-btn {
            width: auto;
            padding: .5rem .65rem;
            min-width: 132px;
         }
      }

      /* NgÄƒn cháº·n menu chÃ­nh bá»‹ xuá»‘ng dòng */
      #jobguru_navigation li a {
         white-space: nowrap !important;
         padding-left: 8px !important;
         padding-right: 8px !important;
      }

      .site-logo {
         padding-right: 15px !important;
         margin-right: 10px !important;
      }
   </style>

   <header class="jobguru-header-area stick-top forsticky page-header client-app-header">
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
               <div class="col-lg-7">
                  <div class="header-menu">
                     <nav id="navigation">
                        <ul id="jobguru_navigation">
                           <li class="active"><a href="/">Trang chủ</a></li>

                           @guest
                              @if($isEmployerHeader ?? false)
                                 <li class="has-children">
                                    <a href="{{ route('employers.portal') }}">Nhà tuyển dụng</a>
                                    <ul>
                                       <li><a href="{{ route('employers.portal') }}#features">Giới thiệu</a></li>
                                       <li><a href="{{ route('employers.browse') }}">Tìm ứng viên</a></li>
                                       <li><a href="{{ route('auth.login', ['role' => 'employer']) }}" target="_blank" rel="noopener noreferrer">Đăng nhập HR</a></li>
                                       <li><a href="{{ route('auth.sign_up', ['role' => 'employer']) }}" target="_blank" rel="noopener noreferrer">Tạo tài khoản HR</a></li>
                                    </ul>
                                 </li>
                              @else
                                 <li class="has-children">
                                    <a href="{{ route('candidates.browse_job') }}">Ứng viên</a>
                                    <ul>
                                       <li><a href="{{ route('candidates.browse_job') }}">Tìm việc</a></li>
                                       <li><a href="{{ route('candidates.browse_categories') }}">Ngành nghề</a></li>
                                       <li><a href="{{ route('candidates.browse_companies') }}">Chi nhánh</a></li>
                                    </ul>
                                 </li>
                                 <li class="has-children">
                                    <a href="{{ route('employers.portal') }}">Nhà tuyển dụng</a>
                                    <ul>
                                       <li><a href="{{ route('employers.portal') }}#features">Giới thiệu</a></li>
                                       <li><a href="{{ route('employers.browse') }}">Tìm ứng viên</a></li>
                                       <li><a href="{{ route('auth.login', ['role' => 'employer']) }}" target="_blank" rel="noopener noreferrer">Đăng nhập HR</a></li>
                                       <li><a href="{{ route('auth.sign_up', ['role' => 'employer']) }}" target="_blank" rel="noopener noreferrer">Tạo tài khoản HR</a></li>
                                    </ul>
                                 </li>
                              @endif
                           @else
                              @if($showCandidateMenu ?? false)
                                 <li><a href="{{ route('candidates.browse_job') }}">Việc làm</a></li>
                                 <li><a href="{{ route('candidates.browse_categories') }}">Ngành nghề</a></li>
                                 <li><a href="{{ route('candidates.browse_companies') }}">Chi nhánh</a></li>
                              @endif

@if($showEmployerMenu ?? false)
                                  <li><a href="{{ route('employers.browse') }}">Tìm ứng viên</a></li>
                                  <li><a href="{{ route('employers.post_job') }}">Đăng tuyển</a></li>
                               @endif

                               @if(Auth::user() && in_array(Auth::user()->role, ['director', 'admin']))
                                  <li><a href="{{ route('director.approve_jobs') }}">Duyệt tin</a></li>
                               @endif
                           @endguest

                           <li><a href="{{ route('pages.about') }}">Về chúng tôi</a></li>
                           <li><a href="{{ route('pages.blog') }}">Tin tức</a></li>
                           <li><a href="{{ route('pages.contact') }}">Liên hệ</a></li>
                        </ul>
                     </nav>
                  </div>
               </div>

               <div class="col-lg-3">

                  <div class="header-right-menu">
                     <ul>
                        @if(request()->routeIs('home'))
                           <li>
                              @if($isEmployerHeader)
                                 <a href="{{ route('auth.post_jobs') }}" class="post-jobs">Đăng tin ngay</a>
                               @else
                                  <a href="{{ ($showRoleSwitcher ?? false) ? route('role.switch', ['type' => 'employer']) : route('employers.portal') }}"
                                     target="_blank" rel="noopener noreferrer"
                                     class="role-switcher-cta">Đăng tuyển &amp; tìm hồ sơ</a>
                               @endif
                            </li>
                         @endif
                        @auth
                           @if(($showRoleSwitcher ?? false) && !request()->routeIs('home'))
                              <li>
                                 <a
                                    href="{{ route('role.switch', ['type' => 'employer']) }}"
                                    class="role-switcher-cta"
                                    style="background:#ff8a1d !important;color:#fff !important;-webkit-text-fill-color:#fff !important;"
                                 >
                                    Khu Nhà Tuyển Dụng
                                 </a>
                              </li>
                           @endif
                           @if($isEmployerHeader && !request()->routeIs('home'))
                              <li>
                                  <a href="{{route('auth.post_jobs')}}" class="post-jobs">Đăng tin ngay</a>
                              </li>
                           @endif




                           @auth
                              <li
                                 class="userbox-menu"
                                 :class="{ 'is-open': openUserMenu }"
                                 @click.outside="openUserMenu = false"
                                 style="position: relative; list-style: none; display: inline-block; margin-left: 10px; vertical-align: middle;"
                              >
                                 <button
                                    type="button"
                                    class="userbox-trigger"
                                    @click="openUserMenu = !openUserMenu"
                                    aria-label="Tài khoản"
                                    :aria-expanded="openUserMenu.toString()"
                                 >
@if(Auth::user() && Auth::user()->avatar && file_exists(public_path('storage/' . Auth::user()->avatar)))
                                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar"
                                           style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                     @else
                                        <span
                                           style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #ff7e3e 0%, #ff5722 100%); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                           {{ Auth::user() ? substr(Auth::user()->name, 0, 1) : '?' }}
                                        </span>
                                     @endif
                                 </button>

                                 <div class="userbox-dropdown" x-show="openUserMenu" x-transition.opacity.duration.150ms>
                                    <div class="userbox-account-info" style="padding: 12px 20px; border-bottom: 1px solid #f1f5f9;">
                                       <span style="display: block; font-weight: 700; color: #1e293b; font-size: 14px; line-height: 1.2;">{{ Auth::user()->name }}</span>
                                       <span style="display: block; color: #64748b; font-size: 11px; margin-top: 2px;">{{ Auth::user()->email }}</span>
                                    </div>

                                    <ul class="dropdown-list-wrapper">
                                       <li>
                                          <a href="{{ $isEmployerHeader ? route('employers.dashboard') : route('candidates.candidate_dashboard') }}" class="user-link">
                                             <span>Tổng quan hồ sơ</span>
                                          </a>
                                       </li>
                                       <li>
                                          <a href="{{ $isEmployerHeader ? route('employers.company_profile') : route('candidates.candidate_profile') }}" class="user-link">
                                             <span>Thông tin cá nhân</span>
                                          </a>
                                       </li>
                                       <li>
                                          <a href="{{ $isEmployerHeader ? route('employers.manage_jobs') : route('candidates.manage_jobs') }}" class="user-link">
                                             <span>Việc làm của tôi</span>
                                          </a>
                                       </li>
                                       <li>
                                          <a href="{{ $isEmployerHeader ? route('employers.message') : route('candidates.messages') }}" class="user-link">
                                             <span>Tin nhắn</span>
                                          </a>
                                       </li>
                                       <li>
                                          <a href="{{ $isEmployerHeader ? route('employers.transaction') : route('candidates.earnings') }}" class="user-link">
                                             <span>Thu nhập</span>
                                          </a>
                                       </li>
                                       <li>
                                          <a href="{{ $isEmployerHeader ? route('employers.change_password') : route('candidates.change_password') }}" class="user-link">
                                             <span>Đổi mật khẩu</span>
                                          </a>
                                       </li>
                                       <li class="logout-section">
                                          <div class="logout-wrapper">
                                             <livewire:client.logout-button />
                                          </div>
                                       </li>
                                    </ul>
                                 </div>
                              </li>
                           @endauth
                        @endauth
                         @guest
                            @php
                               $guestRole = ($isEmployerHeader ?? false) ? 'employer' : 'candidate';
                            @endphp
                            <li><a href="{{ route('auth.sign_up', ['role' => $guestRole]) }}"><i class="fa fa-user"></i> Đăng ký</a></li>
                            <li><a href="{{ route('auth.login', ['role' => $guestRole]) }}"><i class="fa fa-lock"></i> Đăng nhập</a></li>
                         @endguest
                     </ul>
                  </div>

               </div>
            </div>
         </div>
      </div>
   </header>
</div>

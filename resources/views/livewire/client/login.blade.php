<div>
      <section class="jobguru-breadcromb-area">
         <div class="breadcromb-top section_100">
            <div class="container">
               <div class="row">
                  <div class="col-md-12">
                     <div class="breadcromb-box">
                        <h3>Đăng nhập</h3>
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
                           <li class="active-breadcromb"><a href="{{ route('auth.login') }}">Đăng nhập</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <section class="jobguru-login-area section_70">
         <div class="container">
            <div class="row">
               <div class="col-lg-4 offset-lg-4 col-sm-6 offset-sm-3">
                  <div class="login-box">
                     <div class="login-title">
                        <h3>Đăng nhập</h3>
                     </div>
                     <form wire:submit.prevent="login" novalidate>
                        <div class="single-login-field">
                           <input type="email" placeholder="Địa chỉ Email" wire:model="email">
                           @error('email')
                              <p class="text-danger" style="margin:6px 0 0;">{{ $message }}</p>
                           @enderror
                        </div>
                        <div class="single-login-field">
                           <input type="password" placeholder="Mật khẩu" wire:model="password">
                           @error('password')
                              <p class="text-danger" style="margin:6px 0 0;">{{ $message }}</p>
                           @enderror
                        </div>
                        <div class="remember-row single-login-field clearfix">
                           <p class="checkbox remember">
                              <input class="checkbox-spin" type="checkbox" id="Freelance" wire:model="remember">
                              <label for="Freelance"><span></span>Duy trì đăng nhập</label>
                           </p>
                           <p class="lost-pass">
                              <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
                           </p>
                        </div>
                        <div class="single-login-field">
                           <button type="submit">Đăng nhập</button>
                        </div>
                     </form>
                     <div class="dont_have">
                        <a href="{{ route('auth.sign_up') }}">Chưa có tài khoản? Đăng ký ngay</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      </div>

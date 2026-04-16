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
         <div class="row justify-content-center">
            <div class="col-12 auth-card">
               <div class="auth-shell auth-shell--candidate">
                  <div class="auth-shell__main" style="width: 100%;">
                     <div class="auth-shell__header">
                        <h3>Đăng nhập</h3>
                        <p>Vui lòng nhập thông tin tài khoản của bạn.</p>
                     </div>
                     <form wire:submit.prevent="login" class="auth-form">
                        <div class="single-login-field">
                           <label>Email</label>
                           <input type="email" placeholder="Địa chỉ email" class="form-control" wire:model="email">
                           @error('email')
                              <p class="text-danger invalid-text">{{ $message }}</p>
                           @enderror
                        </div>
                        
                        <div class="single-login-field">
                           <label>Mật khẩu</label>
                           <input type="password" placeholder="Mật khẩu" class="form-control" wire:model="password">
                           @error('password')
                              <p class="text-danger invalid-text">{{ $message }}</p>
                           @enderror
                        </div>
                        
                        <div class="remember-row single-login-field d-flex justify-content-between align-items-center mb-4">
                           <div class="terms-field mb-0">
                              <input type="checkbox" id="remember-login" wire:model="remember" style="width:16px; height:16px;">
                              <div class="terms-copy">
                                 <label for="remember-login" style="font-size:14px; margin-bottom:0;">Ghi nhớ đăng nhập</label>
                              </div>
                           </div>
                           <a href="#" class="small text-muted">Quên mật khẩu?</a>
                        </div>
                        
                        <div class="auth-actions single-login-field">
                           <button type="submit" class="auth-submit-btn">Đăng nhập</button>
                        </div>
                     </form>
                     <div class="auth-links mt-4 text-center">
                        <a href="{{ route('auth.sign_up', ['role' => 'candidate']) }}">Bạn chưa có tài khoản? Đăng ký ngay</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
</div>

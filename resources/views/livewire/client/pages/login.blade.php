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
  <section class="jobguru-login-area section_70 auth-page-wrap">
      <div class="container">
         <div class="row justify-content-center">
            <div class="col-12 auth-card">
               <div class="auth-shell auth-shell--candidate">
                  <aside class="auth-shell__aside">
                     <span class="auth-shell__badge">
                        <i class="fa fa-shield"></i> Bảo mật tài khoản
                     </span>
                     <h4 class="auth-shell__title">
                        Đăng nhập tài khoản
                     </h4>
                     <p class="auth-shell__subtitle">
                        Một tài khoản có thể dùng để truy cập vai trò phù hợp của bạn sau khi đăng nhập.
                     </p>
                     <ul class="auth-shell__list">
                        <li><i class="fa fa-check-circle"></i><span>Đăng nhập nhanh bằng email và mật khẩu của bạn.</span></li>
                        <li><i class="fa fa-check-circle"></i><span>Hệ thống tự điều hướng về đúng khu vực ứng viên hoặc nhà tuyển dụng.</span></li>
                        <li><i class="fa fa-check-circle"></i><span>Bạn có thể kích hoạt thêm vai trò ứng viên ngay trong tài khoản HR.</span></li>
                     </ul>
                  </aside>
                  <div class="auth-shell__main">
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
                           <a href="#" class="small auth-link-muted">Quên mật khẩu?</a>
                        </div>
                        
                        <div class="auth-actions single-login-field">
                           <button type="submit" class="auth-submit-btn">Đăng nhập</button>
                        </div>
                     </form>
                     <div class="auth-links mt-4 text-center">
                        <a href="{{ route('auth.sign_up') }}">Bạn chưa có tài khoản? Đăng ký ngay</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
</div>

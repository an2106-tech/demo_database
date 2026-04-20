<div>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('candidates.candidate_dashboard') }}">Ứng viên</a></li>
            <li class="active">Đổi mật khẩu</li>
        </ul>
    </div>
      <section class="candidate-dashboard-area section_70">
         <div class="container-fluid px-lg-5">
            <div class="row">
               <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                  @include('livewire.client.partials.candidate-sidebar')
               </div>
               <div class="col-lg-9 col-md-8">
                  <div class="dashboard-right">
                        <div class="premium-panel">
                            <div class="manage-jobs-heading">
                                <h3>Thiết lập mật khẩu</h3>
                                <p style="margin: 10px 0 0; color: #64748b;">
                                    Đảm bảo mật khẩu của bạn an toàn bằng cách sử dụng kết hợp nhiều loại ký tự.
                                </p>
                            </div>
                            <form style="margin-top: 2rem;">
                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label for="old_pass">Mật khẩu cũ</label>
                                        <input type="password" placeholder="*******" id="old_pass">
                                    </div>
                                </div>
                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label for="new_pass">Mật khẩu mới</label>
                                        <input type="password" placeholder="*******" id="new_pass">
                                    </div>
                                </div>
                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label for="confirm_pass">Xác nhận mật khẩu</label>
                                        <input type="password" placeholder="*******" id="confirm_pass">
                                    </div>
                                </div>
                                <div class="submit-resume" style="margin-top: 1.5rem;">
                                    <button type="submit">Cập nhật mật khẩu</button>
                                </div>
                            </form>
                        </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      </div>
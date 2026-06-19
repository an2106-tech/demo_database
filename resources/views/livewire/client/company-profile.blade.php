<div>
      <section class="jobguru-breadcromb-area">
         <div class="breadcromb-top section_100">
            <div class="container">
               <div class="row">
                  <div class="col-md-12">
                     <div class="breadcromb-box">
                        <h3>Hồ sơ công ty</h3>
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
                           <li><a href="#">Nhà tuyển dụng</a></li>
                           <li class="active-breadcromb"><a href="#">Hồ sơ công ty</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <section class="candidate-dashboard-area section_70">
         <div class="container">
            <div class="row">
               <div class="col-md-4 col-lg-3 dashboard-left-border">
                  <div class="dashboard-left">
                     <ul class="dashboard-menu">
                        <li>
                           <a href="employer-dashboard.html">
                           <i class="fa fa-tachometer"></i>
                           Bảng điều khiển
                           </a>
                        </li>
                        <li class="active"><a href="company-profile.html"><i class="fa fa-users"></i>Hồ sơ công ty</a></li>
                        <li><a href="message.html"><i class="fa fa-envelope-open"></i>Tin nhắn</a></li>
                        <li><a href="post-job.html"><i class="fa fa-envelope-open"></i>Đăng tin tuyển dụng</a></li>
                        <li><a href="manage-candidates.html"><i class="fa fa-briefcase"></i>Quản lý ứng viên</a></li>
                        <li><a href="transaction.html"><i class="fa fa-rocket"></i>Giao dịch</a></li>
                        <li><a href="change-password.html"><i class="fa fa-lock"></i>Đổi mật khẩu</a></li>
                        <li><a href="#"><i class="fa fa-power-off"></i>Đăng xuất</a></li>
                     </ul>
                  </div>
               </div>
               <div class="col-md-8 col-lg-9">
                  <div class="dashboard-right">
                     <div class="candidate-profile">
                        <div class="candidate-single-profile-info">
                           <div class="single-resume-feild resume-avatar">
                              <div class="resume-image company-resume-image">
                                 <img src="{{ asset('assets/img/company_page_logo.jpg') }}" alt="resume avatar">
                                 <div class="resume-avatar-hover">
                                    <div class="resume-avatar-upload">
                                       <p>
                                          <i class="fa fa-pencil"></i>
                                          Sửa
                                       </p>
                                       <input type="file">
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="candidate-single-profile-info">
                           <form>
                              <div class="resume-box">
                                 <h3>Thông tin công ty</h3>
                                 <div class="single-resume-feild feild-flex-2">
                                    <div class="single-input">
                                       <label for="name">Tên công ty:</label>
                                       <input type="text" value="Jennie Wilson" id="name">
                                    </div>
                                    <div class="single-input">
                                       <label for="c_cat">Lĩnh vực kinh doanh:</label>
                                       <select id="c_cat">
                                          <option selected>Chọn danh mục</option>
                                          <option>Dịch vụ CNTT</option>
                                          <option>Phi lợi nhuận</option>
                                          <option>Khởi nghiệp (Startup)</option>
                                          <option>Tập đoàn</option>
                                       </select>
                                    </div>
                                 </div>
                                 <div class="single-resume-feild feild-flex-2">
                                    <div class="single-input">
                                       <label for="Start">Năm thành lập:</label>
                                       <input type="text" value="1990" id="Start">
                                    </div>
                                    <div class="single-input">
                                       <label for="member">Số lượng nhân viên:</label>
                                       <input type="text" value="132" id="member">
                                    </div>
                                 </div>
                                 <div class="single-resume-feild feild-flex-2">
                                    <div class="single-input">
                                       <label for="Location">Quốc gia:</label>
                                       <input type="text" value="Vi?t Nam" id="Location">
                                    </div>
                                    <div class="single-input">
                                       <label for="City">Thành phố:</label>
                                       <input type="text" value="H? N?i" id="City">
                                    </div>
                                 </div>
                                 <div class="single-resume-feild ">
                                    <div class="single-input">
                                       <label for="Bio">Mô tả chi tiết:</label>
                                       <textarea id="Bio">M? t? ng?n g?n v? doanh nghi?p, l?nh v?c ho?t ??ng, v?n h?a l?m vi?c v? nhu c?u tuy?n d?ng hi?n t?i.</textarea>
                                    </div>
                                 </div>
                              </div>
                              <div class="resume-box">
                                 <h3>Thông tin liên hệ</h3>
                                 <div class="single-resume-feild feild-flex-2">
                                    <div class="single-input">
                                       <label for="Phone">Điện thoại:</label>
                                       <input type="text" value="+88-123-4467-9" id="Phone">
                                    </div>
                                    <div class="single-input">
                                       <label for="Email">Email:</label>
                                       <input type="text" value="hr@example.com" id="Email">
                                    </div>
                                 </div>
                                 <div class="single-resume-feild feild-flex-2">
                                    <div class="single-input">
                                       <label for="contry">Quốc gia (Liên hệ):</label>
                                       <select id="contry">
                                          <option>Ả Rập Thống Nhất</option>
                                          <option>Mỹ</option>
                                          <option>Hà Lan</option>
                                          <option>Nga</option>
                                          <option selected>Bangladesh</option>
                                          <option>Ấn Độ</option>
                                          <option>Pakistan</option>
                                          <option>Brazil</option>
                                          <option>Châu Phi</option>
                                       </select>
                                    </div>
                                    <div class="single-input">
                                       <label for="City2">Thành phố:</label>
                                       <input type="text" value="Dhaka" id="City2">
                                    </div>
                                 </div>
                                 <div class="single-resume-feild feild-flex-2">
                                    <div class="single-input">
                                       <label for="Zip">Mã bưu điện:</label>
                                       <input type="text" value="6100" id="Zip">
                                    </div>
                                    <div class="single-input">
                                       <label for="Address22">Địa chỉ:</label>
                                       <input type="text" value="New york city,22/A Street,01" id="Address22">
                                    </div>
                                 </div>
                              </div>
                              <div class="resume-box">
                                 <h3>Liên kết mạng xã hội</h3>
                                 <div class="single-resume-feild feild-flex-2">
                                    <div class="single-input">
                                       <label for="twitter">
                                       <i class="fa fa-twitter twitter"></i>
                                       twitter
                                       </label>
                                       <input type="text" value="https://www.twitter.com/" id="twitter" name="twitter">
                                    </div>
                                    <div class="single-input">
                                       <label for="twitter">
                                       <i class="fa fa-facebook facebook"></i>
                                       facebook
                                       </label>
                                       <input type="text" value="https://www.facebook.com/" id="facebook" name="facebook">
                                    </div>
                                 </div>
                                 <div class="single-resume-feild feild-flex-2">
                                    <div class="single-input">
                                       <label for="google">
                                       <i class="fa fa-google-plus google"></i>
                                       Google
                                       </label>
                                       <input type="text" value="https://www.google.com/" id="google" name="twitter">
                                    </div>
                                    <div class="single-input">
                                       <label for="linkedin">
                                       <i class="fa fa-linkedin linkedin"></i>
                                       linkedin
                                       </label>
                                       <input type="text" value="https://www.linkedin.com/" id="linkedin" name="twitter">
                                    </div>
                                 </div>
                              </div>
                              <div class="submit-resume">
                                 <button type="submit">Cập nhật</button>
                              </div>
                           </form>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      </div>
<div>
      <section class="jobguru-breadcromb-area">
         <div class="breadcromb-top section_100">
            <div class="container">
               <div class="row">
                  <div class="col-md-12">
                     <div class="breadcromb-box">
                        <h3>Nộp hồ sơ</h3>
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
                           <li class="active-breadcromb"><a href="#">Nộp hồ sơ</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <section class="jobguru-submit-resume-area section_70">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="submit-resume-box">
                     <form>
                        <div class="resume-box">
                           <div class="single-resume-feild resume-avatar">
                              <div class="resume-image">
                                 <img src="{{ asset('assets/img/resume-avatar.jpg') }}" alt="ảnh đại diện" />
                                 <div class="resume-avatar-hover">
                                    <div class="resume-avatar-upload">
                                       <p>
                                          <i class="fa fa-upload"></i>
                                          Tải lên
                                       </p>
                                       <input type="file">
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <h3>Thông tin cá nhân</h3>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="name">Họ và Tên:</label>
                                 <input type="text" placeholder="Nhập đầy đủ họ tên của bạn" id="name">
                              </div>
                              <div class="single-input">
                                 <label for="p_title">Chức danh chuyên môn:</label>
                                 <input type="text" placeholder="Ví dụ: Lập trình viên Web" id="p_title">
                              </div>
                           </div>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="Email">Email:</label>
                                 <input type="email" placeholder="Địa chỉ Email của bạn" id="Email">
                              </div>
                              <div class="single-input">
                                 <label for="Phone">Số điện thoại:</label>
                                 <input type="tel" placeholder="Số điện thoại liên lạc" id="Phone">
                              </div>
                           </div>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="Region">Quốc gia:</label>
                                 <select id="Region">
                                    <option selected>Chọn quốc gia</option>
                                    <option>Việt Nam</option>
                                    <option>Mỹ</option>
                                    <option>Hà Lan</option>
                                    <option>Nga</option>
                                    <option>Bangladesh</option>
                                    <option>Ấn Độ</option>
                                    <option>Pakistan</option>
                                    <option>Brazil</option>
                                    <option>Châu Phi</option>
                                    <option>Các Tiểu vương quốc Ả Rập</option>
                                 </select>
                              </div>
                              <div class="single-input">
                                 <label for="Address">Địa chỉ:</label>
                                 <input type="text" placeholder="Địa chỉ cụ thể" id="Address">
                              </div>
                           </div>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="Bio">Giới thiệu bản thân:</label>
                                 <textarea id="Bio" placeholder="Viết giới thiệu ngắn về bạn..."></textarea>
                              </div>
                           </div>
                        </div>
                        <div class="resume-box">
                           <h3>Học vấn</h3>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="Degree">Bằng cấp:</label>
                                 <input type="text" placeholder="Tên bằng cấp / Chuyên ngành" id="Degree">
                              </div>
                           </div>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="datepicker_form">Từ ngày:</label>
                                 <input type="text" placeholder="02.10.2015" id="datepicker_form" class="datepicker">
                              </div>
                              <div class="single-input">
                                 <label for="datepicker_to">Đến ngày:</label>
                                 <input type="text" placeholder="06.11.2017" id="datepicker_to" class="datepicker">
                              </div>
                           </div>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="Institute"> Trường / Viện đào tạo:</label>
                                 <input type="text" placeholder="Tên trường học" id="Institute">
                              </div>
                           </div>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="edu_info">Thông tin bổ sung: <span>(không bắt buộc)</span></label>
                                 <textarea id="edu_info" placeholder="Mô tả ngắn gọn về quá trình học tập"></textarea>
                              </div>
                           </div>
                        </div>
                        <div class="resume-box">
                           <h3>Kinh nghiệm làm việc</h3>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="j_post">Vị trí công việc:</label>
                                 <input type="text" placeholder="Tên vị trí đảm nhận" id="j_post">
                              </div>
                           </div>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="datepicker_form2">Từ ngày:</label>
                                 <input type="text" placeholder="02-10-2015" id="datepicker_form2" class="datepicker">
                              </div>
                              <div class="single-input">
                                 <label for="datepicker_to2">Đến ngày:</label>
                                 <input type="text" placeholder="06-11-2017" id="datepicker_to2" class="datepicker">
                              </div>
                           </div>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="Company"> Công ty:</label>
                                 <input type="text" placeholder="Tên công ty" id="Company">
                              </div>
                           </div>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="work_info">Thông tin bổ sung: <span>(không bắt buộc)</span></label>
                                 <textarea id="work_info" placeholder="Mô tả ngắn gọn về kinh nghiệm làm việc"></textarea>
                              </div>
                           </div>
                        </div>
                        <div class="resume-box">
                           <h3>Kỹ năng & Sản phẩm</h3>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="skill">Kỹ năng:</label>
                                 <input type="text" placeholder="Liệt kê các kỹ năng liên quan, cách nhau bằng dấu phẩy" id="skill">
                              </div>
                           </div>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="Portfolio">Sản phẩm (Portfolio):</label>
                                 <input type="text" placeholder="Đường dẫn (URL) sản phẩm mẫu" id="Portfolio">
                              </div>
                              <div class="single-input">
                                 <label for="w_screen">Ảnh chụp màn hình sản phẩm</label>
                                 <div class="product-upload">
                                    <p>
                                       <i class="fa fa-upload"></i>
                                       Dung lượng tối đa 3MB, định dạng .jpg hoặc .png
                                    </p>
                                    <input type="file" id="w_screen">
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="submit-resume">
                           <button type="submit">Lưu hồ sơ</button>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </section>
      </div>
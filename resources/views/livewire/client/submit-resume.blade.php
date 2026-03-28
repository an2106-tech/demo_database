<div>
      <!-- Breadcromb Area Start -->
      <section class="jobguru-breadcromb-area">
         <div class="breadcromb-top section_100">
            <div class="container">
               <div class="row">
                  <div class="col-md-12">
                     <div class="breadcromb-box">
                        <h3>Submit resume</h3>
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
                           <li><a href="#">home</a></li>
                           <li><a href="#">candidates</a></li>
                           <li class="active-breadcromb"><a href="#">Submit resume</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <!-- Breadcromb Area End -->
       
       
      <!-- Submit Resume Area Start -->
      <section class="jobguru-submit-resume-area section_70">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="submit-resume-box">
                     <form>
                        <div class="resume-box">
                           <div class="single-resume-feild resume-avatar">
                              <div class="resume-image">
                                 <img src="{{ asset('assets/img/resume-avatar.jpg') }}" alt="resume avatar" />
                                 <div class="resume-avatar-hover">
                                    <div class="resume-avatar-upload">
                                       <p>
                                          <i class="fa fa-upload"></i>
                                          upload
                                       </p>
                                       <input type="file">
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <h3>Personal Information</h3>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="name">Your Name:</label>
                                 <input type="text" placeholder="Your Full Name" id="name">
                              </div>
                              <div class="single-input">
                                 <label for="p_title">Professional title:</label>
                                 <input type="text" placeholder="e.g Web Developer" id="p_title">
                              </div>
                           </div>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="Email">Email:</label>
                                 <input type="email" placeholder="Your Email Address" id="Email">
                              </div>
                              <div class="single-input">
                                 <label for="Phone">Phone:</label>
                                 <input type="tel" placeholder="Phone Number" id="Phone">
                              </div>
                           </div>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="Region">Country:</label>
                                 <select id="Region">
                                    <option selected>select Country</option>
                                    <option>Arab Amirats</option>
                                    <option>America</option>
                                    <option>Netherlands</option>
                                    <option>Russia</option>
                                    <option>Bangladesh</option>
                                    <option>India</option>
                                    <option>Pakistan</option>
                                    <option>Brazil</option>
                                    <option>Africa</option>
                                 </select>
                              </div>
                              <div class="single-input">
                                 <label for="Address">Address:</label>
                                 <input type="text" placeholder="Street Address" id="Address">
                              </div>
                           </div>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="Bio">Introduce Yourself:</label>
                                 <textarea id="Bio" placeholder="Write Your Bio Here..."></textarea>
                              </div>
                           </div>
                        </div>
                        <div class="resume-box">
                           <h3>Education</h3>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="Degree">Degree:</label>
                                 <input type="text" placeholder="Degree's Title" id="Degree">
                              </div>
                           </div>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="datepicker_form">From:</label>
                                 <input type="text" placeholder="02.10.2015" id="datepicker_form" class="datepicker">
                              </div>
                              <div class="single-input">
                                 <label for="datepicker_to">To:</label>
                                 <input type="text" placeholder="06.11.2017" id="datepicker_to" class="datepicker">
                              </div>
                           </div>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="Institute"> Institute:</label>
                                 <input type="text" placeholder="Institution Name" id="Institute">
                              </div>
                           </div>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="edu_info">Additional Information: <span>(optional)</span></label>
                                 <textarea id="edu_info" placeholder="Education Experience Brief"></textarea>
                              </div>
                           </div>
                        </div>
                        <div class="resume-box">
                           <h3>Work Experience</h3>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="j_post">Job Post:</label>
                                 <input type="text" placeholder="Job Post Title" id="j_post">
                              </div>
                           </div>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="datepicker_form2">From:</label>
                                 <input type="text" placeholder="02-10-2015" id="datepicker_form2" class="datepicker">
                              </div>
                              <div class="single-input">
                                 <label for="datepicker_to2">To:</label>
                                 <input type="text" placeholder="06-11-2017" id="datepicker_to2" class="datepicker">
                              </div>
                           </div>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="Company"> Company:</label>
                                 <input type="text" placeholder="Company Name" id="Company">
                              </div>
                           </div>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="work_info">Additional Information: <span>(optional)</span></label>
                                 <textarea id="work_info" placeholder="Work Experience Brief"></textarea>
                              </div>
                           </div>
                        </div>
                        <div class="resume-box">
                           <h3>Skills & Portfolio</h3>
                           <div class="single-resume-feild ">
                              <div class="single-input">
                                 <label for="skill">Skills:</label>
                                 <input type="text" placeholder="Comma separate a list of relevant skills" id="skill">
                              </div>
                           </div>
                           <div class="single-resume-feild feild-flex-2">
                              <div class="single-input">
                                 <label for="Portfolio">Portfolio:</label>
                                 <input type="text" placeholder="Portfolio Demo URL" id="Portfolio">
                              </div>
                              <div class="single-input">
                                 <label for="w_screen">Works Screenshot</label>
                                 <div class="product-upload">
                                    <p>
                                       <i class="fa fa-upload"></i>
                                       Max file size is 3MB,Suitable files are .jpg & .png
                                    </p>
                                    <input type="file" id="w_screen">
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="submit-resume">
                           <button type="submit">Save Resume</button>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <!-- Submit Resume Area End -->
       
</div>

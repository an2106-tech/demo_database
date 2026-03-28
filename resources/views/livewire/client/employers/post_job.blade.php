<div>
    <!-- Breadcromb Area Start -->
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Post A job</h3>
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
                                <li><a href="{{ route('home') }}">home</a></li>
                                <li><a href="{{ route('employers.dashboard') }}">Employeer</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.post_job') }}">Post A job</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcromb Area End -->


    <!-- Candidate Dashboard Area Start -->
    <section class="candidate-dashboard-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-lg-3  mx-auto dashboard-left-border">
                    <div class="dashboard-left">
                        <ul class="dashboard-menu">
                            <li>
                                <a href="{{ route('employers.dashboard') }}">
                                    <i class="fa fa-tachometer"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li><a href="{{ route('employers.candidate_profile') }}"><i class="fa fa-users"></i>My Profile</a></li>
                            <li><a href="{{ route('employers.message') }}"><i class="fa fa-envelope-open"></i>messages</a></li>
                            <li><a href="{{ route('employers.manage_jobs') }}"><i class="fa fa-briefcase"></i>manage jobs</a></li>
                            <li class="active"><a href="{{ route('employers.candidate_earnings') }}"><i class="fa fa-rocket"></i>earnings</a></li>
                            <li><a href="{{ route('employers.change_password') }}"><i class="fa fa-lock"></i>change password</a></li>
                            <li><a href="#"><i class="fa fa-power-off"></i>LogOut</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-8 col-lg-9 mx-auto">
                    <div class="dashboard-right">
                        <div class="earnings-page-box manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>Post A new job</h3>
                            </div>
                            <div class="new-job-submission">
                                <form>
                                    <div class="resume-box">
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="j_title">Job Title:</label>
                                                <input type="text" id="j_title">
                                            </div>
                                            <div class="single-input">
                                                <label for="Location">Location:</label>
                                                <input type="text" placeholder="e.g. London" id="Location">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="j_reg">Job Region:</label>
                                                <select id="j_reg">
                                                    <option value=''>Select Region</option>
                                                    <option value="1">Los Angeles</option>
                                                    <option value="2">Miami</option>
                                                    <option value="3">New York</option>
                                                    <option value="4">San Francisco</option>
                                                </select>
                                            </div>
                                            <div class="single-input">
                                                <label for="j_type">Job Type:</label>
                                                <select id="j_type">
                                                    <option value=''>Select Region</option>
                                                    <option value="1">Full TIme</option>
                                                    <option value="2">Freelance</option>
                                                    <option value="3">Part Time</option>
                                                    <option value="4">Internship</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="j_category">Job Category:</label>
                                                <select id="j_category">
                                                    <option value="122">Accounting / Finance</option>
                                                    <option value="124">Automotive Jobs</option>
                                                    <option value="132">Construction / Facilities</option>
                                                    <option value="137">Design, Art & Multimedia</option>
                                                    <option value="172">Adobe Photoshop</option>
                                                    <option value="173">Animation</option>
                                                    <option value="145">Graphic Design</option>
                                                    <option value="147">Illustration</option>
                                                    <option value="150">Logo Design</option>
                                                    <option value="168">Video</option>
                                                    <option value="140">Education Training</option>
                                                    <option value="146">Healthcare</option>
                                                    <option value="157">Restaurant / Food Service</option>
                                                    <option value="159">Sales / Marketing</option>
                                                    <option value="175">Display Advertising</option>
                                                    <option value="176">Email Marketing</option>
                                                    <option value="177">Lead Generation</option>
                                                    <option value="179">Marketing Strategy</option>
                                                    <option value="180">Public Relations</option>
                                                    <option value="165">Telecommunications</option>
                                                    <option value="167">Transportation / Logistics</option>
                                                </select>
                                            </div>
                                            <div class="single-input">
                                                <label for="External">External "Apply for Job" link :
                                                    <span>(optional)</span></label>
                                                <input type="text" placeholder="http://" id="External">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="mn_salary">Minimum Salary ($):</label>
                                                <input type="text" placeholder="e.g. 20000" id="mn_salary">
                                            </div>
                                            <div class="single-input">
                                                <label for="mx_salary">Maximum Salary ($):</label>
                                                <input type="text" placeholder="e.g. 50000" id="mx_salary">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild">
                                            <div class="single-input">
                                                <label for="j_desc">Job Description:</label>
                                                <textarea id="j_desc"></textarea>
                                            </div>
                                        </div>
                                        <div class="single-resume-feild upload_file">
                                            <div class="product-upload">
                                                <p>
                                                    <i class="fa fa-upload"></i>
                                                    Upload Files
                                                </p>
                                                <input type="file" id="w_screen">
                                            </div>
                                            <p>Images or documents that might be helpful in describing your job</p>
                                        </div>
                                    </div>
                                    <div class="single-input submit-resume">
                                        <button type="submit">Post Job</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Candidate Dashboard Area End -->
</div>
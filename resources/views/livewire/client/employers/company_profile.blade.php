<div>
    <!-- Breadcromb Area Start -->
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Company Profile</h3>
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
                                <li><a href="{{ route('employers.dashboard') }}">employer</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.company_profile') }}">Company Profile</a></li>
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
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    <div class="dashboard-left">
                        <ul class="dashboard-menu">
                            <li>
                                <a href="{{ route('employers.dashboard') }}">
                                    <i class="fa fa-tachometer"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li class="active"><a href="{{ route('employers.company_profile') }}"><i class="fa fa-users"></i>Company
                                    Profile</a></li>
                            <li><a href="{{ route('employers.message') }}"><i class="fa fa-envelope-open"></i>messages</a></li>
                            <li><a href="{{ route('employers.post_job') }}"><i class="fa fa-envelope-open"></i>post a job</a></li>
                            <li><a href="{{ route('employers.manage_candidates') }}"><i class="fa fa-briefcase"></i>manage candidates</a>
                            </li>
                            <li><a href="{{ route('employers.transaction') }}"><i class="fa fa-rocket"></i>transaction</a></li>
                            <li><a href="{{ route('employers.change_password') }}"><i class="fa fa-lock"></i>change password</a></li>
                            <li><a href="#"><i class="fa fa-power-off"></i>LogOut</a></li>
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
                                                    Edit
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
                                        <h3>company profile</h3>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="name">Company Title:</label>
                                                <input type="text" value="Jennie Wilson" id="name">
                                            </div>
                                            <div class="single-input">
                                                <label for="c_cat">company category:</label>
                                                <select id="c_cat">
                                                    <option selected>Choose Category</option>
                                                    <option>IT Service</option>
                                                    <option>Non-Profit</option>
                                                    <option>StartUP</option>
                                                    <option>Corporate</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Start">Start:</label>
                                                <input type="text" value="1990" id="Start">
                                            </div>
                                            <div class="single-input">
                                                <label for="member">Team Member:</label>
                                                <input type="text" value="132" id="member">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Location">Country:</label>
                                                <input type="text" value="London" id="Location">
                                            </div>
                                            <div class="single-input">
                                                <label for="City">City:</label>
                                                <input type="text" value="Westminster" id="City">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild ">
                                            <div class="single-input">
                                                <label for="Bio">Description:</label>
                                                <textarea
                                                    id="Bio">Maecenas is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="resume-box">
                                        <h3>Contact Information</h3>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Phone">Phone:</label>
                                                <input type="text" value="+88-123-4467-9" id="Phone">
                                            </div>
                                            <div class="single-input">
                                                <label for="Email">Email:</label>
                                                <input type="text" value="demo@mail.com" id="Email">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="contry">contry:</label>
                                                <select id="contry">
                                                    <option>Arab Amirats</option>
                                                    <option>America</option>
                                                    <option>Netherlands</option>
                                                    <option>Russia</option>
                                                    <option selected>Bangladesh</option>
                                                    <option>India</option>
                                                    <option>Pakistan</option>
                                                    <option>Brazil</option>
                                                    <option>Africa</option>
                                                </select>
                                            </div>
                                            <div class="single-input">
                                                <label for="City2">City:</label>
                                                <input type="text" value="Dhaka" id="City2">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Zip">Zip:</label>
                                                <input type="text" value="6100" id="Zip">
                                            </div>
                                            <div class="single-input">
                                                <label for="Address22">Address:</label>
                                                <input type="text" value="New york city,22/A Street,01" id="Address22">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="resume-box">
                                        <h3>social link</h3>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="twitter">
                                                    <i class="fa fa-twitter twitter"></i>
                                                    twitter
                                                </label>
                                                <input type="text" value="https://www.twitter.com/" id="twitter"
                                                    name="twitter">
                                            </div>
                                            <div class="single-input">
                                                <label for="twitter">
                                                    <i class="fa fa-facebook facebook"></i>
                                                    facebook
                                                </label>
                                                <input type="text" value="https://www.facebook.com/" id="facebook"
                                                    name="facebook">
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="google">
                                                    <i class="fa fa-google-plus google"></i>
                                                    Google
                                                </label>
                                                <input type="text" value="https://www.google.com/" id="google"
                                                    name="twitter">
                                            </div>
                                            <div class="single-input">
                                                <label for="linkedin">
                                                    <i class="fa fa-linkedin linkedin"></i>
                                                    linkedin
                                                </label>
                                                <input type="text" value="https://www.linkedin.com/" id="linkedin"
                                                    name="twitter">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="submit-resume">
                                        <button type="submit">Update</button>
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

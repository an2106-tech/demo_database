<div>
    <!-- Breadcromb Area Start -->
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Change Password</h3>
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
                                <li><a href="{{ route('candidates.browse_job') }}">candidates</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.change_password') }}">Change Password</a></li>
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
                <div class="col-lg-3 col-md-4 dashboard-left-border">
                    <div class="dashboard-left">
                        <ul class="dashboard-menu">
                            <li>
                                <a href="{{ route('employers.dashboard') }}">
                                    <i class="fa fa-tachometer"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li><a href="{{ route('employers.company_profile') }}"><i class="fa fa-users"></i>My Profile</a></li>
                            <li><a href="{{ route('employers.message') }}"><i class="fa fa-envelope-open"></i>messages</a></li>
                            <li><a href="{{ route('employers.manage_jobs') }}"><i class="fa fa-briefcase"></i>manage jobs</a></li>
                            <li><a href="{{ route('employers.candidate_earnings') }}"><i class="fa fa-rocket"></i>earnings</a></li>
                            <li class="active"><a href="{{ route('employers.change_password') }}"><i class="fa fa-lock"></i>change
                                    password</a></li>
                            <li><a href="#"><i class="fa fa-power-off"></i>LogOut</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="dashboard-right">
                        <div class="change-pass manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>Change Password</h3>
                            </div>
                            <form>
                                <p>
                                    <label for="old_pass">Old Password</label>
                                    <input type="password" placeholder="*******" id="old_pass">
                                </p>
                                <p>
                                    <label for="new_pass">New Password</label>
                                    <input type="password" placeholder="*******" id="new_pass">
                                </p>
                                <p>
                                    <label for="confirm_pass">confirm Password</label>
                                    <input type="password" placeholder="*******" id="confirm_pass">
                                </p>
                                <p>
                                    <button type="submit">Update</button>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Candidate Dashboard Area End -->
</div>
<div>
    <!-- Breadcromb Area Start -->
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Dashboard</h3>
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
                                <li><a href="{{ route('employers.browse') }}">employer</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.dashboard') }}">Dashboard</a></li>
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
                            <li class="active">
                                <a href="{{ route('employers.dashboard') }}">
                                    <i class="fa fa-tachometer"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li><a href="{{ route('employers.company_profile') }}"><i class="fa fa-users"></i>Company Profile</a></li>
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
                        <div class="welcome-dashboard">
                            <h3>Welcome <span>Arino inc !</span></h3>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex widget_bg_blue">
                                    <div class="widget-icon">
                                        <i class="fa fa-gavel"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>1426</h4>
                                        <h2>new Bids</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex  widget_bg_purple">
                                    <div class="widget-icon">
                                        <i class="fa fa-usd"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>$12,000</h4>
                                        <h2> Payment</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="widget_card_page grid_flex widget_bg_dark_red">
                                    <div class="widget-icon">
                                        <i class="fa fa-users"></i>
                                    </div>
                                    <div class="widget-page-text">
                                        <h4>127</h4>
                                        <h2>Candidates</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Candidate Dashboard Area End -->
</div>
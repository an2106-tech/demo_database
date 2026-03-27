<div>
    <!DOCTYPE html>
    <html lang="en-US">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="jobguru | Job Board HTML Templates from Themescare">
        <meta name="keyword" content="Job, freelancer, employee, marketplace">
        <meta name="author" content="Themescare">
        <!-- Title -->
        <title>jobguru</title>
        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon/favicon-32x32.png">
        <!--Bootstrap css-->
        <link rel="stylesheet" href="assets/css/bootstrap.css">
        <!--Font Awesome css-->
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">
        <!--Magnific css-->
        <link rel="stylesheet" href="assets/css/magnific-popup.css">
        <!--Owl-Carousel css-->
        <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
        <link rel="stylesheet" href="assets/css/owl.theme.default.min.css">
        <!--Animate css-->
        <link rel="stylesheet" href="assets/css/animate.min.css">
        <!--Select2 css-->
        <link rel="stylesheet" href="assets/css/select2.min.css">
        <!--Slicknav css-->
        <link rel="stylesheet" href="assets/css/slicknav.min.css">
        <!--Bootstrap-Datepicker css-->
        <link rel="stylesheet" href="assets/css/bootstrap-datepicker.min.css">
        <!--Jquery UI css-->
        <link rel="stylesheet" href="assets/css/jquery-ui.min.css">
        <!--Perfect-Scrollbar css-->
        <link rel="stylesheet" href="assets/css/perfect-scrollbar.min.css">
        <!--Site Main Style css-->
        <link rel="stylesheet" href="assets/css/style.css">
        <!--Responsive css-->
        <link rel="stylesheet" href="assets/css/responsive.css">
    </head>

    <body>


        <!-- Header Area Start -->
        <header class="jobguru-header-area stick-top forsticky">
            <div class="menu-animation">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-2">
                            <div class="site-logo">
                                <a href="{{ route('home') }}">
                                    <img src="{{ asset('assets/img/logo.png') }}" alt="jobguru" class="non-stick-logo" />
                                    <img src="{{ asset('assets/img/logo-2.png') }}" alt="jobguru" class="stick-logo" />
                                </a>
                            </div>
                            <!-- Responsive Menu Start -->
                            <div class="jobguru-responsive-menu"></div>
                            <!-- Responsive Menu Start -->
                        </div>
                        <div class="col-lg-6">
                            <div class="header-menu">
                                <nav id="navigation">
                                    <ul id="jobguru_navigation">
                                        <li class="active has-children">
                                            <a href="#">home</a>
                                            <ul>
                                                <li><a href="{{ route('home') }}">Home 1</a></li>
                                                <li><a href="index-2.html">Home 2</a></li>
                                            </ul>
                                        </li>
                                        <li class=" has-children">
                                            <a href="#">for candidates</a>
                                            <ul>
                                                <li class="has-inner-child">
                                                    <a href="#">browse jobs</a>
                                                    <ul>
                                                        <li><a href="browse-jobs.html">full page grid</a></li>
                                                        <li><a href="job-grid-sidebar.html">grid sidebar</a></li>
                                                        <li><a href="job-list-sidebar.html">list sidebar</a></li>
                                                    </ul>
                                                </li>
                                                <li><a href="browse-categories.html">Browse Categories</a></li>
                                                <li><a href="browse-companies.html">browse companies</a></li>
                                                <li><a href="single-candidates.html">candidates details</a></li>
                                                <li><a href="submit-resume.html">submit resume</a></li>
                                                <li class="has-inner-child">
                                                    <a href="#">candidate dashboard</a>
                                                    <ul>
                                                        <li><a href="candidate-dashboard.html">Candidate dashboard</a>
                                                        </li>
                                                        <li><a href="candidate-profile.html">Candidate profile</a></li>
                                                        <li><a href="message.html">messages</a></li>
                                                        <li><a href="manage-jobs.html">manage jobs</a></li>
                                                        <li><a href="candidate-earnings.html">earnings</a></li>
                                                        <li><a href="change-password.html">change password</a></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="has-children">
                                            <a href="#">for employers</a>
                                            <ul>
                                                <li><a href="{{ route('employers.browse') }}">Browse Candidates</a></li>
                                                <li><a href="{{ route('employers.single_company') }}">company details</a></li>
                                                <li><a href="post-job.html">Post A job</a></li>
                                                <li class="has-inner-child">
                                                    <a href="#">employer dashboard</a>
                                                    <ul>
                                                        <li><a href="employer-dashboard.html">employer dashboard</a>
                                                        </li>
                                                        <li><a href="company-profile.html">company profile</a></li>
                                                        <li><a href="message.html">messages</a></li>
                                                        <li><a href="manage-candidates.html">manage candidates</a></li>
                                                        <li><a href="transaction.html">transaction</a></li>
                                                        <li><a href="change-password.html">change password</a></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="has-children">
                                            <a href="#">pages</a>
                                            <ul>
                                                <li><a href="about.html">About us</a></li>
                                                <li class="has-inner-child">
                                                    <a href="#">blog</a>
                                                    <ul>
                                                        <li><a href="blog.html">blog</a></li>
                                                        <li><a href="single-blog.html">single blog</a></li>
                                                    </ul>
                                                </li>
                                                <li><a href="job-page.html">job page</a></li>
                                                <li><a href="login.html">login</a></li>
                                                <li><a href="register.html">register</a></li>
                                                <li><a href="contact.html">contact us</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="header-right-menu">
                                <ul>
                                    <li><a href="post-job.html" class="post-jobs">Post jobs</a></li>
                                    <li><a href="register.html"><i class="fa fa-user"></i>sign up</a></li>
                                    <li><a href="login.html"><i class="fa fa-lock"></i>login</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- Header Area End -->
</div>
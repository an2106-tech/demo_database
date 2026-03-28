<div>
    <!-- Breadcromb Area Start -->
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Messages</h3>
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
                                <li><a href="#">candidates</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.message') }}">Messages</a></li>
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
                <div class="col-lg-3 col-md-4  mx-auto dashboard-left-border">
                    <div class="dashboard-left">
                        <ul class="dashboard-menu">
                            <li>
                                <a href="{{ route('employers.dashboard') }}">
                                    <i class="fa fa-tachometer"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li><a href="{{ route('employers.candidate_profile') }}"><i class="fa fa-users"></i>My Profile</a></li>
                            <li class="active"><a href="{{ route('employers.message') }}"><i class="fa fa-envelope-open"></i>messages</a>
                            </li>
                            <li><a href="{{ route('employers.manage_jobs') }}"><i class="fa fa-briefcase"></i>manage jobs</a></li>
                            <li><a href="{{ route('employers.candidate_earnings') }}"><i class="fa fa-rocket"></i>earnings</a></li>
                            <li><a href="{{ route('employers.change_password') }}"><i class="fa fa-lock"></i>change password</a></li>
                            <li><a href="#"><i class="fa fa-power-off"></i>LogOut</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9 col-md-8  mx-auto">
                    <div class="dashboard-right message-page-box">
                        <div class="manage-jobs-heading">
                            <h3>New Messages</h3>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-md-12 ">
                                <div class="chat-list-left">
                                    <div class="chat-search-form">
                                        <form>
                                            <input type="search" placeholder="Search Contact">
                                            <button type="submit"><i class="fa fa-search"></i></button>
                                        </form>
                                    </div>
                                    <!-- End Chat Search -->
                                    <div class="chat-list-body">
                                        <ul class="chat-list">
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-1.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>David Johnson</h4>
                                                        <small class="online">Online</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <!-- end list -->
                                            <li class="clearfix active">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-2.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Aiden Chavez</h4>
                                                        <small class="away">Away</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <!-- end list -->
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-3.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Margaret Govan</h4>
                                                        <small class="busy">busy</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <!-- end list -->
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-4.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Emanual Doe</h4>
                                                        <small class="online">online</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <!-- end list -->
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-2.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Eric Alsobrook</h4>
                                                        <small class="online">online</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <!-- end list -->
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-4.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Christian Kelly</h4>
                                                        <small class="away">away</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <!-- end list -->
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-1.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>David Johnson</h4>
                                                        <small class="online">Online</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <!-- end list -->
                                            <li class="clearfix active">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-2.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Aiden Chavez</h4>
                                                        <small class="away">Away</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <!-- end list -->
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-3.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Margaret Govan</h4>
                                                        <small class="busy">busy</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <!-- end list -->
                                        </ul>
                                    </div>
                                    <!-- End Chat List Body -->
                                </div>
                            </div>
                            <!-- Col End -->
                            <div class="col-lg-8 col-md-12">
                                <div class="chat-board-right">
                                    <div class="chat-board-header">
                                        <div class="navbar">
                                            <div class="user-details-nav">
                                                <div class="chat-user-details">
                                                    <div class="pull-left chat-user-img">
                                                        <a href="#">
                                                            <img src="{{ asset('assets/img/msg-3.png') }}" alt="">
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="user-info pull-left">
                                                    <a href="#" title="Mike Litorus">
                                                        <h4>Mike Litorus</h4>
                                                    </a>
                                                    <small class="online">online</small>
                                                </div>
                                            </div>
                                            <ul class="nav navbar-nav pull-right custom-menu">
                                                <li class="dropdown">
                                                    <a href="#"><i class="fa fa-cog"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- End Chatbot Header -->
                                    <div class="chat-board-content">
                                        <div class="chat-box-wrapper">
                                            <div class="chat-box-inner">
                                                <ul class="chat-list">
                                                    <li>
                                                        <div class="chat-img">
                                                            <a href="#">
                                                                <img src="{{ asset('assets/img/msg-3.png') }}" alt="user">
                                                            </a>
                                                        </div>
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                It’s Great opportunity to work.
                                                            </div>
                                                            <div class="chat-time">10:57 am</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                simply dummy text of the printing and typesetting
                                                                industry. Lorem Ipsum has been the industry's
                                                            </div>
                                                            <div class="chat-action">seen</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                It was popularised in the 1960s
                                                            </div>
                                                            <div class="chat-action">seen</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="chat-img">
                                                            <a href="#">
                                                                <img src="{{ asset('assets/img/msg-3.png') }}" alt="user">
                                                            </a>
                                                        </div>
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                Ipsum has been the industry's standard
                                                            </div>
                                                            <div class="chat-time">10:57 am</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                It’s Great opportunity to work.
                                                            </div>
                                                            <div class="chat-action">seen</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="chat-img">
                                                            <a href="#">
                                                                <img src="{{ asset('assets/img/msg-3.png') }}" alt="user">
                                                            </a>
                                                        </div>
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                It’s Great opportunity to work.
                                                            </div>
                                                            <div class="chat-time">10:57 am</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                I like singing, playing basketballand so on.
                                                            </div>
                                                            <div class="chat-action">seen</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="chat-img">
                                                            <a href="#">
                                                                <img src="{{ asset('assets/img/msg-3.png') }}" alt="user">
                                                            </a>
                                                        </div>
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                It’s Great opportunity to work.
                                                            </div>
                                                            <div class="chat-time">10:57 am</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                It’s Great opportunity to work.
                                                            </div>
                                                            <div class="chat-action">seen</div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Chatbot COntent -->
                                    <div class="chat-footer">
                                        <div class="message-bar">
                                            <div class="message-bar-inner">
                                                <div class="attach-icon">
                                                    <p>
                                                        <i class="fa fa-paperclip"></i>
                                                    </p>
                                                    <input type="file">
                                                </div>
                                                <div class="message-text-area">
                                                    <form>
                                                        <textarea placeholder="Type message..."></textarea>
                                                        <button type="submit"><i class="fa fa-send"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Chat Footer -->
                                </div>
                            </div>
                            <!-- Col Start -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Candidate Dashboard Area End -->
</div>
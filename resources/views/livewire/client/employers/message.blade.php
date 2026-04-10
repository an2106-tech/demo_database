<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Tin nhắn</h3>
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
                                <li><a href="{{ route('home') }}">trang chủ</a></li>
                                <li><a href="#">ứng viên</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.message') }}">Tin nhắn</a></li>
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
                <div class="col-lg-3 col-md-4  mx-auto dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>
                <div class="col-lg-9 col-md-8  mx-auto">
                    <div class="dashboard-right message-page-box">
                        <div class="manage-jobs-heading">
                            <h3>Tin nhắn mới</h3>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-md-12 ">
                                <div class="chat-list-left">
                                    <div class="chat-search-form">
                                        <form>
                                            <input type="search" placeholder="Tìm kiếm liên hệ">
                                            <button type="submit"><i class="fa fa-search"></i></button>
                                        </form>
                                    </div>
                                    <div class="chat-list-body">
                                        <ul class="chat-list">
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-1.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>David Johnson</h4>
                                                        <small class="online">Đang trực tuyến</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="clearfix active">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-2.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Aiden Chavez</h4>
                                                        <small class="away">Vắng mặt</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-3.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Margaret Govan</h4>
                                                        <small class="busy">Đang bận</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-4.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Emanual Doe</h4>
                                                        <small class="online">Đang trực tuyến</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-2.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Eric Alsobrook</h4>
                                                        <small class="online">Đang trực tuyến</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-4.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Christian Kelly</h4>
                                                        <small class="away">Vắng mặt</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-1.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>David Johnson</h4>
                                                        <small class="online">Đang trực tuyến</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="clearfix active">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-2.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Aiden Chavez</h4>
                                                        <small class="away">Vắng mặt</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="clearfix">
                                                <a href="#">
                                                    <div class="chat-avatar-img">
                                                        <img src="{{ asset('assets/img/msg-3.png') }}" alt="avatar" />
                                                    </div>
                                                    <div class="chat-about">
                                                        <h4>Margaret Govan</h4>
                                                        <small class="busy">Đang bận</small>
                                                    </div>
                                                </a>
                                            </li>
                                            </ul>
                                    </div>
                                    </div>
                            </div>
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
                                                    <small class="online">Đang trực tuyến</small>
                                                </div>
                                            </div>
                                            <ul class="nav navbar-nav pull-right custom-menu">
                                                <li class="dropdown">
                                                    <a href="#"><i class="fa fa-cog"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
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
                                                                Đây là một cơ hội làm việc tuyệt vời.
                                                            </div>
                                                            <div class="chat-time">10:57 sáng</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                đây chỉ là văn bản giả của ngành in ấn và dàn trang. Lorem Ipsum đã là văn bản chuẩn của ngành này.
                                                            </div>
                                                            <div class="chat-action">đã xem</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                Nó đã trở nên phổ biến vào những năm 1960.
                                                            </div>
                                                            <div class="chat-action">đã xem</div>
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
                                                                Ipsum đã là tiêu chuẩn của ngành này.
                                                            </div>
                                                            <div class="chat-time">10:57 sáng</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                Đây là một cơ hội làm việc tuyệt vời.
                                                            </div>
                                                            <div class="chat-action">đã xem</div>
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
                                                                Đây là một cơ hội làm việc tuyệt vời.
                                                            </div>
                                                            <div class="chat-time">10:57 sáng</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                Tôi thích hát, chơi bóng rổ và nhiều thứ khác.
                                                            </div>
                                                            <div class="chat-action">đã xem</div>
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
                                                                Đây là một cơ hội làm việc tuyệt vời.
                                                            </div>
                                                            <div class="chat-time">10:57 sáng</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">
                                                                Đây là một cơ hội làm việc tuyệt vời.
                                                            </div>
                                                            <div class="chat-action">đã xem</div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
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
                                                        <textarea placeholder="Nhập tin nhắn..."></textarea>
                                                        <button type="submit"><i class="fa fa-send"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                            </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

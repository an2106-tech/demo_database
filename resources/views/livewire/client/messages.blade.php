<div>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('candidates.candidate_dashboard') }}">Ứng viên</a></li>
            <li class="active">Tin nhắn</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right message-page-box" style="padding: 28px;">
                        <div class="manage-jobs-heading" style="margin-bottom: 22px;">
                            <span style="display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:999px;background:rgba(243,112,33,.08);color:#9a3412;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Trò chuyện</span>
                            <h3 style="margin: 10px 0 0; color: #0f172a;">Tin nhắn mới</h3>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-4 col-md-12">
                                <div class="chat-list-left" style="background:#fff; border:1px solid #e5ebf3; border-radius:24px; box-shadow:0 18px 42px rgba(15,23,42,.05); overflow:hidden;">
                                    <div class="chat-search-form" style="padding: 18px;">
                                        <form class="position-relative">
                                            <input type="search" placeholder="Tìm kiếm liên hệ..." style="border-radius:16px; border:1px solid #d8e1eb; padding-right:48px;">
                                            <button type="submit" style="right:10px; top:50%; transform: translateY(-50%);">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="chat-list-body" style="max-height: 540px; overflow:auto;">
                                        <ul class="chat-list">
                                            @foreach ([
                                                ['name' => 'David Johnson', 'status' => 'Đang hoạt động', 'class' => 'online', 'avatar' => 'msg-1.png'],
                                                ['name' => 'Aiden Chavez', 'status' => 'Vắng mặt', 'class' => 'away', 'avatar' => 'msg-2.png'],
                                                ['name' => 'Margaret Govan', 'status' => 'Đang bận', 'class' => 'busy', 'avatar' => 'msg-3.png'],
                                                ['name' => 'Emanual Doe', 'status' => 'Đang hoạt động', 'class' => 'online', 'avatar' => 'msg-4.png'],
                                                ['name' => 'Eric Alsobrook', 'status' => 'Đang hoạt động', 'class' => 'online', 'avatar' => 'msg-2.png'],
                                            ] as $contact)
                                                <li class="clearfix {{ $loop->iteration === 2 ? 'active' : '' }}">
                                                    <a href="#">
                                                        <div class="chat-avatar-img">
                                                            <img src="{{ asset('assets/img/' . $contact['avatar']) }}" alt="{{ $contact['name'] }}" />
                                                        </div>
                                                        <div class="chat-about">
                                                            <h4>{{ $contact['name'] }}</h4>
                                                            <small class="{{ $contact['class'] }}">{{ $contact['status'] }}</small>
                                                        </div>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8 col-md-12">
                                <div class="chat-board-right" style="background:#fff; border:1px solid #e5ebf3; border-radius:24px; box-shadow:0 18px 42px rgba(15,23,42,.05); overflow:hidden;">
                                    <div class="chat-board-header" style="border-bottom:1px solid #edf2f7; padding: 18px 20px;">
                                        <div class="navbar">
                                            <div class="user-details-nav">
                                                <div class="chat-user-details">
                                                    <div class="pull-left chat-user-img">
                                                        <a href="#">
                                                            <img src="{{ asset('assets/img/msg-3.png') }}" alt="Mike Litorus">
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="user-info pull-left">
                                                    <a href="#" title="Mike Litorus">
                                                        <h4>Mike Litorus</h4>
                                                    </a>
                                                    <small class="online">Đang hoạt động</small>
                                                </div>
                                            </div>
                                            <ul class="nav navbar-nav pull-right custom-menu">
                                                <li class="dropdown">
                                                    <a href="#"><i class="fa fa-cog"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="chat-board-content" style="background:linear-gradient(180deg,#f8fbff 0%, #f7f8fb 100%);">
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
                                                            <div class="chat-text">Đó là một cơ hội tuyệt vời để làm việc.</div>
                                                            <div class="chat-time">10:57 sáng</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">Đây chỉ là văn bản giả trong ngành in ấn và thiết kế.</div>
                                                            <div class="chat-action">Đã xem</div>
                                                        </div>
                                                    </li>
                                                    <li class="chat-list-right">
                                                        <div class="chat-content">
                                                            <div class="chat-text">Nó đã trở nên phổ biến vào những năm 1960.</div>
                                                            <div class="chat-action">Đã xem</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="chat-img">
                                                            <a href="#">
                                                                <img src="{{ asset('assets/img/msg-3.png') }}" alt="user">
                                                            </a>
                                                        </div>
                                                        <div class="chat-content">
                                                            <div class="chat-text">Đã trở thành tiêu chuẩn của ngành.</div>
                                                            <div class="chat-time">10:57 sáng</div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="chat-footer" style="border-top:1px solid #edf2f7; background:#fff;">
                                        <div class="message-bar">
                                            <div class="message-bar-inner">
                                                <div class="attach-icon">
                                                    <p><i class="fa fa-paperclip"></i></p>
                                                    <input type="file">
                                                </div>
                                                <div class="message-text-area">
                                                    <form>
                                                        <textarea placeholder="Nhập tin nhắn..." style="border-radius:18px; border:1px solid #d8e1eb; min-height:64px; resize:none;"></textarea>
                                                        <button type="submit" style="border-radius:16px;"><i class="fa fa-send"></i></button>
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

<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Chi tiết bài viết</h3>
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
                                <li><a href="#">Trang</a></li>
                                <li class="active-breadcromb"><a href="#">Chi tiết bài viết</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="jobguru-blog-page-area section_70">
        <div class="container">
            <div class="row">
                <!-- Nội dung bài viết -->
                <div class="col-lg-8 col-sm-10 mx-auto">
                    <div class="single-blog-page-item">
                        @forelse($posts as $post)
                        <div class="single-blog-item-img">
                            <img src="{{ asset($post->image) }}" alt="{{ $post->title }}">
                        </div>

                        <div class="blog-meta d-flex align-items-center">
                            <div class="single-blog-item-date">
                                <h4>{{ $post->created_at->format('d') }}<span>Tháng {{ $post->created_at->format('m') }}</span></h4>
                            </div>
                            <div class="blog-title">
                                <h3>{{ $post->title }}</h3>
                                <p><i class="fa fa-user"></i> <a href="#">Quản trị viên</a></p>
                                <p><i class="fa fa-tag"></i> <a href="#">thức ăn nhanh</a></p>
                                <p><i class="fa fa-comments-o"></i> <a href="#">{{ $post->comments_count }}</a></p>
                            </div>
                        </div>

                        <div class="blog-content">
                            {!! $post->content !!}

                            {{-- <blockquote>
                                <div class="quote-inner">
                                    <i class="quote-icon fa fa-quote-right"></i>
                                    <div class="quote-text">
                                        Mọi lời giải thích đều hướng tới một cuộc sống hạnh phúc. Không ai thực sự khao khát nỗi đau vì chính nó, mà chỉ vì những hệ quả nảy sinh từ những hoàn cảnh nhất định.
                                    </div>
                                </div>
                            </blockquote> --}}

                            {{-- <ul class="company-desc-list">
                                <li><i class="fa fa-check"></i> Luôn chú trọng vào các chi tiết nhỏ nhất</li>
                                <li><i class="fa fa-check"></i> Đảm bảo chất lượng nội dung tốt nhất cho người đọc</li>
                                <li><i class="fa fa-check"></i> Cập nhật xu hướng thị trường thường xuyên</li>
                                <li><i class="fa fa-check"></i> Kết nối cộng đồng thông qua các bài chia sẻ</li>
                                <li><i class="fa fa-check"></i> Hỗ trợ giải đáp thắc mắc của độc giả nhanh chóng</li>
                                <li><i class="fa fa-check"></i> Tối ưu hóa trải nghiệm người dùng trên website</li>
                            </ul> --}}

                            {{-- <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p> --}}

                            <div class="share-this-post">
                                <h3>Chia sẻ bài viết này</h3>
                                <ul>
                                    <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                    <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
                                    <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                </ul>
                            </div>
                        </div>

                        @empty
                        <div class="col-12">
                            <p>Không có bài viết nào</p>
                        </div>
                        @endforelse

                        <!-- Comment -->
                        <div class="comment-box">
                            <h3>3 bình luận</h3>
                            <ul>
                                <!-- Comment mẫu -->
                                <li>
                                    <div class="single-work-history">
                                        <div class="single-candidate-list">
                                            <div class="main-comment">
                                                <div class="candidate-image">
                                                    <img alt="author" src="{{ asset('assets/img/msg-2.png') }}">
                                                </div>
                                                <div class="candidate-text">
                                                    <div class="candidate-info">
                                                        <div class="candidate-title">
                                                            <h3><a href="#">Jennie Wilson</a></h3>
                                                        </div>
                                                        <p><i class="fa fa-calendar-check-o"></i> 15 Tháng 1, 2018</p>
                                                    </div>
                                                    <div class="candidate-text-inner">
                                                        <p>Bài viết rất hữu ích, cảm ơn bạn đã chia sẻ.</p>
                                                        <a href="#" class="reply">Trả lời</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <!-- Thêm comment khác tương tự -->
                            </ul>
                        </div>

                        <div class="leave-comment">
                            <h3>Để lại bình luận</h3>
                            <form>
                                <input type="text" placeholder="Họ và tên" name="name" class="ns-com-name">
                                <input type="email" placeholder="Email" name="email" class="ns-com-name">
                                <textarea name="comment" placeholder="Bình luận..." class="comment"></textarea>
                                <button type="submit">Gửi bình luận</button>
                            </form>
                        </div>

                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4 col-sm-10 mx-auto">
                    <div class="blog-page-right">
                        <!-- Search -->
                        <div class="blog-sidebar-widget">
                            <form>
                                <input type="search" placeholder="Tìm kiếm...">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </form>
                        </div>

                        <!-- Social -->
                        <div class="blog-sidebar-widget">
                            <div class="blog-social-follow">
                                <a href="#" class="facebook-bg"><i class="fa fa-facebook"></i> 2.1k Fans</a>
                                <a href="#" class="twitter-bg"><i class="fa fa-twitter"></i> 811 Followers</a>
                                <a href="#" class="pinterest-bg"><i class="fa fa-pinterest"></i> 1.5k Fans</a>
                                <a href="#" class="instagram-bg"><i class="fa fa-instagram"></i> 5.2k Followers</a>
                                <a href="#" class="vk-bg"><i class="fa fa-vk"></i> 940k Followers</a>
                                <a href="#" class="youtube-bg"><i class="fa fa-youtube"></i> 2.2k Subscriber</a>
                            </div>
                        </div>

                        <!-- Bài viết liên quan -->
                        <div class="blog-sidebar-widget">
                            <h3>Bài viết liên quan</h3>
                            <ul class="featured-list">
                                @forelse($posts as $post)
                                <li class="sidebr-pro-widget">
                                    <div class="blog-thumb-info">
                                        <div class="blog-thumb-info-image">
                                            <a href="#">
                                                <img src="{{ asset($post->image) }}" alt="{{ $post->title }}">
                                            </a>
                                        </div>
                                        <div class="blog-thumb-info-content">
                                            <h4><a href="#">{{ $post->title }}</a></h4>
                                            <p>Đăng ngày: <a href="#">{{ $post->created_at->format('d/m/Y') }}</a></p>
                                        </div>
                                    </div>
                                </li>
                                @empty
                                <p>Không có bài viết liên quan</p>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
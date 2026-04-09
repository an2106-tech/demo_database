<div>
   <section class="jobguru-breadcromb-area">
      <div class="breadcromb-top section_100">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="breadcromb-box">
                     <h3>trang blog</h3>
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
                        <li><a href="#">trang chủ</a></li>
                        <li><a href="#">trang</a></li>
                        <li class="active-breadcromb"><a href="#">trang blog</a></li>
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
            <div class="col-lg-8 col-sm-10 mx-auto">
            @forelse($posts as $post)
               <div class="single-blog-page-item">
                  <div class="single-blog-item-img">
                     <a href="#">
                        <img src="{{ asset($post->image) }}" alt="{{ $post->title }}">
                     </a>
                  </div>
                  <div class="blog-meta d-flex align-items-center">
                     <div class="single-blog-item-date">
                        <h4>{{ $post->created_at->format('d/m') }}</h4>
                     </div>
                     <div class="blog-title">
                        <h3>
                           <a href="#">
                              {{$post->title}}
                           </a>
                        </h3>
                        <p>
                           <i class="fa fa-user"></i>
                           <a href="#">Quản trị viên</a>
                        </p>
                        <p>
                           <i class="fa fa-tag"></i>
                           <a href="#">thức ăn nhanh</a>
                        </p>
                        <p>
                           <i class="fa fa-comments-o"></i>
                           <a href="#">
                              {{$post->comments_count}}
                           </a>
                        </p>
                     </div>
                  </div>
                  <div class="blog-content">
                     <p>{{$post->excerpt}}</p>
                     <a href="#" class="jobguru-btn">tiếp tục đọc</a>
                  </div>
               </div>
               @empty
                    <div class="col-12">
                        <p>Không có bài viết nào</p>
                    </div>
                @endforelse
               <div class="pagination-box-row">
                  <p>Trang 1 trên 6</p>
                  <ul class="pagination">
                     <li class="active"><a href="#">1</a></li>
                     <li><a href="#">2</a></li>
                     <li><a href="#">3</a></li>
                     <li>...</li>
                     <li><a href="#">6</a></li>
                     <li><a href="#"><i class="fa fa-angle-double-right"></i></a></li>
                  </ul>
               </div>
            </div>
            <div class="col-lg-4 col-sm-10  mx-auto">
               <div class="blog-page-right">
                  <div class="blog-sidebar-widget">
                     <form>
                        <input type="search" placeholder="Tìm kiếm...">
                        <button type="submit"><i class="fa fa-search"></i></button>
                     </form>
                  </div>
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
                  <div class="blog-sidebar-widget">
                     <h3>Theo Danh mục</h3>
                     <ul class="blog-categories">
                        <li>
                           <a href="#">
                              <i class="fa fa-angle-double-right "></i>
                              kinh doanh <span>(23)</span>
                           </a>
                        </li>
                        <li>
                           <a href="#">
                              <i class="fa fa-angle-double-right "></i>
                              tư vấn <span>(12)</span>
                           </a>
                        </li>
                        <li>
                           <a href="#">
                              <i class="fa fa-angle-double-right "></i>
                              đối tác kinh doanh <span>(09)</span>
                           </a>
                        </li>
                        <li>
                           <a href="#">
                              <i class="fa fa-angle-double-right "></i>
                              Kiểm toán & bảo hiểm <span>(32)</span>
                           </a>
                        </li>
                        <li>
                           <a href="#">
                              <i class="fa fa-angle-double-right "></i>
                              đầu tư <span>(11)</span>
                           </a>
                        </li>
                     </ul>
                  </div>
                  <div class="blog-sidebar-widget">
                     <h3>thẻ</h3>
                     <ul class="Tags-catagory">
                        <li><a href="#">kinh doanh</a></li>
                        <li><a href="#">đầu tư </a></li>
                        <li><a href="#">Kiểm toán</a></li>
                        <li><a href="#">bảo hiểm</a></li>
                        <li><a href="#">tư vấn </a></li>
                        <li><a href="#">đối tác</a></li>
                        <li><a href="#">đời sống</a></li>
                        <li><a href="#">An ninh</a></li>
                        <li><a href="#">Camera</a></li>
                     </ul>
                  </div>
                  <div class="blog-sidebar-widget">
                     <h3>Bài viết liên quan</h3>
                     <ul class="featured-list">
                     @forelse($posts as $post)
                        <li class="sidebr-pro-widget">
                           <div class="blog-thumb-info">
                              <div class="blog-thumb-info-image">
                                 <a href="#">
                                    <img src="{{ asset($post->image) }}" />
                                 </a>
                              </div>
                              <div class="blog-thumb-info-content">
                                 <h4><a href="#">{{$post->title}}</a></h4>
                                 <p>Đăng ngày: <a href="#">{{ $post->created_at->format('d/m/Y') }}</a></p>
                              </div>
                           </div>
                        </li>
                         @empty
                    <div class="col-12">
                        <p>Không có bài viết nào</p>
                    </div>
                     @endforelse
                     </ul>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
   </div>
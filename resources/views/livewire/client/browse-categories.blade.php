<div>
    <style>
        .browse-category-page .browse-job-head-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            padding: 18px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .08);
            border: 1px solid rgba(226, 232, 240, .9);
        }

        .browse-category-page .job-browse-search {
            flex: 1;
            min-width: 280px;
        }

        .browse-category-page .job-browse-search form {
            display: flex;
            align-items: stretch;
        }

        .browse-category-page .job-browse-search input {
            height: 46px;
            border-radius: 12px 0 0 12px;
            border: 1px solid rgba(148, 163, 184, .35);
            border-right: 0;
            padding: 0 14px;
        }

        .browse-category-page .job-browse-search button {
            width: 58px;
            border-radius: 0 12px 12px 0;
            background: #2f7ff7;
            border: 1px solid #2f7ff7;
        }

        .browse-category-page .job-browse-search button:hover {
            background: #1f6fe8;
            border-color: #1f6fe8;
        }

        .browse-category-page .job-browse-action .btn-dropdown {
            height: 46px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, .35);
            background: #fff;
            padding: 0 14px;
            font-weight: 800;
            color: #0f172a;
        }

        .browse-category-page .category-card {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            min-height: 150px;
            padding: 22px 18px;
            border: 1px solid rgba(226, 232, 240, .9);
            background: linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .96));
            box-shadow: 0 22px 60px rgba(15, 23, 42, .10);
        }

        .browse-category-page .category-card .category-holder-icon,
        .browse-category-page .category-card .category-holder-text {
            position: relative;
            z-index: 1;
        }

        .browse-category-page .category-card .category-holder-icon i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: rgba(47, 127, 247, .10);
            color: #2f7ff7;
            font-size: 26px;
        }

        .browse-category-page .category-card .category-holder-text h3 {
            margin: 10px 0 0;
            font-size: 18px;
            line-height: 1.35;
            font-weight: 900;
        }

        .browse-category-page .category-card .category-sub {
            margin: 8px 0 0;
            color: rgba(15, 23, 42, .65);
            font-weight: 700;
            font-size: 13px;
        }

        .browse-category-page .category-card > img {
            z-index: 0;
            opacity: .16;
        }

        .browse-category-page .category-card:hover > img {
            opacity: .45;
        }
    </style>

    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
               <div class="row">
                  <div class="col-md-12">
                     <div class="breadcromb-box">
                        <h3>Duyệt theo danh mục</h3>
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
                           <li><a href="#">Ứng viên</a></li>
                           <li class="active-breadcromb"><a href="#">Duyệt danh mục</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <section class="jobguru-categories-area browse-category-page section_70">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="browse-job-head-option">
                     <div class="job-browse-search">
                        <form>
                           <input type="search" placeholder="Tìm kiếm việc làm tại đây...">
                           <button type="submit"><i class="fa fa-search"></i></button>
                        </form>
                     </div>
                     <div class="job-browse-action">
                        <div class="email-alerts">
                           <input type="checkbox" class="styled" id="b_1">
                           <label class="styled" for="b_1">Nhận thông báo email cho tìm kiếm này</label>
                        </div>
                        <div class="dropdown">
                           <button class="btn-dropdown dropdown-toggle" type="button" id="dropdowncur" data-bs-toggle="dropdown" aria-haspopup="true">Sắp xếp theo</button>
                           <ul class="dropdown-menu" aria-labelledby="dropdowncur">
                              <li>Mới nhất</li>
                              <li>Cũ nhất</li>
                              <li>Ngẫu nhiên</li>
                           </ul>
                        </div>
                    </div>

                    <div class="row">
                        @forelse ($categories as $category)
                            <div class="col-lg-3 col-md-6 col-sm-6" style="margin-top: 22px;">
                                <a href="{{ route('candidates.browse_job') }}" class="single-category-holder category-card">
                                    <div class="category-holder-icon">
                                        @php($icon = trim((string) ($category->icon ?? '')))
                                        <i class="{{ $icon !== '' ? (\Illuminate\Support\Str::startsWith($icon, 'bi') ? $icon : 'bi bi-' . $icon) : 'bi bi-grid' }}"></i>
                                    </div>
                                    <div class="category-holder-text">
                                        <h3>{{ $category->name }}</h3>
                                        
                                    </div>
                                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" />
                                </a>
                            </div>
                        @empty
                            <div class="col-md-12 text-center">
                                <h4>Chưa có danh mục nào</h4>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
         </div>
      </section>
      </div>
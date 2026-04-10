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
            background: rgba(248, 250, 252, .9);
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

        .browse-category-page .email-alerts {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border: 1px solid rgba(148, 163, 184, .28);
            border-radius: 12px;
            background: rgba(248, 250, 252, .7);
            height: 46px;
        }

        .browse-category-page .email-alerts label {
            margin: 0;
            font-weight: 700;
            color: rgba(15, 23, 42, .75);
            font-size: 13px;
            line-height: 1.2;
        }

        .browse-category-page .category-grid {
            margin-top: 22px;
        }

        .browse-category-page .category-card {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            min-height: 170px;
            padding: 22px 20px;
            border: 1px solid rgba(226, 232, 240, .9);
            background: linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .96));
            box-shadow: 0 22px 60px rgba(15, 23, 42, .10);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .browse-category-page .category-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(47, 127, 247, .18), rgba(16, 185, 129, .10));
            opacity: .65;
            pointer-events: none;
            z-index: 0;
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
            background: rgba(255, 255, 255, .92);
            color: #2f7ff7;
            font-size: 26px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .10);
        }

        .browse-category-page .category-card .category-holder-text h3 {
            margin: 10px 0 0;
            font-size: 18px;
            line-height: 1.35;
            font-weight: 900;
            color: #0f172a;
        }

        .browse-category-page .category-card .category-sub {
            margin: 8px 0 0;
            color: rgba(15, 23, 42, .65);
            font-weight: 700;
            font-size: 13px;
        }

        .browse-category-page .category-card > img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            opacity: .22;
        }

        .browse-category-page .category-card:hover > img {
            opacity: .35;
        }

        .browse-category-page .category-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 26px 70px rgba(15, 23, 42, .14);
            border-color: rgba(47, 127, 247, .35);
        }

        @media (max-width: 575px) {
            .browse-category-page .email-alerts label {
                display: none;
            }
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
                           <li><a href="{{ route('home') }}">Trang chủ</a></li>
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
                                <button class="btn-dropdown dropdown-toggle" type="button" id="dropdowncur" data-bs-toggle="dropdown" aria-haspopup="true" style="text-transform:none;">Sắp xếp theo</button>
                            <ul class="dropdown-menu" aria-labelledby="dropdowncur">
                               <li>Mới nhất</li>
                               <li>Cũ nhất</li>
                               <li>Ngẫu nhiên</li>
                            </ul>
                         </div>
                     </div>
                   </div>

                   <div class="row g-4 category-grid">
                       @forelse ($categories as $category)
                           <div class="col-lg-3 col-md-6 col-sm-6">
                               <a href="{{ route('candidates.browse_job', ['category_id' => $category->id]) }}" class="single-category-holder category-card">
                                   <div class="category-holder-icon">
                                       @php($icon = trim((string) ($category->icon ?? '')))
                                       <i class="{{ $icon !== '' ? (\Illuminate\Support\Str::startsWith($icon, 'bi') ? $icon : 'bi bi-' . $icon) : 'bi bi-grid' }}"></i>
                                   </div>
                                   <div class="category-holder-text">
                                       <h3>{{ $category->name }}</h3>
                                   </div>
                                   @php($categoryImage = trim((string) ($category->image ?? '')))
                                   <img src="{{ $categoryImage !== '' ? '/storage/' . ltrim($categoryImage, '/') : asset('assets/img/bg-3_3.jpg') }}"
                                       alt="{{ $category->name }}" loading="lazy" decoding="async" />
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

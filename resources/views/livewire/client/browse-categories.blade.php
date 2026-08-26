<div>
    <style>
        .browse-category-page .browse-job-head-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            padding: 18px 22px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 18px 60px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(226, 232, 240, 0.9);
        }

        .browse-category-page .job-browse-search {
            flex: 1;
            min-width: 280px;
        }

        .browse-category-page .job-browse-search form {
            position: relative;
            height: 50px;
            width: 100%;
            display: flex;
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
        }

        .browse-category-page .job-browse-search input {
            flex: 1;
            border: none;
            padding: 0 18px;
            font-size: 0.95rem;
            color: #0f172a;
            background: transparent;
            min-width: 0;
        }

        .browse-category-page .job-browse-search input:focus {
            outline: none;
            box-shadow: none;
        }

        .browse-category-page .job-browse-search button {
            width: 70px;
            border: none;
            background: #f37021;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .browse-category-page .job-browse-search button:hover {
            background: #d95a12;
        }

        .browse-category-page .job-browse-action {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .browse-category-page .email-alerts {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 14px;
            background: rgba(248, 250, 252, 0.95);
            height: 50px;
        }

        .browse-category-page .email-alerts label {
            margin: 0;
            font-weight: 600;
            color: #0f172a;
            font-size: 0.95rem;
            line-height: 1.3;
        }

        .browse-category-page .job-browse-action .dropdown button {
            height: 50px;
            border-radius: 14px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            background: #fff;
            padding: 0 18px;
            font-weight: 700;
            color: #0f172a;
            min-width: 180px;
            text-align: left;
        }

        .browse-category-page .job-browse-action .dropdown-menu li {
            padding: 10px 16px;
            font-size: 0.95rem;
        }

        .browse-category-page .category-grid {
            margin-top: 30px;
        }

        .browse-category-page .category-card {
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            min-height: 240px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            text-decoration: none;
            color: #fff;
            box-shadow: 0 18px 60px rgba(15, 23, 42, 0.12);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .browse-category-page .category-card__bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            transition: transform 0.5s ease;
        }

        .browse-category-page .category-card__overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(14, 25, 49, 0.18), rgba(14, 25, 49, 0.72));
            z-index: 1;
        }

        .browse-category-page .category-card__content {
            position: relative;
            z-index: 2;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .browse-category-page .category-card__icon {
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
        }

        .browse-category-page .category-card__icon i {
            font-size: 24px;
            color: #fff;
        }

        .browse-category-page .category-card__title {
            font-size: 1.25rem;
            font-weight: 800;
            line-height: 1.25;
            text-shadow: 0 16px 30px rgba(0, 0, 0, 0.22);
            margin: 0;
        }

        .browse-category-page .category-card__meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.88);
        }

        .browse-category-page .category-card__count {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
        }

        .browse-category-page .category-card__count i {
            font-size: 1rem;
        }

        .browse-category-page .category-card__arrow {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.18);
        }

        .browse-category-page .category-card__arrow i {
            color: #fff;
            font-size: 0.95rem;
        }

        .browse-category-page .category-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.18);
        }

        .browse-category-page .category-card:hover .category-card__bg {
            transform: scale(1.08);
        }

        @media (max-width: 767px) {
            .browse-category-page .browse-job-head-option {
                padding: 16px;
            }

            .browse-category-page .job-browse-action {
                justify-content: flex-start;
                width: 100%;
            }

            .browse-category-page .job-browse-action .dropdown button {
                width: 100%;
                text-align: left;
            }
        }
    </style>

    <div class="fpt-breadcrumb-bar">
        <div class="container">
            <div class="fpt-breadcrumb-inner">
                <ul class="fpt-breadcrumb-trail">
                    <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li class="current">Duyệt theo danh mục</li>
                </ul>

                <a href="{{ route('home') }}" class="fpt-back-btn">
                    <i class="fa fa-arrow-left"></i> Về trang chủ
                </a>
            </div>
        </div>
    </div>
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
                                   <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="category-card__bg" loading="lazy" decoding="async" />
                                   <div class="category-card__overlay"></div>
                                   <div class="category-card__content">
                                       <div class="category-card__icon">
                                           <i class="{{ $category->icon_class ?: 'fa fa-briefcase' }}"></i>
                                       </div>
                                       <div>
                                           <h3 class="category-card__title">{{ $category->name }}</h3>
                                       </div>
                                       <div class="category-card__meta">
                                           <div class="category-card__count"><i class="fa fa-briefcase"></i> {{ $category->recruitment_jobs_count ?? 0 }} việc</div>
                                           <div class="category-card__arrow"><i class="fa fa-arrow-right"></i></div>
                                       </div>
                                   </div>
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

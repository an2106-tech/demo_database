<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
               <div class="row">
                  <div class="col-md-12">
                     <div class="breadcromb-box">
                        <h3>Browse categories</h3>
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
                           <li><a href="#">home</a></li>
                           <li><a href="#">candidates</a></li>
                           <li class="active-breadcromb"><a href="#">browse categories</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <!-- Breadcromb Area End -->
       
       
      <!-- Categories Area Start -->
      <section class="jobguru-categories-area browse-category-page section_70">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="browse-job-head-option">
                     <div class="job-browse-search">
                        <form>
                           <input type="search" placeholder="Search Jobs Here...">
                           <button type="submit"><i class="fa fa-search"></i></button>
                        </form>
                     </div>
                     <div class="job-browse-action">
                        <div class="email-alerts">
                           <input type="checkbox" class="styled" id="b_1">
                           <label class="styled" for="b_1">email alerts for this search</label>
                        </div>
                        <div class="dropdown">
                           <button class="btn-dropdown dropdown-toggle" type="button" id="dropdowncur" data-bs-toggle="dropdown" aria-haspopup="true">Sort By</button>
                           <ul class="dropdown-menu" aria-labelledby="dropdowncur">
                              <li>Newest</li>
                              <li>Oldest</li>
                              <li>Random</li>
                           </ul>
                        </div>
                    </div>

                    <div class="row">
                        @forelse ($departments as $department)
                            <div class="col-lg-4 col-md-6">
                                <div class="single-blog" style="margin-bottom: 24px;">
                                    <div class="blog-text" style="padding: 18px 18px 16px;">
                                        <h3 style="margin-bottom: 10px;">
                                            <a href="{{ route('candidates.browse_job') }}">
                                                {{ $department->name }}
                                            </a>
                                        </h3>
                                        <p style="margin: 0; color: #64748b;">
                                            {{ number_format($department->jobs_count ?? 0) }} việc làm
                                        </p>
                                    </div>
                                </div>
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
      <!-- Categories Area End -->
</div>

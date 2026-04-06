<div>
      <!-- Breadcromb Area Start -->
      <section class="jobguru-breadcromb-area">
         <div class="breadcromb-top section_100">
            <div class="container">
               <div class="row">
                  <div class="col-md-12">
                     <div class="breadcromb-box">
                        <h3>Browse Jobs</h3>
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
                           <li class="active-breadcromb"><a href="#">browse jobs</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <!-- Breadcromb Area End -->
       
       
      <!-- Top Job Area Start -->
      <section class="jobguru-top-job-area browse-page section_70">
         <div class="container">
            <div class="row">
               <div class="col-md-10 col-lg-3 mx-auto">
                  <div class="job-grid-sidebar">
                     <!-- Single Job Sidebar Start -->
                     <div class="single-job-sidebar sidebar-location">
                        <h3>location</h3>
                        <div class="job-sidebar-box">
                           <form>
                              <p>
                                 <input type="search" placeholder="Location">
                              </p>
                              <p class="location-value">
                                 <input type="text" value="50">
                              </p>
                              <div class="dropdown">
                                 <button class="btn-dropdown dropdown-toggle" type="button" id="location" data-bs-toggle="dropdown" aria-haspopup="true">km</button>
                                 <ul class="dropdown-menu" aria-labelledby="location">
                                    <li>km</li>
                                    <li>miles</li>
                                 </ul>
                              </div>
                           </form>
                        </div>

                        <div class="single-job-sidebar sidebar-category">
                            <h3>Danh mục</h3>
                            <div class="job-sidebar-box">
                                <form>
                                    <select class="sidebar-category-select-2" name="department_id">
                                        <option value="">Tất cả danh mục</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                     </div>
                     <!-- Single Job Sidebar End -->
                      
                     <!-- Single Job Sidebar Start -->
                     <div class="single-job-sidebar sidebar-category">
                        <h3>Category</h3>
                        <div class="job-sidebar-box">
                           <form>
                              <select class="sidebar-category-select-2" name="states[]">
                                 <option value="1">any category</option>
                                 <option value="2">accounting/finance</option>
                                 <option value="3">automotive jobs</option>
                                 <option value="4">construction</option>
                                 <option value="5">design, art & multimedia</option>
                                 <option value="6">education training</option>
                                 <option value="7">restaurent/food</option>
                                 <option value="7">programming/tech</option>
                                 <option value="7">sales/marketing</option>
                                 <option value="7">data science/analysis</option>
                              </select>
                           </form>
                        </div>
                     </div>
                     <!-- Single Job Sidebar End -->
                      
                     <!-- Single Job Sidebar Start -->
                     <div class="single-job-sidebar sidebar-location">
                        <h3>Date Posted</h3>
                        <div class="date-post-job job-sidebar-box">
                           <div class="form-group form-radio">
                              <input id="last_hour" name="radio" type="radio">
                              <label for="last_hour" class="inline control-label">last hour</label>
                           </div>
                           <div class="form-group form-radio">
                              <input id="last_24" name="radio" type="radio">
                              <label for="last_24" class="inline control-label">Last 24 hours</label>
                           </div>
                           <div class="form-group form-radio">
                              <input id="last_7" name="radio" type="radio">
                              <label for="last_7" class="inline control-label">Last 7 days</label>
                           </div>
                           <div class="form-group form-radio">
                              <input id="last_14" name="radio" type="radio">
                              <label for="last_14" class="inline control-label">Last 14 days</label>
                           </div>
                           <div class="form-group form-radio">
                              <input id="last_30" name="radio" type="radio">
                              <label for="last_30" class="inline control-label">Last 30 days</label>
                           </div>
                           <div class="form-group form-radio">
                              <input id="last_all" name="radio" type="radio">
                              <label for="last_all" class="inline control-label">all</label>
                           </div>
                        </div>
                     </div>
                     <!-- Single Job Sidebar End -->
                      
                     <!-- Single Job Sidebar Start -->
                     <div class="single-job-sidebar sidebar-type">
                        <h3>job type</h3>
                        <div class="job-sidebar-box">
                           <ul>
                              <li class="checkbox">
                                 <input class="checkbox-spin" type="checkbox" id="Freelance" />
                                 <label for="Freelance"><span></span>Freelance</label>
                              </li>
                              <li class="checkbox">
                                 <input class="checkbox-spin" type="checkbox" id="Full_Time" />
                                 <label for="Full_Time"><span></span>Full Time</label>
                              </li>
                              <li class="checkbox">
                                 <input class="checkbox-spin" type="checkbox" id="Internship" />
                                 <label for="Internship"><span></span>Internship</label>
                              </li>
                              <li class="checkbox">
                                 <input class="checkbox-spin" type="checkbox" id="Part_Time" />
                                 <label for="Part_Time"><span></span>Part Time</label>
                              </li>
                              <li class="checkbox">
                                 <input class="checkbox-spin" type="checkbox" id="Temporary" />
                                 <label for="Temporary"><span></span>Temporary</label>
                              </li>
                           </ul>
                        </div>
                     </div>
                     <!-- Single Job Sidebar End -->
                      
                     <!-- Single Job Sidebar Start -->
                     <div class="single-job-sidebar sidebar-salary">
                        <h3>Filter by Salary</h3>
                        <div class="job-sidebar-box">
                           <p>
                              <input type="text" id="amount" readonly>
                           </p>
                           <div id="slider"></div>
                        </div>
                     </div>
                     <!-- Single Job Sidebar End -->
                      
                  </div>
               </div>
               <div class="col-md-10 col-lg-9 mx-auto">
                  <div class="job-grid-right">
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
                     </div>
                     <!-- end job head -->
                     <div class="job-sidebar-list-single">
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-1.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">Regional Sales Manager</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 minutes ago</p>
                                 <p class="varify"><i class="fa fa-check"></i>Fixed price : $1200-$2000</p>
                                 <p class="rating-company">4.1</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">bid now</a>
                              </div>
                           </div>
                        </div>
                        <!-- end sidebar single list -->
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-4.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">C Developer (Senior) C .Net</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 minutes ago</p>
                                 <p class="varify"><i class="fa fa-check"></i>Fixed price : $800-$1200</p>
                                 <p class="rating-company">3.1</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">bid now</a>
                              </div>
                           </div>
                        </div>
                        <!-- end sidebar single list -->
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-2.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">Regional Sales Manager</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 minutes ago</p>
                                 <p class="varify"><i class="fa fa-check"></i>Fixed price : $1200-$2000</p>
                                 <p class="rating-company">4.1</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">bid now</a>
                              </div>
                           </div>
                        </div>
                        <!-- end sidebar single list -->
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-3.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">Asst. Teacher</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 minutes ago</p>
                                 <p class="varify"><i class="fa fa-check"></i>Fixed price : $800-$1200</p>
                                 <p class="rating-company">4.2</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">bid now</a>
                              </div>
                           </div>
                        </div>
                        <!-- end sidebar single list -->
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-2.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">civil engineer</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 minutes ago</p>
                                 <p class="varify"><i class="fa fa-check"></i>Fixed price : $800-$1200</p>
                                 <p class="rating-company">4.2</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">bid now</a>
                              </div>
                           </div>
                        </div>
                        <!-- end sidebar single list -->
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-3.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">Asst. Teacher</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 minutes ago</p>
                                 <p class="varify"><i class="fa fa-check"></i>Fixed price : $800-$1200</p>
                                 <p class="rating-company">4.2</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">bid now</a>
                              </div>
                           </div>
                        </div>
                        <!-- end sidebar single list -->
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-1.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">Regional Sales Manager</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 minutes ago</p>
                                 <p class="varify"><i class="fa fa-check"></i>Fixed price : $1200-$2000</p>
                                 <p class="rating-company">4.1</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">bid now</a>
                              </div>
                           </div>
                        </div>
                        <!-- end sidebar single list -->
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-4.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">C Developer (Senior) C .Net</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 minutes ago</p>
                                 <p class="varify"><i class="fa fa-check"></i>Fixed price : $800-$1200</p>
                                 <p class="rating-company">3.1</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">bid now</a>
                              </div>
                           </div>
                        </div>
                        <!-- end sidebar single list -->
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-3.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">Asst. Teacher</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 minutes ago</p>
                                 <p class="varify"><i class="fa fa-check"></i>Fixed price : $800-$1200</p>
                                 <p class="rating-company">4.2</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">bid now</a>
                              </div>
                           </div>
                        </div>
                        <!-- end sidebar single list -->
                        <div class="sidebar-list-single">
                           <div class="top-company-list">
                              <div class="company-list-logo">
                                 <a href="#">
                                 <img src="{{ asset('assets/img/company-logo-2.png') }}" alt="company list 1">
                                 </a>
                              </div>
                              <div class="company-list-details">
                                 <h3><a href="#">civil engineer</a></h3>
                                 <p class="company-state"><i class="fa fa-map-marker"></i> Chicago, Michigan</p>
                                 <p class="open-icon"><i class="fa fa-clock-o"></i>2 minutes ago</p>
                                 <p class="varify"><i class="fa fa-check"></i>Fixed price : $800-$1200</p>
                                 <p class="rating-company">4.2</p>
                              </div>
                              <div class="company-list-btn">
                                 <a href="#" class="jobguru-btn">bid now</a>
                              </div>
                           </div>
                        </div>
                        <!-- end sidebar single list -->
                     </div>
                     <!-- end job sidebar list -->
                     <div class="pagination-box-row">
                        <p>Page 1 of 6</p>
                        <ul class="pagination">
                           <li class="active"><a href="#">1</a></li>
                           <li><a href="#">2</a></li>
                           <li><a href="#">3</a></li>
                           <li>...</li>
                           <li><a href="#">6</a></li>
                           <li><a href="#"><i class="fa fa-angle-double-right"></i></a></li>
                        </ul>
                     </div>
                     <!-- end pagination -->
                  </div>
               </div>
            </div>
         </div>
      </section>
      <!-- Top Job Area End -->
</div>
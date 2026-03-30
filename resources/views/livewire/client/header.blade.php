<div>
   <!-- Header Area Start -->
   <header class="jobguru-header-area stick-top forsticky page-header">
      <div class="menu-animation">
         <div class="container-fluid">
            <div class="row">
               <div class="col-lg-2">
                  <div class="site-logo">
                     <a href="/">
                        <img src="{{ asset('assets/img/logo-2.png') }}" alt="jobguru" />
                     </a>
                  </div>
                  <div class="col-lg-6">
                     <div class="header-menu">
                        <nav id="navigation">
                           <ul id="jobguru_navigation">
                              <li class="active has-children">
                                 <a href="/">home</a>
                               
                              </li>
                              <li class=" has-children">
                                 <a href="#">for candidates</a>
                           </li>
                           <li class=" has-children">
                              <a href="#">for candidates</a>
                              <ul>
                                 <li class="has-inner-child">
                                    <a href="#">browse jobs</a>
                                    <ul>
                                       <a href="{{ route('candidates.browse_job') }}">Browse Jobs</a>
                                       <a href="{{ route('candidates.sidebar') }}">grid sidebar</a>
                                       <li><a href="{{ route('candidates.joblist_sidebar') }}">list sidebar</a></li>
                                    </ul>
                                 </li>
                                 <li><a href="{{ route('candidates.browse_categories') }}">Browse Categories</a></li>
                                 <li><a href="{{ route('candidates.browse_companies') }}">browse companies</a></li>
                                 <li><a href="{{ route('candidates.candidate_detail') }}">candidates details</a></li>
                                 <li><a href="{{ route('candidates.submit_resume') }}">submit resume</a></li>
                                 <li class="has-inner-child">
                                    <a href="#">candidate dashboard</a>
                                    <ul>
                                       <li><a href="{{ route('candidates.candidate_dashboard') }}">Candidate
                                             dashboard</a></li>
                                       <li><a href="{{ route('candidates.candidate_profile') }}">Candidate profile</a>
                                       </li>
                                       <li><a href="{{ route('candidates.messages') }}">messages</a></li>
                                       <li><a href="{{ route('candidates.manage_jobs') }}">manage jobs</a></li>
                                       <li>
                                          <a href="{{ route('candidates.earnings') }}">Earnings</a>
                                       </li>
                                       <li><a href="{{ route('candidates.change_password') }}">change password</a></li>
                                    </ul>
                                 </li>
                              </ul>       
                              
                           </li>
                           <li class="has-children">
                              <a href="#">for employers</a>
                              <ul>
                                 <li><a href="{{ route('employers.browse') }}">Browse Candidates</a></li>
                                 <li><a href="{{ route('employers.single_company') }}">company details</a></li>
                                 <li><a href="{{ route('employers.post_job') }}">Post A job</a></li>
                                 <li class="has-inner-child">
                                    <a href="#">employer dashboard</a>
                                    <ul>
                                       <li><a href="{{ route('employers.dashboard') }}">employer dashboard</a></li>
                                       <li><a href="{{ route('employers.company_profile') }}">company profile</a></li>
                                       <li><a href="message.html">messages</a></li>
                                       <li><a href="manage-candidates.html">manage candidates</a></li>
                                       <li><a href="transaction.html">transaction</a></li>
                                       <li><a href="change-password.html">change password</a></li>
                                    </ul>
                                 </li>
                              </ul>
                           </li>
                           <li class="has-children">
                              <a href="#">pages</a>
                              <ul>
                                 <li><a href="{{ route('pages.about') }}">About us</a></li>
                                 <li class="has-inner-child">
                                    <a href="#">blog</a>
                                    <ul>
                                       <li><a href="{{ route('pages.blog') }}">blog</a></li>
                                       <li><a href="{{ route('pages.single') }}">single blog</a></li>
                                    </ul>
                                 </li>
                                 <li><a href="{{ route('pages.job') }}">job page</a></li>
                                 <li><a href="{{ route('pages.login') }}">login</a></li>
                                 <li><a href="{{ route('pages.register') }}">register</a></li>
                                 <li><a href="{{ route('pages.contact') }}">contact us</a></li>
                              </ul>
                           </li>
                        </ul>
                     </nav>
                  </div>
               </div>
               <div class="col-lg-4">
                  <div class="header-right-menu">
                     <ul>
                        <li><a href="{{route('auth.post_jobs')}}" class="post-jobs">Post jobs</a></li>
                        <li><a href="{{ route('auth.sign_up') }}"><i class="fa fa-user"></i>Sign up</a></li>
                        <li><a href="{{ route('auth.login') }}"><i class="fa fa-lock"></i>Login</a></li>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </header>
   <!-- Header Area End -->
</div>

<div>
    <!-- Breadcromb Area Start -->
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Manage Candidates</h3>
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
                                <li><a href="{{ route('home') }}">home</a></li>
                                <li><a href="{{ route('employers.dashboard') }}">employer</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.manage_candidates') }}">Manage Candidates</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcromb Area End -->


    <!-- Candidate Dashboard Area Start -->
    <section class="candidate-dashboard-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-lg-3  mx-auto dashboard-left-border">
                    <div class="dashboard-left">
                        <ul class="dashboard-menu">
                            <li>
                                <a href="{{ route('employers.dashboard') }}">
                                    <i class="fa fa-tachometer"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li><a href="{{ route('employers.company_profile') }}"><i class="fa fa-users"></i>Company Profile</a></li>
                            <li><a href="{{ route('employers.message') }}"><i class="fa fa-envelope-open"></i>messages</a></li>
                            <li><a href="{{ route('employers.post_job') }}"><i class="fa fa-envelope-open"></i>post a job</a></li>
                            <li class="active"><a href="{{ route('employers.manage_candidates') }}"><i class="fa fa-briefcase"></i>manage
                                    candidates</a></li>
                            <li><a href="{{ route('employers.transaction') }}"><i class="fa fa-rocket"></i>transaction</a></li>
                            <li><a href="{{ route('employers.change_password') }}"><i class="fa fa-lock"></i>change password</a></li>
                            <li><a href="#"><i class="fa fa-power-off"></i>LogOut</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-8 col-lg-9 mx-auto">
                    <div class="dashboard-right">
                        <div class="manage-jobs manage-candidates">
                            <div class="manage-jobs-heading">
                                <h3>Manage Candidates</h3>
                            </div>
                        </div>
                        <div class="candidate-list-page">
                            @forelse ($candidates as $candidate)
                            <div class="single-candidate-list">
                                <div class="main-comment">
                                    <div class="candidate-image">
                                        <img src="{{ asset('assets/img/avatar_detail.jpg') }}" alt="tác giả">
                                    </div>
                                    <div class="candidate-text">
                                        <div class="candidate-info">
                                            <div class="candidate-title">
                                                <h3><a href="#">{{ $candidate->name }}</a></h3>
                                                <img src="{{ asset('assets/img/de.svg') }}" alt="vùng miền">
                                            <p>UI/UX Designer</p>
                                        </div>
                                                <li><i class="fa fa-star"></i></li>
                                                <li><i class="fa fa-star"></i></li>
                                                <li><i class="fa fa-star"></i></li>
                                                <li><i class="fa fa-star-half-o"></i></li>
                                            </ul>
                                        </div>
                                        <div class="candidate-text-bottom">
                                            <div class="candidate-text-box">
                                                <p class="open-icon"><i class="fa fa-thumbs-up"></i> 100% job success
                                                </p>
                                                <p class="company-state"><i class="fa fa-map-marker"></i> Berlin</p>
                                                <p class="varify"><i class="fa fa-check"></i> $50 / hr</p>
                                            </div>
                                            <div class="candidate-action">
                                                <a href="#" class="jobguru-btn-2">Approve</a>
                                                <a href="#" class="jobguru-btn-danger">Cancel</a>
                                            </div>
                                        </div>
                                        <div class="remove-icon">
                                            <a href="#"><i class="fa fa-times"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                                <p>Không có ứng viên nào.</p>
                            @endforelse
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
                            <!-- End Pagination -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Candidate Dashboard Area End -->
</div>
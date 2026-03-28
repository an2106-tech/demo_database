<div>
    <!-- Breadcromb Area Start -->
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Transaction</h3>
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
                                <li class="active-breadcromb"><a href="{{ route('employers.transaction') }}">Transaction</a></li>
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
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    <div class="dashboard-left">
                        <ul class="dashboard-menu">
                            <li>
                                <a href="employer-dashboard.html">
                                    <i class="fa fa-tachometer"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li><a href="{{ route('employers.company_profile') }}"><i class="fa fa-users"></i>Company Profile</a></li>
                            <li><a href="{{ route('employers.message') }}"><i class="fa fa-envelope-open"></i>messages</a></li>
                            <li><a href="{{ route('employers.post_job') }}"><i class="fa fa-envelope-open"></i>post a job</a></li>
                            <li><a href="{{ route('employers.manage_candidates') }}"><i class="fa fa-briefcase"></i>manage candidates</a>
                            </li>
                            <li class="active"><a href="{{ route('employers.transaction') }}"><i class="fa fa-rocket"></i>transaction</a>
                            </li>
                            <li><a href="change-password.html"><i class="fa fa-lock"></i>change password</a></li>
                            <li><a href="#"><i class="fa fa-power-off"></i>LogOut</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right">
                        <div class="manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>Transaction History</h3>
                            </div>
                            <div class="single-manage-jobs table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Package</th>
                                            <th>Payment Date</th>
                                            <th>Payment Method</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Frontend React Developer</a></td>
                                            <td class="table-date">28 June, 2018</td>
                                            <td>Bank Wire Transfer</td>
                                            <td>$197.00</td>
                                            <td><span class="pending">Approved</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Node.js Developer</a></td>
                                            <td class="table-date">12 June, 2018</td>
                                            <td>Paypal</td>
                                            <td>$210.50</td>
                                            <td><span class="pending">Approved</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Full Stack PHP Developer </a></td>
                                            <td class="table-date">29 May, 2018</td>
                                            <td>Bank Transfer</td>
                                            <td>$122.00</td>
                                            <td><span class="expired">Pending Approval</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Frontend React Developer</a></td>
                                            <td class="table-date">14 May, 2018</td>
                                            <td>Bank Wire Transfer</td>
                                            <td>$197.00</td>
                                            <td><span class="pending">Approved</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Full Stack PHP Developer </a></td>
                                            <td class="table-date">29 May, 2018</td>
                                            <td>Payoneer</td>
                                            <td>$122.00</td>
                                            <td><span class="expired">Pending Approval</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Frontend React Developer</a></td>
                                            <td class="table-date">28 June, 2018</td>
                                            <td>Bank Wire Transfer</td>
                                            <td>$197.00</td>
                                            <td><span class="pending">Approved</span></td>
                                        </tr>
                                        <tr>
                                            <td class="manage-jobs-title"><a href="#">Full Stack PHP Developer </a></td>
                                            <td class="table-date">29 May, 2018</td>
                                            <td>Swift</td>
                                            <td>$122.00</td>
                                            <td><span class="expired">Pending Approval</span></td>
                                        </tr>
                                    </tbody>
                                </table>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Candidate Dashboard Area End -->
</div>
<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Quản lý tin tuyển dụng</h3>
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
                                <li><a href="{{ route('employers.dashboard') }}">Trang chủ</a></li>
                                <li><a href="{{ route('employers.post_job') }}">Đăng tin</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.manage_jobs') }}">Quản lý tin đăng</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="candidate-dashboard-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    <div class="dashboard-left">
                        <ul class="dashboard-menu">
                            <li><a href="{{ route('employers.dashboard') }}"><i class="fa fa-tachometer"></i>Bảng điều khiển</a></li>
                            <li><a href="{{ route('employers.company_profile') }}"><i class="fa fa-users"></i>Hồ sơ công ty</a></li>
                            <li><a href="{{ route('employers.message') }}"><i class="fa fa-envelope-open"></i>Tin nhắn</a></li>
                            <li><a href="{{ route('employers.post_job') }}"><i class="fa fa-bullhorn"></i>Đăng tin tuyển dụng</a></li>
                            <li class="active"><a href="{{ route('employers.manage_jobs') }}"><i class="fa fa-briefcase"></i>Quản lý tin đăng</a></li>
                            <li><a href="{{ route('employers.manage_candidates') }}"><i class="fa fa-user-circle"></i>Quản lý ứng viên</a></li>
                            <li><a href="{{ route('employers.change_password') }}"><i class="fa fa-lock"></i>Đổi mật khẩu</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right">
                        <div class="manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>Danh sách tin đã tạo</h3>
                                <p style="margin: 10px 0 0; color: #6b7280;">
                                    Xem nhanh trạng thái tin đăng, hạn nộp và chi nhánh đang sử dụng.
                                </p>
                            </div>

                            @if (session('status'))
                                <div class="alert alert-success" style="margin-bottom: 24px;">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <div class="single-manage-jobs table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Tiêu đề</th>
                                            <th>Chi nhánh</th>
                                            <th>Ngày tạo</th>
                                            <th>Hạn nộp</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($jobs as $job)
                                            <tr>
                                                <td class="manage-jobs-title">
                                                    <a href="{{ route('employers.job_detail', $job->id) }}">{{ $job->title }}</a>
                                                    <div style="margin-top: 6px; color: #64748b; font-size: 13px;">
                                                        {{ $job->department?->name ?? 'Chưa gán phòng ban' }}
                                                        @if ($job->workplace)
                                                            | {{ $job->workplace->name }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>{{ $job->branch?->name ?? '-' }}</td>
                                                <td class="table-date">{{ optional($job->created_at)->format('d/m/Y') }}</td>
                                                <td class="table-date">{{ optional($job->deadline)->format('d/m/Y') ?? 'Không giới hạn' }}</td>
                                                <td>
                                                    @php
                                                        $statusClass = match((string) $job->status?->value) {
                                                            'published' => 'pending',
                                                            'expired', 'closed' => 'expired',
                                                            default => 'pending',
                                                        };
                                                        $statusLabel = $job->status?->getLabel()
                                                            ?? ucfirst($job->status instanceof \BackedEnum ? $job->status->value : (string) $job->status);
                                                    @endphp
                                                    <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" style="text-align: center; padding: 32px 16px;">
                                                    Bạn chưa có tin tuyển dụng nào. <a href="{{ route('employers.post_job') }}">Đăng tin đầu tiên ngay</a>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

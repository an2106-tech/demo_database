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
                    @include('livewire.client.partials.employer-sidebar')
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



                            <div class="single-manage-jobs table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Tiêu đề</th>
                                            <th>Chi nhánh</th>
                                            <th>Ngày tạo</th>
                                            <th>Hạn nộp</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
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
                                                <td style="white-space: nowrap;">
                                                    <a href="{{ route('employers.edit_job', ['id' => $job->id]) }}" class="btn btn-sm" style="color: #ff7800; background: rgba(255, 120, 0, 0.1); border: 1px solid #ff7800; border-radius: 6px; padding: 6px 12px; margin-right: 8px; font-size: 13px; font-weight: 500; transition: all 0.3s; box-shadow: 0 2px 4px rgba(255,120,0,0.1);">
                                                        <i class="fa fa-pencil" style="margin-right: 4px;"></i> Sửa
                                                    </a>
                                                    <button type="button" onclick="confirmDeleteJob({{ $job->id }});" class="btn btn-sm" style="color: #dc3545; background: rgba(220, 53, 69, 0.1); border: 1px solid #dc3545; border-radius: 6px; padding: 6px 12px; border: none; font-size: 13px; font-weight: 500; transition: all 0.3s; box-shadow: 0 2px 4px rgba(220,53,69,0.1);">
                                                        <i class="fa fa-trash-o" style="margin-right: 4px;"></i> Xoá
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" style="text-align: center; padding: 32px 16px;">
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

    <script>
        function confirmDeleteJob(jobId) {
            Swal.fire({
                title: 'Lưu ý!',
                text: "Bạn có chắc chắn muốn xoá tin đăng này? Hành động này không thể hoàn tác.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-trash-o"></i> Vâng, xoá ngay',
                cancelButtonText: 'Huỷ'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('deleteJob', jobId);
                }
            })
        }
    </script>
</div>

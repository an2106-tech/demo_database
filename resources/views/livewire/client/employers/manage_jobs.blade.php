<div>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li class="active">Quản lý tin đăng</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right">
                        <div class="premium-panel">
                            <div class="manage-jobs-heading">
                                <h3>Danh sách tin đã tạo</h3>
                                <p style="margin: 10px 0 0; color: #64748b;">
                                    Theo dõi trạng thái, hiệu chỉnh hoặc đóng các tin tuyển dụng của bạn.
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
                                                            'published' => 'published',
                                                            'pending' => 'pending',
                                                            'expired', 'closed' => 'expired',
                                                            default => 'draft',
                                                        };
                                                        $statusLabel = $job->status?->getLabel()
                                                            ?? ucfirst($job->status instanceof \BackedEnum ? $job->status->value : (string) $job->status);
                                                    @endphp
                                                    <span class="badge rounded-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                                                </td>
                                                <td style="white-space: nowrap;">
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('employers.edit_job', ['id' => $job->id]) }}" class="btn btn-sm" style="color: var(--fpt-orange); background: rgba(243, 112, 33, 0.08); border: 1px solid rgba(243, 112, 33, 0.2); border-radius: 8px; padding: 6px 12px; font-size: 13px; font-weight: 600; transition: all 0.3s;">
                                                            <i class="fa fa-pencil"></i> Sửa
                                                        </a>
                                                        <button type="button" onclick="confirmDeleteJob({{ $job->id }});" class="btn btn-sm" style="color: #ef4444; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 8px; padding: 6px 12px; font-size: 13px; font-weight: 600; transition: all 0.3s;">
                                                            <i class="fa fa-trash-o"></i> Xoá
                                                        </button>
                                                    </div>
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

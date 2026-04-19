<div>
    <div class="employer-page-head">
        <h1>Duyệt Tin</h1>
        <p>Xem và duyệt các tin tuyển dụng.</p>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-lg-3 dashboard-left-border" style="overflow: visible;">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right">
                        <div class="manage-job-box">
                            <div class="manage-job-heading">
                                <h4>Tin chờ duyệt</h4>
                            </div>

                            @if($pendingJobs->isEmpty())
                                <div class="empty-state text-center py-5">
                                    <i class="fa fa-check-circle" style="font-size: 48px; color: #10b981;"></i>
                                    <p class="mt-3">Không có tin nào chờ duyệt!</p>
                                </div>
                            @else
                                <div class="manage-job-table">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Vị trí</th>
                                                <th>Chi nhánh</th>
                                                <th>Địa điểm</th>
                                                <th>Người đăng</th>
                                                <th>Ngày nộp</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingJobs as $job)
                                                <tr>
                                                    <td>
                                                        <div class="job-title">
                                                            <h5>{{ $job->title }}</h5>
                                                            <span class="job-status status-pending">
                                                                <i class="fa fa-clock-o"></i> Chờ duyệt
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>{{ $job->branch?->name ?? 'N/A' }}</td>
                                                    <td>{{ $job->workplace?->name ?? 'N/A' }}</td>
                                                    <td>{{ $job->creator?->name ?? 'N/A' }}</td>
                                                    <td>{{ $job->created_at->format('d/m/Y') }}</td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button
                                                                wire:click="approve({{ $job->id }})"
                                                                class="btn btn-success btn-sm"
                                                                onclick="return confirm('Bạn có chắc muốn duyệt tin này?')"
                                                            >
                                                                <i class="fa fa-check"></i> Duyệt
                                                            </button>
                                                            <button
                                                                wire:click="reject({{ $job->id }})"
                                                                class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Bạn có chắc muốn từ chối tin này?')"
                                                            >
                                                                <i class="fa fa-times"></i> Từ chối
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="manage-job-pagination">
                                    {{ $pendingJobs->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .manage-job-box {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .manage-job-heading {
        padding: 20px 25px;
        border-bottom: 1px solid #e2e8f0;
    }
    .manage-job-heading h4 {
        margin: 0;
        color: #1e293b;
        font-weight: 600;
    }
    .manage-job-table table {
        width: 100%;
    }
    .manage-job-table th,
    .manage-job-table td {
        padding: 15px 20px;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
    }
    .manage-job-table th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
    }
    .job-title h5 {
        margin: 0 0 5px;
        color: #1e293b;
        font-weight: 600;
    }
    .job-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    .btn-success {
        background: #10b981;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
    }
    .btn-danger {
        background: #ef4444;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
    }
    .empty-state {
        padding: 40px;
        color: #64748b;
    }
    .manage-job-pagination {
        padding: 20px;
        display: flex;
        justify-content: center;
    }
</style>
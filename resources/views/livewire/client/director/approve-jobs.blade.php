<div>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li class="active">Phê duyệt tin</li>
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
                                <h3>Tin chờ phê duyệt</h3>
                                <p style="margin: 10px 0 0; color: #64748b;">
                                    Danh sách các tin tuyển dụng đang chờ Director phê duyệt để hiển thị công khai.
                                </p>
                            </div>

                            @if($pendingJobs->isEmpty())
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="fa fa-snowflake-o" style="font-size: 64px; color: #e2e8f0;"></i>
                                    </div>
                                    <h4 style="color: #94a3b8; font-weight: 600;">Tuyệt vời! Không có tin nào đang chờ duyệt.</h4>
                                    <p class="text-muted">Tất cả các yêu cầu đã được xử lý xong.</p>
                                </div>
                            @else
                                <div class="table-responsive mt-4">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Vị trí tuyển dụng</th>
                                                <th>Người đăng</th>
                                                <th>Chi nhánh</th>
                                                <th>Ngày tạo</th>
                                                <th>Trạng thái</th>
                                                <th style="text-align: right;">Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingJobs as $job)
                                                <tr>
                                                    <td class="manage-jobs-title">
                                                        <a href="#">{{ $job->title }}</a>
                                                        <div style="margin-top: 4px; color: #94a3b8; font-size: 13px;">
                                                            {{ $job->department?->name ?? 'Không gán phòng ban' }} | {{ $job->workplace?->name ?? 'N/A' }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--fpt-orange); font-size: 12px;">
                                                                {{ substr($job->creator?->name, 0, 1) }}
                                                            </div>
                                                            <span style="font-weight: 600; font-size: 14px;">{{ $job->creator?->name }}</span>
                                                        </div>
                                                    </td>
                                                    <td>{{ $job->branch?->name ?? '-' }}</td>
                                                    <td class="table-date">{{ $job->created_at->format('d/m/Y') }}</td>
                                                    <td>
                                                        <span class="badge rounded-pill pending">Chờ duyệt</span>
                                                    </td>
                                                    <td style="text-align: right; white-space: nowrap;">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button 
                                                                wire:click="approve({{ $job->id }})"
                                                                wire:confirm="Bạn có chắc muốn DUYỆT tin '{{ addslashes($job->title) }}'?"
                                                                class="btn btn-sm" 
                                                                style="color: #10b981; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 8px; padding: 6px 14px; font-weight: 600; transition: all 0.3s;"
                                                            >
                                                                <i class="fa fa-check"></i> Duyệt
                                                            </button>
                                                            <button 
                                                                wire:click="reject({{ $job->id }})"
                                                                wire:confirm="Bạn có chắc muốn TỪ CHỐI tin '{{ addslashes($job->title) }}'?"
                                                                class="btn btn-sm" 
                                                                style="color: #ef4444; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 8px; padding: 6px 14px; font-weight: 600; transition: all 0.3s;"
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

                                <div class="mt-4">
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
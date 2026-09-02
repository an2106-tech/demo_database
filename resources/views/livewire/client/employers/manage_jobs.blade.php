<div class="premium-dashboard-container">
    <section class="candidate-dashboard-area section_70" style="padding: 28px 0 60px 0; background: #f8fafc; min-height: 85vh;">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right d-flex flex-column gap-4">
                        <!-- Top Header & Action -->
                        <div class="p-4 rounded-4 shadow-sm bg-white border d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                            <div>
                                <div class="d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill mb-2" style="background: rgba(243, 112, 33, 0.1); color: #f37021; font-size: 11.5px; font-weight: 700;">
                                    <i class="fa fa-briefcase"></i> Quản trị việc làm
                                </div>
                                <h3 class="fw-bold mb-1" style="font-size: 20px; color: #0f172a;">
                                    Danh sách tin tuyển dụng
                                </h3>
                                <p class="mb-0 text-muted" style="font-size: 13px;">
                                    Theo dõi trạng thái, hồ sơ ứng tuyển và hiệu chỉnh tin đăng tuyển.
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('employers.post_job') }}" class="btn px-4 py-2 text-white fw-bold d-inline-flex align-items-center gap-2 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #f37021 0%, #ea580c 100%); border: none; font-size: 13px;">
                                    <i class="fa fa-plus-circle"></i> Đăng tin mới
                                </a>
                            </div>
                        </div>

                        <!-- 4 Status Quick Filter Cards -->
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div wire:click="$set('statusFilter', '')" class="p-3 rounded-4 bg-white shadow-sm border cursor-pointer {{ $statusFilter === '' ? 'border-primary' : '' }}" style="cursor: pointer; transition: all 0.2s ease;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="text-muted fw-semibold" style="font-size: 12px;">Tất cả tin</span>
                                        <i class="fa fa-list text-muted"></i>
                                    </div>
                                    <div class="fw-bold" style="font-size: 22px; color: #0f172a;">{{ $stats['total'] }}</div>
                                </div>
                            </div>

                            <div class="col-6 col-md-3">
                                <div wire:click="$set('statusFilter', 'published')" class="p-3 rounded-4 bg-white shadow-sm border cursor-pointer {{ $statusFilter === 'published' ? 'border-success' : '' }}" style="cursor: pointer; transition: all 0.2s ease;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="text-success fw-semibold" style="font-size: 12px;">Đang tuyển</span>
                                        <i class="fa fa-check-circle text-success"></i>
                                    </div>
                                    <div class="fw-bold text-success" style="font-size: 22px;">{{ $stats['published'] }}</div>
                                </div>
                            </div>

                            <div class="col-6 col-md-3">
                                <div wire:click="$set('statusFilter', 'pending')" class="p-3 rounded-4 bg-white shadow-sm border cursor-pointer {{ $statusFilter === 'pending' ? 'border-warning' : '' }}" style="cursor: pointer; transition: all 0.2s ease;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="text-warning fw-semibold" style="font-size: 12px;">Chờ duyệt</span>
                                        <i class="fa fa-clock-o text-warning"></i>
                                    </div>
                                    <div class="fw-bold text-warning" style="font-size: 22px;">{{ $stats['pending'] }}</div>
                                </div>
                            </div>

                            <div class="col-6 col-md-3">
                                <div wire:click="$set('statusFilter', 'expired')" class="p-3 rounded-4 bg-white shadow-sm border cursor-pointer {{ $statusFilter === 'expired' ? 'border-danger' : '' }}" style="cursor: pointer; transition: all 0.2s ease;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="text-danger fw-semibold" style="font-size: 12px;">Đã đóng/Hết hạn</span>
                                        <i class="fa fa-ban text-danger"></i>
                                    </div>
                                    <div class="fw-bold text-danger" style="font-size: 22px;">{{ $stats['closed'] }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Table Panel -->
                        <div class="bg-white rounded-4 shadow-sm border overflow-hidden">
                            <!-- Search & Filter Bar -->
                            <div class="p-3.5 border-bottom bg-light d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                <div class="position-relative flex-grow-1" style="max-width: 380px;">
                                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm theo tiêu đề việc làm..." class="form-control rounded-pill ps-4 pe-4" style="font-size: 13px; height: 38px;">
                                    <i class="fa fa-search position-absolute text-muted" style="left: 12px; top: 12px; font-size: 12px;"></i>
                                    @if(filled($search))
                                        <button type="button" wire:click="$set('search', '')" class="btn btn-link position-absolute p-0 text-muted" style="right: 12px; top: 8px; font-size: 14px;">&times;</button>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <select wire:model.live="statusFilter" class="form-select form-select-sm rounded-pill" style="height: 38px; min-width: 160px; font-size: 12.5px;">
                                        <option value="">Tất cả trạng thái</option>
                                        <option value="published">Đang tuyển (Published)</option>
                                        <option value="pending">Chờ phê duyệt (Pending)</option>
                                        <option value="expired">Hết hạn (Expired)</option>
                                        <option value="closed">Đã đóng (Closed)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Table Content -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                    <thead class="bg-light text-muted" style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <tr>
                                            <th class="ps-4 py-3">Tiêu đề việc làm</th>
                                            <th class="py-3">Cơ sở / Đơn vị</th>
                                            <th class="py-3 text-center">Hồ sơ</th>
                                            <th class="py-3">Hạn nộp</th>
                                            <th class="py-3">Trạng thái</th>
                                            <th class="pe-4 py-3 text-end">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($jobs as $job)
                                            @php
                                                $appCount = $job->applications?->count() ?? 0;
                                                $statusValue = (string) ($job->status instanceof \BackedEnum ? $job->status->value : $job->status);
                                                $statusBadge = match($statusValue) {
                                                    'published' => ['bg' => '#ecfdf5', 'color' => '#059669', 'label' => 'Đang tuyển'],
                                                    'pending' => ['bg' => '#fff7ed', 'color' => '#ea580c', 'label' => 'Chờ duyệt'],
                                                    'expired', 'closed' => ['bg' => '#fef2f2', 'color' => '#ef4444', 'label' => 'Hết hạn'],
                                                    default => ['bg' => '#f1f5f9', 'color' => '#64748b', 'label' => 'Bản nháp'],
                                                };
                                            @endphp
                                            <tr wire:key="manage-job-row-{{ $job->id }}">
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center gap-2.5">
                                                        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(243, 112, 33, 0.1); color: #f37021; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                                                            <i class="fa fa-briefcase"></i>
                                                        </div>
                                                        <div>
                                                            <a href="{{ route('employers.job_detail', $job->id) }}" class="fw-bold text-dark text-decoration-none" style="font-size: 13.5px;">
                                                                {{ $job->title }}
                                                            </a>
                                                            <div class="text-muted" style="font-size: 11.5px; margin-top: 2px;">
                                                                {{ $job->department?->name ?? 'Phòng ban chung' }}
                                                                @if ($job->workplace)
                                                                    • {{ $job->workplace->name }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <div class="text-dark" style="font-size: 12.5px;">
                                                        <i class="fa fa-map-marker text-danger me-1"></i> {{ $job->branch?->name ?? 'Toàn hệ thống' }}
                                                    </div>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <a href="{{ route('employers.application_pipeline', ['selectedJobId' => $job->id]) }}" class="badge rounded-pill text-decoration-none px-2.5 py-1" style="background: #e0f2fe; color: #0284c7; font-size: 11.5px; font-weight: 700;" title="Xem trên Pipeline">
                                                        <i class="fa fa-users me-1"></i> {{ $appCount }} hồ sơ
                                                    </a>
                                                </td>
                                                <td class="py-3 text-muted" style="font-size: 12px;">
                                                    {{ optional($job->deadline)->format('d/m/Y') ?? 'Không giới hạn' }}
                                                </td>
                                                <td class="py-3">
                                                    <span class="badge rounded-pill px-2.5 py-1" style="background: {{ $statusBadge['bg'] }}; color: {{ $statusBadge['color'] }}; font-weight: 700; font-size: 11px;">
                                                        {{ $statusBadge['label'] }}
                                                    </span>
                                                </td>
                                                <td class="pe-4 py-3 text-end">
                                                    <div class="d-inline-flex align-items-center gap-1.5">
                                                        <a href="{{ route('employers.job_detail', $job->id) }}" target="_blank" class="btn btn-sm btn-light border px-2 py-1 rounded-2 text-secondary" title="Xem trang tin tuyển dụng" style="font-size: 11.5px;">
                                                            <i class="fa fa-external-link"></i>
                                                        </a>
                                                        <a href="{{ route('employers.edit_job', ['id' => $job->id]) }}" class="btn btn-sm btn-light border px-2.5 py-1 rounded-2 text-primary fw-bold" title="Chỉnh sửa" style="font-size: 11.5px;">
                                                            <i class="fa fa-pencil"></i> Sửa
                                                        </a>
                                                        <button type="button" onclick="confirmDeleteJob({{ $job->id }});" class="btn btn-sm btn-light border border-danger-subtle text-danger px-2 py-1 rounded-2" title="Xoá tin" style="font-size: 11.5px;">
                                                            <i class="fa fa-trash-o"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i class="fa fa-inbox" style="font-size: 36px; color: #cbd5e1; margin-bottom: 8px;"></i>
                                                    <p class="m-0 mb-2">Chưa có tin tuyển dụng nào phù hợp với điều kiện tìm kiếm.</p>
                                                    <a href="{{ route('employers.post_job') }}" class="btn btn-sm text-white fw-bold px-3 py-1.5 rounded-pill" style="background: #f37021; border: none; font-size: 12px;">
                                                        <i class="fa fa-plus me-1"></i> Đăng tin mới ngay
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($jobs->hasPages())
                                <div class="p-3 border-top d-flex justify-content-end">
                                    {{ $jobs->links() }}
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function confirmDeleteJob(jobId) {
            Swal.fire({
                title: 'Xác nhận xoá tin tuyển dụng?',
                text: "Toàn bộ thông tin tin đăng này sẽ bị xoá khỏi hệ thống.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa fa-trash-o me-1"></i> Xoá ngay',
                cancelButtonText: 'Huỷ'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('deleteJob', jobId);
                }
            })
        }
    </script>
</div>

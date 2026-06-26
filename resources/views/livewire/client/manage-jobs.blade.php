<div>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('candidates.candidate_dashboard') }}">Ứng viên</a></li>
            <li class="active">Việc làm đã ứng tuyển</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right">
                        <div class="premium-panel" style="padding: 28px;">
                            <div class="manage-jobs-heading" style="margin-bottom: 22px;">
                                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                                    <div>
                                        <span style="display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:999px;background:rgba(243,112,33,.08);color:#9a3412;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Hồ sơ ứng tuyển</span>
                                        <h3 style="margin: 10px 0 0; color: #0f172a;">Danh sách hồ sơ đã ứng tuyển</h3>
                                        <p style="margin: 8px 0 0; color: #64748b; max-width: 760px;">
                                            Theo dõi trạng thái từng hồ sơ, mở chi tiết snapshot tại thời điểm nộp và rút hồ sơ khi cần.
                                        </p>
                                    </div>
                                    <a href="{{ route('candidates.browse_job') }}" class="jobguru-btn-2" style="white-space: nowrap;">Tìm việc mới</a>
                                </div>
                            </div>

                            <div class="single-manage-jobs table-responsive">
                                @if ($applications->isEmpty())
                                    <div class="alert alert-info mb-0" style="border-radius: 18px; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0;">
                                        Bạn chưa có hồ sơ ứng tuyển nào. Hãy bắt đầu bằng một vị trí phù hợp ở trang tìm việc.
                                    </div>
                                @else
                                    <table class="table align-middle">
                                        <thead>
                                            <tr>
                                                <th>Vị trí</th>
                                                <th>Chi nhánh</th>
                                                <th>Ngày ứng tuyển</th>
                                                <th>Trạng thái</th>
                                                <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($applications as $application)
                                                @php
                                                    $job = $application->job;
                                                    $status = $application->status;
                                                    $statusValue = $status instanceof \App\Enums\StatusApplicationEnum ? $status->value : (string) $status;
                                                    $statusLabel = $status instanceof \App\Enums\StatusApplicationEnum ? $status->getLabel() : ucfirst((string) $status);
                                                    $statusClass = match ($statusValue) {
                                                        'new', 'cv_reviewing', 'screening' => 'pending',
                                                        'interview_scheduled', 'interview', 'offer' => 'active',
                                                        'hired' => 'hired',
                                                        'rejected' => 'rejected',
                                                        default => 'pending',
                                                    };
                                                @endphp
                                                <tr>
                                                    <td class="manage-jobs-title">
                                                        <a href="{{ route('candidates.application_detail', ['application' => $application->id]) }}" style="color:#0f172a; font-weight:700;">
                                                            {{ $job?->title ?? 'Vị trí không còn khả dụng' }}
                                                        </a>
                                                    </td>
                                                    <td class="table-date">
                                                        {{ $job?->branch?->name ?? '-' }}
                                                    </td>
                                                    <td class="table-date">
                                                        {{ optional($application->applied_at ?? $application->created_at)->format('d/m/Y H:i') }}
                                                    </td>
                                                    <td>
                                                        <span class="{{ $statusClass }}" style="font-weight:700; letter-spacing:0;">{{ $statusLabel }}</span>
                                                    </td>
                                                    <td class="action">
                                                        <a href="{{ route('candidates.application_detail', ['application' => $application->id]) }}" class="action-edit" title="Xem chi tiết" style="background:rgba(15,23,42,.04); color:#0f172a;">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

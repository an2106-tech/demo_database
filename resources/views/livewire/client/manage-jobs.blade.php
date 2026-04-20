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
                        <div class="premium-panel">
                            <div class="manage-jobs-heading">
                                <h3>Danh sách hồ sơ đã ứng tuyển</h3>
                                <p style="margin: 10px 0 0; color: #64748b;">
                                    Danh sách các công việc bạn đã nộp hồ sơ và trạng thái xử lý từ nhà tuyển dụng.
                                </p>
                            </div>

                            <div class="single-manage-jobs table-responsive">
                                @if ($applications->isEmpty())
                                    <div class="alert alert-info mb-0">
                                        Bạn chưa có hồ sơ ứng tuyển nào.
                                    </div>
                                @else
                                    <table class="table">
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
                                                        'cv_reviewing', 'screening' => 'pending',
                                                        'interview_scheduled', 'interviewing', 'offered' => 'active',
                                                        'hired' => 'hired',
                                                        'rejected' => 'rejected',
                                                        default => 'pending',
                                                    };
                                                @endphp
                                                <tr>
                                                    <td class="manage-jobs-title">
                                                        <a href="{{ route('candidates.application_detail', ['application' => $application->id]) }}">
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
                                                        <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                                                    </td>
                                                    <td class="action">
                                                        <a href="{{ route('candidates.application_detail', ['application' => $application->id]) }}" class="action-edit" title="Xem chi tiết">
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

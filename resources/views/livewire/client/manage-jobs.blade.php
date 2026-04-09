<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Quản lý đơn ứng tuyển</h3>
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
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right">
                        <div class="manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>Danh sách hồ sơ đã ứng tuyển</h3>
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
                                                        'new' => 'pending',
                                                        'screening' => 'pending',
                                                        'interview' => 'active',
                                                        'offer' => 'active',
                                                        'hired' => 'approved',
                                                        'rejected' => 'expired',
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

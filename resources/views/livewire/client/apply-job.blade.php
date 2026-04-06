<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Ứng tuyển</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="candidate-dashboard-area section_70">
        <div class="container">
            @if (session('status'))
                <div class="alert alert-success mb-3">{{ session('status') }}</div>
            @endif

            <div class="row">
                <div class="col-lg-3 col-md-4 dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8">
                    <div class="dashboard-right">
                        <div class="resume-box">
                            <h3>Nộp hồ sơ: {{ $job->title }}</h3>
                            <p style="margin-top:6px;color:#6b7280;">Chọn cách nộp: dùng hồ sơ đã nhập hoặc dùng CV.</p>

                            <form wire:submit.prevent="submit" enctype="multipart/form-data">
                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label>Cách nộp</label>
                                        <div style="display:flex; gap:18px; flex-wrap:wrap;">
                                            <label style="display:flex; gap:8px; align-items:center;">
                                                <input type="radio" wire:model="apply_method" value="profile">
                                                Dùng hồ sơ đã nhập
                                            </label>
                                            <label style="display:flex; gap:8px; align-items:center;">
                                                <input type="radio" wire:model="apply_method" value="cv">
                                                Dùng CV
                                            </label>
                                        </div>
                                        @error('apply_method')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                @if ($apply_method === 'profile')
                                    <div class="alert alert-info">
                                        Hệ thống sẽ lấy dữ liệu bạn đã nhập trong <a href="{{ route('candidates.candidate_profile') }}">Hồ sơ của tôi</a> và lưu snapshot vào lượt nộp hồ sơ.
                                    </div>
                                @endif

                                @if ($apply_method === 'cv')
                                    <div class="single-resume-feild">
                                        <div class="single-input">
                                            <label>CV</label>
                                            @if ($hasCv)
                                                <label style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
                                                    <input type="checkbox" wire:model="use_existing_cv">
                                                    Dùng CV đã upload trong hồ sơ (nếu không chọn file mới)
                                                </label>
                                            @endif

                                            <input type="file" wire:model="cv" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                            <div wire:loading wire:target="cv" class="mt-2">Đang tải lên…</div>
                                            @error('cv')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                @endif

                                <div class="submit-resume">
                                    <button type="submit" wire:loading.attr="disabled">Nộp ứng tuyển</button>
                                </div>
                            </form>
                        </div>

                        @if ($apply_method === 'profile')
                            <div class="resume-box" style="margin-top:16px;">
                                <h3>Xem nhanh hồ sơ sẽ nộp</h3>
                                <p><strong>Họ tên:</strong> {{ $candidate?->name }}</p>
                                <p><strong>Email:</strong> {{ $candidate?->email }}</p>
                                <p><strong>SĐT:</strong> {{ $candidate?->phone ?? 'Chưa có' }}</p>
                                <p><strong>Kinh nghiệm:</strong> {{ $candidate?->experience_years ? ($candidate->experience_years.' năm') : 'Chưa có' }}</p>
                                <p><strong>Tiêu đề hồ sơ:</strong> {{ $candidate?->resume?->profile_title ?? 'Chưa có' }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

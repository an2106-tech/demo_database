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

            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="dashboard-right">
                        <div class="resume-box">
                            <h3>Nộp hồ sơ: {{ $job->title }}</h3>
                            <p style="margin-top: 6px; color: #6b7280;">
                                Bạn không cần đăng nhập để ứng tuyển. Chỉ cần điền thông tin cơ bản và tải CV lên.
                            </p>

                            <form wire:submit.prevent="submit" enctype="multipart/form-data">
                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label>Họ và tên</label>
                                        <input type="text" wire:model.defer="name" placeholder="Nhập họ và tên">
                                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label>Email</label>
                                        <input type="email" wire:model.defer="email" placeholder="Nhập email">
                                        @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label>Số điện thoại</label>
                                        <input type="text" wire:model.defer="phone" placeholder="Nhập số điện thoại">
                                        @error('phone')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label>Số năm kinh nghiệm</label>
                                        <input type="number" min="0" wire:model.defer="experience_years" placeholder="Ví dụ: 2">
                                        @error('experience_years')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label>Tiêu đề hồ sơ</label>
                                        <input type="text" wire:model.defer="profile_title" placeholder="Ví dụ: Backend Developer">
                                        @error('profile_title')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label>Mục tiêu nghề nghiệp</label>
                                        <textarea wire:model.defer="career_objective" rows="4" placeholder="Giới thiệu ngắn gọn về định hướng công việc của bạn"></textarea>
                                        @error('career_objective')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="single-resume-feild">
                                    <div class="single-input">
                                        <label>CV</label>

                                        @if ($hasExistingCv)
                                            <label style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
                                                <input type="checkbox" wire:model="use_existing_cv">
                                                Dùng lại CV đã có trong hồ sơ ứng viên
                                            </label>
                                        @endif

                                        <input type="file" wire:model="cv" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                        <div wire:loading wire:target="cv" class="mt-2">Đang tải lên...</div>
                                        @error('cv')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="submit-resume">
                                    <button type="submit" wire:loading.attr="disabled">Nộp ứng tuyển</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

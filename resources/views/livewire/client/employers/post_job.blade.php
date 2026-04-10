<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Đăng tin tuyển dụng</h3>
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
                                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                                <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.post_job') }}">Đăng tin</a></li>
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
                <div class="col-md-4 col-lg-3 mx-auto dashboard-left-border">
                    <div class="dashboard-left">
                        <ul class="dashboard-menu">
                            <li><a href="{{ route('employers.dashboard') }}"><i class="fa fa-tachometer"></i>Bảng điều khiển</a></li>
                            <li><a href="{{ route('employers.company_profile') }}"><i class="fa fa-users"></i>Hồ sơ công ty</a></li>
                            <li><a href="{{ route('employers.message') }}"><i class="fa fa-envelope-open"></i>Tin nhắn</a></li>
                            <li class="active"><a href="{{ route('employers.post_job') }}"><i class="fa fa-bullhorn"></i>Đăng tin tuyển dụng</a></li>
                            <li><a href="{{ route('employers.manage_jobs') }}"><i class="fa fa-briefcase"></i>Quản lý tin đăng</a></li>
                            <li><a href="{{ route('employers.manage_candidates') }}"><i class="fa fa-user-circle"></i>Quản lý ứng viên</a></li>
                            <li><a href="{{ route('employers.change_password') }}"><i class="fa fa-lock"></i>Đổi mật khẩu</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-8 col-lg-9 mx-auto">
                    <div class="dashboard-right">
                        <div class="earnings-page-box manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>Tạo tin tuyển dụng mới</h3>
                                <p style="margin: 10px 0 0; color: #6b7280;">
                                    Điền đầy đủ thông tin để tin đăng có thể hiển thị đúng trên danh sách việc làm.
                                </p>
                            </div>

                            @if (session('status'))
                                <div class="alert alert-success" style="margin-bottom: 24px;">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <div class="new-job-submission">
                                <form wire:submit="save">
                                    <div class="resume-box">
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="job-title">Tiêu đề công việc</label>
                                                <input id="job-title" type="text" wire:model.defer="title" placeholder="Ví dụ: Laravel Developer">
                                                @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="single-input">
                                                <label for="job-status">Trạng thái</label>
                                                <select id="job-status" wire:model.defer="status">
                                                    <option value="published">Đăng ngay</option>
                                                    <option value="draft">Lưu nháp</option>
                                                </select>
                                                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="job-branch">Chi nhánh</label>
                                                <select id="job-branch" wire:model.live="branch_id" @disabled($isBranchLocked)>
                                                    <option value="">Chọn chi nhánh</option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('branch_id') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="single-input">
                                                <label for="job-department">Phòng ban</label>
                                                <select id="job-department" wire:model.live="department_id" @disabled(! $branch_id)>
                                                    <option value="">{{ $branch_id ? 'Không bắt buộc' : 'Chọn chi nhánh trước' }}</option>
                                                    @foreach ($departments as $department)
                                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('department_id') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="job-workplace">Nơi làm việc</label>
                                                <select id="job-workplace" wire:model.defer="workplace_id" @disabled(! $department_id)>
                                                    <option value="">{{ $department_id ? 'Không bắt buộc' : 'Chọn phòng ban trước' }}</option>
                                                    @foreach ($workplaces as $workplace)
                                                        <option value="{{ $workplace->id }}">{{ $workplace->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('workplace_id') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="single-input">
                                                <label for="job-public-url">Link ứng tuyển ngoài</label>
                                                <input id="job-public-url" type="url" wire:model.defer="public_url" placeholder="https://example.com/apply">
                                                @error('public_url') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="job-salary-min">Lương tối thiểu</label>
                                                <input id="job-salary-min" type="number" min="0" wire:model.defer="salary_min" placeholder="15000000">
                                                @error('salary_min') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="single-input">
                                                <label for="job-salary-max">Lương tối đa</label>
                                                <input id="job-salary-max" type="number" min="0" wire:model.defer="salary_max" placeholder="25000000">
                                                @error('salary_max') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="job-deadline">Hạn nộp</label>
                                                <input id="job-deadline" type="date" wire:model.defer="deadline">
                                                @error('deadline') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="single-input">
                                                <label for="job-positions">Số lượng cần tuyển</label>
                                                <input id="job-positions" type="number" min="1" max="99" wire:model.defer="positions_count">
                                                @error('positions_count') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="single-resume-feild">
                                            <div class="single-input">
                                                <label for="job-description">Mô tả công việc</label>
                                                <textarea id="job-description" wire:model.defer="description" rows="8" placeholder="Mô tả công việc, yêu cầu, quyền lợi và cách ứng tuyển"></textarea>
                                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="single-input submit-resume">
                                        <button type="submit" wire:loading.attr="disabled" wire:target="save">
                                            <span wire:loading.remove wire:target="save">Đăng tin tuyển dụng</span>
                                            <span wire:loading wire:target="save">Đang xử lý...</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

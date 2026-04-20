<div>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li class="active">{{ $jobId ? 'Cập nhật tin' : 'Đăng tin mới' }}</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-md-4 col-lg-3 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-md-8 col-lg-9 mx-auto">
                    <div class="dashboard-right">
                        <div class="premium-panel">
                            <div class="manage-jobs-heading">
                                <h3>{{ $jobId ? 'Cập nhật tin tuyển dụng' : 'Tạo tin tuyển dụng mới' }}</h3>
                                <p style="margin: 10px 0 0; color: #64748b;">
                                    Điền đầy đủ thông tin bên dưới để hoàn tất việc đăng tin tuyển dụng.
                                </p>
                            </div>

                            <div class="new-job-submission" style="margin-top: 2rem;">
                                <form wire:submit="save">
                                        <div class="form-section-title">
                                            <i class="fa fa-info-circle"></i> Thông tin cơ bản
                                        </div>
                                        <div class="single-resume-feild">
                                            <div class="single-input">
                                                <label for="job-title">Tiêu đề công việc</label>
                                                <input id="job-title" type="text" wire:model.defer="title" placeholder="Ví dụ: Laravel Developer">
                                                @error('title') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="single-input">
                                                    <label for="job-branch">Chi nhánh</label>
                                                    <select id="job-branch" wire:model.live="branch_id" @disabled($isBranchLocked)>
                                                        <option value="">Chọn chi nhánh</option>
                                                        @foreach ($branches as $branch)
                                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('branch_id') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="single-input">
                                                    <label for="job-department">Phòng ban</label>
                                                    <select id="job-department" wire:model.live="department_id" @disabled(! $branch_id)>
                                                        <option value="">{{ $branch_id ? 'Không bắt buộc' : 'Chọn chi nhánh trước' }}</option>
                                                        @foreach ($departments as $department)
                                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('department_id') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-section-title mt-4">
                                            <i class="fa fa-money"></i> Thu nhập & Địa điểm
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="single-input">
                                                    <label for="job-workplace">Nơi làm việc</label>
                                                    <select id="job-workplace" wire:model.defer="workplace_id" @disabled(! $department_id)>
                                                        <option value="">{{ $department_id ? 'Không bắt buộc' : 'Chọn phòng ban trước' }}</option>
                                                        @foreach ($workplaces as $workplace)
                                                            <option value="{{ $workplace->id }}">{{ $workplace->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('workplace_id') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="single-input">
                                                    <label for="job-public-url">Link ứng tuyển ngoài</label>
                                                    <input id="job-public-url" type="url" wire:model.defer="public_url" placeholder="https://example.com/apply">
                                                    @error('public_url') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-3 mt-1">
                                            <div class="col-md-6">
                                                <div class="single-input">
                                                    <label for="job-salary-min">Lương tối thiểu</label>
                                                    <input id="job-salary-min" type="number" min="0" wire:model.defer="salary_min" placeholder="15000000">
                                                    @error('salary_min') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="single-input">
                                                    <label for="job-salary-max">Lương tối đa</label>
                                                    <input id="job-salary-max" type="number" min="0" wire:model.defer="salary_max" placeholder="25000000">
                                                    @error('salary_max') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-section-title mt-4">
                                            <i class="fa fa-calendar"></i> Thời hạn & Số lượng
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="single-input">
                                                    <label for="job-deadline">Hạn nộp</label>
                                                    <input id="job-deadline" type="date" wire:model.defer="deadline">
                                                    @error('deadline') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="single-input">
                                                    <label for="job-positions">Số lượng cần tuyển</label>
                                                    <input id="job-positions" type="number" min="1" max="99" wire:model.defer="positions_count">
                                                    @error('positions_count') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-section-title mt-4">
                                            <i class="fa fa-mortar-board"></i> Kỹ năng & Yêu cầu
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="single-input" x-data="{
                                                    initSelect2() {
                                                        $(this.$refs.select).select2({
                                                            placeholder: 'Chọn một hoặc nhiều danh mục',
                                                            width: '100%',
                                                            allowClear: true
                                                        }).on('change', (e) => {
                                                            @this.set('selected_categories', $(this.$refs.select).val());
                                                        });
                                                    }
                                                }" x-init="initSelect2()">
                                                    <label for="job-categories">Danh mục nghề nghiệp</label>
                                                    <div wire:ignore>
                                                        <select id="job-categories" x-ref="select" multiple="multiple">
                                                            @foreach ($categoriesOptions as $category)
                                                                <option value="{{ $category->id }}" @if(in_array($category->id, $selected_categories)) selected @endif>{{ $category->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @error('selected_categories') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                    @error('selected_categories.*') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-3 mt-1">
                                            <div class="col-md-8">
                                                <div class="single-input" x-data="{
                                                    initSelect2() {
                                                        $(this.$refs.select).select2({
                                                            placeholder: 'Chọn một hoặc nhiều kỹ năng',
                                                            width: '100%',
                                                            allowClear: true
                                                        }).on('change', (e) => {
                                                            @this.set('skills', $(this.$refs.select).val());
                                                        });
                                                    }
                                                }" x-init="initSelect2()">
                                                    <label for="job-skills">Kỹ năng yêu cầu</label>
                                                    <div wire:ignore>
                                                        <select id="job-skills" x-ref="select" multiple="multiple">
                                                            @foreach ($skillsOptions as $skill)
                                                                <option value="{{ $skill->id }}" @if(in_array($skill->id, $skills)) selected @endif>{{ $skill->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @error('skills') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                    @error('skills.*') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="single-input" x-data="{
                                                    initSelect2() {
                                                        $(this.$refs.select).select2({
                                                            placeholder: 'Chọn trình độ',
                                                            width: '100%',
                                                            minimumResultsForSearch: Infinity
                                                        }).on('change', (e) => {
                                                            @this.set('skills_level', $(this.$refs.select).val());
                                                        });
                                                    }
                                                }" x-init="initSelect2()">
                                                    <label for="job-skills-level">Trình độ</label>
                                                    <div wire:ignore>
                                                        <select id="job-skills-level" x-ref="select">
                                                            <option value="junior" {{ $skills_level == 'junior' ? 'selected' : '' }}>Junior (Cơ bản)</option>
                                                            <option value="mid" {{ $skills_level == 'mid' ? 'selected' : '' }}>Mid (Trung cấp)</option>
                                                            <option value="senior" {{ $skills_level == 'senior' ? 'selected' : '' }}>Senior (Cao cấp)</option>
                                                        </select>
                                                    </div>
                                                    @error('skills_level') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-section-title mt-4">
                                            <i class="fa fa-pencil-square-o"></i> Nội dung chi tiết
                                        </div>
                                        <div class="single-resume-feild">
                                            <div class="single-input" x-data="{
                                                initEditor() {
                                                    ClassicEditor
                                                        .create(this.$refs.editor, {
                                                            toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo' ]
                                                        })
                                                        .then(editor => {
                                                            editor.model.document.on('change:data', () => {
                                                                @this.set('description', editor.getData());
                                                            });
                                                        })
                                                        .catch(error => {
                                                            console.error(error);
                                                        });
                                                }
                                            }" x-init="initEditor()">
                                                <label for="job-description">Mô tả công việc</label>
                                                <div wire:ignore class="premium-editor-container">
                                                    <textarea x-ref="editor" id="job-description" rows="8">{{ $description ?? '' }}</textarea>
                                                </div>
                                                @error('description') <span class="text-danger-custom">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="single-input submit-resume">
                                        <button type="submit" wire:loading.attr="disabled" wire:target="save">
                                            <span wire:loading.remove wire:target="save">{{ $jobId ? 'Cập nhật tin tuyển dụng' : 'Đăng tin tuyển dụng' }}</span>
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

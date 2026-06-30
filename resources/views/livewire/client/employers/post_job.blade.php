<div>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    @php
        $summarySalary = filled($salary_min) || filled($salary_max)
            ? trim(($salary_min ? number_format((float) $salary_min, 0, ',', '.') : '...') . ' - ' . ($salary_max ? number_format((float) $salary_max, 0, ',', '.') : '...'))
            : 'Chưa thiết lập';
    @endphp

    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li class="active">{{ $jobId ? 'Cập nhật tin' : 'Đăng tin mới' }}</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <div class="col-lg-3 col-xl-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-lg-9 col-xl-9">
                    <div class="portal-form-shell">
                        <div class="portal-form-main">
                            <div class="portal-shell portal-shell--subtle p-4 p-lg-5">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-4">
                                    <div>
                                        <span class="portal-eyebrow">{{ $jobId ? 'Chỉnh sửa tin đăng' : 'Tạo tin đăng mới' }}</span>
                                        <h1 class="portal-title">{{ $jobId ? 'Cập nhật nội dung tuyển dụng' : 'Thiết lập một tin tuyển dụng gọn gàng' }}</h1>
                                        <p class="portal-subtitle">
                                            Giữ form theo từng cụm rõ ràng để người dùng dễ đọc, dễ sửa và tránh cảm giác rối mắt khi nhập dữ liệu.
                                        </p>
                                    </div>
                                </div>

                                <form wire:submit="save" class="form-stack">
                                    <div class="form-section">
                                        <h2 class="form-section__title">
                                            <i class="fa fa-info-circle"></i>
                                            Thông tin cơ bản
                                        </h2>
                                        <p class="form-section__hint">Nhóm này quyết định ấn tượng đầu tiên của tin tuyển dụng.</p>

                                        <div class="field-stack">
                                            <div class="field-card">
                                                <label for="job-title">Tiêu đề công việc</label>
                                                <input id="job-title" type="text" wire:model.defer="title" placeholder="Ví dụ: Senior Laravel Developer">
                                                @error('title') <span class="field-error">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="field-card">
                                                        <label for="job-branch">Chi nhánh</label>
                                                        <select id="job-branch" wire:model.live="branch_id" @disabled($isBranchLocked)>
                                                            <option value="">Chọn chi nhánh</option>
                                                            @foreach ($branches as $branch)
                                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('branch_id') <span class="field-error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="field-card">
                                                        <label for="job-department">Phòng ban</label>
                                                        <select id="job-department" wire:model.live="department_id" @disabled(! $branch_id)>
                                                            <option value="">{{ $branch_id ? 'Không bắt buộc' : 'Chọn chi nhánh trước' }}</option>
                                                            @foreach ($departments as $department)
                                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('department_id') <span class="field-error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <h2 class="form-section__title">
                                            <i class="fa fa-map-marker"></i>
                                            Thu nhập & địa điểm
                                        </h2>
                                        <p class="form-section__hint">Đây là phần giúp ứng viên so sánh nhanh trước khi đọc chi tiết.</p>

                                        <div class="field-stack">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="field-card">
                                                        <label for="job-workplace">Nơi làm việc</label>
                                                        <select id="job-workplace" wire:model.defer="workplace_id" @disabled(! $branch_id)>
                                                            <option value="">{{ $branch_id ? 'Không bắt buộc' : 'Chọn chi nhánh trước' }}</option>
                                                            @foreach ($workplaces as $workplace)
                                                                <option value="{{ $workplace->id }}">{{ $workplace->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('workplace_id') <span class="field-error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="field-card">
                                                        <label for="job-public-url">Link ứng tuyển ngoài</label>
                                                        <input id="job-public-url" type="url" wire:model.defer="public_url" placeholder="https://example.com/apply">
                                                        @error('public_url') <span class="field-error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="field-card">
                                                        <label for="job-salary-min">Lương tối thiểu</label>
                                                        <input id="job-salary-min" type="number" min="0" wire:model.defer="salary_min" placeholder="15000000">
                                                        @error('salary_min') <span class="field-error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="field-card">
                                                        <label for="job-salary-max">Lương tối đa</label>
                                                        <input id="job-salary-max" type="number" min="0" wire:model.defer="salary_max" placeholder="25000000">
                                                        @error('salary_max') <span class="field-error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <h2 class="form-section__title">
                                            <i class="fa fa-calendar"></i>
                                            Thời hạn & số lượng
                                        </h2>
                                        <p class="form-section__hint">Giữ khối này ngắn và rõ để tránh nhầm lẫn khi cập nhật tin.</p>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="field-card">
                                                    <label for="job-deadline">Hạn nộp</label>
                                                    <input id="job-deadline" type="date" wire:model.defer="deadline">
                                                    @error('deadline') <span class="field-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="field-card">
                                                    <label for="job-positions">Số lượng cần tuyển</label>
                                                    <input id="job-positions" type="number" min="1" max="99" wire:model.defer="positions_count">
                                                    @error('positions_count') <span class="field-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <h2 class="form-section__title">
                                            <i class="fa fa-tags"></i>
                                            Danh mục, kỹ năng và cấp độ
                                        </h2>
                                        <p class="form-section__hint">Các trường này nên luôn giữ độ tương phản tốt vì đây là phần dễ đọc lướt.</p>

                                        <div class="field-stack">
                                            <div class="field-card" x-data="{
                                                initSelect2() {
                                                    $(this.$refs.select).select2({
                                                        placeholder: 'Chọn một hoặc nhiều danh mục',
                                                        width: '100%',
                                                        allowClear: true
                                                    }).on('change', () => {
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
                                                @error('selected_categories') <span class="field-error">{{ $message }}</span> @enderror
                                                @error('selected_categories.*') <span class="field-error">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <div class="field-card" x-data="{
                                                        initSelect2() {
                                                            $(this.$refs.select).select2({
                                                                placeholder: 'Chọn một hoặc nhiều kỹ năng',
                                                                width: '100%',
                                                                allowClear: true
                                                            }).on('change', () => {
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
                                                        @error('skills') <span class="field-error">{{ $message }}</span> @enderror
                                                        @error('skills.*') <span class="field-error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="field-card" x-data="{
                                                        initSelect2() {
                                                            $(this.$refs.select).select2({
                                                                placeholder: 'Chọn trình độ',
                                                                width: '100%',
                                                                minimumResultsForSearch: Infinity
                                                            }).on('change', () => {
                                                                @this.set('skills_level', $(this.$refs.select).val());
                                                            });
                                                        }
                                                    }" x-init="initSelect2()">
                                                        <label for="job-skills-level">Trình độ</label>
                                                        <div wire:ignore>
                                                            <select id="job-skills-level" x-ref="select">
                                                                <option value="junior" {{ $skills_level === 'junior' ? 'selected' : '' }}>Junior</option>
                                                                <option value="mid" {{ $skills_level === 'mid' ? 'selected' : '' }}>Mid</option>
                                                                <option value="senior" {{ $skills_level === 'senior' ? 'selected' : '' }}>Senior</option>
                                                            </select>
                                                        </div>
                                                        @error('skills_level') <span class="field-error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <h2 class="form-section__title">
                                            <i class="fa fa-file-text-o"></i>
                                            Nội dung chi tiết
                                        </h2>
                                        <p class="form-section__hint">Phần mô tả nên thoáng, chia đoạn rõ và có đủ thông tin để ứng viên quét nhanh.</p>

                                        <div class="field-card" x-data="{
                                            initEditor() {
                                                if (! window.ClassicEditor) {
                                                    return;
                                                }

                                                ClassicEditor
                                                    .create(this.$refs.editor, {
                                                        toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo']
                                                    })
                                                    .then(editor => {
                                                        editor.model.document.on('change:data', () => {
                                                            @this.set('description', editor.getData());
                                                        });
                                                    })
                                                    .catch(error => console.error(error));
                                            }
                                        }" x-init="initEditor()">
                                            <label for="job-description">Mô tả công việc</label>
                                            <div wire:ignore class="premium-editor-container">
                                                <textarea x-ref="editor" id="job-description" rows="8">{{ $description ?? '' }}</textarea>
                                            </div>
                                            @error('description') <span class="field-error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="job-form-actions">
                                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="jobguru-btn-2">
                                            <span wire:loading.remove wire:target="save">{{ $jobId ? 'Cập nhật tin tuyển dụng' : 'Đăng tin tuyển dụng' }}</span>
                                            <span wire:loading wire:target="save">Đang xử lý...</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <aside class="portal-form-aside">
                            <div class="job-summary-card">
                                <span class="portal-eyebrow">Tóm tắt nhanh</span>
                                <h4 class="mt-3 mb-2">{{ $jobId ? 'Trạng thái chỉnh sửa' : 'Trạng thái bản nháp' }}</h4>
                                <p class="portal-subtitle mb-0" style="font-size: 14px;">
                                    Bố cục này giúp bạn kiểm tra nhanh các trường chính trước khi gửi duyệt hoặc lưu.
                                </p>

                                <div class="job-summary-list">
                                    <div class="job-summary-item">
                                        <span>Tiêu đề</span>
                                        <strong>{{ filled($title) ? $title : 'Chưa nhập' }}</strong>
                                    </div>
                                    <div class="job-summary-item">
                                        <span>Chi nhánh</span>
                                        <strong>{{ $branches->firstWhere('id', $branch_id)?->name ?? 'Chưa chọn' }}</strong>
                                    </div>
                                    <div class="job-summary-item">
                                        <span>Phòng ban</span>
                                        <strong>{{ $departments->firstWhere('id', $department_id)?->name ?? 'Không bắt buộc' }}</strong>
                                    </div>
                                    <div class="job-summary-item">
                                        <span>Nơi làm việc</span>
                                        <strong>{{ $workplaces->firstWhere('id', $workplace_id)?->name ?? 'Không bắt buộc' }}</strong>
                                    </div>
                                    <div class="job-summary-item">
                                        <span>Thu nhập</span>
                                        <strong>{{ $summarySalary }}</strong>
                                    </div>
                                    <div class="job-summary-item">
                                        <span>Số lượng</span>
                                        <strong>{{ $positions_count }}</strong>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

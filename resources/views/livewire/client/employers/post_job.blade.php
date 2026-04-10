<div>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
        /* Form Box Sleek Design */
        .earnings-page-box.manage-jobs {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            padding: 40px;
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }
        
        .manage-jobs-heading h3 {
            font-weight: 800;
            color: #1e293b;
            position: relative;
            padding-bottom: 12px;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .manage-jobs-heading h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #ff7800, #ffa73b);
            border-radius: 4px;
        }

        .manage-jobs-heading p {
            font-size: 15px;
            margin-bottom: 30px;
        }

        /* Inputs & Typography Base */
        .single-resume-feild .single-input label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 10px;
            font-size: 14px;
            letter-spacing: 0.2px;
        }

        .single-resume-feild .single-input input, 
        .single-resume-feild .single-input select,
        .single-resume-feild .single-input textarea {
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px !important;
            padding: 14px 18px !important;
            transition: all 0.3s ease-in-out !important;
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-size: 15px !important;
            width: 100% !important;
        }

        /* Focus Effects */
        .single-resume-feild .single-input input:focus, 
        .single-resume-feild .single-input select:focus,
        .single-resume-feild .single-input textarea:focus {
            border-color: #ff7800 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(255, 120, 0, 0.15) !important;
            outline: none !important;
        }

        /* Interactive Custom Button */
        .submit-resume button {
            background: linear-gradient(135deg, #ff952b 0%, #ff7800 100%) !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 15px 35px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            box-shadow: 0 6px 20px rgba(255, 120, 0, 0.3) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            font-size: 16px !important;
        }

        .submit-resume button:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 10px 25px rgba(255, 120, 0, 0.4) !important;
            background: linear-gradient(135deg, #ff7800 0%, #e66a00 100%) !important;
        }

        .submit-resume button:disabled {
            background: #94a3b8 !important;
            box-shadow: none !important;
            transform: none !important;
            cursor: not-allowed !important;
        }

        /* Sleek CKEditor */
        .ck-editor__editable_inline {
            min-height: 280px;
            border-radius: 0 0 10px 10px !important;
            border-color: #cbd5e1 !important;
            box-shadow: none !important;
            background-color: #f8fafc !important;
            transition: all 0.3s ease !important;
            font-family: inherit !important;
        }
        .ck-editor__editable_inline.ck-focused {
            background-color: #ffffff !important;
            border-color: #ff7800 !important;
            box-shadow: 0 0 0 4px rgba(255, 120, 0, 0.15) !important;
        }
        .ck-toolbar {
            border-radius: 10px 10px 0 0 !important;
            border-color: #cbd5e1 !important;
            background: #f1f5f9 !important;
            padding: 8px !important;
        }
        
        /* Select2 Aesthetics */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 8px 12px;
            min-height: 52px;
            background-color: #f8fafc;
            transition: all 0.3s ease-in-out;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #ff7800;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(255, 120, 0, 0.15);
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #fff1e6;
            border: 1px solid #ffd8b8;
            border-radius: 6px;
            color: #cc5e00;
            padding: 5px 10px;
            font-weight: 600;
            margin-top: 5px;
            font-size: 13px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #cc5e00;
            margin-right: 6px;
            font-weight: bold;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #994700;
            background-color: transparent;
        }
    </style>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>{{ $jobId ? 'Chỉnh sửa tin tuyển dụng' : 'Đăng tin tuyển dụng' }}</h3>
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
                                <li class="active-breadcromb"><a href="#">{{ $jobId ? 'Cập nhật' : 'Đăng tin' }}</a></li>
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
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-md-8 col-lg-9 mx-auto">
                    <div class="dashboard-right">
                        <div class="earnings-page-box manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>{{ $jobId ? 'Cập nhật tin tuyển dụng' : 'Tạo tin tuyển dụng mới' }}</h3>
                                <p style="margin: 10px 0 0; color: #6b7280;">
                                    Điền đầy đủ thông tin để tin đăng có thể hiển thị đúng trên danh sách việc làm.
                                </p>
                            </div>



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
                                                @error('selected_categories') <span class="text-danger">{{ $message }}</span> @enderror
                                                @error('selected_categories.*') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="single-resume-feild feild-flex-2">
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
                                                <label for="job-skills">Kỹ năng</label>
                                                <div wire:ignore>
                                                    <select id="job-skills" x-ref="select" multiple="multiple">
                                                        @foreach ($skillsOptions as $skill)
                                                            <option value="{{ $skill->id }}" @if(in_array($skill->id, $skills)) selected @endif>{{ $skill->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('skills') <span class="text-danger">{{ $message }}</span> @enderror
                                                @error('skills.*') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="single-input">
                                                <label for="job-skills-level">Trình độ kỹ năng</label>
                                                <select id="job-skills-level" wire:model.defer="skills_level">
                                                    <option value="junior">Junior</option>
                                                    <option value="mid">Mid</option>
                                                    <option value="senior">Senior</option>
                                                </select>
                                                @error('skills_level') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
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
                                                <div wire:ignore>
                                                    <textarea x-ref="editor" id="job-description" rows="8">{{ $description ?? '' }}</textarea>
                                                </div>
                                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
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

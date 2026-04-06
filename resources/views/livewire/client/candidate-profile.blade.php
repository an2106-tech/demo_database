<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Thông tin cá nhân</h3>
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
                        <div class="candidate-profile">
                            <form wire:submit.prevent="save" enctype="multipart/form-data">
                                <div class="resume-box">
                                    <h3>Hồ sơ của tôi</h3>

                                    <div class="single-resume-feild">
                                        <ul style="display:flex; flex-wrap:wrap; gap:12px; margin: 0; padding: 0; list-style: none;">
                                            <li><a href="#profile-title">Tiêu đề hồ sơ</a></li>
                                            <li><a href="#personal-info">Thông tin cá nhân</a></li>
                                            <li><a href="#career-objective">Mục tiêu nghề nghiệp</a></li>
                                            <li><a href="#desired-job">Công việc mong muốn</a></li>
                                            <li><a href="#experiences">Kinh nghiệm làm việc</a></li>
                                            <li><a href="#educations">Học vấn</a></li>
                                            <li><a href="#certifications">Chứng chỉ khác</a></li>
                                            <li><a href="#languages">Ngôn ngữ</a></li>
                                            <li><a href="#skills">Kỹ năng chuyên môn</a></li>
                                            <li><a href="#achievements">Thành tích nổi bật</a></li>
                                            <li><a href="#activities">Hoạt động khác</a></li>
                                            <li><a href="#references">Người tham khảo</a></li>
                                        </ul>
                                    </div>

                                    <hr>

                                    <div id="profile-title" class="single-resume-feild">
                                        <div class="single-input">
                                            <label for="profile_title">Tiêu đề hồ sơ</label>
                                            <input id="profile_title" type="text" wire:model.defer="profile_title" placeholder="Ví dụ: Backend Developer (PHP/Laravel)">
                                            @error('profile_title')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="single-resume-feild feild-flex-2">
                                        <div class="single-input">
                                            <label>Họ và tên</label>
                                            <input type="text" value="{{ $name }}" disabled>
                                        </div>
                                        <div class="single-input">
                                            <label>Email</label>
                                            <input type="text" value="{{ $email }}" disabled>
                                        </div>
                                    </div>

                                    <div id="personal-info" class="single-resume-feild">
                                        <h4 style="margin: 6px 0 10px;">Thông tin cá nhân</h4>
                                    </div>

                                    <div class="single-resume-feild feild-flex-2">
                                        <div class="single-input">
                                            <label for="phone">Số điện thoại</label>
                                            <input id="phone" type="text" wire:model.defer="phone">
                                            @error('phone')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="single-input">
                                            <label for="experience_years">Số năm kinh nghiệm</label>
                                            <input id="experience_years" type="number" min="0" max="60" wire:model.defer="experience_years">
                                            @error('experience_years')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="single-resume-feild feild-flex-2">
                                        <div class="single-input">
                                            <label for="date_of_birth">Ngày sinh</label>
                                            <input id="date_of_birth" type="date" wire:model.defer="personal_info.date_of_birth">
                                            @error('personal_info.date_of_birth')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="single-input">
                                            <label for="gender">Giới tính</label>
                                            <input id="gender" type="text" wire:model.defer="personal_info.gender" placeholder="Nam/Nữ/Khác">
                                            @error('personal_info.gender')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="single-resume-feild feild-flex-2">
                                        <div class="single-input">
                                            <label for="country">Quốc gia</label>
                                            <input id="country" type="text" wire:model.defer="personal_info.country">
                                            @error('personal_info.country')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="single-input">
                                            <label for="city">Thành phố</label>
                                            <input id="city" type="text" wire:model.defer="personal_info.city">
                                            @error('personal_info.city')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="single-resume-feild">
                                        <div class="single-input">
                                            <label for="address">Địa chỉ</label>
                                            <input id="address" type="text" wire:model.defer="personal_info.address">
                                            @error('personal_info.address')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="single-resume-feild feild-flex-2">
                                        <div class="single-input">
                                            <label for="website">Website</label>
                                            <input id="website" type="text" wire:model.defer="personal_info.website" placeholder="https://...">
                                            @error('personal_info.website')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="single-input">
                                            <label for="linkedin">LinkedIn</label>
                                            <input id="linkedin" type="text" wire:model.defer="personal_info.linkedin" placeholder="https://linkedin.com/in/...">
                                            @error('personal_info.linkedin')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div id="career-objective" class="single-resume-feild">
                                        <h4 style="margin: 6px 0 10px;">Mục tiêu nghề nghiệp</h4>
                                        <div class="single-input">
                                            <textarea rows="4" wire:model.defer="career_objective" placeholder="Mô tả mục tiêu nghề nghiệp của bạn..."></textarea>
                                            @error('career_objective')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div id="desired-job" class="single-resume-feild">
                                        <h4 style="margin: 6px 0 10px;">Công việc mong muốn</h4>
                                    </div>

                                    <div class="single-resume-feild feild-flex-2">
                                        <div class="single-input">
                                            <label for="desired_position">Vị trí</label>
                                            <input id="desired_position" type="text" wire:model.defer="desired_job.position">
                                        </div>
                                        <div class="single-input">
                                            <label for="desired_level">Cấp bậc</label>
                                            <input id="desired_level" type="text" wire:model.defer="desired_job.level">
                                        </div>
                                    </div>

                                    <div class="single-resume-feild feild-flex-2">
                                        <div class="single-input">
                                            <label for="desired_workplace">Hình thức</label>
                                            <input id="desired_workplace" type="text" wire:model.defer="desired_job.workplace" placeholder="Onsite/Remote/Hybrid">
                                        </div>
                                        <div class="single-input">
                                            <label for="desired_salary">Mức lương kỳ vọng</label>
                                            <input id="desired_salary" type="text" wire:model.defer="desired_job.expected_salary" placeholder="VD: 20-30 triệu">
                                        </div>
                                    </div>

                                    <div class="single-resume-feild">
                                        <div class="single-input">
                                            <label for="desired_location">Địa điểm mong muốn</label>
                                            <input id="desired_location" type="text" wire:model.defer="desired_job.location">
                                        </div>
                                    </div>

                                    <div id="experiences" class="single-resume-feild">
                                        <h4 style="margin: 6px 0 10px;">Kinh nghiệm làm việc</h4>
                                        <button type="button" class="jobguru-btn-2" wire:click="addExperience">+ Thêm kinh nghiệm</button>
                                    </div>

                                    @foreach ($experiences as $i => $exp)
                                        <div class="single-resume-feild" style="border:1px solid #eee; padding:12px; margin-top:12px;">
                                            <div class="single-resume-feild feild-flex-2">
                                                <div class="single-input">
                                                    <label>Công ty</label>
                                                    <input type="text" wire:model.defer="experiences.{{ $i }}.company">
                                                </div>
                                                <div class="single-input">
                                                    <label>Vị trí</label>
                                                    <input type="text" wire:model.defer="experiences.{{ $i }}.position">
                                                </div>
                                            </div>
                                            <div class="single-resume-feild feild-flex-2">
                                                <div class="single-input">
                                                    <label>Từ</label>
                                                    <input type="text" wire:model.defer="experiences.{{ $i }}.from" placeholder="MM/YYYY">
                                                </div>
                                                <div class="single-input">
                                                    <label>Đến</label>
                                                    <input type="text" wire:model.defer="experiences.{{ $i }}.to" placeholder="MM/YYYY hoặc Hiện tại">
                                                </div>
                                            </div>
                                            <div class="single-resume-feild">
                                                <div class="single-input">
                                                    <label>Mô tả</label>
                                                    <textarea rows="3" wire:model.defer="experiences.{{ $i }}.description"></textarea>
                                                </div>
                                            </div>
                                            <button type="button" class="jobguru-btn-2" wire:click="removeExperience({{ $i }})">Xóa</button>
                                        </div>
                                    @endforeach

                                    <div id="educations" class="single-resume-feild" style="margin-top:18px;">
                                        <h4 style="margin: 6px 0 10px;">Học vấn</h4>
                                        <button type="button" class="jobguru-btn-2" wire:click="addEducation">+ Thêm học vấn</button>
                                    </div>

                                    @foreach ($educations as $i => $edu)
                                        <div class="single-resume-feild" style="border:1px solid #eee; padding:12px; margin-top:12px;">
                                            <div class="single-resume-feild feild-flex-2">
                                                <div class="single-input">
                                                    <label>Trường</label>
                                                    <input type="text" wire:model.defer="educations.{{ $i }}.school">
                                                </div>
                                                <div class="single-input">
                                                    <label>Bằng cấp/Chuyên ngành</label>
                                                    <input type="text" wire:model.defer="educations.{{ $i }}.degree">
                                                </div>
                                            </div>
                                            <div class="single-resume-feild feild-flex-2">
                                                <div class="single-input">
                                                    <label>Từ</label>
                                                    <input type="text" wire:model.defer="educations.{{ $i }}.from" placeholder="MM/YYYY">
                                                </div>
                                                <div class="single-input">
                                                    <label>Đến</label>
                                                    <input type="text" wire:model.defer="educations.{{ $i }}.to" placeholder="MM/YYYY">
                                                </div>
                                            </div>
                                            <div class="single-resume-feild">
                                                <div class="single-input">
                                                    <label>Mô tả</label>
                                                    <textarea rows="3" wire:model.defer="educations.{{ $i }}.description"></textarea>
                                                </div>
                                            </div>
                                            <button type="button" class="jobguru-btn-2" wire:click="removeEducation({{ $i }})">Xóa</button>
                                        </div>
                                    @endforeach

                                    <div id="certifications" class="single-resume-feild" style="margin-top:18px;">
                                        <h4 style="margin: 6px 0 10px;">Chứng chỉ khác</h4>
                                        <button type="button" class="jobguru-btn-2" wire:click="addCertification">+ Thêm chứng chỉ</button>
                                    </div>

                                    @foreach ($certifications as $i => $cert)
                                        <div class="single-resume-feild" style="border:1px solid #eee; padding:12px; margin-top:12px;">
                                            <div class="single-resume-feild feild-flex-2">
                                                <div class="single-input">
                                                    <label>Tên chứng chỉ</label>
                                                    <input type="text" wire:model.defer="certifications.{{ $i }}.name">
                                                </div>
                                                <div class="single-input">
                                                    <label>Tổ chức</label>
                                                    <input type="text" wire:model.defer="certifications.{{ $i }}.issuer">
                                                </div>
                                            </div>
                                            <div class="single-resume-feild feild-flex-2">
                                                <div class="single-input">
                                                    <label>Ngày</label>
                                                    <input type="text" wire:model.defer="certifications.{{ $i }}.date" placeholder="MM/YYYY">
                                                </div>
                                                <div class="single-input">
                                                    <label>Mô tả</label>
                                                    <input type="text" wire:model.defer="certifications.{{ $i }}.description">
                                                </div>
                                            </div>
                                            <button type="button" class="jobguru-btn-2" wire:click="removeCertification({{ $i }})">Xóa</button>
                                        </div>
                                    @endforeach

                                    <div id="languages" class="single-resume-feild" style="margin-top:18px;">
                                        <h4 style="margin: 6px 0 10px;">Ngôn ngữ</h4>
                                        <button type="button" class="jobguru-btn-2" wire:click="addLanguage">+ Thêm ngôn ngữ</button>
                                    </div>

                                    @foreach ($languages as $i => $lang)
                                        <div class="single-resume-feild feild-flex-2" style="border:1px solid #eee; padding:12px; margin-top:12px;">
                                            <div class="single-input">
                                                <label>Ngôn ngữ</label>
                                                <input type="text" wire:model.defer="languages.{{ $i }}.name">
                                            </div>
                                            <div class="single-input">
                                                <label>Trình độ</label>
                                                <input type="text" wire:model.defer="languages.{{ $i }}.level">
                                            </div>
                                            <button type="button" class="jobguru-btn-2" wire:click="removeLanguage({{ $i }})">Xóa</button>
                                        </div>
                                    @endforeach

                                    <div id="skills" class="single-resume-feild" style="margin-top:18px;">
                                        <h4 style="margin: 6px 0 10px;">Kỹ năng chuyên môn</h4>
                                        <button type="button" class="jobguru-btn-2" wire:click="addSkill">+ Thêm kỹ năng</button>
                                    </div>

                                    @foreach ($skills as $i => $skill)
                                        <div class="single-resume-feild feild-flex-2" style="border:1px solid #eee; padding:12px; margin-top:12px;">
                                            <div class="single-input">
                                                <label>Kỹ năng</label>
                                                <input type="text" wire:model.defer="skills.{{ $i }}.name">
                                            </div>
                                            <div class="single-input">
                                                <label>Trình độ</label>
                                                <input type="text" wire:model.defer="skills.{{ $i }}.level">
                                            </div>
                                            <button type="button" class="jobguru-btn-2" wire:click="removeSkill({{ $i }})">Xóa</button>
                                        </div>
                                    @endforeach

                                    <div id="achievements" class="single-resume-feild" style="margin-top:18px;">
                                        <h4 style="margin: 6px 0 10px;">Thành tích nổi bật</h4>
                                        <button type="button" class="jobguru-btn-2" wire:click="addAchievement">+ Thêm thành tích</button>
                                    </div>

                                    @foreach ($achievements as $i => $ach)
                                        <div class="single-resume-feild" style="border:1px solid #eee; padding:12px; margin-top:12px;">
                                            <div class="single-resume-feild feild-flex-2">
                                                <div class="single-input">
                                                    <label>Tiêu đề</label>
                                                    <input type="text" wire:model.defer="achievements.{{ $i }}.title">
                                                </div>
                                                <div class="single-input">
                                                    <label>Ngày</label>
                                                    <input type="text" wire:model.defer="achievements.{{ $i }}.date" placeholder="MM/YYYY">
                                                </div>
                                            </div>
                                            <div class="single-resume-feild">
                                                <div class="single-input">
                                                    <label>Mô tả</label>
                                                    <textarea rows="3" wire:model.defer="achievements.{{ $i }}.description"></textarea>
                                                </div>
                                            </div>
                                            <button type="button" class="jobguru-btn-2" wire:click="removeAchievement({{ $i }})">Xóa</button>
                                        </div>
                                    @endforeach

                                    <div id="activities" class="single-resume-feild" style="margin-top:18px;">
                                        <h4 style="margin: 6px 0 10px;">Hoạt động khác</h4>
                                        <button type="button" class="jobguru-btn-2" wire:click="addActivity">+ Thêm hoạt động</button>
                                    </div>

                                    @foreach ($activities as $i => $act)
                                        <div class="single-resume-feild" style="border:1px solid #eee; padding:12px; margin-top:12px;">
                                            <div class="single-resume-feild feild-flex-2">
                                                <div class="single-input">
                                                    <label>Tiêu đề</label>
                                                    <input type="text" wire:model.defer="activities.{{ $i }}.title">
                                                </div>
                                                <div class="single-input">
                                                    <label>Từ</label>
                                                    <input type="text" wire:model.defer="activities.{{ $i }}.from" placeholder="MM/YYYY">
                                                </div>
                                                <div class="single-input">
                                                    <label>Đến</label>
                                                    <input type="text" wire:model.defer="activities.{{ $i }}.to" placeholder="MM/YYYY">
                                                </div>
                                            </div>
                                            <div class="single-resume-feild">
                                                <div class="single-input">
                                                    <label>Mô tả</label>
                                                    <textarea rows="3" wire:model.defer="activities.{{ $i }}.description"></textarea>
                                                </div>
                                            </div>
                                            <button type="button" class="jobguru-btn-2" wire:click="removeActivity({{ $i }})">Xóa</button>
                                        </div>
                                    @endforeach

                                    <div id="references" class="single-resume-feild" style="margin-top:18px;">
                                        <h4 style="margin: 6px 0 10px;">Người tham khảo</h4>
                                        <button type="button" class="jobguru-btn-2" wire:click="addReference">+ Thêm người tham khảo</button>
                                    </div>

                                    @foreach ($references as $i => $ref)
                                        <div class="single-resume-feild" style="border:1px solid #eee; padding:12px; margin-top:12px;">
                                            <div class="single-resume-feild feild-flex-2">
                                                <div class="single-input">
                                                    <label>Họ tên</label>
                                                    <input type="text" wire:model.defer="references.{{ $i }}.name">
                                                </div>
                                                <div class="single-input">
                                                    <label>Công ty</label>
                                                    <input type="text" wire:model.defer="references.{{ $i }}.company">
                                                </div>
                                            </div>
                                            <div class="single-resume-feild feild-flex-2">
                                                <div class="single-input">
                                                    <label>Chức danh</label>
                                                    <input type="text" wire:model.defer="references.{{ $i }}.position">
                                                </div>
                                                <div class="single-input">
                                                    <label>SĐT</label>
                                                    <input type="text" wire:model.defer="references.{{ $i }}.phone">
                                                </div>
                                            </div>
                                            <div class="single-resume-feild">
                                                <div class="single-input">
                                                    <label>Email</label>
                                                    <input type="text" wire:model.defer="references.{{ $i }}.email">
                                                </div>
                                            </div>
                                            <div class="single-resume-feild">
                                                <div class="single-input">
                                                    <label>Ghi chú</label>
                                                    <textarea rows="2" wire:model.defer="references.{{ $i }}.note"></textarea>
                                                </div>
                                            </div>
                                            <button type="button" class="jobguru-btn-2" wire:click="removeReference({{ $i }})">Xóa</button>
                                        </div>
                                    @endforeach

                                    <div class="single-resume-feild" style="margin-top:18px;">
                                        <h4 style="margin: 6px 0 10px;">Thông tin bổ sung</h4>
                                        <div class="single-input">
                                            <textarea rows="4" wire:model.defer="extra" placeholder="Các hoạt động/giải thưởng/ghi chú khác..."></textarea>
                                            @error('extra')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="single-resume-feild">
                                        <div class="single-input">
                                            <label for="cv">CV (PDF/DOC/DOCX, tối đa 10MB)</label>
                                            <input
                                                id="cv"
                                                type="file"
                                                wire:model="cv"
                                                accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                            >

                                            @error('cv')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror

                                            <div wire:loading wire:target="cv" class="mt-2">Đang tải lên…</div>

                                            @if ($this->currentCvUrl)
                                                <div class="mt-2">
                                                    <a href="{{ $this->currentCvUrl }}" target="_blank" rel="noopener">Tải CV hiện tại</a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="submit-resume">
                                    <button type="submit" wire:loading.attr="disabled">Cập nhật</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@php
    $sections = [
        ['id' => 'personal-info', 'label' => 'Thông tin', 'index' => '01'],
        ['id' => 'career-objective', 'label' => 'Mục tiêu', 'index' => '02'],
        ['id' => 'desired-job', 'label' => 'Mong muốn', 'index' => '03'],
        ['id' => 'experiences', 'label' => 'Kinh nghiệm', 'index' => '04'],
        ['id' => 'educations', 'label' => 'Học vấn', 'index' => '05'],
        ['id' => 'skills', 'label' => 'Kỹ năng', 'index' => '06'],
        ['id' => 'languages', 'label' => 'Ngôn ngữ', 'index' => '07'],
        ['id' => 'certifications', 'label' => 'Chứng chỉ', 'index' => '08'],
        ['id' => 'extra-info', 'label' => 'CV', 'index' => '09'],
    ];

    $isApplicationReady = empty($missingApplicationFields);
@endphp

<div class="profile-redesign candidate-profile-redesign" x-data="{ activeSection: $wire.entangle('activeSection') }">
    @if (session('status'))
        <div class="profile-redesign__alert" role="status">
            {{ session('status') }}
        </div>
    @endif

    <section class="profile-redesign__section">
        <div class="profile-redesign__container">
            <aside class="profile-redesign__rail profile-redesign__rail--premium">
                <a href="{{ route('candidates.candidate_dashboard') }}" class="profile-redesign__back">
                    <span>←</span>
                    Dashboard
                </a>

                <div class="profile-redesign__status-shell">
                    <div class="profile-redesign__status-core">
                        <span class="profile-redesign__eyebrow">Điều kiện ứng tuyển</span>
                        <div class="profile-redesign__score">
                            <strong>{{ $applicationCompletion }}%</strong>
                            <span>{{ $isApplicationReady ? 'Sẵn sàng apply' : 'Cần bổ sung' }}</span>
                        </div>
                        <div class="profile-redesign__progress-track">
                            <span style="width: {{ $applicationCompletion }}%"></span>
                        </div>

                        @if ($isApplicationReady)
                            <p class="profile-redesign__status-note is-ready">Hồ sơ đã đủ họ tên, email, số điện thoại và CV.</p>
                        @else
                            <div class="profile-redesign__missing-list">
                                <span>Còn thiếu</span>
                                @foreach ($missingApplicationFields as $field)
                                    <strong>{{ $field }}</strong>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <nav class="profile-redesign__nav" aria-label="Điều hướng hồ sơ">
                    @foreach ($sections as $section)
                        <button
                            type="button"
                            wire:click="switchSection('{{ $section['id'] }}')"
                            wire:loading.attr="disabled"
                            wire:target="switchSection,saveSection,save"
                            class="profile-redesign__nav-item {{ $activeSection === $section['id'] ? 'is-active' : '' }}"
                        >
                            <em>{{ $section['index'] }}</em>
                            <span>{{ $section['label'] }}</span>
                        </button>
                    @endforeach
                </nav>
            </aside>

            <main class="profile-redesign__main">
                <header class="profile-redesign__hero profile-redesign__hero--candidate">
                    <div class="profile-redesign__avatar-wrap">
                        <img src="{{ $avatar ? $avatar->temporaryUrl() : $this->currentAvatarUrl }}" alt="Avatar ứng viên">
                        <input type="file" id="avatar_upload" wire:model="avatar" class="d-none">
                        <label for="avatar_upload" class="profile-redesign__avatar-action" title="Cập nhật ảnh đại diện">
                            +
                        </label>
                    </div>

                    <div class="profile-redesign__hero-copy">
                        <span class="profile-redesign__eyebrow">Hồ sơ ứng viên</span>
                        <h1>{{ $name ?: 'Ứng viên' }}</h1>
                        <p>{{ $profile_title ?: 'Cập nhật hồ sơ rõ ràng để nhà tuyển dụng hiểu định hướng, năng lực và mức độ sẵn sàng của bạn.' }}</p>
                        <div class="profile-redesign__meta-row">
                            <span>{{ $email ?: 'Chưa có email' }}</span>
                            <span>{{ $phone ?: 'Chưa cập nhật số điện thoại' }}</span>
                        </div>
                    </div>
                </header>

                <form wire:submit.prevent="save" class="profile-redesign__form" novalidate>
                    @if ($lastSavedSectionLabel)
                        <div class="profile-redesign__save-state" role="status" aria-live="polite">
                            Đã lưu {{ $lastSavedSectionLabel }}.
                        </div>
                    @endif

                    <section x-show="activeSection === 'personal-info'" x-transition.opacity class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>01</span>
                                <h2>Thông tin cá nhân</h2>
                            </div>
                        </div>

                        <div class="profile-redesign__grid">
                            <label class="profile-redesign__field profile-redesign__field--wide">
                                <span>Tiêu đề hồ sơ</span>
                                <input type="text" wire:model.defer="profile_title" placeholder="VD: Backend Developer" />
                                @error('profile_title') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field">
                                <span>Số năm kinh nghiệm</span>
                                <input type="number" wire:model.defer="experience_years" placeholder="0" />
                                @error('experience_years') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field">
                                <span>Họ tên</span>
                                <input type="text" wire:model.defer="name" />
                                @error('name') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field">
                                <span>Email liên hệ</span>
                                <input type="email" wire:model.defer="email" />
                                @error('email') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field">
                                <span>Số điện thoại</span>
                                <input type="text" wire:model.defer="phone" placeholder="0901234567" />
                                @error('phone') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field">
                                <span>Giới tính</span>
                                <input type="text" wire:model.defer="personal_info.gender" placeholder="Nam / Nữ / Khác" />
                            </label>
                            <label class="profile-redesign__field profile-redesign__field--full">
                                <span>Địa chỉ</span>
                                <input type="text" wire:model.defer="personal_info.address" />
                            </label>
                        </div>

                        <div class="profile-redesign__actions">
                            <button type="button" wire:click="saveSection('career-objective')" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & tiếp theo</span>
                                <span wire:loading wire:target="saveSection">Đang lưu...</span>
                                <span>→</span>
                            </button>
                        </div>
                    </section>

                    <section x-show="activeSection === 'career-objective'" x-transition.opacity class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>02</span>
                                <h2>Mục tiêu nghề nghiệp</h2>
                            </div>
                        </div>
                        <label class="profile-redesign__field">
                            <span>Giới thiệu bản thân và mục tiêu ứng tuyển</span>
                            <textarea wire:model.defer="career_objective" rows="7" placeholder="Viết ngắn gọn về định hướng, thế mạnh và mục tiêu phát triển của bạn."></textarea>
                            @error('career_objective') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                        </label>
                        <div class="profile-redesign__actions">
                            <button type="button" wire:click="saveSection('desired-job')" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & tiếp theo</span>
                                <span wire:loading wire:target="saveSection">Đang lưu...</span>
                                <span>→</span>
                            </button>
                        </div>
                    </section>

                    <section x-show="activeSection === 'desired-job'" x-transition.opacity class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>03</span>
                                <h2>Công việc mong muốn</h2>
                            </div>
                        </div>
                        <div class="profile-redesign__grid profile-redesign__grid--two">
                            <label class="profile-redesign__field">
                                <span>Vị trí mong muốn</span>
                                <input type="text" wire:model.defer="desired_job.position" />
                            </label>
                            <label class="profile-redesign__field">
                                <span>Mức lương kỳ vọng</span>
                                <input type="text" wire:model.defer="desired_job.expected_salary" />
                            </label>
                            <label class="profile-redesign__field">
                                <span>Cấp bậc</span>
                                <input type="text" wire:model.defer="desired_job.level" />
                            </label>
                            <label class="profile-redesign__field">
                                <span>Địa điểm làm việc</span>
                                <input type="text" wire:model.defer="desired_job.location" />
                            </label>
                        </div>
                        <div class="profile-redesign__actions">
                            <button type="button" wire:click="saveSection('experiences')" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & tiếp theo</span>
                                <span wire:loading wire:target="saveSection">Đang lưu...</span>
                                <span>→</span>
                            </button>
                        </div>
                    </section>

                    <section x-show="activeSection === 'experiences'" x-transition.opacity class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>04</span>
                                <h2>Kinh nghiệm làm việc</h2>
                            </div>
                            <button type="button" wire:click="addExperience" class="profile-redesign__ghost-btn">Thêm</button>
                        </div>

                        <div class="profile-redesign__stack">
                            @forelse($experiences as $index => $exp)
                                <div class="profile-redesign__repeat">
                                    <button type="button" wire:click="removeExperience({{ $index }})" class="profile-redesign__remove">Xóa</button>
                                    <div class="profile-redesign__grid profile-redesign__grid--two">
                                        <label class="profile-redesign__field">
                                            <span>Tên công ty</span>
                                            <input type="text" wire:model.defer="experiences.{{ $index }}.company" />
                                        </label>
                                        <label class="profile-redesign__field">
                                            <span>Chức danh</span>
                                            <input type="text" wire:model.defer="experiences.{{ $index }}.position" />
                                        </label>
                                        <label class="profile-redesign__field">
                                            <span>Bắt đầu</span>
                                            <input type="text" wire:model.defer="experiences.{{ $index }}.from" placeholder="01/2023" />
                                        </label>
                                        <label class="profile-redesign__field">
                                            <span>Kết thúc</span>
                                            <input type="text" wire:model.defer="experiences.{{ $index }}.to" placeholder="Hiện tại" />
                                        </label>
                                        <label class="profile-redesign__field profile-redesign__field--full">
                                            <span>Mô tả công việc</span>
                                            <textarea wire:model.defer="experiences.{{ $index }}.description" rows="4"></textarea>
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <button type="button" wire:click="addExperience" class="profile-redesign__empty-action">
                                    Thêm kinh nghiệm đầu tiên
                                </button>
                            @endforelse
                        </div>

                        <div class="profile-redesign__actions">
                            <button type="button" wire:click="saveSection('educations')" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & tiếp theo</span>
                                <span wire:loading wire:target="saveSection">Đang lưu...</span>
                                <span>→</span>
                            </button>
                        </div>
                    </section>

                    <section x-show="activeSection === 'educations'" x-transition.opacity class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>05</span>
                                <h2>Học vấn</h2>
                            </div>
                            <button type="button" wire:click="addEducation" class="profile-redesign__ghost-btn">Thêm</button>
                        </div>

                        <div class="profile-redesign__stack">
                            @forelse($educations as $index => $edu)
                                <div class="profile-redesign__repeat">
                                    <button type="button" wire:click="removeEducation({{ $index }})" class="profile-redesign__remove">Xóa</button>
                                    <div class="profile-redesign__grid">
                                        <label class="profile-redesign__field profile-redesign__field--wide">
                                            <span>Trường học</span>
                                            <input type="text" wire:model.defer="educations.{{ $index }}.school" />
                                        </label>
                                        <label class="profile-redesign__field">
                                            <span>Bằng cấp</span>
                                            <input type="text" wire:model.defer="educations.{{ $index }}.degree" />
                                        </label>
                                        <label class="profile-redesign__field">
                                            <span>Bắt đầu</span>
                                            <input type="text" wire:model.defer="educations.{{ $index }}.from" />
                                        </label>
                                        <label class="profile-redesign__field">
                                            <span>Kết thúc</span>
                                            <input type="text" wire:model.defer="educations.{{ $index }}.to" />
                                        </label>
                                        <label class="profile-redesign__field profile-redesign__field--full">
                                            <span>Mô tả</span>
                                            <textarea wire:model.defer="educations.{{ $index }}.description" rows="3"></textarea>
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <button type="button" wire:click="addEducation" class="profile-redesign__empty-action">
                                    Thêm học vấn
                                </button>
                            @endforelse
                        </div>

                        <div class="profile-redesign__actions">
                            <button type="button" wire:click="saveSection('skills')" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & tiếp theo</span>
                                <span wire:loading wire:target="saveSection">Đang lưu...</span>
                                <span>→</span>
                            </button>
                        </div>
                    </section>

                    <section x-show="activeSection === 'skills'" x-transition.opacity class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>06</span>
                                <h2>Kỹ năng chuyên môn</h2>
                            </div>
                            <button type="button" wire:click="addSkill" class="profile-redesign__ghost-btn">Thêm</button>
                        </div>
                        <div class="profile-redesign__grid profile-redesign__grid--two">
                            @forelse($skills as $index => $skill)
                                <div class="profile-redesign__inline-field">
                                    <input type="text" wire:model.defer="skills.{{ $index }}.name" placeholder="Tên kỹ năng" />
                                    <input type="text" wire:model.defer="skills.{{ $index }}.level" placeholder="Mức độ" />
                                    <button type="button" wire:click="removeSkill({{ $index }})">Xóa</button>
                                </div>
                            @empty
                                <button type="button" wire:click="addSkill" class="profile-redesign__empty-action">
                                    Thêm kỹ năng mới
                                </button>
                            @endforelse
                        </div>
                        <div class="profile-redesign__actions">
                            <button type="button" wire:click="saveSection('languages')" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & tiếp theo</span>
                                <span wire:loading wire:target="saveSection">Đang lưu...</span>
                                <span>→</span>
                            </button>
                        </div>
                    </section>

                    <section x-show="activeSection === 'languages'" x-transition.opacity class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>07</span>
                                <h2>Ngôn ngữ</h2>
                            </div>
                            <button type="button" wire:click="addLanguage" class="profile-redesign__ghost-btn">Thêm</button>
                        </div>
                        <div class="profile-redesign__stack">
                            @forelse($languages as $index => $lang)
                                <div class="profile-redesign__row-repeat">
                                    <input type="text" wire:model.defer="languages.{{ $index }}.name" placeholder="VD: Tiếng Anh" />
                                    <input type="text" wire:model.defer="languages.{{ $index }}.level" placeholder="VD: IELTS 7.0" />
                                    <button type="button" wire:click="removeLanguage({{ $index }})">Xóa</button>
                                </div>
                            @empty
                                <button type="button" wire:click="addLanguage" class="profile-redesign__empty-action">
                                    Thêm ngôn ngữ
                                </button>
                            @endforelse
                        </div>
                        <div class="profile-redesign__actions">
                            <button type="button" wire:click="saveSection('certifications')" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & tiếp theo</span>
                                <span wire:loading wire:target="saveSection">Đang lưu...</span>
                                <span>→</span>
                            </button>
                        </div>
                    </section>

                    <section x-show="activeSection === 'certifications'" x-transition.opacity class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>08</span>
                                <h2>Chứng chỉ</h2>
                            </div>
                            <button type="button" wire:click="addCertification" class="profile-redesign__ghost-btn">Thêm</button>
                        </div>
                        <div class="profile-redesign__stack">
                            @forelse($certifications as $index => $cert)
                                <div class="profile-redesign__repeat">
                                    <button type="button" wire:click="removeCertification({{ $index }})" class="profile-redesign__remove">Xóa</button>
                                    <div class="profile-redesign__grid">
                                        <label class="profile-redesign__field profile-redesign__field--wide">
                                            <span>Tên chứng chỉ</span>
                                            <input type="text" wire:model.defer="certifications.{{ $index }}.name" />
                                        </label>
                                        <label class="profile-redesign__field">
                                            <span>Tổ chức cấp</span>
                                            <input type="text" wire:model.defer="certifications.{{ $index }}.issuer" />
                                        </label>
                                        <label class="profile-redesign__field">
                                            <span>Thời gian</span>
                                            <input type="text" wire:model.defer="certifications.{{ $index }}.date" />
                                        </label>
                                        <label class="profile-redesign__field profile-redesign__field--full">
                                            <span>Mô tả</span>
                                            <textarea wire:model.defer="certifications.{{ $index }}.description" rows="3"></textarea>
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <button type="button" wire:click="addCertification" class="profile-redesign__empty-action">
                                    Thêm chứng chỉ
                                </button>
                            @endforelse
                        </div>
                        <div class="profile-redesign__actions">
                            <button type="button" wire:click="saveSection('extra-info')" wire:loading.attr="disabled" wire:target="saveSection">
                                <span wire:loading.remove wire:target="saveSection">Lưu & tiếp theo</span>
                                <span wire:loading wire:target="saveSection">Đang lưu...</span>
                                <span>→</span>
                            </button>
                        </div>
                    </section>

                    <section x-show="activeSection === 'extra-info'" x-transition.opacity class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>09</span>
                                <h2>CV & thông tin bổ sung</h2>
                            </div>
                        </div>

                        <div
                            class="profile-redesign__upload"
                            x-data="{ selectedCvName: '' }"
                        >
                            <div>
                                <span class="profile-redesign__upload-mark">CV</span>
                                <h3>Tải lên CV cá nhân</h3>
                                <p>Hỗ trợ PDF, DOC, DOCX. Dung lượng tối đa 10MB.</p>
                            </div>
                            <input
                                type="file"
                                id="cv_upload"
                                wire:model="cv"
                                class="d-none"
                                accept="{{ \App\Support\CvUpload::acceptAttribute() }}"
                                x-on:change="selectedCvName = $event.target.files?.[0]?.name || ''"
                            >
                            <label for="cv_upload">Chọn file</label>
                            <div wire:loading wire:target="cv" class="profile-redesign__uploading">Đang tải lên...</div>
                            <div
                                x-show="selectedCvName"
                                x-cloak
                                class="profile-redesign__selected-cv"
                                role="status"
                                aria-live="polite"
                            >
                                <span>Đã chọn</span>
                                <strong x-text="selectedCvName"></strong>
                                <small>Bấm lưu để cập nhật CV trong hồ sơ.</small>
                            </div>
                            @if($cv && method_exists($cv, 'getClientOriginalName'))
                                <div class="profile-redesign__selected-cv" role="status" aria-live="polite">
                                    <span>Đã tải lên tạm thời</span>
                                    <strong>{{ $cv->getClientOriginalName() }}</strong>
                                    <small>Bấm lưu để cập nhật CV trong hồ sơ.</small>
                                </div>
                            @endif
                            @if($this->currentCvUrl)
                                <a href="{{ $this->currentCvUrl }}" target="_blank" class="profile-redesign__current-cv">
                                    Xem CV hiện tại
                                </a>
                            @endif
                            @error('cv') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                        </div>

                        <label class="profile-redesign__field">
                            <span>Thông tin bổ sung</span>
                            <textarea wire:model.defer="extra" rows="5" placeholder="Giải thưởng, hoạt động ngoại khóa hoặc ghi chú khác..."></textarea>
                        </label>

                        <div class="profile-redesign__actions profile-redesign__actions--split">
                            <small>Hồ sơ đủ điều kiện ứng tuyển khi có họ tên, email, số điện thoại và CV.</small>
                            <button type="submit" class="profile-redesign__submit" wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">Lưu tất cả thay đổi</span>
                                <span wire:loading wire:target="save">Đang lưu...</span>
                                <span>→</span>
                            </button>
                        </div>
                    </section>
                </form>
            </main>
        </div>
    </section>
</div>

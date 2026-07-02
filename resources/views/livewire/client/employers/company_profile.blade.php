@php
    $isProfileReady = empty($missingProfileFields);
    $cityLabel = \App\Enums\VietnamProvince::tryFrom((string) $city)?->label() ?? $city;
@endphp

<div class="profile-redesign employer-profile-redesign">
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li class="active">Hồ sơ chi nhánh</li>
        </ul>
    </div>

    <section class="profile-redesign__section">
        <div class="profile-redesign__container">
            <aside class="profile-redesign__rail profile-redesign__rail--premium">
                <a href="{{ route('employers.dashboard') }}" class="profile-redesign__back">
                    <span>←</span>
                    Dashboard
                </a>

                <div class="profile-redesign__company-card">
                    <div class="profile-redesign__company-logo">
                        <img
                            src="{{ $logo ? $logo->temporaryUrl() : ($branch?->image ? asset('storage/' . ltrim($branch->image, '/')) : asset('assets/img/company-logo-1.png')) }}"
                            alt="{{ $name ?: 'Logo chi nhánh' }}"
                        >
                    </div>

                    @if($canEdit)
                        <input
                            type="file"
                            id="company_logo_upload"
                            wire:model="logo"
                            class="d-none"
                            accept="image/jpeg,image/png,image/webp"
                        >
                        <label for="company_logo_upload" class="profile-redesign__current-cv">Chọn logo</label>
                        <div wire:loading wire:target="logo" class="profile-redesign__uploading">Đang tải logo...</div>
                        @if($logo && method_exists($logo, 'getClientOriginalName'))
                            <div class="profile-redesign__selected-cv" role="status" aria-live="polite">
                                <span>Logo mới</span>
                                <strong>{{ $logo->getClientOriginalName() }}</strong>
                                <small>Bấm cập nhật để lưu logo.</small>
                            </div>
                        @endif
                        @error('logo') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                    @endif

                    <span>{{ $canEdit ? 'Giám đốc chi nhánh có quyền cập nhật' : 'Chế độ xem' }}</span>
                    <strong>{{ $name ?: 'Hồ sơ chi nhánh' }}</strong>
                    <p>{{ $code ? 'Mã chi nhánh: ' . $code : 'Thông tin chi nhánh tuyển dụng' }}</p>
                </div>

                <div class="profile-redesign__status-shell">
                    <div class="profile-redesign__status-core">
                        <span class="profile-redesign__eyebrow">Mức hoàn thiện</span>
                        <div class="profile-redesign__score">
                            <strong>{{ $profileCompletion }}%</strong>
                            <span>{{ $isProfileReady ? 'Đã sẵn sàng' : 'Cần bổ sung' }}</span>
                        </div>
                        <div class="profile-redesign__progress-track">
                            <span style="width: {{ $profileCompletion }}%"></span>
                        </div>

                        @if ($isProfileReady)
                            <p class="profile-redesign__status-note is-ready">Hồ sơ chi nhánh đã đủ thông tin hiển thị cho ứng viên.</p>
                        @else
                            <div class="profile-redesign__missing-list">
                                <span>Còn thiếu</span>
                                @foreach ($missingProfileFields as $field)
                                    <strong>{{ $field }}</strong>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="profile-redesign__summary-list">
                    <div>
                        <span>Tỉnh/TP</span>
                        <strong>{{ $province_code ?: '-' }}</strong>
                    </div>
                    <div>
                        <span>Địa phương</span>
                        <strong>{{ $cityLabel ?: '-' }}</strong>
                    </div>
                    <div>
                        <span>Trạng thái</span>
                        <strong>{{ $branch?->is_active ? 'Đang hoạt động' : 'Chưa kích hoạt' }}</strong>
                    </div>
                </div>
            </aside>

            <main class="profile-redesign__main">
                <header class="profile-redesign__hero profile-redesign__hero--company">
                    <div class="profile-redesign__hero-copy">
                        <span class="profile-redesign__eyebrow">Hồ sơ nhà tuyển dụng</span>
                        <h1>{{ $name ?: 'Hồ sơ chi nhánh' }}</h1>
                        <p>{{ $address ?: 'Quản lý thông tin nhận diện, liên hệ và mô tả chi nhánh để ứng viên có đủ dữ liệu trước khi ứng tuyển.' }}</p>
                    </div>
                </header>

                <form class="profile-redesign__form" wire:submit.prevent="save" novalidate>
                    <section class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>01</span>
                                <h2>Thông tin chi nhánh</h2>
                            </div>
                        </div>

                        <div class="profile-redesign__grid">
                            <label class="profile-redesign__field profile-redesign__field--wide">
                                <span>Tên chi nhánh</span>
                                <input type="text" wire:model.defer="name" @readonly(! $canEdit)>
                                @error('name') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field">
                                <span>Mã chi nhánh</span>
                                <input type="text" wire:model.defer="code" readonly>
                            </label>
                            <label class="profile-redesign__field">
                                <span>Số lượng nhân sự</span>
                                <input type="number" min="0" wire:model.defer="employee_count" placeholder="Chưa cập nhật" @readonly(! $canEdit)>
                                @error('employee_count') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field">
                                <span>Tỉnh/TP</span>
                                <select wire:model.live="city" @disabled(! $canEdit)>
                                    <option value="">Chọn tỉnh/thành phố</option>
                                    @foreach($provinceOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('city') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field">
                                <span>Mã tỉnh</span>
                                <input type="text" wire:model.defer="province_code" readonly>
                                @error('province_code') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field profile-redesign__field--full">
                                <span>Mô tả chi tiết</span>
                                <textarea rows="7" wire:model.defer="description" @readonly(! $canEdit)></textarea>
                                @error('description') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                        </div>
                    </section>

                    <section class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>02</span>
                                <h2>Thông tin liên hệ</h2>
                            </div>
                        </div>

                        <div class="profile-redesign__grid profile-redesign__grid--two">
                            <label class="profile-redesign__field">
                                <span>Điện thoại</span>
                                <input type="text" wire:model.defer="phone" @readonly(! $canEdit)>
                                @error('phone') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field">
                                <span>Email liên hệ</span>
                                <input type="email" wire:model.defer="email_contact" @readonly(! $canEdit)>
                                @error('email_contact') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field profile-redesign__field--full">
                                <span>Địa chỉ hiện tại</span>
                                <input type="text" wire:model.defer="address" @readonly(! $canEdit)>
                                @error('address') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field profile-redesign__field--full">
                                <span>Website</span>
                                <input type="url" wire:model.defer="website" placeholder="https://..." @readonly(! $canEdit)>
                                @error('website') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                        </div>
                    </section>

                    <section class="profile-redesign__panel">
                        <div class="profile-redesign__panel-head">
                            <div>
                                <span>03</span>
                                <h2>Liên kết mạng xã hội</h2>
                            </div>
                        </div>

                        <div class="profile-redesign__grid profile-redesign__grid--two">
                            <label class="profile-redesign__field">
                                <span>Twitter</span>
                                <input type="url" wire:model.defer="twitter_url" placeholder="https://twitter.com/..." @readonly(! $canEdit)>
                                @error('twitter_url') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field">
                                <span>Facebook</span>
                                <input type="url" wire:model.defer="facebook_url" placeholder="https://facebook.com/..." @readonly(! $canEdit)>
                                @error('facebook_url') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                            <label class="profile-redesign__field profile-redesign__field--full">
                                <span>LinkedIn</span>
                                <input type="url" wire:model.defer="linkedin_url" placeholder="https://linkedin.com/company/..." @readonly(! $canEdit)>
                                @error('linkedin_url') <small class="profile-redesign__error">{{ $message }}</small> @enderror
                            </label>
                        </div>
                    </section>

                    @if($canEdit)
                        <div class="profile-redesign__actions">
                            <button type="submit" class="profile-redesign__submit" wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">Cập nhật hồ sơ chi nhánh</span>
                                <span wire:loading wire:target="save">Đang lưu...</span>
                                <span>→</span>
                            </button>
                        </div>
                    @else
                        <div class="profile-redesign__actions profile-redesign__actions--muted">
                            <button type="button" class="profile-redesign__submit" disabled>
                                Chỉ Giám đốc chi nhánh được cập nhật hồ sơ
                            </button>
                        </div>
                    @endif
                </form>
            </main>
        </div>
    </section>
</div>

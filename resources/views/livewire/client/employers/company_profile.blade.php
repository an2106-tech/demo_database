<div>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('employers.dashboard') }}">Nhà tuyển dụng</a></li>
            <li class="active">Hồ sơ công ty</li>
        </ul>
    </div>
    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>
                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right">
                        <div class="candidate-profile">
                            <div class="candidate-single-profile-info">
                                <div class="single-resume-feild resume-avatar">
                                    <div class="resume-image company-resume-image">
                                        <img src="{{ asset('assets/img/company_page_logo.jpg') }}" alt="resume avatar">
                                        <div class="resume-avatar-hover">
                                            <div class="resume-avatar-upload">
                                                <p>
                                                    <i class="fa fa-pencil"></i>
                                                    Chỉnh sửa
                                                </p>
                                                <input type="file">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="candidate-single-profile-info">
                                <form>
                                    <div class="premium-panel">
                                        <h3>Thông tin công ty</h3>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="name">Tên công ty:</label>
                                                <input type="text" value="{{ $branch?->name ?? '' }}" id="name" {{ $canEdit ? '' : 'readonly' }}>
                                            </div>
                                            <div class="single-input">
                                                <label for="c_cat">Mã chi nhánh:</label>
                                                <input type="text" value="{{ $branch?->code ?? '' }}" id="c_cat" readonly>
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="member">Số lượng nhân sự:</label>
                                                <input type="text" placeholder="Nhập số lượng" id="member" {{ $canEdit ? '' : 'readonly' }}>
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Location">Tỉnh/TP:</label>
                                                <input type="text" value="{{ $branch?->province_code ?? '' }}" id="Location" {{ $canEdit ? '' : 'readonly' }}>
                                            </div>
                                            <div class="single-input">
                                                <label for="City">Thành phố:</label>
                                                <input type="text" value="{{ $branch?->city ?? '' }}" id="City" {{ $canEdit ? '' : 'readonly' }}>
                                            </div>
                                        </div>
                                        <div class="single-resume-feild ">
                                            <div class="single-input">
                                                <label for="Bio">Mô tả chi tiết:</label>
                                                <textarea id="Bio" {{ $canEdit ? '' : 'readonly' }}>{{ $branch?->description ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="premium-panel">
                                        <h3>Thông tin liên hệ</h3>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Phone">Điện thoại:</label>
                                                <input type="text" value="{{ $branch?->phone ?? '' }}" id="Phone" {{ $canEdit ? '' : 'readonly' }}>
                                            </div>
                                            <div class="single-input">
                                                <label for="Email">Email liên hệ:</label>
                                                <input type="text" value="{{ $branch?->email_contact ?? '' }}" id="Email" {{ $canEdit ? '' : 'readonly' }}>
                                            </div>
                                        </div>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="Address22">Địa chỉ hiện tại:</label>
                                                <input type="text" value="{{ $branch?->address ?? '' }}" id="Address22" {{ $canEdit ? '' : 'readonly' }}>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="premium-panel">
                                        <h3>Liên kết mạng xã hội</h3>
                                        <div class="single-resume-feild feild-flex-2">
                                            <div class="single-input">
                                                <label for="twitter"><i class="fa fa-twitter twitter"></i> Twitter</label>
                                                <input type="text" value="https://www.twitter.com/" id="twitter">
                                            </div>
                                            <div class="single-input">
                                                <label for="facebook"><i class="fa fa-facebook facebook"></i> Facebook</label>
                                                <input type="text" value="https://www.facebook.com/" id="facebook">
                                            </div>
                                        </div>
                                    </div>
                                    @if($canEdit)
                                    <div class="submit-resume">
                                        <button type="submit">Cập nhật</button>
                                    </div>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Candidate Dashboard Area End -->
</div>

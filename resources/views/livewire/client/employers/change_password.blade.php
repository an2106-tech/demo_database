<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Đổi mật khẩu</h3>
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
                                <li><a href="{{ route('candidates.browse_job') }}">Ứng viên</a></li>
                                <li class="active-breadcromb"><a href="{{ route('employers.change_password') }}">Đổi mật khẩu</a></li>
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
                <div class="col-lg-3 col-md-4 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="dashboard-right">
                        <div class="change-pass manage-jobs">
                            <div class="manage-jobs-heading">
                                <h3>Đổi mật khẩu</h3>
                            </div>
                            <form>
                                <p>
                                    <label for="old_pass">Mật khẩu cũ</label>
                                    <input type="password" placeholder="*******" id="old_pass">
                                </p>
                                <p>
                                    <label for="new_pass">Mật khẩu mới</label>
                                    <input type="password" placeholder="*******" id="new_pass">
                                </p>
                                <p>
                                    <label for="confirm_pass">Xác nhận mật khẩu</label>
                                    <input type="password" placeholder="*******" id="confirm_pass">
                                </p>
                                <p>
                                    <button type="submit">Cập nhật</button>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

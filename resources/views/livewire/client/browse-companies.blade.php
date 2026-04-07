<div>
    <section class="jobguru-breadcromb-area">
        <div class="breadcromb-top section_100">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcromb-box">
                            <h3>Duyệt công ty</h3>
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
                                <li><a href="#">Ứng viên</a></li>
                                <li class="active-breadcromb"><a href="#">Duyệt công ty</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="jobguru-browse-company-area section_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-tabs" id="companyTabs" role="tablist">
                        @foreach ($letters as $letter)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="company_{{ strtolower($letter) }}_tab" data-bs-toggle="tab"
                                    href="#company_{{ strtolower($letter) }}" role="tab"
                                    aria-controls="company_{{ strtolower($letter) }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ strtolower($letter) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content" id="companyTabContent">
                        @foreach ($letters as $letter)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                id="company_{{ strtolower($letter) }}" role="tabpanel"
                                aria-labelledby="company_{{ strtolower($letter) }}_tab">
                                <div class="row">
                                    @forelse ($branchesByLetter->get($letter, collect()) as $branch)
                                        <div class="col-lg-4 col-md-6">
                                            <div class="single-browse-company">
                                                <div class="browse-company-logo">
                                                    <a href="#">
                                                        <img src="{{ $branch->image ? '/storage/' . ltrim($branch->image, '/') : asset('assets/img/company-logo-1.png') }}"
                                                            alt="{{ $branch->name }}"
                                                            style="display:block; width:120px; height:80px; margin:0 auto; object-fit:contain;">
                                                    </a>
                                                </div>
                                                <h3><a href="#">{{ $branch->name }}</a></h3>
                                                <ul>
                                                    <li><i class="fa fa-star"></i></li>
                                                    <li><i class="fa fa-star"></i></li>
                                                    <li><i class="fa fa-star"></i></li>
                                                    <li><i class="fa fa-star"></i></li>
                                                    <li><i class="fa fa-star-half-o"></i></li>
                                                </ul>
                                                <div class="single-browse-company-btn">
                                                    <a href="#" class="jobguru-btn">Xem hồ sơ</a>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center">
                                            <p>Không có công ty bắt đầu bằng chữ {{ $letter }}</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


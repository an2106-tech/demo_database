@php
    $isEmployer = $isEmployerFooter ?? false;
    $homeUrl = $isEmployer ? route('employers.portal') : route('home');
    $brandTitle = 'Tổ chức Giáo dục FPT';

    $primaryHeading = $isEmployer ? 'Nhà tuyển dụng' : 'Ứng viên';

    $primaryLinks = $isEmployer
        ? [
            ['label' => 'Trang tuyển dụng', 'url' => route('employers.portal')],
            ['label' => 'Đăng tin', 'url' => route('employers.post_job')],
            ['label' => 'Quản lý tin', 'url' => route('employers.manage_jobs')],
            ['label' => 'Giao diện ứng viên', 'url' => route('candidates.browse_job')],
        ]
        : [
            ['label' => 'Tìm việc làm', 'url' => route('candidates.browse_job')],
            ['label' => 'Ngành nghề', 'url' => route('candidates.browse_categories')],
            ['label' => 'Chi nhánh', 'url' => route('candidates.browse_companies')],
            ['label' => 'Hồ sơ ứng viên', 'url' => route('candidates.submit_resume')],
        ];

    $systemLinks = [
        ['label' => 'Về chúng tôi', 'url' => route('pages.about')],
        ['label' => 'Tin tức', 'url' => route('pages.blog')],
        ['label' => 'Liên hệ', 'url' => route('pages.contact')],
    ];

    $accountLinks = [];
    if (($isEmployer && !($canEmployerAccess ?? false)) || (!$isEmployer && !($canCandidateAccess ?? false))) {
        $accountLinks[] = [
            'label' => 'Đăng nhập',
            'url' => $isEmployer ? route('employers.login') : route('candidates.login'),
        ];
        $accountLinks[] = [
            'label' => 'Đăng ký',
            'url' => $isEmployer ? route('employers.register') : route('candidates.register'),
        ];
    }

    $contactItems = [
        ['icon' => 'fa-map-marker', 'label' => 'Hỗ trợ', 'value' => 'Hỗ trợ Hệ thống tuyển dụng trực tuyến'],
        ['icon' => 'fa-envelope-o', 'label' => 'Email', 'value' => 'support@jobguru.com', 'href' => 'mailto:support@jobguru.com'],
        ['icon' => 'fa-phone', 'label' => 'Điện thoại', 'value' => '+(09) 2134-7689', 'href' => 'tel:+0921347689'],
    ];

    $branches = [
        ['city' => 'Hà Nội', 'address' => 'Tòa nhà FPT, 10 Phạm Văn Bạch, Cầu Giấy'],
        ['city' => 'TP.HCM', 'address' => 'Lô L29B-31B-33B, Tân Thuận, Quận 7'],
        ['city' => 'Đà Nẵng', 'address' => 'KCN An Đồn, Sơn Trà'],
        ['city' => 'Cần Thơ', 'address' => 'Số 69 Hùng Vương, Ninh Kiều'],
    ];
@endphp

<footer class="jobguru-footer-area client-app-footer {{ $isEmployer ? 'footer--employer' : 'footer--candidate' }}">
    <div class="footer-top section_50">
        <div class="container">
            <div class="client-footer-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
                <div class="single-footer-widget client-footer-brand client-footer-column">
                    <div class="footer-logo">
                        <a href="{{ $homeUrl }}">
                            <img src="{{ asset('assets/img/fe-logo.png') }}" alt="FPT Careers" style="max-width: 160px; margin-bottom: 20px; display: block;" />
                        </a>
                    </div>
                    
                    <p style="margin-bottom: 25px; font-size: 14px; line-height: 1.7; color: #777; font-weight: 400;">
                        <strong>{{ $brandTitle }}</strong> là một trong những đơn vị đào tạo uy tín và quy mô lớn nhất tại Việt Nam. Sứ mệnh cung cấp năng lực cạnh tranh toàn cầu, mang đến môi trường học tập và làm việc chuẩn quốc tế cho người học.
                    </p>

                    <ul class="footer-social">
                        <li>
                            <a href="{{ route('pages.contact') }}" aria-label="Liên hệ hỗ trợ">
                                <i class="fa fa-envelope-o"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pages.blog') }}" aria-label="Tin tức tuyển dụng">
                                <i class="fa fa-newspaper-o"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pages.about') }}" aria-label="Về chúng tôi">
                                <i class="fa fa-building-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="single-footer-widget client-footer-links client-footer-column">
                    <h3>{{ $primaryHeading }}</h3>
                    <ul>
                        @foreach ($primaryLinks as $link)
                            <li>
                                <a href="{{ $link['url'] }}">
                                    <i class="fa fa-angle-right"></i>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="single-footer-widget client-footer-links client-footer-column">
                    <h3>Liên kết</h3>
                    <ul>
                        @foreach ($systemLinks as $link)
                            <li>
                                <a href="{{ $link['url'] }}">
                                    <i class="fa fa-angle-right"></i>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            </li>
                        @endforeach

                        @foreach ($accountLinks as $link)
                            <li>
                                <a href="{{ $link['url'] }}">
                                    <i class="fa fa-angle-right"></i>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="single-footer-widget client-footer-contact client-footer-column">
                    <h3>Liên hệ</h3>

                    <div class="client-footer-contact-list">
                        @foreach ($contactItems as $item)
                            <div class="client-footer-contact-item" style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 10px;">
                                <span class="client-footer-contact-icon" style="flex-shrink: 0; margin-top: 2px;">
                                    <i class="fa {{ $item['icon'] }}" style="color: #f36c21;"></i>
                                </span>
                                <div class="client-footer-contact-copy">
                                    <small style="display: block; font-size: 14px; font-weight: 600; color: #333;">{{ $item['label'] }}</small>
                                    @if (!empty($item['href']))
                                        <a href="{{ $item['href'] }}" style="display: block; font-size: 13px; color: #666; margin-top: 2px; line-height: 1.4;">{{ $item['value'] }}</a>
                                    @else
                                        <span style="display: block; font-size: 13px; color: #666; margin-top: 2px; line-height: 1.4;">{{ $item['value'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="single-footer-widget client-footer-contact client-footer-column">
                    <h3>Chi nhánh FPT</h3>
                    <div class="client-footer-contact-list">
                        @foreach ($branches as $branch)
                            <div class="client-footer-contact-item" style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 10px;">
                                <span class="client-footer-contact-icon" style="flex-shrink: 0; margin-top: 2px;">
                                    <i class="fa fa-building-o" style="color: #f36c21;"></i>
                                </span>
                                <div class="client-footer-contact-copy">
                                    <strong style="display: block; font-size: 14px; font-weight: 600; color: #333;">{{ $branch['city'] }}</strong>
                                    <span style="display: block; font-size: 13px; color: #666; margin-top: 2px; line-height: 1.4;">{{ $branch['address'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center" style="padding: 20px 0; border-top: 1px solid #eaeaea; font-size: 14px; color: #888; margin-top: 20px;">
                    <p>&copy; {{ date('Y') }} <strong>{{ $brandTitle }}</strong>. Đã đăng ký bản quyền. Phát triển bởi Đội ngũ kỹ thuật FPT.</p>
                </div>
            </div>
        </div>
    </div>
</footer>

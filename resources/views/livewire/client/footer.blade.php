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
@endphp

<footer class="jobguru-footer-area client-app-footer {{ $isEmployer ? 'footer--employer' : 'footer--candidate' }}">
    <div class="footer-top section_50">
        <div class="container">
            <div class="client-footer-grid">
                <div class="single-footer-widget client-footer-brand client-footer-column">
                    <div class="footer-logo">
                        <a href="{{ $homeUrl }}">
                            <img src="{{ asset('assets/img/fe-logo.png') }}" alt="FPT Careers" />
                        </a>
                    </div>
                    <h3>{{ $brandTitle }}</h3>

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
                            <div class="client-footer-contact-item">
                                <span class="client-footer-contact-icon">
                                    <i class="fa {{ $item['icon'] }}"></i>
                                </span>
                                <div class="client-footer-contact-copy">
                                    <small>{{ $item['label'] }}</small>
                                    @if (!empty($item['href']))
                                        <a href="{{ $item['href'] }}">{{ $item['value'] }}</a>
                                    @else
                                        <span>{{ $item['value'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</footer>

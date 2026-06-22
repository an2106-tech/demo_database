<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $baseTitle = config('app.name', 'FPT Careers');
        $pageTitle = filled($authTitle ?? null) ? $authTitle : $baseTitle;
        $documentTitle = \Illuminate\Support\Str::contains($pageTitle, $baseTitle) ? $pageTitle : "{$pageTitle} | {$baseTitle}";
        $authContextRole = ($authContextRole ?? 'candidate') === 'employer' ? 'employer' : 'candidate';
    @endphp

    <title>{{ $documentTitle }}</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/fe-logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/fe-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/fe-logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/slicknav.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/perfect-scrollbar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/compat.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/client-topcv.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/ui-enterprise.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/employer-portal.css') }}">

    @livewireStyles
</head>

<body class="client-app candidate-app">
    <div
        class="app-toast-stack"
        data-toast-stack
        @if (session('status'))
            data-initial-message='@json(session('status'))'
        @endif
    ></div>

    <livewire:header type="candidate" />
    {{ $slot }}
    <livewire:footer type="candidate" />

    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.slicknav.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/jarallax.min.js') }}"></script>
    <script src="{{ asset('assets/js/jarallax-video.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    @stack('scripts')

    @livewireScripts

    <script>
        (() => {
            const toastStack = document.querySelector('[data-toast-stack]');

            if (!toastStack) {
                return;
            }

            const closeToast = (toast) => {
                if (!toast || toast.dataset.closing === 'true') {
                    return;
                }

                toast.dataset.closing = 'true';
                toast.classList.add('is-closing');

                window.setTimeout(() => {
                    toast.remove();
                }, 220);
            };

            const showToast = (message) => {
                if (!message) {
                    return;
                }

                const toast = document.createElement('div');
                toast.className = 'app-toast';
                toast.innerHTML = `
                    <div class="app-toast__icon"><i class="fa fa-check"></i></div>
                    <div class="app-toast__body"></div>
                    <button type="button" class="app-toast__close" aria-label="Đóng thông báo">&times;</button>
                `;

                toast.querySelector('.app-toast__body').textContent = message;
                toast.querySelector('.app-toast__close').addEventListener('click', () => closeToast(toast));

                toastStack.appendChild(toast);
                window.setTimeout(() => closeToast(toast), 4500);
            };

            const initialMessage = toastStack.dataset.initialMessage;

            if (initialMessage) {
                try {
                    showToast(JSON.parse(initialMessage));
                } catch (error) {
                    showToast(initialMessage);
                }
            }

            window.addEventListener('app-notify', (event) => {
                showToast(event.detail?.message);
            });
        })();
    </script>
</body>

</html>

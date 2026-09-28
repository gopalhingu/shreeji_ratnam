<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            var theme = 'light';
            try {
                var saved = localStorage.getItem('shreeji-theme');
                if (saved === 'dark' || saved === 'light') {
                    theme = saved;
                }
            } catch (e) {}
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ ucwords(str_replace("_", " ", config('app.name', 'Laravel'))) }}</title>
    <link rel="icon" type="image/gif" href="{{ url('public/image/diamond.gif') }}">

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .theme-switch {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--bs-border-color);
            border-radius: 999px;
            padding: 2px;
            background: var(--bs-tertiary-bg);
        }
        .theme-switch-btn {
            border: 0;
            background: transparent;
            color: var(--bs-secondary-color);
            font-size: 12px;
            line-height: 1;
            padding: 5px 10px;
            border-radius: 999px;
        }
        .theme-switch-btn.is-active {
            background: var(--bs-body-bg);
            color: var(--bs-emphasis-color);
            font-weight: 650;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
        }
        html[data-bs-theme="dark"] #settingOutput {
            background: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color);
            border-color: var(--bs-border-color) !important;
        }
    </style>
    @yield('css')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md bg-body shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ ucwords(str_replace("_", " ", config('app.name', 'Laravel'))) }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto align-items-md-center">
                        <li class="nav-item me-md-2 mb-2 mb-md-0">
                            <div class="theme-switch" role="group" aria-label="Color mode">
                                <button type="button" class="theme-switch-btn" data-theme-value="light" aria-pressed="true">Light</button>
                                <button type="button" class="theme-switch-btn" data-theme-value="dark" aria-pressed="false">Dark</button>
                            </div>
                        </li>
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            {{--
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                            --}}
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('setting.index') }}">Setting</a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    @yield('script')
    <script>
        (function () {
            var storageKey = 'shreeji-theme';

            function currentTheme() {
                return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
            }

            function applyTheme(theme) {
                document.documentElement.setAttribute('data-bs-theme', theme);
                try {
                    localStorage.setItem(storageKey, theme);
                } catch (e) {}
                var buttons = document.querySelectorAll('[data-theme-value]');
                var index;
                for (index = 0; index < buttons.length; index++) {
                    var active = buttons[index].getAttribute('data-theme-value') === theme;
                    buttons[index].classList.toggle('is-active', active);
                    buttons[index].setAttribute('aria-pressed', active ? 'true' : 'false');
                }
            }

            document.addEventListener('click', function (event) {
                var button = event.target.closest ? event.target.closest('[data-theme-value]') : null;
                if (!button) {
                    return;
                }
                applyTheme(button.getAttribute('data-theme-value'));
            });

            applyTheme(currentTheme());

            if (window.Swal && typeof window.Swal.fire === 'function' && !window.Swal.fire.__themed) {
                var fire = window.Swal.fire.bind(window.Swal);
                function themedFire(options) {
                    if (options && typeof options === 'object') {
                        options.theme = currentTheme();
                    }
                    return fire(options);
                }
                themedFire.__themed = true;
                window.Swal.fire = themedFire;
            }
        })();
    </script>
</body>
</html>

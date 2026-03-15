<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — FFPRAMS</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --navy: #1a2c4e;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .left-panel {
            background-color: var(--navy);
            color: #fff;
        }

        .left-panel .brand {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .left-panel .system-name {
            font-size: 1.05rem;
            opacity: .85;
            line-height: 1.6;
        }

        .left-panel .divider {
            width: 60px;
            height: 2px;
            background-color: rgba(255, 255, 255, .3);
        }

        .left-panel .tagline {
            font-size: .95rem;
            opacity: .65;
            font-style: italic;
        }

        .left-panel .footer-text {
            font-size: .8rem;
            opacity: .5;
        }

        .sign-in-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--navy);
        }

        .btn-navy {
            background-color: var(--navy);
            color: #fff;
            border: none;
        }

        .btn-navy:hover {
            background-color: #243a5e;
            color: #fff;
        }

        .form-control:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 .2rem rgba(26, 44, 78, .15);
        }

        .mobile-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--navy);
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row min-vh-100">

            {{-- Left Column — Branding --}}
            <div class="col-md-6 d-none d-md-flex left-panel flex-column justify-content-between p-5">
                <div></div>
                <div class="text-center">
                    <div class="brand mb-3">FFPRAMS</div>
                    <p class="system-name mb-4">
                        Farmer-Fisherfolk Precision Resource<br>
                        Allocation Management System<br>
                        with Geo-Mapping
                    </p>
                    <div class="divider mx-auto mb-4"></div>
                    <p class="tagline">
                        Precision Resource Allocation<br>
                        for Farmers and Fisherfolk
                    </p>
                </div>
                <div class="text-center footer-text">
                    Municipality of Enrique B. Magalona<br>
                    Negros Occidental, Philippines
                </div>
            </div>

            {{-- Right Column — Login Form --}}
            <div class="col-md-6 d-flex align-items-center justify-content-center bg-white p-4">
                <div style="width: 100%; max-width: 420px;">

                    {{-- Mobile-only brand label --}}
                    <div class="text-center mb-4 d-block d-md-none">
                        <span class="mobile-brand">FFPRAMS</span>
                    </div>

                    <h1 class="sign-in-title mb-1">Sign In</h1>
                    <p class="text-muted mb-4">Enter your credentials to continue.</p>

                    {{-- Global error alert --}}
                    @if ($errors->any())
                        <div class="alert alert-danger py-2 small">
                            These credentials do not match our records.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                            >
                            @foreach ($errors->get('email') as $error)
                                <div class="text-danger small mt-1">{{ $error }}</div>
                            @endforeach
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    required
                                    autocomplete="current-password"
                                >
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    id="togglePassword"
                                    tabindex="-1"
                                >
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            @foreach ($errors->get('password') as $error)
                                <div class="text-danger small mt-1">{{ $error }}</div>
                            @endforeach
                        </div>

                        {{-- Remember Me --}}
                        <div class="form-check mb-4">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="remember"
                                name="remember"
                            >
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="btn btn-navy w-100 py-2 fw-semibold">
                            Sign In
                        </button>
                    </form>

                    <p class="text-center text-muted small mt-4 mb-0">
                        For account issues, contact the System Administrator.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            var input = document.getElementById('password');
            var icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password — FFPRAMS</title>

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

        .page-title {
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

            {{-- Right Column — Forgot Password Form --}}
            <div class="col-md-6 d-flex align-items-center justify-content-center bg-white p-4">
                <div style="width: 100%; max-width: 420px;">

                    {{-- Mobile-only brand label --}}
                    <div class="text-center mb-4 d-block d-md-none">
                        <span class="mobile-brand">FFPRAMS</span>
                    </div>

                    <h1 class="page-title mb-1">Forgot your password? No problem.</h1>
                    <p class="text-muted mb-4">
                        Just let us know your email address and we will email you a password reset link
                        that will allow you to choose a new one.
                    </p>

                    {{-- Success alert --}}
                    @if (session('status'))
                        <div class="alert alert-success py-2 small d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                required
                                autofocus
                            >
                            @foreach ($errors->get('email') as $error)
                                <div class="text-danger small mt-1">{{ $error }}</div>
                            @endforeach
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="btn btn-navy w-100 py-2 fw-semibold">
                            Email Password Reset Link
                        </button>
                    </form>

                    {{-- Back to login --}}
                    <p class="text-center small mt-3">
                        <a href="{{ route('login') }}" class="text-muted text-decoration-none">
                            &larr; Back to Login
                        </a>
                    </p>

                    <p class="text-center text-muted small mt-4 mb-0">
                        Access is restricted to authorized LGU personnel only.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

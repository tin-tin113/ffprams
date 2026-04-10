<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — FFPRAMS</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/ebmag-logo.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --forest-green: #1a3c34;
            --forest-green-light: #2a5c4a;
            --accent-green: #28a745;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('{{ asset('images/login-bg.jpg') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            background-color: #1a3c34;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 0;
        }

        .login-card {
            background: var(--forest-green);
            border-radius: 20px;
            padding: 40px 35px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            position: relative;
            z-index: 1;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .system-title {
            color: #fff;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 500;
            line-height: 1.5;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .input-group-custom {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .input-icon {
            padding: 12px 15px;
            color: #666;
            font-size: 1.1rem;
        }

        .input-group-custom input {
            border: none;
            outline: none;
            padding: 12px 10px;
            flex: 1;
            font-size: 1rem;
            background: transparent;
        }

        .input-group-custom input::placeholder {
            color: #999;
        }

        .input-with-btn {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .input-with-btn .input-group-custom {
            flex: 1;
            margin-bottom: 0;
        }

        .btn-login {
            background: var(--accent-green);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
            white-space: nowrap;
        }

        .btn-login:hover {
            background: #218838;
            color: #fff;
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .login-card {
                margin: 15px;
                padding: 30px 25px;
            }

            .input-with-btn {
                flex-direction: column;
            }

            .btn-login {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        {{-- Logo --}}
        <div class="logo-container">
            <img src="{{ asset('images/ebemag logo.jpg') }}" alt="E.B. Magalona Logo" class="logo-circle">
        </div>

        {{-- Title --}}
        <div class="system-title">
            Farmer-Fisherfolk Resource<br>
            Allocation Management System
        </div>

        {{-- Error Alert --}}
        @if ($errors->any())
            <div class="alert-error">
                <i class="bi bi-exclamation-circle me-2"></i>
                These credentials do not match our records.
            </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            {{-- Email Input --}}
            <div class="input-group-custom">
                <span class="input-icon"><i class="bi bi-envelope"></i></span>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                >
            </div>

            {{-- Password Input with Login Button --}}
            <div class="input-with-btn">
                <div class="input-group-custom">
                    <span class="input-icon"><i class="bi bi-lock"></i></span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Password"
                        required
                        autocomplete="current-password"
                    >
                </div>
                <button type="submit" class="btn-login">Login</button>
            </div>
        </form>

    </div>
</body>
</html>

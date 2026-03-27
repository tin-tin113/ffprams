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

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.3);
        }

        .divider span {
            color: rgba(255, 255, 255, 0.6);
            padding: 0 15px;
            font-size: 0.9rem;
        }

        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            background: #fff;
            color: #333;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 1rem;
            font-weight: 500;
            cursor: not-allowed;
            opacity: 0.6;
            transition: all 0.2s ease;
        }

        .btn-google img {
            width: 20px;
            height: 20px;
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

        {{-- Divider --}}
        <div class="divider">
            <span>or</span>
        </div>

        {{-- Google Sign In (Placeholder - Not Implemented) --}}
        <button type="button" class="btn-google" disabled title="Coming soon">
            <svg width="20" height="20" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Sign in with Google
        </button>

    </div>
</body>
</html>

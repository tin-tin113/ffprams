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

        .toggle-password {
            padding: 12px 15px;
            color: #666;
            font-size: 1.1rem;
            cursor: pointer;
            background: transparent;
            border: none;
            outline: none;
        }

        .toggle-password:hover {
            color: #333;
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

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin: 20px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .divider:not(:empty)::before {
            margin-right: 1em;
        }

        .divider:not(:empty)::after {
            margin-left: 1em;
        }

        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: #333;
            border: none;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease;
            text-decoration: none;
        }

        .btn-google:hover {
            background: #f1f1f1;
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
                    <button type="button" class="toggle-password" id="togglePasswordBtn" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right me-1"></i> Login</button>
            </div>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <button type="button" class="btn-google">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20px" height="20px" style="margin-right:10px;">
                <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
            </svg>
            Sign in with Google
        </button>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');

            if (togglePasswordBtn && passwordInput) {
                togglePasswordBtn.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    if (type === 'password') {
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    } else {
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    }
                });
            }
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'FFPRAMS') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @stack('styles')

    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
            --sidebar-bg: #1a472a;
            --sidebar-hover: #245a35;
            --sidebar-active: #2d6e3f;
            --accent-green: #22c55e;
            --accent-teal: #14b8a6;
            --accent-coral: #f87171;
            --accent-blue: #3b82f6;
            --body-bg: #f1f5f9;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--body-bg);
            overflow-x: hidden;
        }

        /* --- Page Loading Indicator --- */
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-green), var(--accent-teal));
            z-index: 9999;
            transition: width 0.4s ease;
        }
        #page-loader.loading { width: 85%; }
        #page-loader.done { width: 100%; opacity: 0; transition: width 0.2s ease, opacity 0.3s ease 0.2s; }

        /* --- Sidebar --- */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            z-index: 1050;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1rem 1rem 0.75rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            margin-bottom: 0.4rem;
            border: 2px solid rgba(255,255,255,0.2);
            object-fit: cover;
        }

        .sidebar-title {
            color: #fff;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: hidden;
            padding: 0.5rem 0;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.7);
            padding: 0.55rem 1rem;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background-color: var(--sidebar-hover);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: var(--sidebar-active);
            border-left-color: var(--accent-green);
        }

        .sidebar .nav-link i {
            width: 18px;
            margin-right: 0.65rem;
            font-size: 1rem;
            color: var(--accent-green);
        }

        .sidebar .nav-link.active i,
        .sidebar .nav-link:hover i {
            color: var(--accent-green);
        }

        .sidebar-heading {
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.4);
            padding: 0.9rem 1rem 0.4rem;
            font-weight: 600;
        }

        .sidebar-user {
            padding: 0.85rem 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            background-color: rgba(0,0,0,0.2);
        }

        .sidebar-user-avatar {
            width: 32px;
            height: 32px;
            background-color: var(--accent-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .sidebar-user-info {
            margin-left: 0.65rem;
            overflow: hidden;
        }

        .sidebar-user-name {
            color: #fff;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 0.65rem;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* --- Sidebar Overlay (mobile) --- */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        .sidebar-overlay.show { display: block; }

        /* --- Top Header --- */
        .top-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background-color: #fff;
            border-bottom: 1px solid #e2e8f0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            transition: left 0.3s ease;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            margin-right: 1rem;
            cursor: pointer;
        }

        .header-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .header-breadcrumb {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: #64748b;
            margin-left: 1rem;
            padding-left: 1rem;
            border-left: 1px solid #e2e8f0;
        }

        .header-breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }

        .header-breadcrumb a:hover {
            color: var(--sidebar-bg);
        }

        .header-breadcrumb .separator {
            margin: 0 0.5rem;
            color: #cbd5e1;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background-color: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .header-icon-btn:hover {
            background-color: #e2e8f0;
            color: #1e293b;
        }

        .header-icon-btn .badge {
            position: absolute;
            top: -2px;
            right: -2px;
            font-size: 0.6rem;
            padding: 0.2rem 0.4rem;
        }

        .header-user {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .header-user:hover {
            background-color: #f1f5f9;
        }

        .header-user-avatar {
            width: 36px;
            height: 36px;
            background-color: var(--sidebar-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .header-user-info {
            margin-left: 0.75rem;
            text-align: left;
        }

        .header-user-name {
            font-size: 0.875rem;
            font-weight: 500;
            color: #1e293b;
        }

        .header-user-role {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* --- Main Content --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: calc(var(--header-height) + 1.5rem);
            padding-bottom: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .main-content .container-fluid {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            max-width: 1600px;
        }

        /* --- Dashboard Cards --- */
        .stat-card {
            background: #fff;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            height: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .stat-card-icon {
            width: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: #fff;
        }

        .stat-card-icon.bg-green { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .stat-card-icon.bg-teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }
        .stat-card-icon.bg-coral { background: linear-gradient(135deg, #f87171, #ef4444); }
        .stat-card-icon.bg-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .stat-card-icon.bg-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .stat-card-icon.bg-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-card-icon.bg-slate { background: linear-gradient(135deg, #64748b, #475569); }

        .stat-card-body {
            flex: 1;
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-card-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .stat-card-label {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        /* --- Section Headers --- */
        .section-header {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .section-header i {
            margin-right: 0.5rem;
            color: var(--accent-green);
        }

        /* --- Card Styles --- */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 1rem 1.25rem;
        }

        /* --- Table Polish --- */
        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            background-color: #f8fafc;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* --- Badge Styles --- */
        .badge-admin { background-color: #dc2626; }
        .badge-staff { background-color: #2563eb; }
        .badge-viewer { background-color: #7c3aed; }

        /* --- Breadcrumb --- */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .breadcrumb-item a { color: #64748b; text-decoration: none; }
        .breadcrumb-item a:hover { color: var(--sidebar-bg); }
        .breadcrumb-item.active { color: #1e293b; font-weight: 500; }

        /* --- Form Polish --- */
        .form-label .text-danger {
            font-weight: 700;
        }

        .form-control, .form-select {
            border-radius: 0.5rem;
            border-color: #e2e8f0;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-green);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
        }

        .btn-primary {
            background-color: var(--sidebar-bg);
            border-color: var(--sidebar-bg);
        }

        .btn-primary:hover {
            background-color: var(--sidebar-hover);
            border-color: var(--sidebar-hover);
        }

        textarea + .char-counter {
            font-size: 0.75rem;
            color: #94a3b8;
            text-align: right;
            margin-top: 0.25rem;
        }

        .btn-submit-spinner .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
            margin-right: 0.4rem;
        }

        /* --- Responsive --- */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .top-header {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .header-toggle {
                display: block;
            }
            .header-breadcrumb {
                display: none;
            }
            .btn-action-label {
                display: none;
            }
        }

        @media (max-width: 575.98px) {
            .header-user-info {
                display: none;
            }
            .stat-card-icon {
                width: 60px;
            }
            .stat-card-value {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- ============ PAGE LOADING INDICATOR ============ -->
    <div id="page-loader"></div>

    <!-- ============ SIDEBAR OVERLAY (mobile) ============ -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ============ SIDEBAR ============ -->
    <aside class="sidebar" id="sidebar">
        <!-- Logo Section -->
        <div class="sidebar-header">
            <img src="{{ asset('images/ebemag logo.jpg') }}" alt="EBMag Logo" class="sidebar-logo">
            <div class="sidebar-title">
                Municipality of<br>Enrique B. Magalona
            </div>
        </div>

        <!-- Navigation -->
        <div class="sidebar-nav">
            <nav class="nav flex-column">
                <span class="sidebar-heading">Main Menu</span>

                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>

                <a class="nav-link {{ request()->routeIs('beneficiaries.*') ? 'active' : '' }}"
                   href="{{ route('beneficiaries.index') }}">
                    <i class="bi bi-people-fill"></i> Beneficiaries
                </a>

                <a class="nav-link {{ request()->routeIs('distribution-events.*') ? 'active' : '' }}"
                   href="{{ route('distribution-events.index') }}">
                    <i class="bi bi-calendar-event-fill"></i> Distribution Events
                </a>

                <a class="nav-link {{ request()->routeIs('sms.*') ? 'active' : '' }}"
                   href="{{ route('sms.index') }}">
                    <i class="bi bi-chat-dots-fill"></i> SMS Broadcast
                </a>

                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                   href="{{ route('reports.index') }}">
                    <i class="bi bi-bar-chart-fill"></i> Reports
                </a>

                <a class="nav-link {{ request()->routeIs('resource-types.*') ? 'active' : '' }}"
                   href="{{ route('resource-types.index') }}">
                    <i class="bi bi-tags-fill"></i> Resource Types
                </a>

                <a class="nav-link {{ request()->routeIs('geo-map.*') ? 'active' : '' }}"
                   href="{{ route('geo-map.index') }}">
                    <i class="bi bi-geo-alt-fill"></i> Geo-Mapping
                </a>

                @if(Auth::user()->isAdmin())
                    <span class="sidebar-heading">Administration</span>

                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                       href="{{ route('admin.users.index') }}">
                        <i class="bi bi-person-gear"></i> User Management
                    </a>

                    <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                       href="{{ route('admin.settings.index') }}">
                        <i class="bi bi-gear-fill"></i> System Settings
                    </a>
                @endif
            </nav>
        </div>

        <!-- Sidebar User Info -->
        <div class="sidebar-user">
            <div class="d-flex align-items-center">
                <div class="sidebar-user-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                    <div class="sidebar-user-role">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- ============ TOP HEADER ============ -->
    <header class="top-header">
        <div class="header-left">
            <button class="header-toggle" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="header-title">@yield('title', 'Dashboard')</h1>
            @hasSection('breadcrumb')
                <div class="header-breadcrumb">
                    @yield('breadcrumb')
                </div>
            @endif
        </div>

        <div class="header-right">
            <!-- Notification Bell -->
            <button class="header-icon-btn" type="button" title="Notifications" disabled>
                <i class="bi bi-bell"></i>
                <span class="badge rounded-pill bg-secondary">0</span>
            </button>

            <!-- User Dropdown -->
            <div class="dropdown">
                <div class="header-user" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="header-user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="header-user-info">
                        <div class="header-user-name">{{ Auth::user()->name }}</div>
                        <div class="header-user-role">{{ ucfirst(Auth::user()->role) }}</div>
                    </div>
                    <i class="bi bi-chevron-down ms-2 text-muted"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person me-2"></i> Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">
        <div class="container-fluid">
            @include('partials.flash')
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Page Loading Indicator -->
    <script>
    (function () {
        var loader = document.getElementById('page-loader');
        loader.classList.add('loading');
        window.addEventListener('load', function () {
            loader.classList.remove('loading');
            loader.classList.add('done');
            setTimeout(function () { loader.remove(); }, 600);
        });
    })();
    </script>

    <!-- Sidebar Toggle -->
    <script>
    (function () {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        var toggle  = document.getElementById('sidebarToggle');

        function closeSidebar() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }

        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', closeSidebar);

        sidebar.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) closeSidebar();
            });
        });
    })();
    </script>

    <!-- Form Enhancements -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Submit-button spinner
        document.querySelectorAll('form[data-submit-spinner]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('[type="submit"]');
                if (!btn || btn.disabled) return;
                btn.disabled = true;
                var origHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting…';
                btn.classList.add('btn-submit-spinner');
                setTimeout(function () { btn.disabled = false; btn.innerHTML = origHtml; }, 8000);
            });
        });

        // Textarea character counter
        document.querySelectorAll('textarea[maxlength]').forEach(function (ta) {
            var max = ta.getAttribute('maxlength');
            var counter = document.createElement('div');
            counter.className = 'char-counter';
            counter.textContent = ta.value.length + ' / ' + max;
            ta.parentNode.insertBefore(counter, ta.nextSibling);
            ta.addEventListener('input', function () {
                counter.textContent = ta.value.length + ' / ' + max;
            });
        });
    });
    </script>

    @include('partials.confirm-modal')

    @stack('scripts')
</body>
</html>

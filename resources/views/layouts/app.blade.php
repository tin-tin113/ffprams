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

    @stack('styles')

    <style>
        :root {
            --sidebar-width: 250px;
            --navbar-height: 56px;
            --primary-green: #2e7d32;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }

        /* --- Page Loading Indicator --- */
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 0;
            height: 3px;
            background-color: #0d6efd;
            z-index: 9999;
            transition: width 0.4s ease;
        }
        #page-loader.loading { width: 85%; }
        #page-loader.done { width: 100%; opacity: 0; transition: width 0.2s ease, opacity 0.3s ease 0.2s; }

        /* --- Top Navbar --- */
        .top-navbar {
            height: var(--navbar-height);
            background-color: var(--primary-green);
            z-index: 1040;
        }

        .top-navbar .navbar-brand {
            font-weight: 700;
            font-size: 1.15rem;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .top-navbar .navbar-brand:hover {
            color: #e8f5e9;
        }

        /* --- Sidebar --- */
        .sidebar {
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background-color: #263238;
            overflow-y: auto;
            z-index: 1030;
            transition: transform 0.3s ease;
            padding-top: 0.5rem;
            display: flex;
            flex-direction: column;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            color: #b0bec5;
            padding: 0.65rem 1.25rem;
            font-size: 0.9rem;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: transparent;
            border-left-color: var(--primary-green);
            font-weight: 600;
        }

        .sidebar .nav-link i {
            width: 22px;
            text-align: center;
            margin-right: 0.6rem;
        }

        .sidebar .sidebar-heading {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #607d8b;
            padding: 1rem 1.25rem 0.4rem;
        }

        .sidebar-user {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            background-color: rgba(0,0,0,0.15);
        }

        /* --- Sidebar Overlay (mobile) --- */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1029;
        }
        .sidebar-overlay.show { display: block; }

        /* --- Main Content --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: calc(var(--navbar-height) + 1.5rem);
            padding-bottom: 2rem;
            min-height: 100vh;
        }

        .main-content .container-fluid {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        /* --- Role Badge --- */
        .badge-admin { background-color: #c62828; }
        .badge-staff { background-color: #1565c0; }

        /* --- Breadcrumb --- */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }
        .breadcrumb-item a { color: #6c757d; text-decoration: none; }
        .breadcrumb-item a:hover { color: var(--primary-green); }
        .breadcrumb-item.active { color: #212529; font-weight: 500; }

        /* --- Table Polish --- */
        .table-hover tbody tr:hover {
            background-color: rgba(46, 125, 50, 0.04);
        }

        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            display: block;
            opacity: 0.4;
        }

        /* --- Form Polish --- */
        .form-label .text-danger {
            font-weight: 700;
        }
        textarea + .char-counter {
            font-size: 0.75rem;
            color: #6c757d;
            text-align: right;
            margin-top: 0.2rem;
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
            .main-content {
                margin-left: 0;
            }
            /* Icon-only action buttons on mobile */
            .btn-action-label {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- ============ PAGE LOADING INDICATOR ============ -->
    <div id="page-loader"></div>

    <!-- ============ TOP NAVBAR ============ -->
    <nav class="navbar navbar-dark fixed-top top-navbar px-3">
        <!-- Sidebar toggle (mobile) -->
        <button class="btn btn-sm btn-outline-light d-lg-none me-2" id="sidebarToggle" type="button"
                aria-label="Toggle sidebar">
            <i class="bi bi-list fs-5"></i>
        </button>

        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="bi bi-tsunami me-1"></i> FFPRAMS
        </a>

        <div class="d-flex align-items-center ms-auto gap-2">
            <!-- Notification Bell (placeholder) -->
            <button class="btn btn-sm btn-outline-light position-relative" type="button"
                    title="Notifications" disabled>
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary"
                      style="font-size: 0.6rem;">0</span>
            </button>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button"
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-1"></i>
                    <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li class="dropdown-item-text">
                        <div class="fw-semibold">{{ Auth::user()->name }}</div>
                        <span class="badge {{ Auth::user()->role === 'admin' ? 'badge-admin' : 'badge-staff' }}">
                            {{ ucfirst(Auth::user()->role) }}
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person me-1"></i> Profile
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ============ SIDEBAR OVERLAY (mobile) ============ -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ============ SIDEBAR ============ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-nav">
            <nav class="nav flex-column">
                <span class="sidebar-heading">Main</span>

                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                <a class="nav-link {{ request()->routeIs('beneficiaries.*') ? 'active' : '' }}"
                   href="{{ route('beneficiaries.index') }}">
                    <i class="bi bi-people"></i> Beneficiaries
                </a>

                <a class="nav-link {{ request()->routeIs('distribution-events.*') ? 'active' : '' }}"
                   href="{{ route('distribution-events.index') }}">
                    <i class="bi bi-calendar-event"></i> Distribution Events
                </a>

                <a class="nav-link {{ request()->routeIs('resource-types.*') ? 'active' : '' }}"
                   href="{{ route('resource-types.index') }}">
                    <i class="bi bi-tags"></i> Resource Types
                </a>

                <a class="nav-link {{ request()->routeIs('geo-map.*') ? 'active' : '' }}"
                   href="{{ route('geo-map.index') }}">
                    <i class="bi bi-geo-alt"></i> Geo-Map
                </a>

                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                   href="{{ route('reports.index') }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> Reports
                </a>

                @if(Auth::user()->isAdmin())
                    <span class="sidebar-heading">Administration</span>

                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                       href="{{ route('admin.users.index') }}">
                        <i class="bi bi-person-gear"></i> User Management
                    </a>
                @endif
            </nav>
        </div>

        <!-- Sidebar User Info -->
        <div class="sidebar-user">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center me-2"
                     style="width: 32px; height: 32px;">
                    <i class="bi bi-person-fill text-white small"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-white small text-truncate">{{ Auth::user()->name }}</div>
                    <span class="badge {{ Auth::user()->role === 'admin' ? 'badge-admin' : 'badge-staff' }}"
                          style="font-size: 0.65rem;">
                        {{ ucfirst(Auth::user()->role) }}
                    </span>
                </div>
            </div>
        </div>
    </aside>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">
        <div class="container-fluid">
            @include('partials.flash')

            {{-- Breadcrumb --}}
            @hasSection('breadcrumb')
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            @endif

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

        // Close sidebar when a nav link is clicked (mobile)
        sidebar.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) closeSidebar();
            });
        });
    })();
    </script>

    <!-- Form Enhancements: Submit Spinner + Textarea Character Counter -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Submit-button spinner (all forms with data-submit-spinner)
        document.querySelectorAll('form[data-submit-spinner]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('[type="submit"]');
                if (!btn || btn.disabled) return;
                btn.disabled = true;
                var origHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting…';
                btn.classList.add('btn-submit-spinner');
                // Re-enable if user navigates back
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

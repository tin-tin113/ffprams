<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FFPRAMS — Farmer-Fisherfolk Precision Resource Allocation Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --navy: #1a2c4e;
            --navy-light: #243a5e;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        /* Navbar */
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--navy) !important;
            letter-spacing: .5px;
        }

        /* Hero */
        .hero-section {
            background-color: var(--navy);
            color: #fff;
            padding: 6rem 0;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .hero-section .lead {
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: .9;
        }

        .hero-section .tagline {
            font-size: 1.05rem;
            opacity: .75;
            max-width: 600px;
            margin: 0 auto;
        }

        .btn-outline-light:hover {
            background-color: #fff;
            color: var(--navy);
        }

        /* Sections */
        .section-title {
            font-weight: 700;
            color: var(--navy);
            margin-bottom: .5rem;
        }

        .section-divider {
            width: 60px;
            height: 3px;
            background-color: var(--navy);
            margin: 0 auto 2.5rem;
        }

        /* Feature cards */
        .feature-card {
            border: none;
            border-radius: .75rem;
            transition: box-shadow .2s ease;
        }

        .feature-card:hover {
            box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .1);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--navy);
        }

        /* Agency cards */
        .agency-section {
            background-color: #f8f9fa;
        }

        .agency-card {
            border: none;
            border-radius: .75rem;
            transition: box-shadow .2s ease;
        }

        .agency-card:hover {
            box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .1);
        }

        .agency-card .card-title {
            color: var(--navy);
            font-weight: 700;
        }

        /* Footer */
        .footer-section {
            background-color: var(--navy);
            color: rgba(255, 255, 255, .8);
        }

        .footer-section a {
            color: rgba(255, 255, 255, .8);
            text-decoration: none;
        }

        .footer-section a:hover {
            color: #fff;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 4rem 0;
            }

            .hero-section h1 {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">FFPRAMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#agencies">Partner Agencies</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary px-4">Go to Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary px-4">Login</a>
                        @endauth
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="mb-3">FFPRAMS</h1>
            <p class="lead fw-light mb-3">
                Farmer-Fisherfolk Precision Resource Allocation Management System<br>
                with Geo-Mapping
            </p>
            <p class="tagline mb-4">
                Empowering the Municipality of Enrique B. Magalona
                to deliver resources precisely where they are needed.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg px-4 fw-semibold">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4 fw-semibold">
                        Login to System
                    </a>
                @endauth
                <a href="#about" class="btn btn-outline-light btn-lg px-4">
                    Learn More
                </a>
            </div>
        </div>
    </section>

    {{-- About Section --}}
    <section id="about" class="py-5">
        <div class="container">
            <div class="text-center mb-2">
                <h2 class="section-title">About FFPRAMS</h2>
            </div>
            <div class="section-divider"></div>
            <p class="text-center text-muted mx-auto mb-5" style="max-width: 750px;">
                FFPRAMS is a web-based management system designed to support the Municipality of
                Enrique B. Magalona in efficiently registering farmer and fisherfolk beneficiaries,
                managing resource distribution, and visualizing delivery coverage through an
                interactive geo-map.
            </p>

            <div class="row g-4">
                {{-- Card 1 --}}
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm text-center h-100 p-4">
                        <div class="card-body">
                            <i class="bi bi-people-fill feature-icon mb-3 d-block"></i>
                            <h5 class="fw-bold mb-3">Beneficiary Management</h5>
                            <p class="text-muted mb-0">
                                Register and manage farmer and fisherfolk beneficiaries
                                aligned with DA RSBSA and BFAR FishR standards.
                            </p>
                        </div>
                    </div>
                </div>
                {{-- Card 2 --}}
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm text-center h-100 p-4">
                        <div class="card-body">
                            <i class="bi bi-box-seam-fill feature-icon mb-3 d-block"></i>
                            <h5 class="fw-bold mb-3">Resource Allocation</h5>
                            <p class="text-muted mb-0">
                                Track and distribute agricultural and fishery resources
                                to verified beneficiaries per barangay.
                            </p>
                        </div>
                    </div>
                </div>
                {{-- Card 3 --}}
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm text-center h-100 p-4">
                        <div class="card-body">
                            <i class="bi bi-geo-alt-fill feature-icon mb-3 d-block"></i>
                            <h5 class="fw-bold mb-3">Geo-Mapping</h5>
                            <p class="text-muted mb-0">
                                Visualize beneficiary distribution and resource delivery
                                across all barangays of Enrique B. Magalona.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Partner Agencies Section --}}
    <section id="agencies" class="agency-section py-5">
        <div class="container">
            <div class="text-center mb-2">
                <h2 class="section-title">Partner Agencies</h2>
            </div>
            <div class="section-divider"></div>

            <div class="row g-4 justify-content-center">
                <div class="col-md-3 col-6">
                    <div class="card agency-card shadow-sm text-center h-100 p-4">
                        <div class="card-body">
                            <i class="bi bi-building feature-icon mb-3 d-block"></i>
                            <h5 class="card-title mb-2">DA</h5>
                            <p class="text-muted small mb-0">Department of Agriculture</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card agency-card shadow-sm text-center h-100 p-4">
                        <div class="card-body">
                            <i class="bi bi-water feature-icon mb-3 d-block"></i>
                            <h5 class="card-title mb-2">BFAR</h5>
                            <p class="text-muted small mb-0">Bureau of Fisheries and Aquatic Resources</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card agency-card shadow-sm text-center h-100 p-4">
                        <div class="card-body">
                            <i class="bi bi-shield-check feature-icon mb-3 d-block"></i>
                            <h5 class="card-title mb-2">DILG</h5>
                            <p class="text-muted small mb-0">Department of Interior and Local Government</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card agency-card shadow-sm text-center h-100 p-4">
                        <div class="card-body">
                            <i class="bi bi-cpu feature-icon mb-3 d-block"></i>
                            <h5 class="card-title mb-2">DICT</h5>
                            <p class="text-muted small mb-0">Department of Information and Communications Technology</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card agency-card shadow-sm text-center h-100 p-4">
                        <div class="card-body">
                            <i class="bi bi-file-earmark-text feature-icon mb-3 d-block"></i>
                            <h5 class="card-title mb-2">DAR</h5>
                            <p class="text-muted small mb-0">Department of Agrarian Reform</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="footer-section py-4">
        <div class="container text-center">
            <p class="fw-semibold mb-1">FFPRAMS &copy; {{ date('Y') }}</p>
            <p class="small mb-1">Municipality of Enrique B. Magalona, Negros Occidental</p>
            <p class="small mb-0">
                Developed by BS Information Systems Students,
                Carlos Hilado Memorial State University &mdash; Talisay Campus
            </p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

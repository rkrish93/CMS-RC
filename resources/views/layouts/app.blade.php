<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @hasSection('title')
            @yield('title') | CMS-RC
        @else
            CMS-RC Dashboard
        @endif
    </title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


    <style>
        * {
            transition: all 0.2s ease-in-out;
        }

        body {
            padding-top: 0;
        }

        /* NAVBAR */
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            height: 60px;
            padding: 0 20px;
        }

        .navbar-brand img {
            height: 40px;
        }

        /* MAIN */
        .main-panel {
            margin-top: 0 !important;
        }

        .content-wrapper {
            padding: 20px !important;
            background: #f9fafb;
        }

        .page-header {
            margin: 0;
            padding: 10px 0;
        }

        /* CARD */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* SIDEBAR */
        .sidebar {
            background: #ffffff;
            width: 240px;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid #e5e7eb;
            transition: 0.3s;
        }

        .sidebar-icon-only .sidebar {
            width: 70px;
        }

        .sidebar-icon-only .menu-title,
        .sidebar-icon-only .badge {
            display: none;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            border-radius: 10px;
            margin: 5px 10px;
        }

        .sidebar .nav-link i {
            font-size: 20px;
        }

        .sidebar .nav-link:hover {
            background: #f3f4f6;
        }

        .sidebar .nav-link.active {
            background: #e0e7ff;
            color: #4f46e5;
            font-weight: 600;
        }

        /* SIDEBAR LEFT BORDER EFFECT */
        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: #4f46e5;
            opacity: 0;
        }

        .sidebar .nav-link:hover::before,
        .sidebar .nav-link.active::before {
            opacity: 1;
        }

        /* SUB MENU */
        .sub-menu {
            padding-left: 40px;
        }

        /* BADGE */
        .badge {
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

<div id="loader" style="position:fixed;top:0;left:0;width:100%;height:100%;background:white;z-index:9999;"></div>

<div class="container-scroller">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">

            <button class="btn me-3" id="menu-toggle">
                <i class="mdi mdi-menu"></i>
            </button>

            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="{{ asset('assets/images/cms-rc-logo1.png') }}">
                <span class="ms-2 fw-bold">CMS-RC</span>
            </a>

            <div class="ms-auto d-flex align-items-center">
                <button class="btn me-3">
                    <i class="mdi mdi-bell-outline"></i>
                </button>

                <div class="dropdown">
                    <button class="btn dropdown-toggle" data-bs-toggle="dropdown">
                        {{ auth()->user()->name ?? 'Admin' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </nav>

    <div class="container-fluid page-body-wrapper">

        <!-- SIDEBAR -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">

                <li class="nav-item text-center py-3">
                    <img src="{{ asset('assets/images/cms-rc-logo1.png') }}" style="height:35px;">
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">
                        <i class="mdi mdi-view-dashboard-outline"></i>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#patientsMenu">
                        <i class="mdi mdi-account-heart-outline"></i>
                        <span class="menu-title">Patients</span>
                        {{-- <span class="badge bg-primary ms-auto">{{ $patients ?? 0 }}</span> --}}
                    </a>
                    <div class="collapse" id="patientsMenu">
                        <ul class="nav sub-menu">
                            <li><a class="nav-link" href="{{ route('patients.index') }}">All</a></li>
                            <li><a class="nav-link" href="{{ route('patients.create') }}">Add</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#appointmentsMenu">
                        <i class="mdi mdi-calendar-clock-outline"></i>
                        <span class="menu-title">Appointments</span>
                        <span class="badge bg-danger ms-auto">{{ $todayAppointments ?? 0 }}</span>
                    </a>
                    <div class="collapse" id="appointmentsMenu">
                        <ul class="nav sub-menu">
                            <li><a class="nav-link" href="{{ route('appointments.index') }}">All</a></li>
                            <li><a class="nav-link" href="{{ route('appointments.today') }}">Today</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('consultations.index') }}">
                        <i class="mdi mdi-stethoscope"></i>
                        <span class="menu-title">Consultation</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#usersMenu">
                        <i class="mdi mdi-account-cog-outline"></i>
                        <span class="menu-title">Users</span>
                    </a>
                    <div class="collapse" id="usersMenu">
                        <ul class="nav sub-menu">
                            <li><a class="nav-link" href="{{ route('users.index') }}">List</a></li>
                            <li><a class="nav-link" href="{{ route('users.create') }}">Add</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#unitsMenu">
                        <i class="mdi mdi-hospital-building"></i>
                        <span class="menu-title">Units</span>
                    </a>
                    <div class="collapse" id="unitsMenu">
                        <ul class="nav sub-menu">
                            <li><a class="nav-link" href="{{ route('units.index') }}">All</a></li>
                            <li><a class="nav-link" href="{{ route('units.create') }}">Add</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#settingsMenu">
                        <i class="mdi mdi-cog-outline"></i>
                        <span class="menu-title">Settings</span>
                    </a>
                    <div class="collapse" id="settingsMenu">
                        <ul class="nav sub-menu">
                            <li><a class="nav-link" href="{{ route('roles.index') }}">Roles</a></li>
                            <li><a class="nav-link" href="{{ route('permissions.index') }}">Permissions</a></li>
                            <li><a class="nav-link" href="{{ route('permission-groups.index') }}">Permission Group</a></li>
                        </ul>
                    </div>
                </li>

            </ul>
        </nav>

        <!-- MAIN -->
        <div class="main-panel">
            <div class="content-wrapper">

                <div class="page-header">
                    <h3 class="page-title">@yield('title')</h3>
                </div>

                @yield('content')

            </div>

            <footer class="footer text-center py-3">
                <small class="text-muted">
                    © {{ date('Y') }} CMS-RC | Clinic Management System for Rural Clinis
                </small>
            </footer>
        </div>

    </div>
</div>

<!-- JS -->
<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('assets/vendors/chart.js/chart.umd.js') }}"></script>
<script src="{{ asset('assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('assets/js/misc.js') }}"></script>

<script>
    document.getElementById("menu-toggle").addEventListener("click", function () {
        document.body.classList.toggle("sidebar-icon-only");
    });

    window.addEventListener("load", function(){
        document.getElementById("loader").style.display = "none";
    });
</script>

@yield('scripts')

</body>
</html>

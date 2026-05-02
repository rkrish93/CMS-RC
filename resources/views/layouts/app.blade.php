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
        /* =========================
        GLOBAL SMOOTH UI
        ========================= */
        * {
            transition: all 0.2s ease-in-out;
        }

        body {
            padding-top: 0;
            background: #f9fafb;
            font-family: 'Segoe UI', sans-serif;
        }

        /* =========================
        NAVBAR (Clean + Light)
        ========================= */
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            height: 60px;
            padding: 0 18px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }

        .navbar-brand img {
            height: 45px;
        }

        /* =========================
        SIDEBAR (MODERN)
        ========================= */
        .sidebar {
            background: #ffffff;
            width: 210px;
            height: 100vh;
            border-right: 1px solid #e5e7eb;
            padding-top: 10px;
            overflow-y: auto;
        }

        /* COLLAPSED MODE */
        .sidebar-icon-only .sidebar {
            width: 70px;
        }

        .sidebar-icon-only .menu-title,
        .sidebar-icon-only .badge {
            display: none;
        }

        /* =========================
        NAV ITEMS
        ========================= */
        .sidebar .nav {
            padding: 0;
        }

        .sidebar .nav-item {
            list-style: none;
        }

        /* MAIN LINKS */
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            margin: 3px 8px;
            border-radius: 8px;
            font-size: 14px;
            color: #374151;
            position: relative;
        }

        /* ICON */
        .sidebar .nav-link i {
            font-size: 18px;
            color: #6b7280;
        }

        /* HOVER EFFECT (MODERN) */
        .sidebar .nav-link:hover {
            background: #f1f5f9;
            color: #111827;
        }

        /* ACTIVE ITEM (PRO LOOK) */
        .sidebar .nav-link.active {
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 600;
        }

        /* ACTIVE ICON */
        .sidebar .nav-link.active i {
            color: #4f46e5;
        }

        /* REMOVE OLD LEFT BORDER */
        .sidebar .nav-link::before {
            display: none;
        }

        /* =========================
        SUB MENU
        ========================= */
        .sub-menu {
            padding-left: 26px;
        }

        /* SUB MENU LINKS */
        .sub-menu .nav-link {
            font-size: 13px;
            padding: 6px 12px;
            margin: 2px 6px;
            border-radius: 6px;
        }

        /* SUB MENU HOVER */
        .sub-menu .nav-link:hover {
            background: #f3f4f6;
        }

        /* =========================
        BADGE (SMALL + CLEAN)
        ========================= */
        .badge {
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 5px;
        }

        /* =========================
        MAIN CONTENT
        ========================= */
        .main-panel {
            margin-top: 0 !important;
        }

        .content-wrapper {
            padding: 18px !important;
            background: #f9fafb;
        }

        /* =========================
        CARD (SOFT SHADOW)
        ========================= */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.05);
        }

        /* =========================
        PAGE HEADER
        ========================= */
        .page-header {
            margin-bottom: 10px;
        }

        /* =========================
        BUTTON POLISH
        ========================= */
        .btn {
            border-radius: 8px;
        }

        /* =========================
        SCROLLBAR (OPTIONAL NICE)
        ========================= */
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }

        /* =========================
        LOADER FIX
        ========================= */
        #loader {
            background: #fff;
        }

        /* =========================
        RESPONSIVE
        ========================= */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 999;
                left: -22px;
            }

            .sidebar.active {
                left: 0;
            }
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
                    <button class="btn dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        @if(auth()->user() && auth()->user()->image)
                            <img src="{{ asset('assets/images/profiles/' . auth()->user()->image) }}"
                                 alt="{{ auth()->user()->name ?? 'User' }}"
                                 class="rounded-circle"
                                 style="width:32px; height:32px; object-fit:cover;">
                        @else
                            <i class="mdi mdi-account-circle" style="font-size:24px;"></i>
                        @endif
                        <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('users.show', auth()->id()) }}">Profile</a></li>
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
        @if(!isset($hideSidebar))
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">

                <li class="nav-item text-center py-3">
                    <img src="{{ asset('assets/images/cms-rc-logo1.png') }}" style="height:35px;">
                </li>

                @can('dashboard-view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">
                        <i class="mdi mdi-view-dashboard-outline"></i>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>
                @endcan

                @can('menu-patients')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#patientsMenu">
                        <i class="mdi mdi-account-heart-outline"></i>
                        <span class="menu-title">Patients</span>
                        {{-- <span class="badge bg-primary ms-auto">{{ $patients ?? 0 }}</span> --}}
                    </a>
                    <div class="collapse" id="patientsMenu">
                        <ul class="nav sub-menu">
                            <li><a class="nav-link" href="{{ route('patients.index') }}">All</a></li>
                            @can('patients-create')
                            <li><a class="nav-link" href="{{ route('patients.create') }}">Add</a></li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcan

                @can('menu-appointments')
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
                @endcan

                @can('menu-consultations')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('consultations.index') }}">
                        <i class="mdi mdi-stethoscope"></i>
                        <span class="menu-title">Consultation</span>
                    </a>
                </li>
                @endcan

                @can('menu-users')
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
                @endcan

                @can('menu-units')
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
                @endcan

                @can('menu-reports')
                <li class="nav-item">
                    <a class="nav-link" href="/">
                        <i class="mdi mdi-chart-line"></i>
                        <span class="menu-title">Reports</span>
                    </a>
                </li>
                @endcan

                @can('menu-roles')
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
                @endcan

            </ul>
        </nav>
        @endif

        <!-- MAIN -->
        <div class="main-panel {{ isset($hideSidebar) ? 'w-100' : '' }}">
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

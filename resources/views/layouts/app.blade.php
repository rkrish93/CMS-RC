@php
    $currentUser = auth()->user();
    $currentUserName = $currentUser?->name ?: 'Admin';
    $currentUserRole = $currentUser?->designation ?: 'CMS-RC User';

    $dashboardActive = request()->routeIs('dashboard') || request()->is('/');
    $patientsActive = request()->routeIs('patients.*');
    $appointmentsActive = request()->routeIs('appointments.*');
    $consultationsActive = request()->routeIs('consultations.*');
    $usersActive = request()->routeIs('users.*');
    $unitsActive = request()->routeIs('units.*');
    $settingsActive = request()->routeIs('roles.*') || request()->routeIs('permissions.*') || request()->routeIs('permission-groups.*');
@endphp

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

    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        :root {
            --cms-sidebar-width: 268px;
            --cms-sidebar-collapsed-width: 84px;
            --cms-navbar-height: 68px;
            --cms-bg: #f6f8fb;
            --cms-panel: #ffffff;
            --cms-text: #152033;
            --cms-muted: #667085;
            --cms-line: #e4e9f0;
            --cms-primary: #2563eb;
            --cms-primary-soft: #eaf2ff;
            --cms-teal: #0f766e;
            --cms-shadow: 0 18px 45px rgba(21, 32, 51, 0.08);
        }

        html {
            min-height: 100%;
        }

        body.cms-shell {
            min-height: 100%;
            background: var(--cms-bg);
            color: var(--cms-text);
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
            letter-spacing: 0;
        }

        .cms-shell a,
        .cms-shell button,
        .cms-shell .nav-link,
        .cms-shell .btn,
        .cms-shell .card {
            transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .cms-shell .container-scroller {
            background: var(--cms-bg);
        }

        .cms-shell .topbar {
            min-height: var(--cms-navbar-height);
            padding: 0 24px;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid var(--cms-line);
            box-shadow: 0 8px 28px rgba(21, 32, 51, 0.06);
            backdrop-filter: blur(10px);
        }

        .cms-shell .navbar.fixed-top + .page-body-wrapper {
            padding-top: var(--cms-navbar-height);
        }

        .cms-shell .brand-mark {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #f8fbff;
            border: 1px solid var(--cms-line);
        }

        .cms-shell .brand-mark img {
            max-width: 34px;
            max-height: 34px;
            object-fit: contain;
        }

        .cms-shell .brand-text {
            line-height: 1.1;
        }

        .cms-shell .brand-title {
            display: block;
            color: var(--cms-text);
            font-size: 16px;
            font-weight: 800;
        }

        .cms-shell .brand-subtitle {
            color: var(--cms-muted);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .cms-shell .icon-button {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 1px solid var(--cms-line);
            border-radius: 10px;
            background: #ffffff;
            color: #344054;
        }

        .cms-shell .icon-button:hover,
        .cms-shell .icon-button:focus {
            background: var(--cms-primary-soft);
            border-color: #c7d7fe;
            color: var(--cms-primary);
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.12);
        }

        .cms-shell .notification-link {
            position: relative;
        }

        .cms-shell .notification-link .notification-count {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            border: 2px solid #ffffff;
            border-radius: 999px;
            background: #dc2626;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            line-height: 15px;
            text-align: center;
        }

        .cms-shell .user-menu-button {
            min-height: 44px;
            padding: 5px 8px 5px 6px;
            border: 1px solid var(--cms-line);
            border-radius: 12px;
            background: #ffffff;
            color: var(--cms-text);
        }

        .cms-shell .user-menu-button:hover,
        .cms-shell .user-menu-button:focus {
            border-color: #c7d7fe;
            box-shadow: 0 10px 24px rgba(21, 32, 51, 0.08);
        }

        .cms-shell .avatar,
        .cms-shell .avatar-placeholder {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            border-radius: 50%;
            object-fit: cover;
        }

        .cms-shell .avatar-placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--cms-primary), var(--cms-teal));
            color: #ffffff;
            font-size: 18px;
        }

        .cms-shell .user-meta {
            max-width: 160px;
            line-height: 1.15;
            text-align: left;
        }

        .cms-shell .user-name,
        .cms-shell .user-role {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cms-shell .user-name {
            color: var(--cms-text);
            font-size: 13px;
            font-weight: 800;
        }

        .cms-shell .user-role {
            margin-top: 2px;
            color: var(--cms-muted);
            font-size: 11px;
            font-weight: 600;
        }

        .cms-shell .page-body-wrapper {
            background: var(--cms-bg);
        }

        .cms-shell .sidebar {
            width: var(--cms-sidebar-width);
            min-height: calc(100vh - var(--cms-navbar-height));
            padding: 18px 14px 20px;
            background: var(--cms-panel);
            border-right: 1px solid var(--cms-line);
            box-shadow: 12px 0 34px rgba(21, 32, 51, 0.04);
            z-index: 1000;
        }

        .cms-shell .sidebar .nav {
            gap: 4px;
            margin-bottom: 0;
            overflow: visible;
        }

        .cms-shell .sidebar .nav-item {
            width: 100%;
            padding: 0;
            list-style: none;
        }

        .cms-shell .sidebar-brand {
            display: none;
        }

        .cms-shell .nav-section-label {
            margin: 12px 12px 8px;
            color: #98a2b3;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .cms-shell .sidebar .nav-link {
            min-height: 43px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            color: #44546a;
            font-size: 14px;
            font-weight: 700;
            white-space: normal;
        }

        .cms-shell .sidebar .nav-link i:first-child {
            width: 22px;
            flex: 0 0 22px;
            color: #7c8aa0;
            font-size: 20px;
            line-height: 1;
            text-align: center;
        }

        .cms-shell .sidebar .nav-link .menu-title {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cms-shell .sidebar .nav-link .menu-arrow {
            width: 18px;
            flex: 0 0 18px;
            color: #98a2b3;
            font-size: 18px;
        }

        .cms-shell .sidebar .nav-link:hover {
            background: var(--cms-primary-soft);
            color: var(--cms-primary);
        }

        .cms-shell .sidebar .nav-link.active {
            background: var(--cms-primary-soft);
            color: var(--cms-primary);
        }

        .cms-shell .sidebar .sub-menu .nav-link.active {
            background: var(--cms-primary-soft);
            color: var(--cms-primary);
        }

        .cms-shell .sidebar .nav-link:hover i,
        .cms-shell .sidebar .nav-link.active i {
            color: var(--cms-primary);
        }

        .cms-shell .sidebar .sub-menu .nav-link.active i {
            color: var(--cms-primary);
        }

        .cms-shell .sidebar .nav-link[aria-expanded="true"] .menu-arrow {
            transform: rotate(180deg);
        }

        .cms-shell .sidebar .sub-menu {
            margin: 4px 0 8px 34px;
            padding: 4px 0 4px 12px;
            border-left: 1px solid #d8e0eb;
        }

        .cms-shell .sidebar .sub-menu .nav-link {
            min-height: 34px;
            padding: 7px 10px;
            border-radius: 8px;
            color: #59677a;
            font-size: 13px;
            font-weight: 700;
        }

        .cms-shell .sidebar .sub-menu .nav-link:before {
            display: none;
        }

        .cms-shell .sidebar .badge {
            min-width: 24px;
            padding: 5px 7px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }

        .cms-shell .main-panel {
            width: calc(100% - var(--cms-sidebar-width));
            min-height: calc(100vh - var(--cms-navbar-height));
            background: var(--cms-bg);
        }

        .cms-shell.cms-no-sidebar .main-panel {
            width: 100%;
        }

        .cms-shell .content-wrapper {
            width: 100%;
            padding: 26px;
            background: var(--cms-bg);
            flex-grow: 1;
        }

        .cms-shell .page-header {
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 0 0 20px;
        }

        .cms-shell .page-title {
            margin: 0;
            color: var(--cms-text);
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .cms-shell .card {
            border: 1px solid var(--cms-line);
            border-radius: 12px;
            box-shadow: var(--cms-shadow);
        }

        .cms-shell .card .card-title,
        .cms-shell .card h4,
        .cms-shell .card h5,
        .cms-shell .card h6 {
            color: var(--cms-text);
            letter-spacing: 0;
        }

        .cms-shell .table thead th {
            color: #475467;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .cms-shell .btn {
            border-radius: 9px;
            font-weight: 700;
        }

        .cms-shell .dropdown-menu {
            padding: 8px;
            border: 1px solid var(--cms-line);
            border-radius: 12px;
            box-shadow: 0 18px 48px rgba(21, 32, 51, 0.14);
        }

        .cms-shell .dropdown-item {
            border-radius: 8px;
            color: #344054;
            font-size: 13px;
            font-weight: 700;
        }

        .cms-shell .footer {
            padding: 16px 26px;
            background: transparent;
            border-top: 1px solid var(--cms-line);
            color: var(--cms-muted);
        }

        .cms-shell #loader {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            z-index: 9999;
        }

        .cms-shell #loader:after {
            width: 36px;
            height: 36px;
            content: "";
            border: 3px solid #dbe6f3;
            border-top-color: var(--cms-primary);
            border-radius: 50%;
            animation: cms-spin 0.8s linear infinite;
        }

        @keyframes cms-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .cms-shell .sidebar-overlay {
            position: fixed;
            inset: var(--cms-navbar-height) 0 0 0;
            display: none;
            background: rgba(15, 23, 42, 0.42);
            z-index: 999;
        }

        .cms-shell .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .cms-shell .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        body.sidebar-icon-only.cms-shell .sidebar {
            width: var(--cms-sidebar-collapsed-width);
        }

        body.sidebar-icon-only.cms-shell .main-panel {
            width: calc(100% - var(--cms-sidebar-collapsed-width));
        }

        body.sidebar-icon-only.cms-shell .sidebar .menu-title,
        body.sidebar-icon-only.cms-shell .sidebar .menu-arrow,
        body.sidebar-icon-only.cms-shell .sidebar .badge,
        body.sidebar-icon-only.cms-shell .sidebar .nav-section-label {
            display: none;
        }

        body.sidebar-icon-only.cms-shell .sidebar .nav-link {
            justify-content: center;
            padding: 11px;
        }

        body.sidebar-icon-only.cms-shell .sidebar .sub-menu {
            display: none;
        }

        @media (max-width: 991px) {
            .cms-shell .topbar {
                padding: 0 14px;
            }

            .cms-shell .brand-subtitle,
            .cms-shell .user-meta {
                display: none;
            }

            .cms-shell .sidebar {
                position: fixed;
                top: var(--cms-navbar-height);
                left: 0;
                height: calc(100vh - var(--cms-navbar-height));
                min-height: 0;
                overflow-y: auto;
                transform: translateX(-104%);
                box-shadow: 22px 0 42px rgba(21, 32, 51, 0.22);
            }

            .cms-shell .sidebar.active {
                transform: translateX(0);
            }

            .cms-shell .sidebar-overlay.is-visible {
                display: block;
            }

            .cms-shell .main-panel,
            body.sidebar-icon-only.cms-shell .main-panel {
                width: 100%;
            }

            body.sidebar-icon-only.cms-shell .sidebar {
                width: var(--cms-sidebar-width);
            }

            body.sidebar-icon-only.cms-shell .sidebar .menu-title,
            body.sidebar-icon-only.cms-shell .sidebar .menu-arrow,
            body.sidebar-icon-only.cms-shell .sidebar .badge,
            body.sidebar-icon-only.cms-shell .sidebar .nav-section-label {
                display: initial;
            }
        }

        @media (max-width: 575px) {
            .cms-shell .content-wrapper {
                padding: 18px 14px;
            }

            .cms-shell .page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .cms-shell .page-title {
                font-size: 19px;
            }

            .cms-shell .brand-title {
                font-size: 14px;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="cms-shell {{ isset($hideSidebar) ? 'cms-no-sidebar' : '' }}">
<div id="loader" aria-hidden="true"></div>

<div class="container-scroller">
    <nav class="navbar topbar navbar-expand-lg fixed-top">
        <div class="container-fluid px-0">
            @if(!isset($hideSidebar))
                <button class="btn icon-button me-3" id="menu-toggle" type="button" aria-label="Toggle sidebar" aria-controls="sidebar">
                    <i class="mdi mdi-menu"></i>
                </button>
            @endif

            <a class="navbar-brand d-flex align-items-center gap-2 m-0" href="{{ route('dashboard') }}">
                <span class="brand-mark">
                    <img src="{{ asset('assets/images/cms-rc-logo1.png') }}" alt="CMS-RC">
                </span>
                <span class="brand-text">
                    <span class="brand-title">CMS-RC</span>
                    <span class="brand-subtitle">Clinic Management</span>
                </span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-2">
                @can('menu-appointments')
                    <a class="btn icon-button notification-link" href="{{ route('appointments.today') }}" aria-label="Today appointments">
                        <i class="mdi mdi-bell-outline"></i>
                        @if(($todayAppointments ?? 0) > 0)
                            <span class="notification-count">{{ $todayAppointments }}</span>
                        @endif
                    </a>
                @endcan

                <div class="dropdown">
                    <button class="btn user-menu-button dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if($currentUser && $currentUser->image)
                            <img src="{{ asset('assets/images/profiles/' . $currentUser->image) }}"
                                 alt="{{ $currentUserName }}"
                                 class="avatar">
                        @else
                            <span class="avatar-placeholder">
                                <i class="mdi mdi-account"></i>
                            </span>
                        @endif
                        <span class="user-meta">
                            <span class="user-name">{{ $currentUserName }}</span>
                            <span class="user-role">{{ $currentUserRole }}</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @if($currentUser)
                            <li>
                                <a class="dropdown-item" href="{{ route('users.show', $currentUser->id) }}">
                                    <i class="mdi mdi-account-outline me-2"></i>Profile
                                </a>
                            </li>
                        @endif
                        <li>
                            <a class="dropdown-item" href="{{ route('change.password') }}">
                                <i class="mdi mdi-lock-reset me-2"></i>Change Password
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item" type="submit">
                                    <i class="mdi mdi-logout me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid page-body-wrapper">
        @if(!isset($hideSidebar))
            <nav class="sidebar sidebar-offcanvas" id="sidebar" aria-label="Main navigation">
                <ul class="nav">
                    <li class="nav-item nav-section-label">Main</li>

                    @can('dashboard-view')
                        <li class="nav-item {{ $dashboardActive ? 'active' : '' }}">
                            <a class="nav-link {{ $dashboardActive ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="mdi mdi-view-dashboard-outline"></i>
                                <span class="menu-title">Dashboard</span>
                            </a>
                        </li>
                    @endcan

                    @can('menu-patients')
                        <li class="nav-item {{ $patientsActive ? 'active' : '' }}">
                            <a class="nav-link" data-bs-toggle="collapse" href="#patientsMenu" role="button" aria-expanded="{{ $patientsActive ? 'true' : 'false' }}" aria-controls="patientsMenu">
                                <i class="mdi mdi-account-heart-outline"></i>
                                <span class="menu-title">Patients</span>
                                <i class="mdi mdi-chevron-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $patientsActive ? 'show' : '' }}" id="patientsMenu">
                                <ul class="nav sub-menu">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('patients.index') ? 'active' : '' }}" href="{{ route('patients.index') }}">All Patients</a></li>
                                    @can('patients-create')
                                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('patients.create') ? 'active' : '' }}" href="{{ route('patients.create') }}">Add Patient</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                    @endcan

                    @can('menu-appointments')
                        <li class="nav-item {{ $appointmentsActive ? 'active' : '' }}">
                            <a class="nav-link" data-bs-toggle="collapse" href="#appointmentsMenu" role="button" aria-expanded="{{ $appointmentsActive ? 'true' : 'false' }}" aria-controls="appointmentsMenu">
                                <i class="mdi mdi-calendar-clock-outline"></i>
                                <span class="menu-title">Appointments</span>
                                @if(($todayAppointments ?? 0) > 0)
                                    <span class="badge bg-danger ms-auto">{{ $todayAppointments }}</span>
                                @endif
                                <i class="mdi mdi-chevron-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $appointmentsActive ? 'show' : '' }}" id="appointmentsMenu">
                                <ul class="nav sub-menu">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('appointments.index') ? 'active' : '' }}" href="{{ route('appointments.index') }}">All Appointments</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('appointments.today') ? 'active' : '' }}" href="{{ route('appointments.today') }}">Today Queue</a></li>
                                </ul>
                            </div>
                        </li>
                    @endcan

                    @can('menu-consultations')
                        <li class="nav-item {{ $consultationsActive ? 'active' : '' }}">
                            <a class="nav-link {{ $consultationsActive ? 'active' : '' }}" href="{{ route('consultations.index') }}">
                                <i class="mdi mdi-stethoscope"></i>
                                <span class="menu-title">Consultations</span>
                            </a>
                        </li>
                    @endcan

                    @canany(['menu-users', 'menu-units', 'menu-reports', 'menu-roles'])
                        <li class="nav-item nav-section-label">Administration</li>

                        @can('menu-users')
                            <li class="nav-item {{ $usersActive ? 'active' : '' }}">
                                <a class="nav-link" data-bs-toggle="collapse" href="#usersMenu" role="button" aria-expanded="{{ $usersActive ? 'true' : 'false' }}" aria-controls="usersMenu">
                                    <i class="mdi mdi-account-cog-outline"></i>
                                    <span class="menu-title">Users</span>
                                    <i class="mdi mdi-chevron-down menu-arrow"></i>
                                </a>
                                <div class="collapse {{ $usersActive ? 'show' : '' }}" id="usersMenu">
                                    <ul class="nav sub-menu">
                                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">User List</a></li>
                                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}" href="{{ route('users.create') }}">Add User</a></li>
                                    </ul>
                                </div>
                            </li>
                        @endcan

                        @can('menu-units')
                            <li class="nav-item {{ $unitsActive ? 'active' : '' }}">
                                <a class="nav-link" data-bs-toggle="collapse" href="#unitsMenu" role="button" aria-expanded="{{ $unitsActive ? 'true' : 'false' }}" aria-controls="unitsMenu">
                                    <i class="mdi mdi-hospital-building"></i>
                                    <span class="menu-title">Units</span>
                                    <i class="mdi mdi-chevron-down menu-arrow"></i>
                                </a>
                                <div class="collapse {{ $unitsActive ? 'show' : '' }}" id="unitsMenu">
                                    <ul class="nav sub-menu">
                                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('units.index') ? 'active' : '' }}" href="{{ route('units.index') }}">All Units</a></li>
                                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('units.create') ? 'active' : '' }}" href="{{ route('units.create') }}">Add Unit</a></li>
                                    </ul>
                                </div>
                            </li>
                        @endcan

                        @can('menu-reports')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">
                                    <i class="mdi mdi-chart-line"></i>
                                    <span class="menu-title">Reports</span>
                                </a>
                            </li>
                        @endcan

                        @can('menu-roles')
                            <li class="nav-item {{ $settingsActive ? 'active' : '' }}">
                                <a class="nav-link" data-bs-toggle="collapse" href="#settingsMenu" role="button" aria-expanded="{{ $settingsActive ? 'true' : 'false' }}" aria-controls="settingsMenu">
                                    <i class="mdi mdi-cog-outline"></i>
                                    <span class="menu-title">Settings</span>
                                    <i class="mdi mdi-chevron-down menu-arrow"></i>
                                </a>
                                <div class="collapse {{ $settingsActive ? 'show' : '' }}" id="settingsMenu">
                                    <ul class="nav sub-menu">
                                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">Roles</a></li>
                                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}" href="{{ route('permissions.index') }}">Permissions</a></li>
                                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('permission-groups.*') ? 'active' : '' }}" href="{{ route('permission-groups.index') }}">Permission Groups</a></li>
                                    </ul>
                                </div>
                            </li>
                        @endcan
                    @endcanany
                </ul>
            </nav>
            <div class="sidebar-overlay" id="sidebar-overlay"></div>
        @endif

        <div class="main-panel {{ isset($hideSidebar) ? 'w-100' : '' }}">
            <div class="content-wrapper">
                @hasSection('title')
                    <div class="page-header">
                        <h3 class="page-title">@yield('title')</h3>
                        @hasSection('page-actions')
                            <div class="page-actions">@yield('page-actions')</div>
                        @endif
                    </div>
                @endif

                @yield('content')
            </div>

            <footer class="footer text-center">
                <small>&copy; {{ date('Y') }} CMS-RC | Clinic Management System for Rural Clinics</small>
            </footer>
        </div>
    </div>
</div>

<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('assets/vendors/chart.js/chart.umd.js') }}"></script>
<script src="{{ asset('assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('assets/js/misc.js') }}"></script>

<script>
    (function () {
        const body = document.body;
        const toggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const loader = document.getElementById('loader');

        function isMobile() {
            return window.matchMedia('(max-width: 991px)').matches;
        }

        function closeMobileSidebar() {
            if (!sidebar || !overlay) {
                return;
            }

            sidebar.classList.remove('active');
            overlay.classList.remove('is-visible');
        }

        if (toggle && sidebar) {
            toggle.addEventListener('click', function () {
                if (isMobile()) {
                    sidebar.classList.toggle('active');
                    overlay?.classList.toggle('is-visible', sidebar.classList.contains('active'));
                    return;
                }

                body.classList.toggle('sidebar-icon-only');
            });
        }

        overlay?.addEventListener('click', closeMobileSidebar);

        window.addEventListener('resize', function () {
            if (!isMobile()) {
                closeMobileSidebar();
            }
        });

        window.addEventListener('load', function () {
            if (loader) {
                loader.style.display = 'none';
            }
        });
    })();
</script>

@yield('scripts')
@stack('scripts')

</body>
</html>

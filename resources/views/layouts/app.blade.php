<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @hasSection('title')
        @yield('title') | CMS-RC
        @else
        CMS-RC
        @endif
    </title>

    <!-- plugins css -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/font-awesome.min.css') }}">

    <!-- page css -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">

    <!-- layout css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
</head>
</head>

<body>

<div class="container-scroller">

    <!-- NAVBAR -->
    <nav class="navbar default-layout-navbar fixed-top d-flex flex-row">
        <div class="navbar-brand-wrapper d-flex align-items-center">
            <a class="navbar-brand brand-logo" href="#">
                <img src="{{ asset('assets/images/logo.svg') }}">
            </a>
        </div>
    </nav>

    <div class="container-fluid page-body-wrapper">

        <!-- SIDEBAR -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">

                <li class="nav-item">
                    <a class="nav-link" href="/">
                        <span class="menu-title">Dashboard</span>
                        <i class="mdi mdi-home menu-icon"></i>
                    </a>
                </li>

                            <!-- Patient MENU -->
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                        <span class="menu-title">Petient</span>
                    <i class="menu-arrow"></i>
                    <i class="mdi mdi-crosshairs-gps menu-icon"></i>
                </a>
                    <div class="collapse" id="ui-basic">
                    <ul class="nav flex-column sub-menu">

                  <li class="nav-item">
                <a class="nav-link" href="{{ route('patients.index') }}">
                    Patients List
                </a>
            </li>
                  <li class="nav-item">
                <a class="nav-link" href="{{ route('patients.create') }}">
                    Create Patient
                </a>
            </li>
                </ul>
              </div>
            </li>

            <!-- APPOINTMENT MENU -->
            <li class="nav-item">
                <a class="nav-link" href="{{ route('appointments.index') }}">
                    <span class="menu-title">Appointment</span>
                    <i class="mdi mdi-calendar-check menu-icon"></i>
                </a>
            </li>
            <!-- USERS MENU -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                    <span class="menu-title">user</span>
                <i class="menu-arrow"></i>
                <i class="mdi mdi-crosshairs-gps menu-icon"></i>
                </a>
                    <div class="collapse" id="ui-basic">
                    <ul class="nav flex-column sub-menu">

                    <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}">
                    User List
                </a>
            </li>
                  <li class="nav-item">
                <a class="nav-link" href="{{ route('users.create') }}">
                    Create User
                </a>
            </li>
                </ul>
              </div>
            </li>


                           <!-- UNIT MENU -->
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                        <span class="menu-title">Units</span>
                    <i class="menu-arrow"></i>
                    <i class="mdi mdi-crosshairs-gps menu-icon"></i>
                </a>
                    <div class="collapse" id="ui-basic">
                    <ul class="nav flex-column sub-menu">

                  <li class="nav-item">
                <a class="nav-link" href="{{ route('units.index') }}">
                    Units List
                </a>
            </li>
                  <li class="nav-item">
                <a class="nav-link" href="{{ route('units.create') }}">
                    Create Unit
                </a>
            </li>
                </ul>
              </div>
            </li>

            </ul>
        </nav>

        <!-- MAIN -->
        <div class="main-panel">
            <div class="content-wrapper">

                @yield('content')

            </div>

            <footer class="footer text-center">
                © {{ date('Y') }} CMS-RC
            </footer>

        </div>

    </div>

</div>

@yield('scripts')

<!-- plugins js -->
<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>

<!-- page js -->
<script src="{{ asset('assets/vendors/chart.js/chart.umd.js') }}"></script>
<script src="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>

<!-- layout js -->
<script src="{{ asset('assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('assets/js/misc.js') }}"></script>
<script src="{{ asset('assets/js/settings.js') }}"></script>
<script src="{{ asset('assets/js/todolist.js') }}"></script>
<script src="{{ asset('assets/js/jquery.cookie.js') }}"></script>

<!-- custom js -->
<script src="{{ asset('assets/js/dashboard.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

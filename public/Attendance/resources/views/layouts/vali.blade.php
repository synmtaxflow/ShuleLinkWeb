<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Attendance System')</title>

    {{-- Vali main CSS --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('vali-master/docs/css/main.css') }}">
    {{-- Font Awesome --}}
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    @stack('styles')
  </head>
  <body class="app sidebar-mini rtl">
    <!-- Navbar-->
    <header class="app-header">
      <a class="app-header__logo" href="{{ route('dashboard') }}">Attendance</a>
      <!-- Sidebar toggle button-->
      <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
      <!-- Navbar Right Menu-->
      <ul class="app-nav">
        {{-- You can add top-right items here later (profile, notifications, etc.) --}}
      </ul>
    </header>

    <!-- Sidebar menu-->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
      <div class="app-sidebar__user">
        <div>
          <p class="app-sidebar__user-name">Attendance System</p>
          <p class="app-sidebar__user-designation">Dashboard</p>
        </div>
      </div>
      <ul class="app-menu">
        <li>
          <a class="app-menu__item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="app-menu__icon fa fa-dashboard"></i>
            <span class="app-menu__label">Dashboard</span>
          </a>
        </li>
        <li>
          <a class="app-menu__item {{ request()->is('users*') ? 'active' : '' }}" href="{{ route('users.index') }}">
            <i class="app-menu__icon fa fa-users"></i>
            <span class="app-menu__label">Users</span>
          </a>
        </li>
        <li>
          <a class="app-menu__item {{ request()->is('attendances*') ? 'active' : '' }}" href="{{ route('attendances.index') }}">
            <i class="app-menu__icon fa fa-calendar"></i>
            <span class="app-menu__label">Attendance</span>
          </a>
        </li>
        <li>
          <a class="app-menu__item {{ request()->is('reports*') ? 'active' : '' }}" href="{{ route('reports.daily') }}">
            <i class="app-menu__icon fa fa-bar-chart"></i>
            <span class="app-menu__label">Reports</span>
          </a>
        </li>
        <li>
          <a class="app-menu__item {{ request()->is('zkteco*') ? 'active' : '' }}" href="{{ route('zkteco.test') }}">
            <i class="app-menu__icon fa fa-plug"></i>
            <span class="app-menu__label">Device</span>
          </a>
        </li>
        <li>
          <a class="app-menu__item {{ request()->is('reset*') ? 'active' : '' }}" href="{{ route('reset.index') }}">
            <i class="app-menu__icon fa fa-refresh"></i>
            <span class="app-menu__label">Reset / Fresh Start</span>
          </a>
        </li>
      </ul>
    </aside>

    <main class="app-content">
      <div class="app-title">
        <div>
          <h1>
            @hasSection('icon')
              <i class="fa @yield('icon')"></i>
            @else
              <i class="fa fa-dashboard"></i>
            @endif
            @yield('title', 'Dashboard')
          </h1>
          @hasSection('subtitle')
            <p>@yield('subtitle')</p>
          @endif
        </div>
        @hasSection('breadcrumb')
          <ul class="app-breadcrumb breadcrumb">
            @yield('breadcrumb')
          </ul>
        @endif
      </div>

      {{-- Flash messages --}}
      @if(session('success'))
        <div class="alert alert-success">
          {{ session('success') }}
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-danger">
          {{ session('error') }}
        </div>
      @endif

      @yield('content')
    </main>

    {{-- Core JS (Vali) --}}
    <script src="{{ asset('vali-master/docs/js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/popper.min.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/main.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/plugins/pace.min.js') }}"></script>
    {{-- Charts and SweetAlert plugins from Vali --}}
    <script src="{{ asset('vali-master/docs/js/plugins/chart.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/plugins/sweetalert.min.js') }}"></script>

    @stack('scripts')
  </body>
</html>



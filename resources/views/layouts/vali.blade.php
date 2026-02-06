@php
    $user_type = $user_type ?? session('user_type', 'Admin');
@endphp

@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

@yield('content')

@stack('scripts')


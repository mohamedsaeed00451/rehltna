<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-UA-Compatible" content="IE=9"/>

    <!-- Title -->
    <title> {{ env('APP_NAME') }} Panel </title>

    <!-- styles -->
    @include('layouts.vertical.styles')

</head>

<body class="main-body app sidebar-mini">

<!-- Loader -->
<div id="global-loader">
    <img src="{{asset('assets/img/loader.svg')}}" class="loader-img" alt="Loader">
</div>
<!-- /Loader -->

<!-- Page -->
<div class="page">

    <!-- main-sidebar -->
    @include('layouts.vertical.app-sidebar')

    <!-- main-content -->
    <div class='main-content app-content'>

        <!-- main-header -->
        @include('layouts.vertical.main-header')

        <!-- Container open -->
        <div class="container-fluid">

            @yield('content')

        </div>
        <!-- Container closed -->

    </div>
    <!-- main-content closed -->

    <!-- footer -->
    @include('layouts.footer')

    <!-- modal -->
    @yield('modal')

</div>
<!-- Page closed -->

<!-- scripts -->
@include('layouts.vertical.scripts')

    </body>

</html>


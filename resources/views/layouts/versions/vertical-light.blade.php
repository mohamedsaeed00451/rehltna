<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-UA-Compatible" content="IE=9"/>

    <title> {{ env('APP_NAME') }} Panel </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @include('layouts.vertical.styles')

</head>

<body class="main-body app sidebar-mini">

<div id="global-loader">
    <img src="{{asset('assets/img/loader.svg')}}" class="loader-img" alt="Loader">
</div>
<div class="page">

    @include('layouts.vertical.app-sidebar')

    <div class='main-content app-content'>

        @include('layouts.vertical.main-header')

        <div class="container-fluid">

            @yield('content')

        </div>
    </div>
    @include('layouts.footer')

    @yield('modal')

</div>
@include('layouts.vertical.scripts')

</body>
</html>

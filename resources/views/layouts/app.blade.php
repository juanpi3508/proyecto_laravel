<!doctype html>
<html lang="es-EC">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'KoKo Market')</title>
    <meta name="description" content="@yield('meta_description', 'KoKo Market - Supermercado ecuatoriano')">
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/logo-removebg.png') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Styles propios -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}?v={{ filemtime(public_path('assets/css/styles.css')) }}">

    @stack('styles')
</head>

<body class="d-flex flex-column min-vh-100">

{{-- HEADER / NAVBAR --}}
@include('layouts.header')

{{-- CONTENIDO --}}
<main class="flex-fill">
    @yield('content')
</main>

{{-- FOOTER --}}
@include('layouts.footer')

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

@stack('scripts')
</body>
</html>

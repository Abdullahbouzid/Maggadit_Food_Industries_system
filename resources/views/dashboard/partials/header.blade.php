<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Maggadit Food Industries Dashboard">
    <meta name="theme-color" content="#6510b9">

    <link rel="shortcut icon" href="{{ asset('img/logo/logo.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/logo/logo.ico') }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Nova Theme System -->
    <script src="{{ asset('js/nova-theme.js') }}"></script>

    <!-- Nova Design System -->
    <link href="{{ asset('css/nova.css') }}" rel="stylesheet">

    @stack('styles')
</head>

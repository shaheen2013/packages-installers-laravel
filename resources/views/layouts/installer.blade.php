<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel Installer</title>

    @include('LaravelInstaller::partials.header')

    @yield('css')
</head>
<body>

<div id="laravel-installer">
    @section('contents')
    @show
</div>

@include('LaravelInstaller::partials.footer')
@yield('js')

</body>
</html>

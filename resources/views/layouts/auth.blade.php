<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') - {{ config('app.name', 'zainERP') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="erp-auth-body">
    <main class="erp-auth-shell">
        <section class="erp-auth-card">
            <div class="erp-auth-brand">
                <span class="erp-logo-mark">ZE</span>
                <div>
                    <strong>SupportDesk Pro</strong>
                    <small>Enterprise ERP Access</small>
                </div>
            </div>

            @yield('content')
        </section>
    </main>
</body>
</html>

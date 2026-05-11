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
    @php
        $companyLogo = \App\Models\Setting::query()->where('key', 'company_logo')->value('value');
        $companyLogoUrl = filled($companyLogo) ? \Illuminate\Support\Facades\Storage::disk('public')->url($companyLogo) : null;
    @endphp
    <main class="erp-auth-shell">
        <section class="erp-auth-card">
            <div class="erp-auth-brand">
                @if ($companyLogoUrl)
                    <img class="erp-auth-logo" src="{{ $companyLogoUrl }}" alt="{{ config('app.name', 'Company') }} logo">
                @else
                    <span class="erp-logo-mark">ZE</span>
                @endif
                <div>
                    <strong>SupportDesk Pro</strong>
                    <small> 	Integrated Support & Ticketing Platform </small>
                </div>
            </div>

            @yield('content')
        </section>
    </main>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'zainERP'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="erp-shell">
        @include('partials.erp.sidebar')

        <main class="erp-main">
            @include('partials.erp.topbar')

            <div class="erp-content">
                @include('partials.erp.page-header', [
                    'breadcrumbs' => $breadcrumbs ?? [],
                    'title' => $title ?? 'Workspace',
                    'subtitle' => $subtitle ?? null,
                    'actions' => $actions ?? null,
                ])

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>

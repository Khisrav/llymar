{{-- @if (!Request::is('/'))
    @php($__inertiaSsr = null)
@endif --}}
@if (Request::is('/'))
    @php($__inertiaSsr = null)
@endif
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@300..800&display=swap');
            
            body {
                font-family: "Open Sans", sans-serif !important;
                font-optical-sizing: auto;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-black">
        @inertia
    </body>
</html>

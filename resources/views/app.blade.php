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
        <meta name="csrf-token" content="{{ csrf_token() }}">

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
        
        @if (Request::is('/'))
            <!-- Canonical URL -->
    		<link rel="canonical" href="https://llymar.ru/" />
    		
    		
            <!-- Yandex.Metrika counter -->
            <script type="text/javascript">
                (function(m,e,t,r,i,k,a){
                    m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                    m[i].l=1*new Date();
                    for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
                    k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
                })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=103938584', 'ym');
            
                ym(103938584, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true});
            </script>
            <noscript><div><img src="https://mc.yandex.ru/watch/103938584" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
            <!-- /Yandex.Metrika counter -->
        @endif
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-black">
        @inertia
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ $page['props']['lang'] ?? 'en' }}" suppressHydrationWarning>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- The page title comes from each page's <Head>. A static <title> here would duplicate it. --}}

        {{-- Each page links to its other language version, as the sitemap does. --}}
        @foreach (['en', 'bn'] as $alternate)
            <link rel="alternate" hreflang="{{ $alternate }}" href="{{ url(preg_replace('#^(en|bn)(?=/|$)#', $alternate, request()->path())) }}">
        @endforeach

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400..700&family=Geist+Mono:wght@400..600&family=Noto+Sans+Bengali:wght@400..700&display=swap" rel="stylesheet">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="alternate" type="application/rss+xml" title="RSS" href="/feed.xml">

        <!-- Applies the saved theme before the first paint, so there is no flash. -->
        <script>
            (function () {
                try {
                    var stored = localStorage.getItem("theme");
                    var prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
                    var dark = stored ? stored === "dark" : prefersDark;
                    document.documentElement.classList.toggle("dark", dark);
                } catch (e) {}
            })();
        </script>

        @viteReactRefresh
        @vite(['resources/css/frontend.css', 'resources/js/frontend/app.tsx'])
        @inertiaHead
    </head>
    <body class="min-h-full flex flex-col">
        @inertia
    </body>
</html>

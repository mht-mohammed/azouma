<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'عزومة — دليل مطاعم غزة')</title>
    <meta name="description" content="@yield('meta_description', 'عزومة: دليل المطاعم في غزة — تصفح المطاعم، شاهد الصور وساعات العمل وحالة الدوام والموقع.')">
    @vite('resources/css/app.css')
    @stack('styles')
</head>
<body class="bg-amber-50 text-stone-800 min-h-screen flex flex-col" style="font-family: system-ui, -apple-system, 'Segoe UI', Tahoma, sans-serif;">
    <header class="bg-orange-800 text-amber-50 shadow">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-2xl font-bold">عزومة</a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}" class="hover:underline">الرئيسية</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:underline">لوحتي</a>
                @else
                    <a href="{{ route('login') }}" class="hover:underline">دخول</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1 w-full max-w-5xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    <footer class="bg-stone-800 text-stone-200 text-sm">
        <div class="max-w-5xl mx-auto px-4 py-4 text-center">
            عزومة — دليل مطاعم غزة · البيانات استرشادية وتُحدَّث من أصحاب المطاعم
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

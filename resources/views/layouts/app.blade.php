<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Templatr - Premium Creative & Web Resources')</title>
    <meta name="description" content="@yield('meta_description', 'Browse thousands of premium design templates, graphics, fonts, audio, and creative assets. Download instantly with a commercial license.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <meta property="og:site_name" content="Templatr">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', 'Templatr - Premium Creative & Web Resources')">
    <meta property="og:description" content="@yield('og_description', 'Browse thousands of premium design templates, graphics, fonts, audio, and creative assets. Download instantly with a commercial license.')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('og-image.jpg'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'Templatr - Premium Creative & Web Resources')">
    <meta name="twitter:description" content="@yield('og_description', 'Browse thousands of premium design templates, graphics, fonts, audio, and creative assets. Download instantly with a commercial license.')">
    <meta name="twitter:image" content="@yield('og_image', asset('og-image.jpg'))">
    @stack('structured_data')
    <link rel="icon" href="{{ asset('favicon.ico')}}" />
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('/apple-icon-57x57.png')}}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{  asset('/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{  asset('/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{  asset('/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{  asset('/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{  asset('/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{  asset('/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{  asset('/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{  asset('/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{  asset('/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{  asset('/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{  asset('/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{  asset('/favicon-16x16.png') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#ffc400">
    <meta name="msapplication-TileImage" content="{{  asset('/ms-icon-144x144.png') }}">
    <meta name="theme-color" content="#000000">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --color-primary: #FFC300;
            --color-primary-dark: #E6B000;
            --color-black: #000000;
            --color-white: #ffffff;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>

<body class="font-sans antialiased bg-white text-gray-900 overflow-x-hidden" style="font-family: 'Bricolage Grotesque', ui-sans-serif, system-ui, sans-serif;">
    {{-- Preloader --}}
    <div id="preloader" class="preloader">
        <div class="text-center">
            <img src="/templatr.svg" alt="Templatr" class="preloader-logo mx-auto mb-6">
            <div class="flex justify-center space-x-2">
                <div class="w-3 h-3 bg-[#FFC300] rounded-full animate-bounce stagger-1"></div>
                <div class="w-3 h-3 bg-[#FFC300] rounded-full animate-bounce stagger-2"></div>
                <div class="w-3 h-3 bg-[#FFC300] rounded-full animate-bounce stagger-3"></div>
            </div>
            <div class="preloader-bar"></div>
        </div>
    </div>

    <script>
        (() => {
            const minimumDisplayTime = 700;
            const startedAt = Date.now();
            let hidden = false;

            const hidePreloader = () => {
                if (hidden) {
                    return;
                }

                hidden = true;
                window.clearTimeout(maxTimeout);

                document.querySelectorAll('#preloader').forEach((preloader) => {
                    preloader.classList.add('hidden');
                    preloader.setAttribute('aria-hidden', 'true');
                });
            };

            const hideWhenReady = () => {
                const elapsed = Date.now() - startedAt;
                window.setTimeout(hidePreloader, Math.max(0, minimumDisplayTime - elapsed));
            };

            const maxTimeout = window.setTimeout(hidePreloader, 2000);

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', hideWhenReady, { once: true });
            } else {
                hideWhenReady();
            }
        })();
    </script>

    <!-- Livewire Popup Modal -->
    @livewire('popup-modal')

    <!-- Advertisement Popup Modal (admin-controlled via position=popup) -->
    @livewire('advertisement-popup-modal')

    <!-- Navigation -->
    <nav class="bg-black text-white sticky top-0 z-50" x-data="{ mobileOpen: false, searchOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex-shrink-0">
                    <img src="/templatr.svg" alt="Templatr" class="h-8 sm:h-10 w-auto max-w-[160px] sm:max-w-[200px]">
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('products.index') }}"
                        class="{{ request()->routeIs('products.*') ? 'text-[#FFC300]' : 'text-gray-300' }} hover:text-[#FFC300] transition-colors text-sm font-medium">Browse</a>
                    <div class="relative group">
                        <button
                            class="{{ request()->routeIs('products.index') && request()->has('category') ? 'text-[#FFC300]' : 'text-gray-300' }} hover:text-[#FFC300] transition-colors text-sm font-medium flex items-center">
                            Categories
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 border border-gray-100 overflow-hidden">
                            <div class="py-2">
                                @php
                                    $navCategories = \App\Models\Category::orderBy('order')->get();
                                @endphp
                                @foreach($navCategories as $cat)
                                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                                        class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-[#FFC300]/10 hover:text-black transition-colors">
                                        {{ $cat->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}"
                                class="text-[#FFC300] hover:text-white transition-colors text-sm font-medium">Admin</a>
                        @endif
                    @endauth
                </div>

                <!-- Right Side -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Search Toggle -->
                    <button @click="searchOpen = !searchOpen"
                        class="text-gray-300 hover:text-[#FFC300] transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>

                    <!-- Notifications -->
                    @auth
                        @livewire('notification-bell')
                    @endauth

                    <!-- Cart (Livewire) -->
                    @livewire('cart-count')

                    <!-- Wishlist -->
                    @auth
                        <a href="{{ route('wishlist.index') }}"
                            class="text-gray-300 hover:text-[#FFC300] transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </a>
                    @endauth

                    <!-- Auth Links -->
                    @auth
                        <div class="relative group">
                            <button
                                class="flex items-center space-x-2 text-gray-300 hover:text-[#FFC300] transition-colors">
                                <div class="w-8 h-8 bg-[#FFC300] rounded-full flex items-center justify-center">
                                    <span
                                        class="text-black font-bold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                                <span class="text-sm font-medium hidden lg:block">{{ auth()->user()->name }}</span>
                            </button>
                            <div
                                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 border border-gray-100 overflow-hidden">
                                <div class="py-2">
                                    <a href="{{ route('dashboard') }}"
                                        class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-[#FFC300]/10 hover:text-black transition-colors">Dashboard</a>
                                    <a href="{{ route('profile.index') }}"
                                        class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-[#FFC300]/10 hover:text-black transition-colors">Profile</a>
                                    <a href="{{ route('orders.index') }}"
                                        class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-[#FFC300]/10 hover:text-black transition-colors">My Orders</a>
                                    <a href="{{ route('wishlist.index') }}"
                                        class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-[#FFC300]/10 hover:text-black transition-colors">Wishlist</a>
                                    <hr class="my-1 border-gray-100">
                                    <a href="{{ route('affiliate.index') }}"
                                        class="flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-[#FFC300]/10 hover:text-black transition-colors">
                                        <span>Referral Program</span>
                                        @if(auth()->user()->coins > 0)
                                            <span class="text-xs text-[#FFC300] font-semibold">{{ auth()->user()->coins }} coins</span>
                                        @endif
                                    </a>
                                    <hr class="my-1 border-gray-100">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="block w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-[#FFC300]/10 hover:text-black transition-colors">Sign
                                            Out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-gray-300 hover:text-[#FFC300] transition-colors text-sm font-medium">Sign In</a>
                        <a href="{{ route('register') }}"
                            class="bg-[#FFC300] text-black px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-[#FFD633] transition-all hover:shadow-lg hover:shadow-[#FFC300]/25">Join
                            Free</a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center space-x-3">
                    @auth
                        @livewire('cart-count')
                    @else
                        <a href="{{ route('cart.index') }}" class="text-gray-300 relative p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                            </svg>
                            @php $cartCount = count(session()->get('cart', [])) @endphp
                            @if($cartCount > 0)
                                <span
                                    class="absolute -top-1 -right-1 bg-[#FFC300] text-black text-xs font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">{{ $cartCount }}</span>
                            @endif
                        </a>
                    @endauth
                    <button @click="mobileOpen = !mobileOpen" class="text-gray-300 hover:text-white p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path :class="{ 'hidden': mobileOpen, 'block': !mobileOpen }" class="block"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ 'block': mobileOpen, 'hidden': !mobileOpen }" class="hidden"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Search Bar Dropdown -->
        <div x-show="searchOpen" x-cloak class="bg-black border-t border-gray-800">
            <div class="max-w-2xl mx-auto px-4 py-4">
                @livewire('smart-search')
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileOpen" class="md:hidden bg-black border-t border-gray-800" x-cloak>
            <div class="px-4 py-4 space-y-1 max-h-[80vh] overflow-y-auto">
                <div class="pb-3 mb-3 border-b border-gray-800">
                    @livewire('smart-search')
                </div>
                @auth
                    <a href="{{ route('profile.index') }}"
                        class="flex items-center space-x-3 py-3 border-b border-gray-800">
                        <div class="w-10 h-10 bg-[#FFC300] rounded-full flex items-center justify-center">
                            <span class="text-black font-bold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">{{ auth()->user()->name }}</p>
                            <p class="text-gray-400 text-xs">{{ auth()->user()->coins }} coins</p>
                        </div>
                    </a>
                @endauth
                <a href="{{ route('products.index') }}"
                    class="block {{ request()->routeIs('products.*') ? 'text-[#FFC300]' : 'text-gray-300' }} hover:text-[#FFC300] py-2.5 text-sm font-medium">Browse All Items</a>
                @foreach(\App\Models\Category::orderBy('order')->get() as $cat)
                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                        class="block text-gray-300 hover:text-[#FFC300] py-2.5 text-sm">{{ $cat->name }}</a>
                @endforeach
                @auth
                    <hr class="border-gray-800 my-3">
                    <a href="{{ route('dashboard') }}"
                        class="block text-gray-300 hover:text-[#FFC300] py-2.5 text-sm">Dashboard</a>
                    <a href="{{ route('profile.index') }}"
                        class="block text-gray-300 hover:text-[#FFC300] py-2.5 text-sm">Profile</a>
                    <a href="{{ route('orders.index') }}"
                        class="block text-gray-300 hover:text-[#FFC300] py-2.5 text-sm">My Orders</a>
                    <a href="{{ route('wishlist.index') }}"
                        class="block text-gray-300 hover:text-[#FFC300] py-2.5 text-sm">Wishlist</a>
                    <a href="{{ route('affiliate.index') }}"
                        class="block text-gray-300 hover:text-[#FFC300] py-2.5 text-sm">Referral Program</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}"
                            class="block text-[#FFC300] hover:text-white py-2.5 text-sm font-medium">Admin Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="block w-full text-left text-gray-300 hover:text-red-400 py-2.5 text-sm">Sign Out</button>
                    </form>
                @else
                    <hr class="border-gray-800 my-3">
                    <a href="{{ route('login') }}" class="block text-gray-300 hover:text-[#FFC300] py-2.5 text-sm">Sign
                        In</a>
                    <a href="{{ route('register') }}"
                        class="block bg-[#FFC300] text-black text-center py-3 rounded-xl text-sm font-semibold mt-2 hover:bg-[#FFD633] transition-colors">Create
                        Free Account</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Category Navigation Header Bar -->
    <div class="bg-[#FFC300] border-b border-gray-200 hidden md:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-1 overflow-x-auto scrollbar-hide py-2">
                <a href="{{ route('products.index') }}"
                    class="px-4 py-1.5 text-xs font-semibold rounded-full bg-black text-white hover:bg-gray-800 transition-colors whitespace-nowrap">
                    All Items
                </a>
                @php
                    $headerCategories = \App\Models\Category::orderBy('order')->get();
                @endphp
                @foreach($headerCategories as $cat)
                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                        class="px-4 py-1.5 text-xs font-medium text-gray-600 rounded-full hover:bg-[#FFC300]/10 hover:text-black hover:font-semibold transition-all whitespace-nowrap {{ request('category') === $cat->slug ? 'bg-[#FFC300]/20 text-black font-semibold' : '' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Marquee Ad Ticker (admin-controlled via position=marquee) -->
    @livewire('marquee-ad')

    <!-- Flash Messages -->
    @if(session('success') || session('error') || session('info') || session('warning'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm flex items-center justify-between animate-fade-in-down"
                    x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-green-500 hover:text-green-700"><svg class="w-4 h-4"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                        </svg></button>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center justify-between animate-fade-in-down"
                    x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-red-500 hover:text-red-700"><svg class="w-4 h-4"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                        </svg></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl text-sm flex items-center justify-between animate-fade-in-down"
                    x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button @click="show = false" class="text-yellow-500 hover:text-yellow-700"><svg class="w-4 h-4"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                        </svg></button>
                </div>
            @endif
            @if(session('info'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm flex items-center justify-between animate-fade-in-down"
                    x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('info') }}</span>
                    </div>
                    <button @click="show = false" class="text-blue-500 hover:text-blue-700"><svg class="w-4 h-4"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                        </svg></button>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-black text-white mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
                <div class="lg:col-span-2">
                    <a href="{{ route('home') }}" class="inline-block mb-4">
                        <img src="/templatr.svg" alt="Templatr" class="h-8 sm:h-10 w-auto max-w-[180px]">
                    </a>

                    <p class="text-gray-400 text-sm leading-relaxed max-w-md">Premium creative and web resources
                        marketplace — affordable prices starting from as low as <strong>₦3,000</strong>.</p>
                    <div class="flex space-x-4 mt-6">
                        <a href="#"
                            class="w-9 h-9 bg-gray-800 rounded-xl flex items-center justify-center text-gray-400 hover:bg-[#FFC300] hover:text-black transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                            </svg>
                        </a>
                        <a href="#"
                            class="w-9 h-9 bg-gray-800 rounded-xl flex items-center justify-center text-gray-400 hover:bg-[#FFC300] hover:text-black transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
                            </svg>
                        </a>
                        <a href="#"
                            class="w-9 h-9 bg-gray-800 rounded-xl flex items-center justify-center text-gray-400 hover:bg-[#FFC300] hover:text-black transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 0C5.373 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.51 11.51 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">Explore</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('products.index') }}"
                                class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">All Items</a></li>
                        @foreach(\App\Models\Category::orderBy('order')->take(4)->get() as $cat)
                            <li><a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                                    class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">{{ $cat->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">Account</h3>
                    <ul class="space-y-3">
                        @auth
                            <li><a href="{{ route('profile.index') }}"
                                    class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">Profile</a></li>
                            <li><a href="{{ route('orders.index') }}"
                                    class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">Orders</a></li>
                            <li><a href="{{ route('wishlist.index') }}"
                                    class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">Wishlist</a></li>
                        @else
                            <li><a href="{{ route('login') }}"
                                    class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">Sign In</a></li>
                            <li><a href="{{ route('register') }}"
                                    class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">Create Account</a>
                            </li>
                        @endauth
                        <li><a href="{{ route('cart.index') }}"
                                class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">Cart</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">Company</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('products.index') }}"
                                class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">Marketplace</a></li>
                        <li><a href="mailto:support@templatr.site"
                                class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">Support</a></li>
                    </ul>
                </div>
            </div>
            <div
                class="border-t border-gray-800 mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-gray-500 text-sm">
                <p>&copy; {{ date('Y') }} Templatr. All rights reserved.</p>

                <p>Premium Creative & Web Resources Marketplace.</p>
            </div>
        </div>
    </footer>

    {{-- Cart Drawer (always mounted, opens dynamically) --}}
    @livewire('cart-drawer')

    {{-- Global Toast Notification --}}
    <div
        x-data="{ show: false, message: '', type: 'success' }"
        x-on:show-toast.window="message = $event.detail.message; type = $event.detail.type || 'success'; show = true; setTimeout(() => show = false, 3500)"
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-3"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[200] flex items-center space-x-3 px-5 py-3.5 rounded-2xl shadow-2xl text-sm font-semibold text-white pointer-events-auto"
        :class="type === 'error' ? 'bg-red-600' : 'bg-gray-900'"
        style="min-width:280px;max-width:380px;"
    >
        <svg x-show="type !== 'error'" class="w-5 h-5 text-[#FFC300] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <svg x-show="type === 'error'" class="w-5 h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span x-text="message" class="flex-1"></span>
        <button @click="show = false" class="text-white/60 hover:text-white transition-colors ml-2 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Admin / Author: Floating Add Product button --}}
    @auth
        @if(auth()->user()->isAdmin() || auth()->user()->isAuthor())
        <a href="{{ route('admin.products.create') }}"
           title="Add New Product"
           class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-[#FFC300] text-black rounded-full shadow-lg hover:bg-[#FFD633] hover:shadow-xl hover:scale-105 transition-all flex items-center justify-center group">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="absolute right-16 bg-black text-white text-xs font-semibold px-3 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none shadow-lg">
                Add New Product
            </span>
        </a>
        @endif
    @endauth

    {{-- Session Timeout Modal --}}
    <div
        x-data="{
            show: false,
            warning: false,
            countdown: 30,
            countdownInterval: null,
            loginUrl: '{{ route("login") }}',
            open() {
                if (this.show) return;
                this.show = true;
                this.warning = false;
                this.countdown = 30;
                this.countdownInterval = setInterval(() => {
                    this.countdown--;
                    if (this.countdown <= 0) {
                        clearInterval(this.countdownInterval);
                        window.location.href = this.loginUrl;
                    }
                }, 1000);
            },
            openWarning() {
                if (this.show || this.warning) return;
                this.warning = true;
            },
            dismiss() {
                this.warning = false;
                this.show = false;
                clearInterval(this.countdownInterval);
            },
            relogin() {
                window.location.href = this.loginUrl;
            }
        }"
        x-on:session-expired.window="open()"
        x-on:session-warning.window="openWarning()"
        x-cloak
    >
        {{-- Warning toast (session expiring soon) --}}
        <div
            x-show="warning && !show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="fixed bottom-24 left-1/2 -translate-x-1/2 z-[300] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl bg-yellow-500 text-black text-sm font-semibold"
            style="min-width:300px;"
        >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="flex-1">Your session will expire soon.</span>
            <button @click="dismiss()" class="text-black/60 hover:text-black transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Session expired modal --}}
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-[400] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4"
        >
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center"
            >
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Session Expired</h3>
                <p class="text-gray-500 text-sm mb-6">Your session has expired due to inactivity. You'll be redirected to the login page in <span class="font-bold text-gray-900" x-text="countdown"></span> seconds.</p>
                <div class="flex gap-3">
                    <button @click="window.location.reload()" class="flex-1 px-4 py-3 border border-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                        Reload Page
                    </button>
                    <button @click="relogin()" class="flex-1 px-4 py-3 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors">
                        Log In Again
                    </button>
                </div>
            </div>
        </div>
    </div>

    @stack('fab')
    @stack('scripts')
    @livewireScripts
    <script>
        (() => {
            const revealElements = () => {
                const elements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');

                if (!('IntersectionObserver' in window)) {
                    elements.forEach((element) => element.classList.add('visible'));
                    return;
                }

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });

                elements.forEach((element) => observer.observe(element));
            };

            document.addEventListener('DOMContentLoaded', revealElements);
            document.addEventListener('livewire:navigated', revealElements);
        })();

        // Session timeout detection
        (() => {
            const lifetimeMs = {{ config('session.lifetime') }} * 60 * 1000;
            const warningMs = Math.max(lifetimeMs - 2 * 60 * 1000, 0);
            let warningTimer, expiredTimer, lastActivity = Date.now();

            function resetTimers() {
                clearTimeout(warningTimer);
                clearTimeout(expiredTimer);
                lastActivity = Date.now();

                if (warningMs > 0) {
                    warningTimer = setTimeout(() => {
                        window.dispatchEvent(new CustomEvent('session-warning'));
                    }, warningMs);
                }

                expiredTimer = setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('session-expired'));
                }, lifetimeMs);
            }

            // Reset on user activity (throttled to once per minute)
            let throttle = false;
            ['click', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
                document.addEventListener(evt, () => {
                    if (!throttle) {
                        throttle = true;
                        resetTimers();
                        setTimeout(() => { throttle = false; }, 60000);
                    }
                }, { passive: true });
            });

            // Intercept fetch to catch 419 (CSRF/session mismatch) responses
            const originalFetch = window.fetch;
            window.fetch = async function(...args) {
                const response = await originalFetch.apply(this, args);
                if (response.status === 419) {
                    window.dispatchEvent(new CustomEvent('session-expired'));
                }
                return response;
            };

            // Intercept XMLHttpRequest for older libraries
            const originalOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(...args) {
                this.addEventListener('load', function() {
                    if (this.status === 419) {
                        window.dispatchEvent(new CustomEvent('session-expired'));
                    }
                });
                return originalOpen.apply(this, args);
            };

            resetTimers();
        })();
    </script>
</body>

</html>

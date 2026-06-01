<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CreativeMarket - Premium Digital Resources')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-primary: #FFC300;
            --color-primary-dark: #E6B000;
            --color-black: #000000;
            --color-white: #ffffff;
        }
    </style>
</head>
<body class="font-sans antialiased bg-white text-gray-900">
    <!-- Navigation -->
    <nav class="bg-black text-white sticky top-0 z-50" x-data="{ mobileOpen: false, searchOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center space-x-2 flex-shrink-0">
                    <div class="w-8 h-8 bg-[#FFC300] rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight">Creative<span class="text-[#FFC300]">Market</span></span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('products.index') }}" class="text-gray-300 hover:text-[#FFC300] transition-colors text-sm font-medium">Browse</a>
                    <div class="relative group">
                        <button class="text-gray-300 hover:text-[#FFC300] transition-colors text-sm font-medium flex items-center">
                            Categories
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="py-2">
                                @php
                                    $navCategories = \App\Models\Category::orderBy('order')->get();
                                @endphp
                                @foreach($navCategories as $cat)
                                <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FFC300] hover:text-black transition-colors">
                                    {{ $cat->name }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @auth
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-[#FFC300] hover:text-white transition-colors text-sm font-medium">Admin</a>
                        @endif
                    @endauth
                </div>

                <!-- Right Side -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Search Toggle -->
                    <button class="text-gray-300 hover:text-[#FFC300] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    <!-- Cart -->
                    <a href="{{ route('cart.index') }}" class="text-gray-300 hover:text-[#FFC300] transition-colors relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        @php $cartCount = count(session()->get('cart', [])) @endphp
                        @if($cartCount > 0)
                        <span class="absolute -top-2 -right-2 bg-[#FFC300] text-black text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $cartCount }}</span>
                        @endif
                    </a>

                    <!-- Auth Links -->
                    @auth
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-300 hover:text-[#FFC300] transition-colors">
                                <div class="w-8 h-8 bg-[#FFC300] rounded-full flex items-center justify-center">
                                    <span class="text-black font-bold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                                <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="py-2">
                                    <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FFC300] hover:text-black transition-colors">Profile</a>
                                    <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FFC300] hover:text-black transition-colors">My Orders</a>
                                    <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FFC300] hover:text-black transition-colors">Wishlist</a>
                                    <hr class="my-1 border-gray-200">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-[#FFC300] hover:text-black transition-colors">Sign Out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-300 hover:text-[#FFC300] transition-colors text-sm font-medium">Sign In</a>
                        <a href="{{ route('register') }}" class="bg-[#FFC300] text-black px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#FFD633] transition-colors">Join Free</a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center space-x-3">
                    <a href="{{ route('cart.index') }}" class="text-gray-300 relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        @if($cartCount > 0)
                        <span class="absolute -top-2 -right-2 bg-[#FFC300] text-black text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $cartCount }}</span>
                        @endif
                    </a>
                    <button @click="mobileOpen = !mobileOpen" class="text-gray-300 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path :class="{ 'hidden': mobileOpen, 'block': !mobileOpen }" class="block" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path :class="{ 'block': mobileOpen, 'hidden': !mobileOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileOpen" class="md:hidden bg-black border-t border-gray-800" x-cloak>
            <div class="px-4 py-4 space-y-3">
                <a href="{{ route('products.index') }}" class="block text-gray-300 hover:text-[#FFC300] py-2 text-sm">Browse All Items</a>
                @foreach(\App\Models\Category::orderBy('order')->get() as $cat)
                <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="block text-gray-300 hover:text-[#FFC300] py-2 text-sm">{{ $cat->name }}</a>
                @endforeach
                @auth
                    <hr class="border-gray-800">
                    <div class="flex items-center space-x-3 py-2">
                        <div class="w-8 h-8 bg-[#FFC300] rounded-full flex items-center justify-center">
                            <span class="text-black font-bold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">{{ auth()->user()->name }}</p>
                            <p class="text-gray-400 text-xs">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <a href="{{ route('profile.index') }}" class="block text-gray-300 hover:text-[#FFC300] py-2 text-sm">Profile</a>
                    <a href="{{ route('orders.index') }}" class="block text-gray-300 hover:text-[#FFC300] py-2 text-sm">My Orders</a>
                    <a href="{{ route('wishlist.index') }}" class="block text-gray-300 hover:text-[#FFC300] py-2 text-sm">Wishlist</a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block text-[#FFC300] hover:text-white py-2 text-sm">Admin Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left text-gray-300 hover:text-red-400 py-2 text-sm">Sign Out</button>
                    </form>
                @else
                    <hr class="border-gray-800">
                    <a href="{{ route('login') }}" class="block text-gray-300 hover:text-[#FFC300] py-2 text-sm">Sign In</a>
                    <a href="{{ route('register') }}" class="block bg-[#FFC300] text-black text-center py-2 rounded-lg text-sm font-semibold mt-2">Join Free</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success') || session('error') || session('info'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            {{ session('success') }}
            <button @click="show = false" class="float-right"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg></button>
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            {{ session('error') }}
            <button @click="show = false" class="float-right"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg></button>
        </div>
        @endif
        @if(session('info'))
        <div class="bg-blue-100 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg text-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            {{ session('info') }}
            <button @click="show = false" class="float-right"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg></button>
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 bg-[#FFC300] rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold">Creative<span class="text-[#FFC300]">Market</span></span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed max-w-md">The leading marketplace for premium digital resources. Discover thousands of creative assets crafted by world-class authors.</p>
                    <div class="flex space-x-4 mt-6">
                        <a href="#" class="text-gray-400 hover:text-[#FFC300] transition-colors"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg></a>
                        <a href="#" class="text-gray-400 hover:text-[#FFC300] transition-colors"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg></a>
                        <a href="#" class="text-gray-400 hover:text-[#FFC300] transition-colors"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg></a>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">Explore</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('products.index') }}" class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">All Items</a></li>
                        @foreach(\App\Models\Category::orderBy('order')->take(4)->get() as $cat)
                        <li><a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">{{ $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">Company</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-[#FFC300] text-sm transition-colors">Support</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-500 text-sm">
                <p>&copy; {{ date('Y') }} CreativeMarket. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>

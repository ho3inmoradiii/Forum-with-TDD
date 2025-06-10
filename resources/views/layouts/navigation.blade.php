<nav x-data="{ open: false }" class="bg-white shadow-lg border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
{{--                <!-- Logo -->--}}
{{--                <div class="shrink-0 flex items-center">--}}
{{--                    <a href="{{ route('dashboard') }}">--}}
{{--                        <x-application-logo class="block h-10 w-auto fill-current text-gray-800" />--}}
{{--                    </a>--}}
{{--                </div>--}}

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:flex items-center">
                    <!-- Thread Dropdown -->
                    <div class="relative">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center text-sm font-semibold text-gray-700 hover:text-blue-600 focus:outline-none focus:text-blue-600 transition duration-300 ease-in-out">
                                    <div>Threads</div>
                                    <div class="ml-2">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('threads.index')" :active="request()->routeIs('threads.index') && !request()->has('by')" class="hover:bg-gray-100 hover:text-gray-900">
                                    All Threads
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('threads.index', ['popular' => 'true'])" :active="request()->routeIs('threads.index') && request()->has('popular') && request()->input('popular') === 'true'" class="hover:bg-gray-100 hover:text-gray-900">
                                    Popular All Time
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('threads.index', ['popular' => 'false'])" :active="request()->routeIs('threads.index') && request()->has('popular') && request()->input('popular') === 'false'" class="hover:bg-gray-100 hover:text-gray-900">
                                    Least Popular
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('threads.index', ['unanswered' => 'true'])" :active="request()->routeIs('threads.index') && request()->has('unanswered') && request()->input('unanswered') === 'true'" class="hover:bg-gray-100 hover:text-gray-900">
                                    Unanswered Threads
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('threads.index', ['unanswered' => 'false'])" :active="request()->routeIs('threads.index') && request()->has('unanswered') && request()->input('unanswered') === 'false'" class="hover:bg-gray-100 hover:text-gray-900">
                                    Answered Threads
                                </x-dropdown-link>
                                @auth
                                    <x-dropdown-link :href="route('threads.index', ['by' => auth()->user()->name])" :active="request()->routeIs('threads.index') && request()->get('by') === auth()->user()->name" class="hover:bg-gray-100 hover:text-gray-900">
                                        My Threads
                                    </x-dropdown-link>
                                @endauth
                            </x-slot>
                        </x-dropdown>
                    </div>
                    <x-nav-link :href="route('threads.create')" :active="request()->routeIs('threads.create')" class="text-gray-700 hover:text-blue-600 font-semibold transition duration-300 ease-in-out">
                        New Thread
                    </x-nav-link>
                    <div x-data="{ open: false }" class="relative inline-block text-left">
                        <div>
                            <button @click="open = !open" type="button" class="inline-flex items-center px-4 py-2 bg-white text-sm font-semibold text-gray-700 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-lg transition duration-300 ease-in-out" id="channels-menu" aria-expanded="false" aria-haspopup="true">
                                Channels
                                <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <div x-show="open"
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-gray-200 ring-opacity-5 focus:outline-none max-h-60 overflow-y-auto"
                             role="menu"
                             aria-orientation="vertical"
                             aria-labelledby="channels-menu">
                            <div class="py-1" role="none">
                                @foreach($channels as $channel)
                                    <a href="{{ route('threads.index', ['channel' => $channel->name]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition duration-200 ease-in-out" role="menuitem">{{ $channel->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 gap-3">
                @auth
                    <!-- Notifications Dropdown -->
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="flex items-center text-sm font-semibold text-gray-700 hover:text-blue-600 focus:outline-none focus:text-blue-600 transition duration-300 ease-in-out">
                                <div class="flex gap-1 flex-row items-center">
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-blue-700/10 ring-inset">{{ auth()->user()->unreadNotifications->count() }}</span>
                                    <i class="fa fa-bell"></i>
                                </div>
                                <div class="ml-2">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="w-80">
                                @foreach(auth()->user()->unreadNotifications as $notification)
                                    <x-dropdown-link :href="route('notifications.read', [$notification->id])" class="hover:bg-gray-100 hover:text-gray-900 overflow-hidden text-ellipsis whitespace-nowrap max-w-72">
                                        {{ $notification->data['message'] }}
                                    </x-dropdown-link>
                                @endforeach
                            </div>
                        </x-slot>
                    </x-dropdown>
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center text-sm font-semibold text-gray-700 hover:text-blue-600 focus:outline-none focus:text-blue-600 transition duration-300 ease-in-out">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ml-2">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link href="{{ route('profile.show', auth()->user()->name) }}">
                                My Profile
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                                 onclick="event.preventDefault(); this.closest('form').submit();"
                                                 class="hover:bg-gray-100 hover:text-gray-900">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <x-nav-link :href="route('login')" class="text-gray-700 hover:text-blue-600 font-semibold transition duration-300 ease-in-out">
                        {{ __('Log In') }}
                    </x-nav-link>
                    <x-nav-link :href="route('register')" class="text-gray-700 hover:text-blue-600 font-semibold transition duration-300 ease-in-out">
                        {{ __('Register') }}
                    </x-nav-link>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-800 transition duration-300 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden bg-gray-50 border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-semibold text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-gray-600">{{ Auth::user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                               onclick="event.preventDefault(); this.closest('form').submit();"
                                               class="text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        {{ __('Log In') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        {{ __('Register') }}
                    </x-responsive-nav-link>
                </div>
            </div>
        @endauth
    </div>
</nav>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800" x-data="{ desktopSidebarOpen: true }">
        <flux:sidebar
            sticky
            collapsible="mobile"
            class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900"
            x-bind:class="{ 'lg:hidden': !desktopSidebarOpen }"
        >
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Catalog')" class="grid">
                    <flux:sidebar.item icon="cube" :href="route('admin.products.index')" :current="request()->routeIs('admin.products.*')" wire:navigate>
                        {{ __('Products') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="folder" :href="route('admin.categories.index')" :current="request()->routeIs('admin.categories.*')" wire:navigate>
                        {{ __('Categories') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Sales')" class="grid">
                    <flux:sidebar.item icon="shopping-bag" :href="route('admin.orders.index')" :current="request()->routeIs('admin.orders.*')" wire:navigate>
                        {{ __('Orders') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="ticket" :href="route('admin.coupons.index')" :current="request()->routeIs('admin.coupons.*')" wire:navigate>
                        {{ __('Coupons') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Engagement')" class="grid">
                    <flux:sidebar.item icon="star" :href="route('admin.reviews.index')" :current="request()->routeIs('admin.reviews.*')" wire:navigate>
                        {{ __('Reviews') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="envelope" :href="route('admin.messages.index')" :current="request()->routeIs('admin.messages.*')" wire:navigate>
                        {{ __('Messages') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Content')" class="grid">
                    <flux:sidebar.item icon="photo" :href="route('admin.gallery.index')" :current="request()->routeIs('admin.gallery.*')" wire:navigate>
                        {{ __('Gallery') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end" class="lg:hidden">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <button
            type="button"
            class="max-lg:hidden! lg:flex fixed top-4 left-4 z-30 size-9 items-center justify-center rounded-lg bg-white text-zinc-700 shadow-sm ring-1 ring-zinc-200 hover:bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700 cursor-pointer"
            @click="desktopSidebarOpen = !desktopSidebarOpen"
            x-bind:aria-expanded="desktopSidebarOpen"
            aria-label="{{ __('Toggle sidebar') }}"
        >
            <flux:icon.bars-2 class="size-5" />
        </button>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

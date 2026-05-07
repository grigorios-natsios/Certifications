<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                @php
                    $logoPng = file_exists(public_path('images/logo.png'));
                    $logoSvg = file_exists(public_path('images/logo.svg'));
                    $logoJpg = file_exists(public_path('images/logo.jpg'));
                    $hasLogo = $logoPng || $logoSvg || $logoJpg;
                @endphp
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
                    @if($logoPng)
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-auto">
                    @elseif($logoSvg)
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-10 w-auto">
                    @elseif($logoJpg)
                        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-10 w-auto">
                    @else
                        <span class="w-9 h-9 rounded-md bg-brand-600 text-white flex items-center justify-center">
                            <i class="fas fa-certificate"></i>
                        </span>
                        <span class="font-semibold text-slate-800 hidden sm:inline">{{ config('app.name', 'Certifications') }}</span>
                    @endif
                </a>

                <div class="hidden sm:flex sm:ms-10 space-x-1">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('clients.*')" wire:navigate>
                        {{ __('Πελάτες') }}
                    </x-nav-link>
                    <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" wire:navigate>
                        {{ __('Κατηγορίες') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-600 hover:text-brand-700 transition">
                            <span x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Προφίλ') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('users.index')" wire:navigate>
                            {{ __('Χρήστες') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('custom-fields.index')" wire:navigate>
                            {{ __('Προσαρμοσμένα Πεδία') }}
                        </x-dropdown-link>
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Αποσύνδεση') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-500 hover:text-brand-600 hover:bg-slate-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-slate-200">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Πελάτες') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" wire:navigate>
                {{ __('Κατηγορίες') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-slate-200">
            <div class="px-4">
                <div class="font-semibold text-base text-slate-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"></div>
                <div class="font-medium text-sm text-slate-500">{{ auth()->user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>{{ __('Προφίλ') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" wire:navigate>
                    {{ __('Χρήστες') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('custom-fields.index')" :active="request()->routeIs('custom-fields.*')" wire:navigate>
                    {{ __('Προσαρμοσμένα Πεδία') }}
                </x-responsive-nav-link>
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>{{ __('Αποσύνδεση') }}</x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>

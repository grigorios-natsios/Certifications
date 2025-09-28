<x-guest-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" required autofocus />
        <x-input-error :messages="$errors->get('email')" />

        <x-input-label for="password" :value="__('Password')" />
        <x-text-input id="password" name="password" type="password" required />
        <x-input-error :messages="$errors->get('password')" />

        <x-primary-button>{{ __('Login') }}</x-primary-button>
    </form>
</x-guest-layout>

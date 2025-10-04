<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 space-y-6">
        <!-- CSV Import Form -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h4 class="text-lg font-semibold mb-4">Import Clients</h4>

                @if(session()->has('message'))
                    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                        {{ session('message') }}
                    </div>
                @endif

                <form action="{{ route('clients.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 mb-1">Upload CSV</label>
                        <input type="file" name="file" accept=".csv" required class="border rounded p-2 w-full">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Import Clients
                    </button>
                </form>
            </div>
        </div>

        <!-- Users Card -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2">Organization: {{ $organization->name }}</h3>

                <h4 class="font-medium mb-2">Users</h4>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($organization->users as $user)
                        <li class="text-gray-700 dark:text-gray-200">
                            {{ $user->name }} ({{ $user->email }})
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Livewire Users Form -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h4 class="text-lg font-semibold mb-4">Add New User</h4>
                <livewire:users-form />
            </div>
        </div>
    </div>
</x-app-layout>

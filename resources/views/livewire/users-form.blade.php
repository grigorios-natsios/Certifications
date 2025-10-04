<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
    @if(session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4 border border-green-200">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="createUser" class="space-y-4">
        <!-- Name -->
        <div>
            <label for="name" class="block text-gray-700 font-medium mb-1">Name</label>
            <input type="text" wire:model="name" id="name" class="border rounded p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none" placeholder="John Doe" />
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
            <input type="email" wire:model="email" id="email" class="border rounded p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none" placeholder="example@mail.com" />
            @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-gray-700 font-medium mb-1">Password</label>
            <input type="password" wire:model="password" id="password" class="border rounded p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none" placeholder="********" />
            @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-gray-700 font-medium mb-1">Confirm Password</label>
            <input type="password" wire:model="password_confirmation" id="password_confirmation" class="border rounded p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none" placeholder="********" />
        </div>

        <!-- Organization (hidden) -->
        <input type="hidden" wire:model="organization_id">

        <!-- Submit Button -->
        <div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition-colors duration-200">
                Create User
            </button>
        </div>
    </form>
</div>

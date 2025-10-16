<div class="p-4">

    {{-- Flash message --}}
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    {{-- Add button --}}
    <div class="mb-4">
        <button wire:click="openModal" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Προσθήκη Κατηγορίας
        </button>
    </div>

    {{-- Table of categories --}}
    <table class="w-full border rounded">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">ID</th>
                <th class="p-2 text-left">Name</th>
                <th class="p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
                <tr class="border-b">
                    <td class="p-2">{{ $category->id }}</td>
                    <td class="p-2">{{ $category->name }}</td>
                    <td class="p-2 space-x-2">
                        <button wire:click="edit({{ $category->id }})" class="bg-yellow-500 text-white px-2 py-1 rounded">Edit</button>
                        <button wire:click="delete({{ $category->id }})" 
                                onclick="confirm('Σίγουρα θέλεις να διαγράψεις αυτήν την κατηγορία;') || event.stopImmediatePropagation()" 
                                class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                <h3 class="text-lg font-semibold mb-4">{{ $category_id ? 'Edit Category' : 'Add Category' }}</h3>

                <input type="text" wire:model="name" placeholder="Category Name" class="border p-2 w-full mb-4">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <div class="flex justify-end space-x-2">
                    <button wire:click="closeModal" class="px-4 py-2 border rounded">Cancel</button>
                    @if($category_id)
                        <button wire:click="update" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update</button>
                    @else
                        <button wire:click="store" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Save</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>

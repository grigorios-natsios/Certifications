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
    <table class="w-full border rounded text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">ID</th>
                <th class="p-2 text-left">Όνομα</th>
                <th class="p-2 text-center">Προεπισκόπηση SVG</th>
                <th class="p-2 text-center">Ενέργειες</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2">{{ $category->id }}</td>
                    <td class="p-2">{{ $category->name }}</td>

                    {{-- SVG Preview --}}
                    <td class="p-2 text-center">
                        @if($category->svg_path)
                            <a href="{{ Storage::url($category->svg_path) }}" target="_blank" class="text-blue-600 underline">
                                Προβολή
                            </a>
                        @else
                            <span class="text-gray-400 italic">—</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="p-2 text-center space-x-2">
                        <button wire:click="edit({{ $category->id }})"
                                class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600">
                            Επεξεργασία
                        </button>

                        <button wire:click="delete({{ $category->id }})"
                                onclick="confirm('Σίγουρα θέλεις να διαγράψεις αυτήν την κατηγορία;') || event.stopImmediatePropagation()"
                                class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                            Διαγραφή
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Modal --}}
    @if($showModal)
        <div wire:key="certificate-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                <h3 class="text-lg font-semibold mb-4">
                    {{ $category_id ? 'Επεξεργασία Κατηγορίας' : 'Προσθήκη Κατηγορίας' }}
                </h3>

                {{-- Όνομα --}}
                <input type="text" wire:model="name" placeholder="Όνομα Κατηγορίας"
                       class="border p-2 w-full mb-4">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                {{-- SVG Upload --}}
                <input type="file" wire:model="svg" accept=".svg"
                       class="border p-2 w-full mb-4">
                @error('svg') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                {{-- Preview if editing --}}
                @if($category_id && $categories->find($category_id)?->svg_path)
                    <div class="mb-3 text-sm">
                        <p class="text-gray-600 mb-1">Τρέχον SVG:</p>
                        <a href="{{ Storage::url($categories->find($category_id)->svg_path) }}" target="_blank"
                           class="text-blue-600 underline">Προβολή Αρχείου</a>
                    </div>
                @endif

                {{-- Buttons --}}
                <div class="flex justify-end space-x-2">
                    <button wire:click="closeModal" class="px-4 py-2 border rounded">Άκυρο</button>
                    @if($category_id)
                        <button wire:click="update" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Ενημέρωση
                        </button>
                    @else
                        <button wire:click="store" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Αποθήκευση
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>

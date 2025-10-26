<div class="p-4">

    {{-- Flash message --}}
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    {{-- Add button --}}
    <div class="mb-4">
        <button wire:click="openModal" class="bg-blue-500 text-white text-sm px-3 py-1.5 shadow hover:bg-blue-700 transition">
            {{ __('+ Προσθήκη Κατηγορίας') }}
        </button>
    </div>

    {{-- Table of categories --}}
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border border-gray-200 rounded-lg shadow-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 text-left">{{ __('ID') }}</th>
                    <th class="p-2 text-left">{{ __('Όνομα') }}</th>
                    <th class="p-2 text-center">{{ __('Ενέργειες') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2">{{ $category->id }}</td>
                        <td class="p-2">{{ $category->name }}</td>

                        {{-- Actions --}}
                        <td class="p-2 text-center space-x-2 flex justify-center">
                            <!-- Edit Icon -->
                            <button wire:click="edit({{ $category->id }})" class="w-8 h-8 flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white rounded-full transition">
                                    <i class="fas fa-edit"></i>
                            </button>

                            <!-- Delete Icon -->
                            <button wire:click="delete({{ $category->id }})" onclick="confirmDelete(message=>'Σίγουρα θέλεις να διαγράψεις αυτήν την κατηγορία;') || event.stopImmediatePropagation()"
                                    class="w-8 h-8 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white rounded-full transition">
                                    <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center p-4 text-gray-500 italic">
                            {{ __('Δεν υπάρχουν δεδομένα') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div wire:key="certificate-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg w-500 p-6">
                <h3 class="text-lg font-semibold mb-4">
                    {{ $category_id ? 'Επεξεργασία Κατηγορίας' : 'Προσθήκη Κατηγορίας' }}
                </h3>

                {{-- Όνομα --}}
                <input type="text" wire:model="name" placeholder="Όνομα Κατηγορίας"
                       class="border p-2 w-full mb-4">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            
                <label for="html_template" class="block mb-1 font-medium">{{ __('HTML Template') }}</label>
                <textarea id="html_template" wire:model="html_template" rows="10"
                        class="border p-2 w-full mb-4"></textarea>
                @error('html_template') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <h4 class="font-semibold mb-2">{{ __('Προεπισκόπηση') }}:</h4>
                <div class="border p-2 w-full bg-gray-50 flex justify-center items-center" 
                    style="height:250px; overflow:auto;">
                    
                    <div x-data
                        x-html="$wire.html_template"
                        x-ref="preview"
                        x-effect="
                            const preview = $refs.preview;
                            const child = preview.firstElementChild || preview;
                            const scaleX = preview.clientWidth / child.scrollWidth;
                            const scaleY = preview.clientHeight / child.scrollHeight;
                            const scale = Math.min(scaleX, scaleY, 1);
                            child.style.transform = 'scale(' + scale + ')';
                            child.style.transformOrigin = 'top left';
                        "
                        style="display:inline-block;"
                    ></div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end space-x-2">
                    <button wire:click="closeModal" class="px-4 py-2 border rounded">{{ __('Άκυρο') }}</button>
                    @if($category_id)
                        <button wire:click="update" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            {{ __('Ενημέρωση') }}
                        </button>
                    @else
                        <button wire:click="store" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            {{ __('Αποθήκευση') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

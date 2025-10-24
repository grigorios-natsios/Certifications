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
                    <th class="p-2 text-center">Ενέργειες</th>
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
                            <button wire:click="edit({{ $category->id }})"
                                    class="text-yellow-500 hover:text-yellow-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5m-5-5l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </button>

                            <!-- Delete Icon -->
                            <button wire:click="delete({{ $category->id }})"
                                    onclick="confirm('Σίγουρα θέλεις να διαγράψεις αυτήν την κατηγορία;') || event.stopImmediatePropagation()"
                                    class="text-red-500 hover:text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center p-4 text-gray-500 italic">
                            Δεν υπάρχουν δεδομένα
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>


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

                {{-- SVG Upload --}}
                <label for="html_template" class="block mb-1 font-medium">HTML Template</label>
                <textarea id="html_template" wire:model="html_template" rows="10"
                        class="border p-2 w-full mb-4"></textarea>
                @error('html_template') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <h4 class="font-semibold mb-2">Προεπισκόπηση:</h4>
                <div class="border p-2 w-full bg-gray-50 flex justify-center items-center" 
                    style="height:250px; overflow:auto;">
                    <div x-data
                        x-ref="preview"
                        x-init="
                            const preview = $refs.preview;
                            const child = preview.firstElementChild;
                            const scaleX = preview.clientWidth / child.scrollWidth;
                            const scaleY = preview.clientHeight / child.scrollHeight;
                            const scale = Math.min(scaleX, scaleY, 1);
                            child.style.transform = 'scale(' + scale + ')';
                            child.style.transformOrigin = 'top left';
                        "
                        style="display:inline-block;"
                    >
                        {!! $html_template !!}
                    </div>
                </div>

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

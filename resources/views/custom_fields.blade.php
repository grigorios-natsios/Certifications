<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ __('Προσαρμοσμένα πεδία') }}</h2>
    </x-slot>

    <div class="py-12 space-y-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <button id="addFieldBtn" class="bg-blue-500 text-white text-sm px-3 py-1.5 rounded shadow hover:bg-blue-700">
                        {{ __('+ Προσθήκη Πεδίου') }}
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table id="fieldsTable" class="min-w-full border border-gray-200 rounded-lg shadow-sm">
                        <thead class="bg-blue-50 text-gray-700">
                            <tr class="uppercase text-sm">
                                <th class="px-4 py-2">{{ __('ID') }}</th>
                                <th class="px-4 py-2">{{ __('Όνομα') }}</th>
                                <th class="px-4 py-2">{{ __('Τύπος') }}</th>
                                <th class="px-4 py-2">{{ __('Απαραίτητο') }}</th>
                                <th class="px-4 py-2 text-center">{{ __('Ενέργειες') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal --}}
        <div id="fieldModal" class="hidden fixed inset-0 bg-gray-800/60 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-96 p-6">
                <h3 class="text-lg font-semibold mb-4" id="modalTitle">{{ __('Προσθήκη Πεδίου') }}</h3>
                <form id="fieldForm">
                    @csrf
                    <input type="hidden" id="field_id">
                    <div class="mb-3">
                        <label class="block text-sm text-gray-700">{{ __('Όνομα') }}</label>
                        <input type="text" id="field_name" class="border rounded w-full p-2" required>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm text-gray-700">{{ __('Τύπος') }}</label>
                        <select id="field_type" class="border rounded w-full p-2" required>
                            <option value="text">{{ __('Κείμενο') }}</option>
                            <option value="number">{{ __('Αριθμός') }}</option>
                            <option value="date">{{ __('Ημερομηνία') }}</option>
                            <option value="checkbox">{{ __('Checkbox') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="field_required" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">{{ __('Απαραίτητο') }}</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" id="cancelBtn" class="border border-gray-400 text-sm px-3 py-1 rounded">{{ __('Άκυρο') }}</button>
                        <button type="submit" class="bg-blue-500 text-white text-sm px-3 py-1 rounded">{{ __('Αποθήκευση') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#fieldsTable').DataTable({
                 ajax: '{{ route('custom-fields.data') }}',
                    columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'type' },
                    { 
                        data: 'is_required', 
                        render: function(d) {
                            return d ? 'Ναι' : 'Όχι';
                        } 
                    },
                    { 
                        data: 'actions', 
                        orderable: false, 
                        searchable: false 
                    }
                ],
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/el.json'
                }
            });


            $('#addFieldBtn').on('click', function() {
                $('#field_id').val('');
                $('#field_name').val('');
                $('#field_type').val('text');
                $('#field_required').prop('checked', false);
                $('#modalTitle').text('Προσθήκη Πεδίου');
                $('#fieldModal').removeClass('hidden');
            });

            $('#cancelBtn').on('click', function() {
                $('#fieldModal').addClass('hidden');
            });

            // Edit
            $(document).on('click', '.editField', function() {
                $('#field_id').val($(this).data('id'));
                $('#field_name').val($(this).data('name'));
                $('#field_type').val($(this).data('type'));
                $('#field_required').prop('checked', $(this).data('required') == 1);
                $('#modalTitle').text('Επεξεργασία Πεδίου');
                $('#fieldModal').removeClass('hidden');
            });

            // Save
            $('#fieldForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#field_id').val();
                const method = id ? 'PUT' : 'POST';
                const url = id ? `/custom-fields/${id}` : `/custom-fields`;

                $.ajax({
                    url,
                    method,
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: method,
                        name: $('#field_name').val(),
                        type: $('#field_type').val(),
                        is_required: $('#field_required').is(':checked') ? 1 : 0
                    },
                    success: () => {
                        $('#fieldModal').addClass('hidden');
                        table.ajax.reload();
                    }
                });
            });

            // Delete
            $(document).on('click', '.deleteField', function() {
                const id = $(this).data('id');
                confirmDelete({
                    message: "Σίγουρα θέλεις να διαγράψεις αυτό το πεδίο;",
                    confirmText: "Διαγραφή",
                    cancelText: "Άκυρο",
                    onConfirm: () => {
                        $.ajax({
                            url: '/custom-fields/' + id,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(res) {
                                showToast(res.message, 'success');
                                table.ajax.reload();
                            },
                            error: function(err) {
                                showToast("Κάτι πήγε στραβά!", 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>

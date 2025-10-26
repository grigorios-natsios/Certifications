<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Διαχείρηση Πελατών') }}
        </h2>
    </x-slot>

    <div class="py-12 space-y-6">
        <!-- Οργανισμός του χρήστη -->
        {{-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2">Εταιρία: {{ $organization->name }}</h3>
            </div>
        </div> --}}

        <!-- CSV Import Form -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h4 class="text-lg font-semibold mb-4">{{ __('Εισαγωγή Πελατών') }}</h4>

                @if(session()->has('message'))
                    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                        {{ session('message') }}
                    </div>
                @endif

                <form action="{{ route('clients.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 mb-1">{{ __('Upload CSV') }}</label>
                        <input type="file" name="file" accept=".csv" required class="border rounded p-2 w-full">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white text-sm px-3 py-1.5 shadow hover:bg-blue-700 transition">
                       {{ __('Εισαγωγή / Ενημέρωση Πελατών') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Προσθήκη & Φίλτρα -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="container mx-auto p-6">
                <div class="flex space-x-2 mb-4">
                    <button id="addClientBtn" 
                            class="bg-blue-500 text-white text-sm px-3 py-1.5 shadow hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-1"></i> {{ __('Προσθήκη Πελάτη') }}
                    </button>

                    <button id="generatePDFsBtn" 
                            class="bg-blue-500 text-white text-sm px-3 py-1.5 shadow hover:bg-blue-700 transition">
                        <i class="fas fa-file-pdf mr-1"></i> {{ __('Δημιουργία Πιστοποιητικών') }}
                    </button>
                </div>

                <!-- DataTable -->
                <div class="overflow-x-auto">
                    <table id="clientsTable" class="min-w-full table-auto border border-gray-200 rounded-lg shadow-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-gray-700 uppercase text-sm">
                                <th class="px-3 py-2">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                                </th>
                                <th class="px-3 py-2 w-12">{{ __('ID') }}</th>
                                <th class="px-3 py-2">{{ __('Όνομα') }}</th>
                                <th class="px-3 py-2">{{ __('Email') }}</th>
                                <th class="px-3 py-2">{{ __('Κατηγορία') }}</th>
                                <th class="px-3 py-2">{{ __('Ημερομηνία Δημιουργίας') }}</th>
                                <th class="px-3 py-2 w-36">{{ __('Ενέργειες') }}</th>
                            </tr>
                            <tr class="bg-blue-200 text-gray-600 text-sm">
                                <th></th>
                                <th><input type="text" id="filterId" class="form-control form-control-sm w-12 border-gray-300 rounded" placeholder="ID"></th>
                                <th><input type="text" id="filterName" class="form-control form-control-sm border-gray-300 rounded" placeholder="Όνομα"></th>
                                <th><input type="text" id="filterEmail" class="form-control form-control-sm border-gray-300 rounded" placeholder="Email"></th>
                                <th>
                                    <select id="filterCategory" class="form-control form-control-sm border-gray-300 rounded">
                                        <option value="">{{ __('Όλες') }}</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </th>
                                <th><input type="date" id="filterDate" class="form-control form-control-sm border-gray-300 rounded"></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-300">
                            {{-- Rows rendered by DataTables --}}
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- Modal Πελάτη -->
        <div id="clientModal" class="hidden fixed inset-0 bg-gray-800/60 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-96 p-6">
                <h3 class="text-lg font-semibold mb-4" id="modalTitle">{{ __('Προσθήκη Πελάτη') }}</h3>
                <form id="clientForm">
                    @csrf
                    <input type="hidden" id="client_id" name="client_id">

                    <x-input-label for="name" :value="__('Όνομα')" class="mb-1"/>
                    <x-text-input id="name" name="name" type="text" class="w-full mb-3" required/>
                    <p id="error_name" class="mt-1 text-red-500 text-sm"></p>

                    <x-input-label for="email" :value="__('Email')" class="mb-1"/>
                    <x-text-input id="email" name="email" type="email" class="w-full mb-3"/>
                    <p id="error_email" class="mt-1 text-red-500 text-sm"></p>

                    <x-input-label for="certificate_category_ids" :value="__('Κατηγορίες Πιστοποιητικού')" class="mb-1"/>
                    <select id="certificate_category_ids" name="certificate_category_ids[]" multiple class="border p-2 w-full rounded mb-3">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <p id="error_certificate_category_ids" class="mt-1 text-red-500 text-sm"></p>

                    <div class="flex justify-end mt-4 space-x-2">
                        <button type="button" id="closeModal" class="mr-2 px-2 py-1 text-sm border border-gray-400 rounded">
                            {{ __('Άκυρο') }}
                        </button>
                        <x-primary-button type="submit" class="px-2 py-1 text-sm">{{ __('Αποθήκευση') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function() {
                let table = $('#clientsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("clients.data") }}',
                        data: function(d) {
                            d.id = $('#filterId').val();
                            d.name = $('#filterName').val();
                            d.email = $('#filterEmail').val();
                            d.certificate_category_id = $('#filterCategory').val();
                            d.created_at = $('#filterDate').val();
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            render: function(data) {
                                return `<input type="checkbox" class="selectClient" value="${data}">`;
                            },
                            orderable: false,
                            searchable: false
                        },
                        { data: 'id',  width: '50px' },
                        { data: 'name' },
                        { data: 'email' },
                        { data: 'category' },
                        { data: 'created_at', render: function(d){ return d ? new Date(d).toLocaleDateString('el-GR') : ''; }},
                        { data: 'actions', orderable: false, searchable: false }
                    ],
                    language: { 
                        emptyTable: '{{ __("Δεν υπάρχουν δεδομένα") }}',
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/el.json',
                    },
                    responsive: true, 
                });

                // Φίλτρο
                $('#filterBtn').click(() => table.ajax.reload());

                // Άνοιγμα modal
                $('#addClientBtn').click(() => {
                    $('#clientForm')[0].reset();
                    $('#client_id').val('');
                    $('#modalTitle').text(@json(__('Προσθήκη Πελάτη')));
                    $('#clientModal').removeClass('hidden');
                });

                // Κλείσιμο modal
                $('#closeModal').click(() => $('#clientModal').addClass('hidden'));

                // Επεξεργασία
                $('#clientsTable').on('click', '.editClient', function() {
                    let data = table.row($(this).parents('tr')).data();
                    $('#client_id').val(data.id);
                    $('#name').val(data.name);
                    $('#email').val(data.email);
                    $('#certificate_category_ids').val(data.certificate_categories).trigger('change');;
                    $('#modalTitle').text(@json(__('Επεξεργασία Πελάτη')));
                    $('#clientModal').removeClass('hidden');
                });

                // Διαγραφή
                $('#clientsTable').on('click', '.deleteClient', function() {
                   
                    let id = $(this).data('id');
                    confirmDelete({
                        message: "{{ __('Σίγουρα θέλεις να διαγράψεις αυτόν τον πελάτη;') }}",
                        confirmText: "{{ __('Διαγραφή') }}",
                        cancelText: "{{ __('Άκυρο') }}",
                        onConfirm: () => {
                            $.ajax({
                                url: '/clients/' + id,
                                type: 'DELETE',
                                data: {_token:'{{ csrf_token() }}'},
                                success: function(res) {
                                    showToast(res.message, 'success');
                                    $('#clientsTable').DataTable().ajax.reload();
                                },
                                error: function(err) {
                                    showToast("{{ __('Κάτι πήγε στραβά!') }}", 'error');
                                }
                            });
                        }
                    });
                   
                    
                });

                $('#selectAll').on('change', function() {
                    $('.selectClient').prop('checked', $(this).prop('checked'));
                });

                // Αποθήκευση (Create/Update)
                $('#clientForm').submit(function(e){
                    e.preventDefault();
                    let id = $('#client_id').val();
                    let url = id ? '/clients/' + id : '{{ route("clients.store") }}';
                    let type = id ? 'PUT' : 'POST';
                    $.ajax({
                        url, type, data: $(this).serialize(),
                        success: function(res){
                            $('#clientModal').addClass('hidden');
                            table.ajax.reload();
                            $('#clientForm')[0].reset();
                            showToast(res.message, type = 'success');    
                        },
                        error: function(xhr){
                            if(xhr.status===422){
                                let errors = xhr.responseJSON.errors;
                                for(let f in errors){ $('#error_'+f).text(errors[f][0]); }
                            }
                        }
                    });
                });

                $('#generatePDFsBtn').click(function() {
                    let ids = [];
                    $('.selectClient:checked').each(function() {
                        ids.push($(this).val());
                    });

                    if (ids.length === 0) {
                        alert('Επέλεξε τουλάχιστον έναν πελάτη.');
                        return;
                    }

                    console.log(ids);

                    if (!confirm('Να δημιουργηθούν πιστοποιητικά PDF για τους επιλεγμένους πελάτες;')) {
                        return;
                    }

                    $.ajax({
                        url: '{{ route("clients.generate-pdfs") }}', // θα το φτιάξουμε παρακάτω
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            clients: ids
                        },
                        success: function(response) {
                            alert(response.message);
                        },
                        error: function(xhr) {
                            console.error(xhr);
                            alert('Κάτι πήγε στραβά.');
                        }
                    });
                });

                function getSelectedIds() {
                    let ids = [];
                    $('.selectClient:checked').each(function() {
                        ids.push($(this).val());
                    });
                    return ids;
                }

                document.querySelectorAll('.filter-input, .filter-select').forEach(input => {
                    input.addEventListener('input', filterTable);
                    input.addEventListener('change', filterTable);
                });

                $('#filterId, #filterName, #filterEmail, #filterCategory, #filterDate').on('blur change', function() {
                    table.draw(); // ξανατρέχει το Ajax και φιλτράρει
                });
            });
        </script>
    @endpush
</x-app-layout>

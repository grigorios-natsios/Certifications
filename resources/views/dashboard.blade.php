<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Διαχείρηση Πελατών') }}
        </h2>
    </x-slot>

    <div class="py-12 space-y-6">
        <!-- Οργανισμός του χρήστη -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2">Εταιρία: {{ $organization->name }}</h3>
            </div>
        </div>

        <!-- CSV Import Form -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h4 class="text-lg font-semibold mb-4">Εισαγωγή Πελατών</h4>

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

        <!-- Προσθήκη & Φίλτρα -->
        <div class="container mx-auto p-6">
            <div class="flex space-x-4 mb-4">
                <button id="addClientBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Προσθήκη Πελάτη
                </button>

                <select id="categoryFilter" class="border p-2 rounded w-1/4">
                    <option value="">{{ __('Όλες οι κατηγορίες') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <input type="text" id="searchName" placeholder="{{ __('Αναζήτηση με Όνομα') }}"
                    class="border p-2 rounded w-1/4" />

                <button id="filterBtn" class="bg-gray-700 text-white px-4 py-2 rounded">
                    {{ __('Φιλτράρισμα') }}
                </button>
            </div>

            <!-- DataTable -->
            <table id="clientsTable" class="min-w-full text-left text-sm border rounded">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">{{ __('ID') }}</th>
                        <th class="px-4 py-2">{{ __('Όνομα') }}</th>
                        <th class="px-4 py-2">{{ __('Email') }}</th>
                        <th class="px-4 py-2">{{ __('Κατηγορία') }}</th>
                        <th class="px-4 py-2">{{ __('Ημερομηνία Δημιουργίας') }}</th>
                        <th class="px-4 py-2">{{ __('Ενέργειες') }}</th>
                    </tr>
                </thead>
            </table>
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

                    <x-input-label for="certificate_category_id" :value="__('Κατηγορία Πιστοποιητικού')" class="mb-1"/>
                    <select id="certificate_category_id" name="certificate_category_id" class="border p-2 w-full rounded mb-3">
                        <option value="">{{ __('Καμία') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <p id="error_certificate_category_id" class="mt-1 text-red-500 text-sm"></p>

                    <div class="flex justify-end mt-4">
                        <button type="button" id="closeModal" class="mr-2 px-4 py-2 rounded border border-gray-400">
                            {{ __('Άκυρο') }}
                        </button>
                        <x-primary-button type="submit">{{ __('Αποθήκευση') }}</x-primary-button>
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
                            d.certificate_category_id = $('#categoryFilter').val();
                            d.searchName = $('#searchName').val();
                        }
                    },
                    columns: [
                        { data: 'id' },
                        { data: 'name' },
                        { data: 'email' },
                        { data: 'category' },
                        { data: 'created_at', render: function(d){ return d ? new Date(d).toLocaleDateString('el-GR') : ''; }},
                        { data: 'actions', orderable: false, searchable: false }
                    ],
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/el.json' }
                });

                // Φίλτρο
                $('#filterBtn').click(() => table.ajax.reload());

                // Άνοιγμα modal
                $('#addClientBtn').click(() => {
                    $('#clientForm')[0].reset();
                    $('#client_id').val('');
                    $('#modalTitle').text('Προσθήκη Πελάτη');
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
                    $('#certificate_category_id').val(data.certificate_category_id);
                    $('#modalTitle').text('Επεξεργασία Πελάτη');
                    $('#clientModal').removeClass('hidden');
                });

                // Διαγραφή
                $('#clientsTable').on('click', '.deleteClient', function() {
                    if(confirm('Σίγουρα θέλεις να διαγράψεις αυτόν τον πελάτη;')) {
                        let id = $(this).data('id');
                        $.ajax({
                            url: '/clients/' + id,
                            type: 'DELETE',
                            data: {_token:'{{ csrf_token() }}'},
                            success: () => table.ajax.reload()
                        });
                    }
                });

                // Αποθήκευση (Create/Update)
                $('#clientForm').submit(function(e){
                    e.preventDefault();
                    let id = $('#client_id').val();
                    let url = id ? '/clients/' + id : '{{ route("clients.store") }}';
                    let type = id ? 'PUT' : 'POST';
                    $.ajax({
                        url, type, data: $(this).serialize(),
                        success: function(){
                            $('#clientModal').addClass('hidden');
                            table.ajax.reload();
                            $('#clientForm')[0].reset();
                        },
                        error: function(xhr){
                            if(xhr.status===422){
                                let errors = xhr.responseJSON.errors;
                                for(let f in errors){ $('#error_'+f).text(errors[f][0]); }
                            }
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>

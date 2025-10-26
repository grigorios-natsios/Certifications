<x-app-layout>
    
     <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Χρήστες') }}
        </h2>
    </x-slot>
    <div class="py-12 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="container mx-auto p-6">
                    <div class="flex space-x-4 mb-4">
                        <button id="addUserBtn" class="bg-blue-500 text-white text-sm px-3 py-1.5 shadow hover:bg-blue-700 transition">
                            {{ __('+ Προσθήκη') }}
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table id="usersTable" class="bg-white min-w-full table-auto border border-gray-200 rounded-lg shadow-sm">
                            <thead class="bg-gray-50">
                                <tr class="text-gray-700 uppercase text-sm">
                                    <th class="px-4 py-2">{{ __('ID') }}</th>
                                    <th class="px-4 py-2">{{ __('Όνομα') }}</th>
                                    <th class="px-4 py-2">{{ __('Email') }}</th>
                                    <th class="px-4 py-2">{{ __('Ημερομηνία') }}</th>
                                    <th class="px-4 py-2">{{ __('Ενέργειες') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal --}}
        <div id="userModal" class="hidden fixed inset-0 bg-gray-800/60 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-96 p-6">
                <h3 class="text-lg font-semibold mb-4" id="modalTitle">{{ __('Προσθήκη Χρήστη') }}</h3>
                <form id="userForm">
                    @csrf
                    <input type="hidden" id="user_id" name="user_id">
                    <div class="mb-3">
                        <x-input-label for="name" :value="__('Όνομα')" />
                        <x-text-input id="name" name="name" type="text" required class="w-full" />
                        <p id="error_name" class="mt-1 text-red-500 text-sm"></p>
                    </div>
                    <div class="mb-3">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" required class="w-full" />
                        <p id="error_email" class="mt-1 text-red-500 text-sm"></p>
                    </div>
                    <div class="mb-3">
                        <x-input-label for="password" :value="__('Κωδικός')" />
                        <x-text-input id="password" name="password" type="password" class="w-full" />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="password_confirmation" :value="__('Επιβεβαίωση Κωδικού')" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="w-full" />
                        <p id="error_password" class="mt-1 text-red-500 text-sm"></p>
                    </div>
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
        $(document).ready(function() {
            let table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("users.data") }}',
                    data: function (d) {
                        d.role = $('#roleFilter').val();
                        d.searchEmail = $('#searchEmail').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { 
                        data: 'created_at', 
                        name: 'created_at',
                        render: function(data, type, row) {
                            if (data) {
                                const date = new Date(data);
                                const day = String(date.getDate()).padStart(2, '0');
                                const month = String(date.getMonth() + 1).padStart(2, '0'); // Μήνες 0-11
                                const year = date.getFullYear();
                                return `${day}-${month}-${year}`;
                            }
                            return '';
                        }
                    },
                    { data: 'actions', orderable: false, searchable: false, className: 'text-center'  }
                ],
                responsive: true, 
                language: { 
                    emptyTable: '{{ __("Δεν υπάρχουν δεδομένα") }}',
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/el.json',
                },
            });

            // Φίλτρα
            //$('#filterBtn').on('click', function() {
            //    table.ajax.reload();
            //});

            // Άνοιγμα modal
            $('#addUserBtn').on('click', function() {
                $('#user_id').val('');
                $('#userForm')[0].reset();
                $('#modalTitle').text('Προσθήκη Χρήστη');
                $('#userModal').removeClass('hidden');
            });

            // Κλείσιμο modal
            $('#closeModal').on('click', function() {
                $('#userModal').addClass('hidden');
            });

            // Επεξεργασία
            $('#usersTable').on('click', '.editUser', function() {
                let data = table.row($(this).parents('tr')).data();
                $('#user_id').val(data.id);
                $('#name').val(data.name);
                $('#email').val(data.email);
                $('#password').val('');
                $('#modalTitle').text('Επεξεργασία Χρήστη');
                $('#userModal').removeClass('hidden');
            });

            // Διαγραφή
            $('#usersTable').on('click', '.deleteUser', function() {       
                let id = $(this).data('id');
                confirmDelete({
                    message: "{{ __('Σίγουρα θέλεις να διαγράψεις αυτόν τον χρήστη;') }}",
                    confirmText: "{{ __('Διαγραφή') }}",
                    cancelText: "{{ __('Άκυρο') }}",
                    onConfirm: () => {
                        $.ajax({
                            url: '/users/' + id,
                            type: 'DELETE',
                            data: { _token: $('meta[name="csrf-token"]').attr('content') },
                            success: function(res) {
                                showToast(res.message, 'success');
                                $('#usersTable').DataTable().ajax.reload();
                            },
                            error: function(err) {
                                showToast("{{ __('Κάτι πήγε στραβά!') }}", 'error');
                            }
                        });
                    }
                });
            });
                       
            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                clearErrors();

                let id = $('#user_id').val();
                let url = id ? '/users/' + id : '{{ route("users.store") }}';
                let type = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: type,
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#userModal').addClass('hidden');
                        $('#userForm')[0].reset();
                        table.ajax.reload();

                        // Success modal
                        showToast(res.message, type = 'success');                  
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            if (errors.name) $('#error_name').text(errors.name[0]);
                            if (errors.email) $('#error_email').text(errors.email[0]);
                            if (errors.password) $('#error_password').text(errors.password[0]);
                            if (errors.password_confirmation) $('#error_password_confirmation').text(errors.password_confirmation[0]);
                        }
                    }
                });
            });
            
            function clearErrors() {
                $('#error_name').text('');
                $('#error_email').text('');
                $('#error_password').text('');
                $('#error_password_confirmation').text('');
            }
        });
    </script>
    @endpush

</x-app-layout>

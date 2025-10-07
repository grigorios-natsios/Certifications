<x-app-layout>
    
     <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Χρήστες') }}
        </h2>
    </x-slot>

    <div class="container mx-auto p-6">
       
        {{-- Φίλτρα --}}
        <div class="flex space-x-4 mb-4">
            <button id="addUserBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                {{ __('+ Προσθήκη') }}
            </button>
            <input type="text" id="searchEmail" placeholder="{{ __('Αναζήτηση με Email') }}"
                   class="border p-2 rounded w-1/3" />
            <select id="roleFilter" class="border p-2 rounded w-1/4">
                <option value="">{{ __('Όλοι οι ρόλοι') }}</option>
                <option value="admin">{{ __('Admin') }}</option>
                <option value="user">{{ __('User') }}</option>
            </select>
            <button id="filterBtn" class="bg-gray-700 text-white px-4 py-2 rounded">
                {{ __('Φιλτράρισμα') }}
            </button>
        </div>

        <table id="usersTable" class="min-w-full text-left text-sm border rounded">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2">{{ __('ID') }}</th>
                    <th class="px-4 py-2">{{ __('Όνομα') }}</th>
                    <th class="px-4 py-2">{{ __('Email') }}</th>
                    <th class="px-4 py-2">{{ __('Ημερομηνία') }}</th>
                    <th class="px-4 py-2">{{ __('Ενέργειες') }}</th>
                </tr>
            </thead>
        </table>
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
                <div class="flex justify-end mt-4">
                    <button type="button" id="closeModal"
                        class="mr-2 px-4 py-2 rounded border border-gray-400">
                        {{ __('Άκυρο') }}
                    </button>
                    <x-primary-button type="submit">{{ __('Αποθήκευση') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div
        x-data="{ show: false, message: '' }"
        x-show="show"
        x-transition
        x-cloak
        id="successModal"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
    >
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full text-center">
            <h2 class="text-lg font-bold mb-2">{{ __('Επιτυχία!') }}</h2>
            <p x-text="message"></p>
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
                    { data: 'actions', orderable: false, searchable: false }
                ],
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/el.json' }
            });

            // Φίλτρα
            $('#filterBtn').on('click', function() {
                table.ajax.reload();
            });

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
                if (confirm('Σίγουρα θέλεις να διαγράψεις αυτόν τον χρήστη;')) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: '/users/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            table.ajax.reload();
                        }
                    });
                }
            });
            document.addEventListener('alpine:init', () => {
                    Alpine.data('autoCloseModal', () => ({
                        show: false,
                        init() {
                            if (this.show) {
                                setTimeout(() => this.show = false, 3000);
                            }
                        }
                    }));
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
                        const modal = document.getElementById('successModal');
                        modal.querySelector('p').textContent = res.message;
                        modal.classList.remove('hidden');
                        setTimeout(() => modal.classList.add('hidden'), 3000);
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

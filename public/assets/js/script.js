function showToast(message, type, duration = 3000) {
    // Δημιουργούμε το container αν δεν υπάρχει
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-5 left-1/2 transform -translate-x-1/2 z-[9999] flex flex-col items-center space-y-2';
        document.body.appendChild(container);
    }

    // Δημιουργούμε το toast element
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.className = `text-white px-6 py-3 rounded-lg shadow-lg max-w-md text-center font-semibold opacity-0 transform -translate-y-5 transition-all duration-300`;

    // Προσθέτουμε χρώμα ανά τύπο
    if (type === 'success') toast.classList.add('bg-green-500');
    else if (type === 'error') toast.classList.add('bg-red-500');
    else if (type === 'info') toast.classList.add('bg-blue-500');

    container.appendChild(toast);

    // Trigger animation
    requestAnimationFrame(() => {
        toast.classList.remove('opacity-0', '-translate-y-5');
        toast.classList.add('opacity-100', 'translate-y-0');
    });

    // Αυτόματο κλείσιμο
    setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', '-translate-y-5');
        setTimeout(() => {
            toast.remove();
        }, 300); // πρέπει να περιμένει το animation
    }, duration);
}

function confirmDelete(options = {}) {
    const {
        message = "{{ __('Είσαι σίγουρος/η;') }}",
        confirmText = "{{ __('Διαγραφή') }}",
        cancelText = "{{ __('Άκυρο') }}",
        onConfirm = null
    } = options;

    const modalId = 'global-delete-modal';
    let modal = document.getElementById(modalId);

    if (modal) modal.remove(); // αν υπάρχει, διαγραφή

    // Overlay
    modal = document.createElement('div');
    modal.id = modalId;
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999]';

    // Modal box
    const box = document.createElement('div');
    box.className = 'bg-white rounded-lg shadow-lg w-80 p-6 text-center';

    const msgEl = document.createElement('p');
    msgEl.className = 'mb-4';
    msgEl.textContent = message;

    const btnContainer = document.createElement('div');
    btnContainer.className = 'flex justify-center space-x-4';

    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'px-4 py-2 rounded border border-gray-400 hover:bg-gray-100';
    cancelBtn.textContent = cancelText;
    cancelBtn.onclick = () => modal.remove();

    const confirmBtn = document.createElement('button');
    confirmBtn.className = 'px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600';
    confirmBtn.textContent = confirmText;
    confirmBtn.onclick = () => {
        modal.remove();
        if (typeof onConfirm === 'function') onConfirm();
    };

    btnContainer.appendChild(cancelBtn);
    btnContainer.appendChild(confirmBtn);

    box.appendChild(msgEl);
    box.appendChild(btnContainer);
    modal.appendChild(box);
    document.body.appendChild(modal);
}

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

async function api(url, method = 'GET', body = null) {
    const options = {
        method,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        credentials: 'same-origin',
    };

    if (body !== null) {
        options.body = JSON.stringify(body);
    }

    let response;

    try {
        response = await fetch(url, options);
    } catch (error) {
        throw { message: 'Tidak dapat terhubung ke server.', errors: {} };
    }

    let payload;

    try {
        payload = await response.json();
    } catch (error) {
        payload = {
            message: response.ok
                ? 'Respons server tidak valid.'
                : `Permintaan gagal (${response.status})`,
            errors: {},
        };
    }

    if (!response.ok) {
        throw { ...payload, status: response.status };
    }

    return payload;
}

function esc(value) {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function toast(message, type = 'success') {
    const warna = type === 'error'
        ? 'bg-red-600 text-white'
        : 'bg-emerald-600 text-white';

    const box = document.createElement('div');
    box.className = `fixed right-4 top-4 z-[9999] max-w-sm rounded-lg px-4 py-3 text-sm font-medium shadow-lg ${warna}`;
    box.setAttribute('role', type === 'error' ? 'alert' : 'status');
    box.textContent = message || (type === 'error' ? 'Terjadi kesalahan.' : 'Berhasil.');

    document.body.appendChild(box);
    window.setTimeout(() => box.remove(), 3000);
}

function clearErrors(formEl) {
    if (!formEl) {
        return;
    }

    formEl.querySelectorAll('[data-error-for]').forEach((el) => {
        el.textContent = '';
        el.classList.add('hidden');
    });

    formEl.querySelectorAll('[data-invalid]').forEach((el) => {
        el.classList.remove('border-red-500', 'ring-red-500');
        el.classList.add('border-gray-300');
        delete el.dataset.invalid;
    });
}

function showErrors(formEl, errors = {}) {
    if (!formEl) {
        return;
    }

    clearErrors(formEl);

    Object.entries(errors).forEach(([field, messages]) => {
        const errorEl = Array.from(formEl.querySelectorAll('[data-error-for]'))
            .find((el) => el.dataset.errorFor === field);

        if (!errorEl) {
            return;
        }

        errorEl.textContent = Array.isArray(messages) ? messages.join(' ') : String(messages);
        errorEl.classList.remove('hidden');

        const input = Array.from(formEl.querySelectorAll('[name]'))
            .find((el) => el.name === field);

        if (input) {
            input.classList.remove('border-gray-300');
            input.classList.add('border-red-500', 'ring-red-500');
            input.dataset.invalid = 'true';
        }
    });
}

function formatTanggal(value) {
    if (!value) {
        return '-';
    }

    const bagian = String(value).slice(0, 10).split('-').map(Number);

    if (bagian.length !== 3 || bagian.some((n) => Number.isNaN(n))) {
        return String(value);
    }

    const [tahun, bulan, tanggal] = bagian;

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(tahun, bulan - 1, tanggal));
}

function openModal(modal) {
    if (!modal) {
        return;
    }

    const form = modal.querySelector('form');

    if (form) {
        form.reset();
        clearErrors(form);
    }

    modal.classList.remove('hidden');

    const firstField = modal.querySelector('form input:not([type="hidden"]), form select, form textarea');

    if (firstField) {
        firstField.focus();
    }
}

function closeModal(modal) {
    if (modal) {
        modal.classList.add('hidden');
    }
}

function initModal(modalId, buttonId) {
    const modal = document.getElementById(modalId);
    const button = document.getElementById(buttonId);

    if (!modal || !button) {
        return;
    }

    button.addEventListener('click', () => openModal(modal));

    modal.querySelectorAll('[data-modal-close]').forEach((el) => {
        el.addEventListener('click', () => closeModal(modal));
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal(modal);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal(modal);
        }
    });
}

window.api = api;
window.esc = esc;
window.toast = toast;
window.showErrors = showErrors;
window.clearErrors = clearErrors;
window.formatTanggal = formatTanggal;
window.openModal = openModal;
window.closeModal = closeModal;
window.initModal = initModal;

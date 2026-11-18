<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mahasiswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Daftar Mahasiswa</h3>
                        <p class="mt-1 text-sm text-gray-500">Kelola data mahasiswa dan pencarian berdasarkan NIM atau nama.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input id="cari-mahasiswa" type="search" placeholder="Cari NIM atau nama" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <button id="btn-tambah-mahasiswa" type="button" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Tambah Mahasiswa
                        </button>
                    </div>
                </div>

                <div id="error" class="hidden mx-4 mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    <span id="error-message"></span>
                    <button id="retry" type="button" class="ml-2 font-semibold underline">Coba lagi</button>
                </div>
                <div id="loading" class="p-6 text-sm text-gray-500">Memuat...</div>

                <div id="konten" class="hidden overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">NIM</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jml Kelas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-mahasiswa" class="divide-y divide-gray-200 bg-white"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-mahasiswa" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 p-4">
        <div class="w-full max-w-lg rounded-lg bg-white shadow-xl">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 id="modal-mahasiswa-title" class="text-lg font-semibold text-gray-900">Tambah Mahasiswa</h3>
                <button type="button" data-modal-close class="text-2xl leading-none text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form id="form-mahasiswa" class="space-y-4 px-6 py-5" novalidate>
                <input id="mahasiswa-id" type="hidden" name="id">
                <div>
                    <label for="nim" class="block text-sm font-medium text-gray-700">NIM</label>
                    <input id="nim" name="nim" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <span data-error-for="nim" class="mt-1 hidden text-sm text-red-600"></span>
                </div>
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input id="nama" name="nama" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <span data-error-for="nama" class="mt-1 hidden text-sm text-red-600"></span>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" data-modal-close class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50">Batal</button>
                    <button id="submit-mahasiswa" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-indigo-500 disabled:opacity-50">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const URL_INDEX = "{{ route('api.mahasiswas.index') }}";
            const URL_STORE = "{{ route('api.mahasiswas.store') }}";
            const URL_UPDATE = "{{ route('api.mahasiswas.update', ':id') }}";
            const URL_DESTROY = "{{ route('api.mahasiswas.destroy', ':id') }}";

            const tbody = document.getElementById('tbody-mahasiswa');
            const search = document.getElementById('cari-mahasiswa');
            const modal = document.getElementById('modal-mahasiswa');
            const form = document.getElementById('form-mahasiswa');
            const submit = document.getElementById('submit-mahasiswa');
            const errorBox = document.getElementById('error');

            let daftarMahasiswa = [];
            let timerCari;

            initModal('modal-mahasiswa', 'btn-tambah-mahasiswa');

            document.getElementById('btn-tambah-mahasiswa').addEventListener('click', () => {
                document.getElementById('modal-mahasiswa-title').textContent = 'Tambah Mahasiswa';
                submit.textContent = 'Simpan';
            });

            document.getElementById('retry').addEventListener('click', muat);

            search.addEventListener('input', () => {
                window.clearTimeout(timerCari);
                timerCari = window.setTimeout(muat, 300);
            });

            function setMuat(aktif) {
                document.getElementById('loading').classList.toggle('hidden', !aktif);
                document.getElementById('konten').classList.toggle('hidden', aktif);
            }

            function render() {
                if (daftarMahasiswa.length === 0) {
                    const pesan = search.value.trim()
                        ? 'Tidak ada mahasiswa yang cocok dengan pencarian.'
                        : 'Belum ada mahasiswa';

                    tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">${esc(pesan)}</td></tr>`;
                    return;
                }

                tbody.innerHTML = daftarMahasiswa.map((m) => `
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${esc(m.nim)}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${esc(m.nama)}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${Number(m.kelas_count ?? 0)}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <button type="button" data-ubah="${m.id}" class="text-xs font-medium text-amber-600 hover:text-amber-900">Ubah</button>
                                <button type="button" data-hapus="${m.id}" class="text-xs font-medium text-red-600 hover:text-red-900">Hapus</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }

            async function muat() {
                errorBox.classList.add('hidden');
                setMuat(true);

                const kata = search.value.trim();
                const url = kata ? `${URL_INDEX}?search=${encodeURIComponent(kata)}` : URL_INDEX;

                try {
                    const response = await api(url);
                    daftarMahasiswa = response.data ?? [];
                    render();
                    setMuat(false);
                } catch (e) {
                    document.getElementById('error-message').textContent = e.message || 'Gagal memuat mahasiswa.';
                    errorBox.classList.remove('hidden');
                    setMuat(false);
                }
            }

            function bukaUbah(id) {
                const mahasiswa = daftarMahasiswa.find((item) => item.id === id);

                if (!mahasiswa) {
                    return;
                }

                openModal(modal);
                document.getElementById('modal-mahasiswa-title').textContent = 'Ubah Mahasiswa';
                submit.textContent = 'Simpan Perubahan';
                document.getElementById('mahasiswa-id').value = mahasiswa.id;
                form.elements.namedItem('nim').value = mahasiswa.nim ?? '';
                form.elements.namedItem('nama').value = mahasiswa.nama ?? '';
            }

            async function hapusMahasiswa(id) {
                if (!confirm('Yakin hapus mahasiswa ini?')) {
                    return;
                }

                try {
                    const response = await api(URL_DESTROY.replace(':id', id), 'DELETE');
                    toast(response.message);
                    await muat();
                } catch (e) {
                    toast(e.message || 'Gagal menghapus mahasiswa.', 'error');
                }
            }

            tbody.addEventListener('click', (event) => {
                const ubah = event.target.closest('[data-ubah]');
                if (ubah) {
                    bukaUbah(Number(ubah.dataset.ubah));
                    return;
                }

                const hapus = event.target.closest('[data-hapus]');
                if (hapus) {
                    hapusMahasiswa(Number(hapus.dataset.hapus));
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const id = document.getElementById('mahasiswa-id').value;
                const body = {
                    nim: form.elements.namedItem('nim').value.trim(),
                    nama: form.elements.namedItem('nama').value.trim(),
                };

                submit.disabled = true;

                try {
                    const response = await api(
                        id ? URL_UPDATE.replace(':id', id) : URL_STORE,
                        id ? 'PUT' : 'POST',
                        body
                    );

                    closeModal(modal);
                    toast(response.message);
                    await muat();
                } catch (e) {
                    if (e.errors) {
                        showErrors(form, e.errors);
                    }
                    toast(e.message || 'Gagal menyimpan mahasiswa.', 'error');
                } finally {
                    submit.disabled = false;
                }
            });

            muat();
        </script>
    @endpush
</x-app-layout>

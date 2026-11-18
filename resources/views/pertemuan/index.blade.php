<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pertemuan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600">{{ $kelas->nama_mata_kuliah }} &middot; {{ $kelas->nama_kelas }}</p>
                        <p class="mt-1 text-sm text-gray-500">Dosen: {{ Auth::user()->name }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="{{ route('page.rekap', $kelas->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">Lihat Rekap</a>
                        <a href="{{ route('page.kelas') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">&larr; Kembali ke Kelas</a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Daftar Pertemuan</h3>
                    <button id="btn-tambah-pertemuan" type="button" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Tambah Pertemuan
                    </button>
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
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Pertemuan ke-</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-pertemuan" class="divide-y divide-gray-200 bg-white"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-pertemuan" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 p-4">
        <div class="w-full max-w-lg rounded-lg bg-white shadow-xl">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 id="modal-pertemuan-title" class="text-lg font-semibold text-gray-900">Tambah Pertemuan</h3>
                <button type="button" data-modal-close class="text-2xl leading-none text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form id="form-pertemuan" class="space-y-4 px-6 py-5" novalidate>
                <input id="pertemuan-id" type="hidden" name="id">
                <div>
                    <label for="pertemuan_ke" class="block text-sm font-medium text-gray-700">Pertemuan ke-</label>
                    <input id="pertemuan_ke" name="pertemuan_ke" type="number" min="1" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <span data-error-for="pertemuan_ke" class="mt-1 hidden text-sm text-red-600"></span>
                </div>
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal</label>
                    <input id="tanggal" name="tanggal" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <span data-error-for="tanggal" class="mt-1 hidden text-sm text-red-600"></span>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" data-modal-close class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50">Batal</button>
                    <button id="submit-pertemuan" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-indigo-500 disabled:opacity-50">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const URL_INDEX = "{{ route('api.kelas.pertemuans.index', $kelas->id) }}";
            const URL_STORE = "{{ route('api.kelas.pertemuans.store', $kelas->id) }}";
            const URL_UPDATE = "{{ route('api.pertemuans.update', ':id') }}";
            const URL_DESTROY = "{{ route('api.pertemuans.destroy', ':id') }}";
            const URL_KEHADIRAN = "{{ route('page.kehadiran', ':id') }}";

            const tbody = document.getElementById('tbody-pertemuan');
            const modal = document.getElementById('modal-pertemuan');
            const form = document.getElementById('form-pertemuan');
            const submit = document.getElementById('submit-pertemuan');
            const errorBox = document.getElementById('error');

            let daftarPertemuan = [];

            initModal('modal-pertemuan', 'btn-tambah-pertemuan');

            document.getElementById('btn-tambah-pertemuan').addEventListener('click', () => {
                openModal(modal);
                document.getElementById('modal-pertemuan-title').textContent = 'Tambah Pertemuan';
                submit.textContent = 'Simpan';
                document.getElementById('pertemuan-id').value = '';

                const terbesar = daftarPertemuan.reduce((maks, item) => Math.max(maks, Number(item.pertemuan_ke ?? 0)), 0);
                const sekarang = new Date();
                const hariIni = [
                    sekarang.getFullYear(),
                    String(sekarang.getMonth() + 1).padStart(2, '0'),
                    String(sekarang.getDate()).padStart(2, '0'),
                ].join('-');

                form.elements.namedItem('pertemuan_ke').value = terbesar + 1;
                form.elements.namedItem('tanggal').value = hariIni;
            });

            document.getElementById('retry').addEventListener('click', muat);

            function setMuat(aktif) {
                document.getElementById('loading').classList.toggle('hidden', !aktif);
                document.getElementById('konten').classList.toggle('hidden', aktif);
            }

            function render() {
                if (daftarPertemuan.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada pertemuan</td></tr>';
                    return;
                }

                tbody.innerHTML = daftarPertemuan.map((p) => {
                    const sudahDiisi = Number(p.kehadirans_count ?? 0) > 0;
                    const badge = sudahDiisi
                        ? '<span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-800">Sudah diisi</span>'
                        : '<span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">Belum diisi</span>';

                    return `
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">${Number(p.pertemuan_ke)}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">${esc(formatTanggal(p.tanggal))}</td>
                            <td class="px-4 py-3">${badge}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="${URL_KEHADIRAN.replace(':id', p.id)}" class="text-xs font-medium text-indigo-600 hover:text-indigo-900">Input Kehadiran</a>
                                    <button type="button" data-ubah="${p.id}" class="text-xs font-medium text-amber-600 hover:text-amber-900">Ubah</button>
                                    <button type="button" data-hapus="${p.id}" class="text-xs font-medium text-red-600 hover:text-red-900">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            async function muat() {
                errorBox.classList.add('hidden');
                setMuat(true);

                try {
                    const response = await api(URL_INDEX);
                    daftarPertemuan = response.data ?? [];
                    render();
                    setMuat(false);
                } catch (e) {
                    document.getElementById('error-message').textContent = e.message || 'Gagal memuat pertemuan.';
                    errorBox.classList.remove('hidden');
                    setMuat(false);
                }
            }

            function bukaUbah(id) {
                const pertemuan = daftarPertemuan.find((item) => item.id === id);

                if (!pertemuan) {
                    return;
                }

                openModal(modal);
                document.getElementById('modal-pertemuan-title').textContent = 'Ubah Pertemuan';
                submit.textContent = 'Simpan Perubahan';
                document.getElementById('pertemuan-id').value = pertemuan.id;
                form.elements.namedItem('pertemuan_ke').value = pertemuan.pertemuan_ke ?? '';
                form.elements.namedItem('tanggal').value = String(pertemuan.tanggal ?? '').slice(0, 10);
            }

            async function hapusPertemuan(id) {
                if (!confirm('Yakin hapus pertemuan ini? Catatan kehadiran pada pertemuan ini juga akan terhapus.')) {
                    return;
                }

                try {
                    const response = await api(URL_DESTROY.replace(':id', id), 'DELETE');
                    toast(response.message);
                    await muat();
                } catch (e) {
                    toast(e.message || 'Gagal menghapus pertemuan.', 'error');
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
                    hapusPertemuan(Number(hapus.dataset.hapus));
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const id = document.getElementById('pertemuan-id').value;
                const body = {
                    pertemuan_ke: form.elements.namedItem('pertemuan_ke').value.trim(),
                    tanggal: form.elements.namedItem('tanggal').value,
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
                    toast(e.message || 'Gagal menyimpan pertemuan.', 'error');
                } finally {
                    submit.disabled = false;
                }
            });

            muat();
        </script>
    @endpush
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Daftar Kelas</h3>
                        <p class="mt-1 text-sm text-gray-500">Kelola mata kuliah, kelas, dan dosen pengampu.</p>
                    </div>
                    <button id="btn-tambah-kelas" type="button" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Tambah Kelas
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
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Mata Kuliah</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kelas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Dosen</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jml Mahasiswa</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jml Pertemuan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-kelas" class="divide-y divide-gray-200 bg-white"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-kelas" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 p-4">
        <div class="w-full max-w-lg rounded-lg bg-white shadow-xl">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 id="modal-kelas-title" class="text-lg font-semibold text-gray-900">Tambah Kelas</h3>
                <button type="button" data-modal-close class="text-2xl leading-none text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form id="form-kelas" class="space-y-4 px-6 py-5" novalidate>
                <input id="kelas-id" type="hidden" name="id">
                <div>
                    <label for="nama_mata_kuliah" class="block text-sm font-medium text-gray-700">Nama Mata Kuliah</label>
                    <input id="nama_mata_kuliah" name="nama_mata_kuliah" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <span data-error-for="nama_mata_kuliah" class="mt-1 hidden text-sm text-red-600"></span>
                </div>
                <div>
                    <label for="nama_kelas" class="block text-sm font-medium text-gray-700">Nama Kelas</label>
                    <input id="nama_kelas" name="nama_kelas" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <span data-error-for="nama_kelas" class="mt-1 hidden text-sm text-red-600"></span>
                </div>
                <div>
                    <label for="dosen" class="block text-sm font-medium text-gray-700">Dosen</label>
                    <input id="dosen" name="dosen" type="text" value="{{ Auth::user()->name }}" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-600 shadow-sm sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Nama dosen diambil dari profil akun yang sedang login.</p>
                    <span data-error-for="dosen" class="mt-1 hidden text-sm text-red-600"></span>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" data-modal-close class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50">Batal</button>
                    <button id="submit-kelas" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-indigo-500 disabled:opacity-50">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const URL_INDEX = "{{ route('api.kelas.index') }}";
            const URL_STORE = "{{ route('api.kelas.store') }}";
            const URL_UPDATE = "{{ route('api.kelas.update', ':id') }}";
            const URL_DESTROY = "{{ route('api.kelas.destroy', ':id') }}";
            const URL_PESERTA = "{{ route('page.peserta', ':id') }}";
            const URL_PERTEMUAN = "{{ route('page.pertemuan', ':id') }}";
            const URL_REKAP = "{{ route('page.rekap', ':id') }}";
            const NAMA_DOSEN = @json(Auth::user()->name);

            const tbody = document.getElementById('tbody-kelas');
            const modal = document.getElementById('modal-kelas');
            const form = document.getElementById('form-kelas');
            const submit = document.getElementById('submit-kelas');
            const errorBox = document.getElementById('error');

            let daftarKelas = [];

            initModal('modal-kelas', 'btn-tambah-kelas');

            document.getElementById('btn-tambah-kelas').addEventListener('click', () => {
                document.getElementById('modal-kelas-title').textContent = 'Tambah Kelas';
                submit.textContent = 'Simpan';
            });

            document.getElementById('retry').addEventListener('click', muat);

            function setMuat(aktif) {
                document.getElementById('loading').classList.toggle('hidden', !aktif);
                document.getElementById('konten').classList.toggle('hidden', aktif);
            }

            function render() {
                if (daftarKelas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada kelas</td></tr>';
                    return;
                }

                tbody.innerHTML = daftarKelas.map((k) => `
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${esc(k.nama_mata_kuliah)}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${esc(k.nama_kelas)}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${esc(NAMA_DOSEN)}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${Number(k.mahasiswas_count ?? 0)}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${Number(k.pertemuans_count ?? 0)}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="${URL_PESERTA.replace(':id', k.id)}" class="text-xs font-medium text-indigo-600 hover:text-indigo-900">Peserta</a>
                                <a href="${URL_PERTEMUAN.replace(':id', k.id)}" class="text-xs font-medium text-indigo-600 hover:text-indigo-900">Pertemuan</a>
                                <a href="${URL_REKAP.replace(':id', k.id)}" class="text-xs font-medium text-indigo-600 hover:text-indigo-900">Rekap</a>
                                <button type="button" data-ubah="${k.id}" class="text-xs font-medium text-amber-600 hover:text-amber-900">Ubah</button>
                                <button type="button" data-hapus="${k.id}" class="text-xs font-medium text-red-600 hover:text-red-900">Hapus</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }

            async function muat() {
                errorBox.classList.add('hidden');
                setMuat(true);

                try {
                    const response = await api(URL_INDEX);
                    daftarKelas = response.data ?? [];
                    render();
                    setMuat(false);
                } catch (e) {
                    document.getElementById('error-message').textContent = e.message || 'Gagal memuat kelas.';
                    errorBox.classList.remove('hidden');
                    setMuat(false);
                }
            }

            function bukaUbah(id) {
                const kelas = daftarKelas.find((item) => item.id === id);

                if (!kelas) {
                    return;
                }

                openModal(modal);
                document.getElementById('modal-kelas-title').textContent = 'Ubah Kelas';
                submit.textContent = 'Simpan Perubahan';
                document.getElementById('kelas-id').value = kelas.id;
                form.elements.namedItem('nama_mata_kuliah').value = kelas.nama_mata_kuliah ?? '';
                form.elements.namedItem('nama_kelas').value = kelas.nama_kelas ?? '';
            }

            async function hapusKelas(id) {
                if (!confirm('Yakin hapus kelas ini?')) {
                    return;
                }

                try {
                    const response = await api(URL_DESTROY.replace(':id', id), 'DELETE');
                    toast(response.message);
                    await muat();
                } catch (e) {
                    toast(e.message || 'Gagal menghapus kelas.', 'error');
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
                    hapusKelas(Number(hapus.dataset.hapus));
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const id = document.getElementById('kelas-id').value;
                const body = {
                    nama_mata_kuliah: form.elements.namedItem('nama_mata_kuliah').value.trim(),
                    nama_kelas: form.elements.namedItem('nama_kelas').value.trim(),
                    dosen: form.elements.namedItem('dosen').value.trim(),
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
                    toast(e.message || 'Gagal menyimpan kelas.', 'error');
                } finally {
                    submit.disabled = false;
                }
            });

            muat();
        </script>
    @endpush
</x-app-layout>

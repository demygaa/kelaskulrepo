<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Peserta Kelas') }}
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
                    <a href="{{ route('page.kelas') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">&larr; Kembali ke Kelas</a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div id="error" class="hidden m-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    <span id="error-message"></span>
                    <button id="retry" type="button" class="ml-2 font-semibold underline">Coba lagi</button>
                </div>
                <div id="loading" class="p-6 text-sm text-gray-500">Memuat...</div>

                <div id="konten" class="hidden grid gap-6 p-4 sm:p-6 lg:grid-cols-2">
                    <section>
                        <h3 class="mb-3 text-base font-semibold text-gray-900">Peserta Terdaftar</h3>
                        <div class="overflow-x-auto rounded-md border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">NIM</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-peserta" class="divide-y divide-gray-200 bg-white"></tbody>
                            </table>
                        </div>
                    </section>

                    <section>
                        <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-base font-semibold text-gray-900">Mahasiswa Belum Terdaftar</h3>
                            <input id="cari-calon" type="search" placeholder="Cari NIM atau nama" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div class="overflow-x-auto rounded-md border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-10 px-4 py-3">
                                            <input id="pilih-semua" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">NIM</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-calon" class="divide-y divide-gray-200 bg-white"></tbody>
                            </table>
                        </div>
                        <button id="btn-daftar" type="button" disabled class="mt-4 w-full rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">
                            Daftarkan Terpilih
                        </button>
                    </section>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const URL_PESERTA = "{{ route('api.kelas.mahasiswas.index', $kelas->id) }}";
            const URL_DAFTAR = "{{ route('api.kelas.mahasiswas.store', $kelas->id) }}";
            const URL_KELUARKAN = "{{ route('api.kelas.mahasiswas.destroy', ['kelas' => $kelas->id, 'mahasiswa' => ':id']) }}";
            const URL_MAHASISWA = "{{ route('api.mahasiswas.index') }}";

            const tbodyPeserta = document.getElementById('tbody-peserta');
            const tbodyCalon = document.getElementById('tbody-calon');
            const search = document.getElementById('cari-calon');
            const pilihSemua = document.getElementById('pilih-semua');
            const btnDaftar = document.getElementById('btn-daftar');
            const errorBox = document.getElementById('error');

            let peserta = [];
            let semuaMahasiswa = [];

            document.getElementById('retry').addEventListener('click', muat);

            search.addEventListener('input', renderCalon);

            function setMuat(aktif) {
                document.getElementById('loading').classList.toggle('hidden', !aktif);
                document.getElementById('konten').classList.toggle('hidden', aktif);
            }

            function daftarCalon() {
                const kata = search.value.trim().toLowerCase();
                const terdaftar = new Set(peserta.map((p) => p.mahasiswa_id));

                return semuaMahasiswa.filter((m) => {
                    const nim = String(m.nim ?? '').toLowerCase();
                    const nama = String(m.nama ?? '').toLowerCase();
                    const cocok = !kata || nim.includes(kata) || nama.includes(kata);

                    return !terdaftar.has(m.id) && cocok;
                });
            }

            function checkboxCalon() {
                return Array.from(tbodyCalon.querySelectorAll('.pilih-calon'));
            }

            function perbaruiTombol() {
                const semua = checkboxCalon();
                btnDaftar.disabled = !semua.some((checkbox) => checkbox.checked);
                pilihSemua.checked = semua.length > 0 && semua.every((checkbox) => checkbox.checked);
                pilihSemua.indeterminate = !pilihSemua.checked && semua.some((checkbox) => checkbox.checked);
            }

            function renderPeserta() {
                if (peserta.length === 0) {
                    tbodyPeserta.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada peserta terdaftar</td></tr>';
                    return;
                }

                tbodyPeserta.innerHTML = peserta.map((p) => `
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${esc(p.mahasiswa?.nim)}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${esc(p.mahasiswa?.nama)}</td>
                        <td class="px-4 py-3">
                            <button type="button" data-keluarkan="${p.mahasiswa_id}" class="text-xs font-medium text-red-600 hover:text-red-900">Keluarkan</button>
                        </td>
                    </tr>
                `).join('');
            }

            function renderCalon() {
                const calon = daftarCalon();

                if (calon.length === 0) {
                    tbodyCalon.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada mahasiswa tersedia</td></tr>';
                } else {
                    tbodyCalon.innerHTML = calon.map((m) => `
                        <tr>
                            <td class="px-4 py-3">
                                <input type="checkbox" class="pilih-calon h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" data-id="${m.id}">
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">${esc(m.nim)}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">${esc(m.nama)}</td>
                        </tr>
                    `).join('');
                }

                perbaruiTombol();
            }

            async function muat() {
                errorBox.classList.add('hidden');
                setMuat(true);

                try {
                    const [resPeserta, resMahasiswa] = await Promise.all([
                        api(URL_PESERTA),
                        api(URL_MAHASISWA),
                    ]);

                    peserta = resPeserta.data ?? [];
                    semuaMahasiswa = resMahasiswa.data ?? [];
                    renderPeserta();
                    renderCalon();
                    setMuat(false);
                } catch (e) {
                    document.getElementById('error-message').textContent = e.message || 'Gagal memuat peserta kelas.';
                    errorBox.classList.remove('hidden');
                    setMuat(false);
                }
            }

            tbodyPeserta.addEventListener('click', async (event) => {
                const tombol = event.target.closest('[data-keluarkan]');

                if (!tombol) {
                    return;
                }

                const mahasiswaId = Number(tombol.dataset.keluarkan);

                if (!confirm('Keluarkan mahasiswa ini dari kelas? Catatan kehadirannya di kelas ini juga akan terhapus.')) {
                    return;
                }

                tombol.disabled = true;

                try {
                    const response = await api(URL_KELUARKAN.replace(':id', mahasiswaId), 'DELETE');
                    toast(response.message);
                    await muat();
                } catch (e) {
                    toast(e.message || 'Gagal mengeluarkan mahasiswa.', 'error');
                    tombol.disabled = false;
                }
            });

            tbodyCalon.addEventListener('change', (event) => {
                if (event.target.classList.contains('pilih-calon')) {
                    perbaruiTombol();
                }
            });

            pilihSemua.addEventListener('change', () => {
                checkboxCalon().forEach((checkbox) => {
                    checkbox.checked = pilihSemua.checked;
                });
                perbaruiTombol();
            });

            btnDaftar.addEventListener('click', async () => {
                const mahasiswaIds = checkboxCalon()
                    .filter((checkbox) => checkbox.checked)
                    .map((checkbox) => Number(checkbox.dataset.id));

                if (mahasiswaIds.length === 0) {
                    return;
                }

                btnDaftar.disabled = true;

                try {
                    const response = await api(URL_DAFTAR, 'POST', { mahasiswa_ids: mahasiswaIds });
                    toast(response.message);
                    await muat();
                } catch (e) {
                    toast(e.message || 'Gagal mendaftarkan mahasiswa.', 'error');
                    btnDaftar.disabled = false;
                }
            });

            muat();
        </script>
    @endpush
</x-app-layout>

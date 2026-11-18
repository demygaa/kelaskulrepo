<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rekap Kehadiran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600">{{ $kelas->nama_mata_kuliah }} &middot; {{ $kelas->nama_kelas }}</p>
                        <p class="mt-1 text-sm text-gray-500">Dosen: {{ Auth::user()->name }}</p>
                        <p class="mt-2 text-sm text-gray-700">
                            Total Pertemuan: <strong id="header-total" class="text-gray-900">0</strong>
                            &middot; Rata-rata Kehadiran: <strong id="header-rata" class="text-gray-900">0</strong>%
                        </p>
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

                <div id="konten" class="hidden p-4 sm:p-6 space-y-5">
                    <div id="tanpa-pertemuan" class="hidden rounded-md bg-amber-50 p-4 text-sm text-amber-800">
                        Belum ada pertemuan pada kelas ini. Persentase kehadiran akan dihitung setelah pertemuan dibuat.
                    </div>

                    <div class="overflow-x-auto rounded-md border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">NIM</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Hadir</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Izin</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sakit</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Alpa</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Belum Diisi</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Persentase Hadir</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-rekap" class="divide-y divide-gray-200 bg-white"></tbody>
                        </table>
                    </div>

                    <p class="text-sm text-gray-500">
                        Izin dan sakit dihitung tidak hadir. Persentase = hadir &divide; total pertemuan &times; 100.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const URL_REKAP = "{{ route('api.kelas.rekap', $kelas->id) }}";

            const tbody = document.getElementById('tbody-rekap');
            const errorBox = document.getElementById('error');

            let rekap = null;

            document.getElementById('retry').addEventListener('click', muat);

            function setMuat(aktif) {
                document.getElementById('loading').classList.toggle('hidden', !aktif);
                document.getElementById('konten').classList.toggle('hidden', aktif);
            }

            function warnaPersentase(persen) {
                if (persen < 75) {
                    return { bar: 'bg-red-500', teks: 'text-red-700' };
                }

                if (persen < 90) {
                    return { bar: 'bg-amber-500', teks: 'text-amber-700' };
                }

                return { bar: 'bg-emerald-500', teks: 'text-emerald-700' };
            }

            function render() {
                const total = Number(rekap.total_pertemuan ?? 0);
                const rata = Number(rekap.rata_rata_hadir ?? 0);
                const mahasiswas = rekap.mahasiswas ?? [];

                document.getElementById('header-total').textContent = total;
                document.getElementById('header-rata').textContent = rata;
                document.getElementById('tanpa-pertemuan').classList.toggle('hidden', total > 0);

                if (mahasiswas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada peserta terdaftar di kelas ini.</td></tr>';
                    return;
                }

                tbody.innerHTML = mahasiswas.map((m) => {
                    const id = m.kelas_mahasiswa_id;
                    const persen = Math.min(100, Math.max(0, Number(m.persentase_hadir ?? 0)));
                    const warna = warnaPersentase(persen);
                    const catatan = m.catatan_izin_sakit ?? [];
                    const detailCatatan = catatan.length
                        ? `<ul class="space-y-1">${catatan.map((c) => `
                            <li class="text-sm text-gray-700">
                                Pertemuan ke-${esc(c.pertemuan_ke)} &middot; ${esc(formatTanggal(c.tanggal))}
                                &middot; <span class="font-medium capitalize">${esc(c.status)}</span>
                                &middot; ${esc(c.keterangan)}
                            </li>
                        `).join('')}</ul>`
                        : '<p class="text-sm text-gray-500">Tidak ada izin/sakit</p>';

                    return `
                        <tr data-detail-toggle="${id}" class="cursor-pointer hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">${esc(m.nim)}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">${esc(m.nama)}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${Number(m.hadir ?? 0)}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${Number(m.izin ?? 0)}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${Number(m.sakit ?? 0)}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${Number(m.alpa ?? 0)}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${Number(m.belum_diisi ?? 0)}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
                                        <div class="h-full ${warna.bar}" style="width: ${persen}%"></div>
                                    </div>
                                    <span class="text-xs font-medium ${warna.teks}">${persen}%</span>
                                </div>
                            </td>
                        </tr>
                        <tr data-detail="${id}" class="hidden">
                            <td colspan="8" class="bg-gray-50 px-4 py-3">
                                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Catatan Izin/Sakit</p>
                                ${detailCatatan}
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            async function muat() {
                errorBox.classList.add('hidden');
                setMuat(true);

                try {
                    const response = await api(URL_REKAP);
                    rekap = response;
                    render();
                    setMuat(false);
                } catch (e) {
                    document.getElementById('error-message').textContent = e.message || 'Gagal memuat rekap.';
                    errorBox.classList.remove('hidden');
                    setMuat(false);
                }
            }

            tbody.addEventListener('click', (event) => {
                const baris = event.target.closest('[data-detail-toggle]');

                if (!baris) {
                    return;
                }

                const detail = tbody.querySelector(`[data-detail="${baris.dataset.detailToggle}"]`);

                if (detail) {
                    detail.classList.toggle('hidden');
                }
            });

            muat();
        </script>
    @endpush
</x-app-layout>

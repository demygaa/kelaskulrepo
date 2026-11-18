<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Kehadiran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600">{{ $pertemuan->kelas->nama_mata_kuliah }} &middot; {{ $pertemuan->kelas->nama_kelas }}</p>
                        <p class="mt-1 text-sm text-gray-500">
                            Pertemuan ke-{{ $pertemuan->pertemuan_ke }} &middot;
                            <span id="header-tanggal">{{ $pertemuan->tanggal?->format('Y-m-d') }}</span>
                        </p>
                    </div>
                    <a href="{{ route('page.pertemuan', $pertemuan->kelas_id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">&larr; Kembali ke Pertemuan</a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form id="form-kehadiran" class="p-4 sm:p-6 space-y-5" novalidate>
                    <div id="error" class="hidden rounded-md bg-red-50 p-4 text-sm text-red-700">
                        <span id="error-message"></span>
                        <button id="retry" type="button" class="ml-2 font-semibold underline">Coba lagi</button>
                    </div>
                    <div id="loading" class="text-sm text-gray-500">Memuat...</div>

                    <div id="konten" class="hidden space-y-5">
                        <div class="flex flex-col gap-4 rounded-md bg-gray-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm">
                                <span class="text-gray-700">Hadir: <strong id="ringkas-hadir" class="text-emerald-700">0</strong></span>
                                <span class="text-gray-700">Izin: <strong id="ringkas-izin" class="text-amber-700">0</strong></span>
                                <span class="text-gray-700">Sakit: <strong id="ringkas-sakit" class="text-sky-700">0</strong></span>
                                <span class="text-gray-700">Alpa: <strong id="ringkas-alpa" class="text-red-700">0</strong></span>
                                <span class="text-gray-700">Belum diisi: <strong id="ringkas-belum" class="text-gray-700">0</strong></span>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button id="btn-semua-hadir" type="button" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50">Semua Hadir</button>
                                <button id="btn-simpan" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-indigo-500 disabled:opacity-50">Simpan Semua</button>
                            </div>
                        </div>

                        <div id="pesan-kosong" class="hidden rounded-md bg-amber-50 p-4 text-sm text-amber-800">
                            Kelas ini belum punya peserta.
                            <a href="{{ route('page.peserta', $pertemuan->kelas_id) }}" class="font-semibold underline">Tambah peserta sekarang</a>.
                        </div>

                        <div id="tabel-wrapper" class="hidden overflow-x-auto rounded-md border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">No</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">NIM</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kehadiran" class="divide-y divide-gray-200 bg-white"></tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const URL_INDEX = "{{ route('api.pertemuans.kehadirans.index', $pertemuan->id) }}";
            const URL_SIMPAN = "{{ route('api.pertemuans.kehadirans.store', $pertemuan->id) }}";

            const tbody = document.getElementById('tbody-kehadiran');
            const form = document.getElementById('form-kehadiran');
            const submit = document.getElementById('btn-simpan');
            const errorBox = document.getElementById('error');

            let rows = [];
            let urutanKirim = [];

            document.getElementById('header-tanggal').textContent = formatTanggal(
                document.getElementById('header-tanggal').textContent.trim()
            );

            document.getElementById('retry').addEventListener('click', muat);

            function setMuat(aktif) {
                document.getElementById('loading').classList.toggle('hidden', !aktif);
                document.getElementById('konten').classList.toggle('hidden', aktif);
            }

            function radioStatus(id, value, label, warna, statusSaatIni) {
                const centang = statusSaatIni === value ? 'checked' : '';

                return `
                    <label class="inline-flex items-center gap-1 text-sm text-gray-700">
                        <input type="radio" name="status-${id}" value="${value}" data-status="${id}" class="h-4 w-4 border-gray-300 ${warna} focus:ring-indigo-500" ${centang}>
                        ${label}
                    </label>
                `;
            }

            function render() {
                if (rows.length === 0) {
                    document.getElementById('tabel-wrapper').classList.add('hidden');
                    document.getElementById('pesan-kosong').classList.remove('hidden');
                    hitungRingkasan();
                    return;
                }

                document.getElementById('pesan-kosong').classList.add('hidden');
                document.getElementById('tabel-wrapper').classList.remove('hidden');

                tbody.innerHTML = rows.map((r, index) => {
                    const id = r.kelas_mahasiswa_id;
                    const status = r.status ?? 'belum_diisi';
                    const butuhKeterangan = status === 'izin' || status === 'sakit';
                    const keterangan = butuhKeterangan ? (r.keterangan ?? '') : '';
                    const badge = status === 'belum_diisi'
                        ? '<span data-badge class="mt-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Belum diisi</span>'
                        : '<span data-badge class="mt-1 hidden rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Belum diisi</span>';

                    return `
                        <tr data-row="${id}">
                            <td class="px-3 py-3 text-sm text-gray-500">${index + 1}</td>
                            <td class="px-3 py-3 text-sm font-medium text-gray-900">${esc(r.nim)}</td>
                            <td class="px-3 py-3 text-sm text-gray-900">
                                <div>${esc(r.nama)}</div>
                                ${badge}
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap gap-x-3 gap-y-1">
                                    ${radioStatus(id, 'hadir', 'Hadir', 'text-emerald-600', status)}
                                    ${radioStatus(id, 'izin', 'Izin', 'text-amber-600', status)}
                                    ${radioStatus(id, 'sakit', 'Sakit', 'text-sky-600', status)}
                                    ${radioStatus(id, 'alpa', 'Alpa', 'text-red-600', status)}
                                </div>
                                <span data-error-for="row-${id}-status" class="mt-1 hidden text-sm text-red-600"></span>
                            </td>
                            <td class="px-3 py-3">
                                <input type="text" data-keterangan="${id}" value="${esc(keterangan)}" placeholder="Wajib untuk izin/sakit" ${butuhKeterangan ? '' : 'disabled'} class="w-full min-w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                                <span data-error-for="row-${id}-keterangan" class="mt-1 hidden text-sm text-red-600"></span>
                            </td>
                        </tr>
                    `;
                }).join('');

                hitungRingkasan();
            }

            function statusDipilih(id) {
                const terpilih = tbody.querySelector(`input[data-status="${id}"]:checked`);

                return terpilih ? terpilih.value : 'belum_diisi';
            }

            function hitungRingkasan() {
                const hitung = { hadir: 0, izin: 0, sakit: 0, alpa: 0, belum_diisi: 0 };

                rows.forEach((r) => {
                    const status = statusDipilih(r.kelas_mahasiswa_id);
                    hitung[status] = (hitung[status] ?? 0) + 1;
                });

                document.getElementById('ringkas-hadir').textContent = hitung.hadir;
                document.getElementById('ringkas-izin').textContent = hitung.izin;
                document.getElementById('ringkas-sakit').textContent = hitung.sakit;
                document.getElementById('ringkas-alpa').textContent = hitung.alpa;
                document.getElementById('ringkas-belum').textContent = hitung.belum_diisi;

                return hitung;
            }

            function perbaruiBaris(id) {
                const tr = tbody.querySelector(`[data-row="${id}"]`);

                if (!tr) {
                    return;
                }

                const status = statusDipilih(id);
                const butuhKeterangan = status === 'izin' || status === 'sakit';
                const inputKeterangan = tr.querySelector(`[data-keterangan="${id}"]`);
                const badge = tr.querySelector('[data-badge]');

                inputKeterangan.disabled = !butuhKeterangan;

                if (!butuhKeterangan) {
                    inputKeterangan.value = '';
                }

                badge.classList.toggle('hidden', status !== 'belum_diisi');
                bersihkanBaris(id);
                hitungRingkasan();
            }

            function tandaiBaris(id, field, pesan) {
                const tr = tbody.querySelector(`[data-row="${id}"]`);

                if (!tr) {
                    return;
                }

                tr.classList.add('bg-red-50');
                const span = tr.querySelector(`[data-error-for="row-${id}-${field}"]`);

                if (span) {
                    span.textContent = pesan;
                    span.classList.remove('hidden');
                }
            }

            function bersihkanBaris(id) {
                const tr = tbody.querySelector(`[data-row="${id}"]`);

                if (!tr) {
                    return;
                }

                tr.classList.remove('bg-red-50');
                tr.querySelectorAll('[data-error-for]').forEach((span) => {
                    span.textContent = '';
                    span.classList.add('hidden');
                });
            }

            async function muat() {
                errorBox.classList.add('hidden');
                setMuat(true);

                try {
                    const response = await api(URL_INDEX);
                    rows = response.data ?? [];
                    render();
                    setMuat(false);
                } catch (e) {
                    document.getElementById('error-message').textContent = e.message || 'Gagal memuat kehadiran.';
                    errorBox.classList.remove('hidden');
                    setMuat(false);
                }
            }

            tbody.addEventListener('change', (event) => {
                if (event.target.matches('[data-status]')) {
                    perbaruiBaris(Number(event.target.dataset.status));
                }
            });

            tbody.addEventListener('input', (event) => {
                if (event.target.matches('[data-keterangan]')) {
                    bersihkanBaris(Number(event.target.dataset.keterangan));
                }
            });

            document.getElementById('btn-semua-hadir').addEventListener('click', () => {
                rows.forEach((r) => {
                    const id = r.kelas_mahasiswa_id;
                    const radio = tbody.querySelector(`input[data-status="${id}"][value="hadir"]`);

                    if (radio) {
                        radio.checked = true;
                    }

                    perbaruiBaris(id);
                });
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                clearErrors(form);
                urutanKirim = [];

                const kehadirans = [];
                let tidakValid = 0;

                rows.forEach((r) => {
                    const id = r.kelas_mahasiswa_id;
                    const status = statusDipilih(id);

                    if (status === 'belum_diisi') {
                        return;
                    }

                    const butuhKeterangan = status === 'izin' || status === 'sakit';
                    const inputKeterangan = tbody.querySelector(`[data-keterangan="${id}"]`);
                    const keterangan = butuhKeterangan ? inputKeterangan.value.trim() : null;

                    if (butuhKeterangan && !keterangan) {
                        tandaiBaris(id, 'keterangan', 'Keterangan wajib diisi untuk izin/sakit.');
                        tidakValid += 1;
                        return;
                    }

                    urutanKirim.push(id);
                    kehadirans.push({
                        kelas_mahasiswa_id: id,
                        status,
                        keterangan,
                    });
                });

                if (tidakValid > 0) {
                    toast(`${tidakValid} baris belum memiliki keterangan.`, 'error');
                    return;
                }

                if (kehadirans.length === 0) {
                    toast('Belum ada status yang dipilih.', 'error');
                    return;
                }

                const belum = rows.length - urutanKirim.length;

                if (belum > 0 && !confirm(`Masih ada ${belum} mahasiswa yang belum diisi. Tetap simpan data sekarang?`)) {
                    return;
                }

                submit.disabled = true;

                try {
                    const response = await api(URL_SIMPAN, 'PUT', { kehadirans });
                    rows = response.data ?? [];
                    render();
                    toast(response.message);
                } catch (e) {
                    if (e.errors) {
                        Object.entries(e.errors).forEach(([field, messages]) => {
                            const pesan = Array.isArray(messages) ? messages.join(' ') : String(messages);

                            if (field === 'kelas_mahasiswa_id') {
                                toast(pesan || 'Ada mahasiswa yang tidak terdaftar di kelas ini.', 'error');
                                return;
                            }

                            const cocok = field.match(/^kehadirans\.(\d+)\.(status|keterangan)$/);

                            if (!cocok) {
                                return;
                            }

                            const id = urutanKirim[Number(cocok[1])];

                            if (id !== undefined) {
                                tandaiBaris(id, cocok[2], pesan);
                            }
                        });
                    }

                    toast(e.message || 'Gagal menyimpan kehadiran.', 'error');
                } finally {
                    submit.disabled = false;
                }
            });

            muat();
        </script>
    @endpush
</x-app-layout>

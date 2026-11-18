<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KelasMahasiswa;

class RekapController extends Controller
{
    /**
     * Rekap kehadiran satu kelas: jumlah tiap status dan persentase hadir per mahasiswa.
     */
    public function show(Kelas $kelas)
    {
        $totalPertemuan = $kelas->pertemuans()->count();

        $peserta = KelasMahasiswa::where('kelas_id', $kelas->id)
            ->with([
                'mahasiswa:id,nim,nama',
                'kehadirans.pertemuan:id,pertemuan_ke,tanggal',
            ])
            ->get()
            ->sortBy('mahasiswa.nim')
            ->values();

        $mahasiswas = $peserta->map(function ($p) use ($totalPertemuan) {
            $hitung = $p->kehadirans->countBy('status');

            $hadir = $hitung->get('hadir', 0);
            $izin  = $hitung->get('izin', 0);
            $sakit = $hitung->get('sakit', 0);
            $alpa  = $hitung->get('alpa', 0);

            // Izin dan sakit sengaja tidak dihitung sebagai hadir
            $persentase = $totalPertemuan > 0
                ? round($hadir / $totalPertemuan * 100, 2)
                : 0;

            // Daftar izin/sakit beserta keterangannya, urut per pertemuan
            $catatan = $p->kehadirans
                ->whereIn('status', ['izin', 'sakit'])
                ->sortBy('pertemuan.pertemuan_ke')
                ->map(fn ($k) => [
                    'pertemuan_ke' => $k->pertemuan->pertemuan_ke,
                    'tanggal'      => $k->pertemuan->tanggal->format('Y-m-d'),
                    'status'       => $k->status,
                    'keterangan'   => $k->keterangan,
                ])
                ->values();

            return [
                'kelas_mahasiswa_id' => $p->id,
                'nim'                => $p->mahasiswa->nim,
                'nama'               => $p->mahasiswa->nama,
                'hadir'              => $hadir,
                'izin'               => $izin,
                'sakit'              => $sakit,
                'alpa'               => $alpa,
                'belum_diisi'        => max($totalPertemuan - ($hadir + $izin + $sakit + $alpa), 0),
                'persentase_hadir'   => $persentase,
                'catatan_izin_sakit' => $catatan,
            ];
        });

        return response()->json([
            'message'         => 'Rekap kehadiran berhasil diambil',
            'kelas'           => $kelas->only(['id', 'nama_mata_kuliah', 'nama_kelas', 'dosen']),
            'total_pertemuan' => $totalPertemuan,
            'rata_rata_hadir' => round($mahasiswas->avg('persentase_hadir') ?? 0, 2),
            'mahasiswas'      => $mahasiswas,
        ]);
    }
}
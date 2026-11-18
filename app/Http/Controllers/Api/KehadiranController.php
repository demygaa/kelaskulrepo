<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kehadiran;
use App\Models\KelasMahasiswa;
use App\Models\Pertemuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KehadiranController extends Controller
{
    /**
     * Lihat kehadiran satu pertemuan: seluruh mahasiswa kelas beserta statusnya.
     * Mahasiswa yang belum dicatat tampil dengan status "belum_diisi".
     */
    public function index(Pertemuan $pertemuan)
    {
        return response()->json([
            'message'   => 'Data kehadiran berhasil diambil',
            'pertemuan' => $pertemuan->load('kelas:id,nama_mata_kuliah,nama_kelas'),
            'data'      => $this->daftarKehadiran($pertemuan),
        ]);
    }

    /**
     * Input (atau koreksi) kehadiran seluruh mahasiswa dalam satu request.
     */
    public function store(Request $request, Pertemuan $pertemuan)
    {
        $validated = $request->validate([
            'kehadirans'                      => 'required|array|min:1',
            'kehadirans.*.kelas_mahasiswa_id' => 'required|integer|distinct|exists:kelas_mahasiswa,id',
            'kehadirans.*.status'             => 'required|in:hadir,izin,sakit,alpa',
            'kehadirans.*.keterangan'         => 'required_if:kehadirans.*.status,izin,sakit|nullable|string|max:255',
        ]);

        $items = collect($validated['kehadirans']);

        // Aturan bisnis: hanya mahasiswa yang terdaftar di kelas pertemuan ini
        $idValid = KelasMahasiswa::where('kelas_id', $pertemuan->kelas_id)
            ->whereIn('id', $items->pluck('kelas_mahasiswa_id'))
            ->pluck('id');

        $idTidakValid = $items->pluck('kelas_mahasiswa_id')->diff($idValid)->values();

        if ($idTidakValid->isNotEmpty()) {
            return response()->json([
                'message' => 'Ada mahasiswa yang tidak terdaftar di kelas ini',
                'errors'  => ['kelas_mahasiswa_id' => $idTidakValid],
            ], 422);
        }

        DB::transaction(function () use ($items, $pertemuan) {
            foreach ($items as $item) {
                $butuhKeterangan = in_array($item['status'], ['izin', 'sakit']);

                Kehadiran::updateOrCreate(
                    [
                        'pertemuan_id'       => $pertemuan->id,
                        'kelas_mahasiswa_id' => $item['kelas_mahasiswa_id'],
                    ],
                    [
                        'status'     => $item['status'],
                        // hadir/alpa tidak perlu keterangan, kosongkan jika sebelumnya ada
                        'keterangan' => $butuhKeterangan ? $item['keterangan'] : null,
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'Kehadiran berhasil disimpan',
            'data'    => $this->daftarKehadiran($pertemuan),
        ]);
    }

    /**
     * Gabungan daftar peserta kelas dengan status kehadirannya di pertemuan ini.
     */
    private function daftarKehadiran(Pertemuan $pertemuan)
    {
        return KelasMahasiswa::where('kelas_id', $pertemuan->kelas_id)
            ->with([
                'mahasiswa:id,nim,nama',
                'kehadirans' => fn ($q) => $q->where('pertemuan_id', $pertemuan->id),
            ])
            ->get()
            ->sortBy('mahasiswa.nim')
            ->values()
            ->map(function ($peserta) {
                $kehadiran = $peserta->kehadirans->first();

                return [
                    'kelas_mahasiswa_id' => $peserta->id,
                    'nim'                => $peserta->mahasiswa->nim,
                    'nama'               => $peserta->mahasiswa->nama,
                    'status'             => $kehadiran->status ?? 'belum_diisi',
                    'keterangan'         => $kehadiran->keterangan ?? null,
                ];
            });
    }
}
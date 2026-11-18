<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KelasMahasiswa;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class KelasMahasiswaController extends Controller
{
    /**
     * Daftar mahasiswa yang terdaftar di sebuah kelas.
     */
    public function index(Kelas $kelas)
    {
        $peserta = KelasMahasiswa::where('kelas_id', $kelas->id)
            ->with('mahasiswa:id,nim,nama')
            ->get()
            ->sortBy('mahasiswa.nim')
            ->values();

        return response()->json([
            'message' => 'Daftar mahasiswa kelas berhasil diambil',
            'kelas'   => $kelas->only(['id', 'nama_mata_kuliah', 'nama_kelas']),
            'data'    => $peserta,
        ]);
    }

    /**
     * Daftarkan satu atau beberapa mahasiswa ke kelas.
     * Body: {"mahasiswa_ids": [1, 2, 3]}
     */
    public function store(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'mahasiswa_ids'   => 'required|array|min:1',
            'mahasiswa_ids.*' => 'integer|distinct|exists:mahasiswas,id',
        ]);

        $hasil = $kelas->mahasiswas()->syncWithoutDetaching($validated['mahasiswa_ids']);

        $baru = count($hasil['attached']);
        $sudahAda = count($validated['mahasiswa_ids']) - $baru;

        return response()->json([
            'message' => "{$baru} mahasiswa berhasil didaftarkan, {$sudahAda} sudah terdaftar sebelumnya",
            'data'    => KelasMahasiswa::where('kelas_id', $kelas->id)
                            ->with('mahasiswa:id,nim,nama')
                            ->get(),
        ], 201);
    }

    /**
     * Keluarkan mahasiswa dari kelas.
     */
    public function destroy(Kelas $kelas, Mahasiswa $mahasiswa)
    {
        if (! $kelas->mahasiswas()->where('mahasiswas.id', $mahasiswa->id)->exists()) {
            return response()->json([
                'message' => 'Mahasiswa tidak terdaftar di kelas ini',
            ], 404);
        }

        $kelas->mahasiswas()->detach($mahasiswa->id);

        return response()->json([
            'message' => 'Mahasiswa berhasil dikeluarkan dari kelas',
        ]);
    }
}
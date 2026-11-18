<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    /**
     * Daftar semua kelas beserta jumlah mahasiswa dan pertemuan.
     */
    public function index()
    {
        $kelas = Kelas::withCount(['mahasiswas', 'pertemuans'])
            ->orderBy('nama_mata_kuliah')
            ->get();

        return response()->json([
            'message' => 'Daftar kelas berhasil diambil',
            'data' => $kelas,
        ]);
    }

    /**
     * Tambah kelas baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mata_kuliah' => 'required|string|max:255',
            'nama_kelas'       => 'required|string|max:255',
            'dosen'            => 'required|string|max:255',
        ]);

        $kelas = Kelas::create($validated);

        return response()->json([
            'message' => 'Kelas berhasil ditambahkan',
            'data' => $kelas,
        ], 201);
    }

    /**
     * Detail satu kelas beserta mahasiswa dan pertemuannya.
     */
    public function show(Kelas $kelas)
    {
        $kelas->load([
            'mahasiswas:id,nim,nama',
            'pertemuans' => fn ($q) => $q->orderBy('pertemuan_ke'),
        ]);

        return response()->json([
            'message' => 'Detail kelas berhasil diambil',
            'data' => $kelas,
        ]);
    }

    /**
     * Ubah data kelas.
     */
    public function update(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'nama_mata_kuliah' => 'required|string|max:255',
            'nama_kelas'       => 'required|string|max:255',
            'dosen'            => 'required|string|max:255',
        ]);

        $kelas->update($validated);

        return response()->json([
            'message' => 'Kelas berhasil diubah',
            'data' => $kelas,
        ]);
    }

    /**
     * Hapus kelas.
     */
    public function destroy(Kelas $kelas)
    {
        $kelas->delete();

        return response()->json([
            'message' => 'Kelas berhasil dihapus',
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MahasiswaController extends Controller
{
    /**
     * Daftar semua mahasiswa. Bisa dicari lewat ?search=nim/nama.
     */
    public function index(Request $request)
    {
        $mahasiswas = Mahasiswa::withCount('kelas')
            ->when($request->search, function ($q, $search) {
                $q->where('nim', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            })
            ->orderBy('nim')
            ->get();

        return response()->json([
            'message' => 'Daftar mahasiswa berhasil diambil',
            'data' => $mahasiswas,
        ]);
    }

    /**
     * Tambah mahasiswa baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim'  => 'required|string|max:20|unique:mahasiswas,nim',
            'nama' => 'required|string|max:255',
        ]);

        $mahasiswa = Mahasiswa::create($validated);

        return response()->json([
            'message' => 'Mahasiswa berhasil ditambahkan',
            'data' => $mahasiswa,
        ], 201);
    }

    /**
     * Detail satu mahasiswa beserta kelas yang diikuti.
     */
    public function show(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load('kelas:id,nama_mata_kuliah,nama_kelas,dosen');

        return response()->json([
            'message' => 'Detail mahasiswa berhasil diambil',
            'data' => $mahasiswa,
        ]);
    }

    /**
     * Ubah data mahasiswa.
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $validated = $request->validate([
            'nim'  => [
                'required', 'string', 'max:20',
                Rule::unique('mahasiswas', 'nim')->ignore($mahasiswa->id),
            ],
            'nama' => 'required|string|max:255',
        ]);

        $mahasiswa->update($validated);

        return response()->json([
            'message' => 'Mahasiswa berhasil diubah',
            'data' => $mahasiswa,
        ]);
    }

    /**
     * Hapus mahasiswa.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        $mahasiswa->delete();

        return response()->json([
            'message' => 'Mahasiswa berhasil dihapus',
        ]);
    }
}
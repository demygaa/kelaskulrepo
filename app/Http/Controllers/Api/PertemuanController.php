<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pertemuan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PertemuanController extends Controller
{
    /**
     * Daftar pertemuan milik satu kelas, urut dari pertemuan pertama.
     */
    public function index(Kelas $kelas)
    {
        $pertemuans = $kelas->pertemuans()
            ->withCount('kehadirans')
            ->orderBy('pertemuan_ke')
            ->get();

        return response()->json([
            'message' => 'Daftar pertemuan berhasil diambil',
            'kelas'   => $kelas->only(['id', 'nama_mata_kuliah', 'nama_kelas']),
            'data'    => $pertemuans,
        ]);
    }

    /**
     * Buat pertemuan baru di sebuah kelas.
     */
    public function store(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'pertemuan_ke' => [
                'required', 'integer', 'min:1', 'max:100',
                Rule::unique('pertemuans', 'pertemuan_ke')
                    ->where('kelas_id', $kelas->id),
            ],
            'tanggal' => 'required|date',
        ]);

        $pertemuan = $kelas->pertemuans()->create($validated);

        return response()->json([
            'message' => 'Pertemuan berhasil dibuat',
            'data'    => $pertemuan,
        ], 201);
    }

    /**
     * Detail satu pertemuan beserta kelasnya.
     */
    public function show(Pertemuan $pertemuan)
    {
        $pertemuan->load('kelas:id,nama_mata_kuliah,nama_kelas,dosen')
                  ->loadCount('kehadirans');

        return response()->json([
            'message' => 'Detail pertemuan berhasil diambil',
            'data'    => $pertemuan,
        ]);
    }

    /**
     * Ubah nomor atau tanggal pertemuan.
     * Kelas tidak boleh dipindah, jadi kelas_id sengaja tidak divalidasi.
     */
    public function update(Request $request, Pertemuan $pertemuan)
    {
        $validated = $request->validate([
            'pertemuan_ke' => [
                'required', 'integer', 'min:1', 'max:100',
                Rule::unique('pertemuans', 'pertemuan_ke')
                    ->where('kelas_id', $pertemuan->kelas_id)
                    ->ignore($pertemuan->id),
            ],
            'tanggal' => 'required|date',
        ]);

        $pertemuan->update($validated);

        return response()->json([
            'message' => 'Pertemuan berhasil diubah',
            'data'    => $pertemuan,
        ]);
    }

    /**
     * Hapus pertemuan.
     */
    public function destroy(Pertemuan $pertemuan)
    {
        $pertemuan->delete();

        return response()->json([
            'message' => 'Pertemuan berhasil dihapus',
        ]);
    }
}
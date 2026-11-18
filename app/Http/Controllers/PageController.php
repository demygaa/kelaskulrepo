<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Pertemuan;

class PageController extends Controller
{
    public function kelas()
    {
        return view('kelas.index');
    }

    public function mahasiswa()
    {
        return view('mahasiswa.index');
    }

    public function peserta(Kelas $kelas)
    {
        return view('kelas.peserta', compact('kelas'));
    }

    public function pertemuan(Kelas $kelas)
    {
        return view('pertemuan.index', compact('kelas'));
    }

    public function kehadiran(Pertemuan $pertemuan)
    {
        $pertemuan->load('kelas');

        return view('kehadiran.input', compact('pertemuan'));
    }

    public function rekap(Kelas $kelas)
    {
        return view('rekap.show', compact('kelas'));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $fillable = ['nim', 'nama'];

    // kelas-kelas yang diikuti mahasiswa ini
    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_mahasiswa')->withTimestamps();
    }
}
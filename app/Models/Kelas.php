<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = ['nama_mata_kuliah', 'nama_kelas', 'dosen'];

    public function mahasiswas()
    {
        return $this->belongsToMany(Mahasiswa::class, 'kelas_mahasiswa')->withTimestamps();
    }
    public function pertemuans()
    {
        return $this->hasMany(Pertemuan::class);
    }
}

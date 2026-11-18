<?php

namespace App\Models;
use App\Models\Pertemuan;
use Illuminate\Database\Eloquent\Model;

class Kehadiran extends Model
{
    protected $fillable = ['pertemuan_id', 'kelas_mahasiswa_id', 'status', 'keterangan'];
    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class);
    }
    public function kelasMahasiswa()
    {
        return $this->belongsTo(KelasMahasiswa::class);
    }
}

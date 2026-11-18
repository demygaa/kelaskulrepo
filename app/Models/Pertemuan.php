<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pertemuan extends Model
{
    protected $fillable = ['kelas_id', 'pertemuan_ke', 'tanggal'];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function kehadirans()
    {
        return $this->hasMany(Kehadiran::class);
    }
}

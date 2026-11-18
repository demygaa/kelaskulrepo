<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KehadiranResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pertemuan_id' => $this->pertemuan_id,
            'kelas_mahasiswa_id' => $this->kelas_mahasiswa_id,
            'status' => $this->status,
            'keterangan' => $this->keterangan,
            'pertemuan' => $this->whenLoaded('pertemuan', fn ($pertemuan) => new PertemuanResource($pertemuan)),
            'kelas_mahasiswa' => $this->whenLoaded('kelasMahasiswa', fn ($kelasMahasiswa) => new KelasMahasiswaResource($kelasMahasiswa)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

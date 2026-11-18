<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KelasResource extends JsonResource
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
            'nama_mata_kuliah' => $this->nama_mata_kuliah,
            'nama_kelas' => $this->nama_kelas,
            'dosen' => $this->dosen,
            'mahasiswas' => $this->whenLoaded('mahasiswas', fn ($mahasiswas) => MahasiswaResource::collection($mahasiswas)),
            'pertemuans' => $this->whenLoaded('pertemuans', fn ($pertemuans) => PertemuanResource::collection($pertemuans)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

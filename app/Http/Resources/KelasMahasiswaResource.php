<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KelasMahasiswaResource extends JsonResource
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
            'kelas_id' => $this->kelas_id,
            'mahasiswa_id' => $this->mahasiswa_id,
            'kelas' => $this->whenLoaded('kelas', fn ($kelas) => new KelasResource($kelas)),
            'mahasiswa' => $this->whenLoaded('mahasiswa', fn ($mahasiswa) => new MahasiswaResource($mahasiswa)),
            'kehadirans' => $this->whenLoaded('kehadirans', fn ($kehadirans) => KehadiranResource::collection($kehadirans)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

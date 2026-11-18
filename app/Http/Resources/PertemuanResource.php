<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PertemuanResource extends JsonResource
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
            'pertemuan_ke' => $this->pertemuan_ke,
            'tanggal' => $this->tanggal?->toDateString(),
            'kelas' => $this->whenLoaded('kelas', fn ($kelas) => new KelasResource($kelas)),
            'kehadirans' => $this->whenLoaded('kehadirans', fn ($kehadirans) => KehadiranResource::collection($kehadirans)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

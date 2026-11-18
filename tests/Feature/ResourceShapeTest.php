<?php

namespace Tests\Feature;

use App\Http\Resources\KehadiranResource;
use App\Http\Resources\KelasMahasiswaResource;
use App\Http\Resources\KelasResource;
use App\Http\Resources\MahasiswaResource;
use App\Http\Resources\PertemuanResource;
use App\Models\Kehadiran;
use App\Models\Kelas;
use App\Models\KelasMahasiswa;
use App\Models\Mahasiswa;
use App\Models\Pertemuan;
use Illuminate\Http\Resources\Json\JsonResource;
use Tests\TestCase;

class ResourceShapeTest extends TestCase
{
    public function test_kelas_resource_maps_fields_and_only_includes_loaded_relations(): void
    {
        $kelas = (new Kelas)->forceFill([
            'id' => 1,
            'nama_mata_kuliah' => 'Algoritma',
            'nama_kelas' => 'A',
            'dosen' => 'Pak Budi',
        ]);

        $data = $this->payload(new KelasResource($kelas));

        $this->assertSame(1, $data['id']);
        $this->assertSame('Algoritma', $data['nama_mata_kuliah']);
        $this->assertArrayNotHasKey('mahasiswas', $data);
        $this->assertArrayNotHasKey('pertemuans', $data);

        $kelas->setRelation('mahasiswas', collect([
            (new Mahasiswa)->forceFill(['id' => 2, 'nim' => '123', 'nama' => 'Ani']),
        ]));

        $data = $this->payload(new KelasResource($kelas));

        $this->assertSame('Ani', $data['mahasiswas'][0]['nama']);
    }

    public function test_mahasiswa_resource_maps_fields_and_kelas_relation(): void
    {
        $mahasiswa = (new Mahasiswa)->forceFill(['id' => 2, 'nim' => '123', 'nama' => 'Ani']);

        $data = $this->payload(new MahasiswaResource($mahasiswa));

        $this->assertSame('123', $data['nim']);
        $this->assertSame('Ani', $data['nama']);
        $this->assertArrayNotHasKey('kelas', $data);

        $mahasiswa->setRelation('kelas', collect([
            (new Kelas)->forceFill(['id' => 1, 'nama_mata_kuliah' => 'Algoritma', 'nama_kelas' => 'A', 'dosen' => 'Pak Budi']),
        ]));

        $data = $this->payload(new MahasiswaResource($mahasiswa));

        $this->assertSame('A', $data['kelas'][0]['nama_kelas']);
    }

    public function test_kelas_mahasiswa_resource_maps_pivot_and_relations(): void
    {
        $kelasMahasiswa = (new KelasMahasiswa)->forceFill([
            'id' => 5,
            'kelas_id' => 1,
            'mahasiswa_id' => 2,
        ]);

        $data = $this->payload(new KelasMahasiswaResource($kelasMahasiswa));

        $this->assertSame(1, $data['kelas_id']);
        $this->assertSame(2, $data['mahasiswa_id']);
        $this->assertArrayNotHasKey('kelas', $data);
        $this->assertArrayNotHasKey('mahasiswa', $data);
        $this->assertArrayNotHasKey('kehadirans', $data);

        $kelasMahasiswa->setRelation('mahasiswa', (new Mahasiswa)->forceFill(['id' => 2, 'nim' => '123', 'nama' => 'Ani']));

        $data = $this->payload(new KelasMahasiswaResource($kelasMahasiswa));

        $this->assertSame('Ani', $data['mahasiswa']['nama']);
    }

    public function test_pertemuan_resource_formats_tanggal_and_kehadirans(): void
    {
        $pertemuan = (new Pertemuan)->forceFill([
            'id' => 7,
            'kelas_id' => 1,
            'pertemuan_ke' => 3,
            'tanggal' => '2026-11-18',
        ]);

        $data = $this->payload(new PertemuanResource($pertemuan));

        $this->assertSame(3, $data['pertemuan_ke']);
        $this->assertSame('2026-11-18', $data['tanggal']);
        $this->assertArrayNotHasKey('kehadirans', $data);

        $pertemuan->setRelation('kehadirans', collect([
            (new Kehadiran)->forceFill([
                'id' => 9,
                'pertemuan_id' => 7,
                'kelas_mahasiswa_id' => 5,
                'status' => 'Hadir',
                'keterangan' => null,
            ]),
        ]));

        $data = $this->payload(new PertemuanResource($pertemuan));

        $this->assertSame('Hadir', $data['kehadirans'][0]['status']);
    }

    public function test_kehadiran_resource_maps_status_and_kelas_mahasiswa(): void
    {
        $kehadiran = (new Kehadiran)->forceFill([
            'id' => 9,
            'pertemuan_id' => 7,
            'kelas_mahasiswa_id' => 5,
            'status' => 'Hadir',
            'keterangan' => null,
        ]);

        $data = $this->payload(new KehadiranResource($kehadiran));

        $this->assertSame('Hadir', $data['status']);
        $this->assertNull($data['keterangan']);
        $this->assertArrayNotHasKey('kelas_mahasiswa', $data);

        $kelasMahasiswa = (new KelasMahasiswa)->forceFill([
            'id' => 5,
            'kelas_id' => 1,
            'mahasiswa_id' => 2,
        ]);
        $kelasMahasiswa->setRelation('mahasiswa', (new Mahasiswa)->forceFill(['id' => 2, 'nim' => '123', 'nama' => 'Ani']));
        $kehadiran->setRelation('kelasMahasiswa', $kelasMahasiswa);

        $data = $this->payload(new KehadiranResource($kehadiran));

        $this->assertSame('Ani', $data['kelas_mahasiswa']['mahasiswa']['nama']);
    }

    private function payload(JsonResource $resource): array
    {
        return $resource->response()->getData(true)['data'];
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class SekolahModel extends Model
{
    protected $table            = 'sekolah';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'nama_sekolah', 'npsn', 'provinsi', 'kab_kota',
        'dana_diterima', 'total_minggu', 'pengawas_id', 'perencana_id'
    ];
    protected $useTimestamps = true;

    public function getByPengawas(int $pengawasId)
    {
        return $this->where('pengawas_id', $pengawasId)->findAll();
    }

    public function getByPerencana(int $perencanaId): array
    {
        return $this->where('perencana_id', $perencanaId)
                    ->orderBy('nama_sekolah', 'ASC')
                    ->findAll();
    }

    public function getWithPersonil(int $sekolahId)
    {
        return $this->select('sekolah.*, personil_sekolah.*')
                    ->join('personil_sekolah', 'personil_sekolah.sekolah_id = sekolah.id', 'left')
                    ->where('sekolah.id', $sekolahId)
                    ->first();
    }
}

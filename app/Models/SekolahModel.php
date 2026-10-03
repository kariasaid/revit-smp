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
        'dana_diterima', 'total_minggu', 'pengawas_id'
    ];
    protected $useTimestamps = true;

    public function getByPengawas(int $pengawasId)
    {
        return $this->where('pengawas_id', $pengawasId)->findAll();
    }

    public function getByPerencana(string $namaPerencana): array
    {
        return $this->select('sekolah.*')
                    ->join('personil_sekolah', 'personil_sekolah.sekolah_id = sekolah.id')
                    ->where('personil_sekolah.perencana', $namaPerencana)
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

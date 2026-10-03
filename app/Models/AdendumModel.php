<?php

namespace App\Models;

use CodeIgniter\Model;

class AdendumModel extends Model
{
    protected $table            = 'adendum';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'sekolah_id', 'nomor_adendum', 'tanggal', 'perihal',
        'uraian', 'nilai_perubahan', 'file_adendum', 'status', 'created_by'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getBySekolah(int $sekolahId)
    {
        return $this->where('sekolah_id', $sekolahId)
                    ->orderBy('tanggal', 'DESC')
                    ->findAll();
    }

    public function getWithSekolah(int $id)
    {
        return $this->select('adendum.*, sekolah.nama_sekolah, sekolah.npsn')
                    ->join('sekolah', 'sekolah.id = adendum.sekolah_id')
                    ->where('adendum.id', $id)
                    ->first();
    }
}

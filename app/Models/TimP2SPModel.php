<?php

namespace App\Models;

use CodeIgniter\Model;

class TimP2SPModel extends Model
{
    protected $table            = 'tim_p2sp';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['sekolah_id', 'posisi', 'nama', 'nip_nik', 'jabatan', 'ttd'];
    protected $useTimestamps   = true;

    public function getBySekolah(int $schoolId): array
    {
        return $this->where('sekolah_id', $schoolId)->findAll();
    }
}
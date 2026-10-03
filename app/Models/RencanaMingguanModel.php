<?php

namespace App\Models;

use CodeIgniter\Model;

class RencanaMingguanModel extends Model
{
    protected $table            = 'rencana_mingguan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'sekolah_id', 'minggu_ke', 'tanggal_mulai', 'tanggal_selesai',
        'target_rencana', 'keterangan', 'dibuat_oleh', 'status_verval',
        'diverifikasi_oleh', 'diverifikasi_pada', 'catatan_verifikasi',
    ];
    protected $useTimestamps = true;

    public function getBySekolah(int $sekolahId): array
    {
        return $this->where('sekolah_id', $sekolahId)
                    ->orderBy('minggu_ke', 'ASC')
                    ->findAll();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanMingguanBantuanModel extends Model
{
    protected $table = 'laporan_mingguan_bantuan';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'sekolah_id', 'bantuan_sekolah_id', 'minggu_ke', 'target_rencana',
        'realisasi_fisik', 'serapan_dana', 'uraian_kegiatan', 'kendala',
        'tindak_lanjut', 'status_verval', 'dibuat_oleh', 'diverifikasi_oleh',
        'diverifikasi_pada', 'catatan_verifikasi', 'file_pdf'
    ];
    protected $useTimestamps = true;

    public function getBySchool(int $schoolId): array
    {
        return $this->select('laporan_mingguan_bantuan.*, bantuan_sekolah.nama_bantuan')
            ->join('bantuan_sekolah', 'bantuan_sekolah.id = laporan_mingguan_bantuan.bantuan_sekolah_id')
            ->where('laporan_mingguan_bantuan.sekolah_id', $schoolId)
            ->orderBy('minggu_ke', 'DESC')
            ->orderBy('bantuan_sekolah.nama_bantuan', 'ASC')
            ->findAll();
    }
}
